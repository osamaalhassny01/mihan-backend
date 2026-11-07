<?php
/** Run only against a disposable MariaDB instance; never reads application .env. */
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/ServiceRequest.php';
require_once __DIR__ . '/../app/models/User.php';
if (getenv('MIHAN_DISPOSABLE_DB') !== '1') {
    fwrite(STDERR, "Set MIHAN_DISPOSABLE_DB=1 with a disposable database server.\n");
    exit(1);
}
function setting($name, $default = null) { return $default; }
define('PLATFORM_COMMISSION_RATE', 0.1);
function generateRequestNumber(int $id): string { return 'REQ-TEST-' . $id; }
function check(bool $ok, string $label): void {
    if (!$ok) throw new RuntimeException($label);
    echo "PASS: $label\n";
}
$db = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
$db->exec('CREATE DATABASE mihan_workflow_test');
$db->exec('USE mihan_workflow_test');
Database::setInstance($db);
try {
    $db->exec('CREATE TABLE users (id INT PRIMARY KEY, role VARCHAR(20), is_active TINYINT, fcm_token VARCHAR(255) NULL) ENGINE=InnoDB');
    $db->exec('CREATE TABLE provider_profiles (id INT PRIMARY KEY, user_id INT, is_verified TINYINT, is_available TINYINT, completed_jobs INT DEFAULT 0) ENGINE=InnoDB');
    $db->exec('CREATE TABLE provider_services (provider_id INT, service_id INT) ENGINE=InnoDB');
    $db->exec('CREATE TABLE provider_locations (provider_id INT, location_id INT) ENGINE=InnoDB');
    $db->exec('CREATE TABLE provider_availability (provider_id INT, day_of_week INT, start_time TIME, end_time TIME) ENGINE=InnoDB');
    $db->exec("CREATE TABLE service_requests (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT, service_id INT, provider_id INT NULL, location_id INT NULL, status VARCHAR(20), preferred_date DATE NULL, preferred_time TIME NULL) ENGINE=InnoDB");
    $db->exec('CREATE TABLE request_status_history (request_id INT, changed_by INT, old_status VARCHAR(20) NULL, new_status VARCHAR(20)) ENGINE=InnoDB');
    $db->exec('CREATE TABLE provider_rejections (request_id INT, provider_id INT, UNIQUE KEY (request_id, provider_id)) ENGINE=InnoDB');
    $db->exec("INSERT INTO users VALUES (1,'customer',1,NULL),(2,'provider',1,NULL),(3,'provider',1,NULL),(4,'provider',1,NULL)");
    $db->exec('INSERT INTO provider_profiles (id,user_id,is_verified,is_available) VALUES (20,2,1,1),(30,3,1,1),(40,4,0,1)');
    $db->exec('INSERT INTO provider_services VALUES (20,1),(30,1),(40,1)');
    $db->exec('INSERT INTO provider_locations VALUES (20,1),(30,1),(40,1)');
    $db->exec("INSERT INTO service_requests VALUES (1,1,1,NULL,1,'pending',NULL,NULL),(2,1,1,20,1,'pending',NULL,NULL),(3,1,1,20,1,'accepted',NULL,NULL),(4,1,1,NULL,1,'pending',NULL,NULL)");
    $db->exec("ALTER TABLE service_requests ADD COLUMN final_price DECIMAL(10,2) NULL, ADD COLUMN request_number VARCHAR(30), ADD COLUMN description TEXT, ADD COLUMN budget DECIMAL(10,2), ADD COLUMN notes TEXT, ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    $db->exec(file_get_contents(__DIR__ . '/../database/migrations/018_service_agreement_workflow.sql'));
    $db->exec("CREATE TABLE system_settings (`key` VARCHAR(100), value VARCHAR(100)) ENGINE=InnoDB");
    $db->exec("CREATE TABLE invoices (id INT AUTO_INCREMENT PRIMARY KEY, invoice_number VARCHAR(30) UNIQUE, request_id INT UNIQUE, service_price DECIMAL(10,2), commission_rate DECIMAL(5,4), commission_amount DECIMAL(10,2), provider_net DECIMAL(10,2), total_amount DECIMAL(10,2), payment_status VARCHAR(20)) ENGINE=InnoDB");
    $db->exec('CREATE TABLE platform_commissions (invoice_id INT, amount DECIMAL(10,2)) ENGINE=InnoDB');
    $model = new ServiceRequest();
    $snapshot = $model->find(1);
    check($model->providerCanAccess(20, $snapshot) && $model->providerCanAccess(30, $snapshot), 'Two matching providers initially see the pending request');
    check(!$model->providerEligibleForRequest(40, $snapshot), 'Unverified providers cannot accept requests');
    check($model->changeStatus(1, 'accepted', 2, 20), 'First provider claims and accepts atomically');
    check(!$model->changeStatus(1, 'accepted', 3, 30), 'A second acceptance cannot steal the assigned request');
    check((int)$model->find(1)['provider_id'] === 20, 'The winning provider remains assigned');
    check(!$model->rejectAndRequeue(1, 3, 30), 'A stale refusal cannot undo acceptance');
    check(!$model->rejectAndRequeue(3, 2, 20), 'Accepted requests cannot be rejected and reset to pending');
    check($model->rejectAndRequeue(2, 2, 20), 'Assigned provider can decline a pending request');
    $request = $model->find(2);
    check($request['status'] === 'pending' && $request['provider_id'] === null, 'Declined request returns to the pending queue');
    check(!$model->providerCanAccess(20, $request), 'Declining provider cannot reopen it via a remembered URL');
    check(!$model->changeStatus(2, 'accepted', 2, 20), 'Declining provider cannot accept through the API afterwards');
    check($model->providerCanAccess(30, $request), 'Other eligible providers can still access the request');
    check($model->changeStatus(2, 'accepted', 3, 30), 'Another provider can accept the requeued request');
    check($model->rejectAndRequeue(4, 2, 20), 'Unassigned request can be dismissed individually');
    check($model->find(4)['status'] === 'pending' && $model->providerCanAccess(30, $model->find(4)), 'Individual dismissal does not reject the request for everyone');
    check(!$model->changeStatus(1, 'completed', 2, 20), 'Status changes cannot bypass invoice completion');
    check(!$model->completeWithInvoice(3, 20, 100, 2)['ok'], 'Accepted requests must start execution before invoicing');
    check(!$model->completeWithInvoice(3, 20, NAN, 2)['ok'], 'Non-finite prices cannot produce invoices');
    check(!$model->completeWithInvoice(3, 20, 0, 2)['ok'], 'Zero prices are rejected consistently with the app');
    // New requests use the consent-based workflow. Exercise the actual migration and model.
    $id = $model->createRequest(['customer_id' => 1, 'service_id' => 1, 'location_id' => 1,
        'description' => 'Repair the kitchen light', 'budget' => 50, 'notes' => 'Detailed address']);
    check((int)$model->find($id)['workflow_version'] === 2, 'New requests explicitly use agreement workflow version 2');
    check($model->changeStatus($id, 'accepted', 2, 20), 'Provider can reserve a new request');
    check(!$model->changeStatus($id, 'in_progress', 2, 20), 'New work cannot start without an approved quote');
    check(!$model->submitQuote($id, 30, 100, 'Repair with labor and parts')['ok'], 'Another provider cannot quote a reserved request');
    check(!$model->submitQuote($id, 20, NAN, 'Repair with labor and parts')['ok'], 'Non-finite quote prices are rejected');
    check(!$model->submitQuote($id, 20, 100, 'Repair with labor and parts', '2020-01-01 10:00:00')['ok'], 'Past visit times cannot be quoted');
    $quote1 = $model->submitQuote($id, 20, 100, 'Labor and replacement light included');
    check($quote1['ok'], 'Provider submits a scoped quote');
    $quote2 = $model->submitQuote($id, 20, 125, 'Labor and two replacement lights included');
    check($quote2['ok'] && (int)$quote2['quote_id'] !== (int)$quote1['quote_id'], 'A revision is stored as a separate quote');
    check($db->query('SELECT status FROM request_quotes WHERE id = ' . (int)$quote1['quote_id'])->fetchColumn() === 'superseded', 'Quote history is retained when revising');
    check(!$model->decideQuote($id, 1, (int)$quote1['quote_id'], true)['ok'], 'A stale quote cannot be approved');
    check(!$model->decideQuote($id, 3, (int)$quote2['quote_id'], true)['ok'], 'Only the owning customer can approve');
    check($model->decideQuote($id, 1, (int)$quote2['quote_id'], false)['ok'], 'Customer can decline and request a new offer');
    check(!$model->changeStatus($id, 'in_progress', 2, 20), 'Declined quotes never authorize execution');
    $quote = $model->submitQuote($id, 20, 120, 'Labor and two lights included, no additional charges');
    check($model->decideQuote($id, 1, (int)$quote['quote_id'], true)['ok'], 'Customer approves the exact current quote');
    check(!$model->submitQuote($id, 20, 150, 'Changed price after the customer agreed')['ok'], 'Approved price and scope cannot be changed');
    check(!$model->decideQuote($id, 1, (int)$quote['quote_id'], true)['ok'], 'Repeating approval does not produce another decision');
    check($model->changeStatus($id, 'in_progress', 2, 20), 'Execution starts after customer consent');
    check(!$model->changeStatus($id, 'cancelled', 1), 'Customer cannot cancel unilaterally after execution starts');
    check(!$model->completeWithInvoice($id, 20, 120, 2)['ok'], 'Provider cannot issue a version 2 invoice directly');
    check(!$model->requestCompletion($id, 30, 3, 'Work is complete')['ok'], 'Another provider cannot request handover');
    check($model->requestCompletion($id, 20, 2, 'Replaced the lights and tested them')['ok'], 'Provider requests customer handover with a summary');
    check($model->find($id)['status'] === 'awaiting_confirmation', 'Finished work waits for customer confirmation');
    check((int)$db->query('SELECT COUNT(*) FROM invoices WHERE request_id = ' . $id)->fetchColumn() === 0, 'No invoice is created before customer receipt');
    check(!$model->confirmCompletion($id, 3)['ok'], 'Another customer cannot accept delivery');
    check($model->requestRework($id, 1, 'The second light still flickers')['ok'], 'Customer can request corrections before receipt');
    check($model->find($id)['status'] === 'in_progress' && (float)$model->currentQuote($id)['total_price'] === 120.0, 'Rework keeps the agreed price and returns to execution');
    check($model->requestCompletion($id, 20, 2, 'Fixed the connection and retested both lights')['ok'], 'Provider can resubmit after corrections');
    check(!$model->completeWithInvoice($id, 20, 999, 1)['ok'], 'Even the customer cannot substitute a different invoice price');
    check($model->confirmCompletion($id, 1)['ok'], 'Customer receipt issues the invoice atomically');
    check((float)$db->query('SELECT total_amount FROM invoices WHERE request_id = ' . $id)->fetchColumn() === 120.0, 'Invoice uses the approved quote, not the budget');
    check(!$model->confirmCompletion($id, 1)['ok'], 'Repeated handover confirmation cannot issue duplicate invoices');
    check((int)$db->query('SELECT COUNT(*) FROM invoices WHERE request_id = ' . $id)->fetchColumn() === 1, 'Exactly one invoice exists');
    check((int)$db->query('SELECT completed_jobs FROM provider_profiles WHERE id = 20')->fetchColumn() === 1, 'Provider statistics increment exactly once');
    check($model->changeStatus(3, 'in_progress', 2, 20), 'Legacy accepted requests can continue without a new quote');
    check($model->completeWithInvoice(3, 20, 80, 2)['ok'], 'Legacy jobs still complete using their existing flow');
    $db->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) NULL, ADD COLUMN name VARCHAR(100) DEFAULT 'Test User'");
    $db->exec('CREATE TABLE notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, title VARCHAR(200), message TEXT, link VARCHAR(255), is_read TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
    $db->exec('CREATE TABLE activity_logs (user_id INT, action VARCHAR(100), description TEXT, ip_address VARCHAR(50))');
    $db->exec('ALTER TABLE users ADD COLUMN phone VARCHAR(50) NULL');
    $db->exec('ALTER TABLE provider_profiles ADD COLUMN profession VARCHAR(100), ADD COLUMN experience_years INT, ADD COLUMN avg_rating DECIMAL(3,2)');
    $db->exec('CREATE TABLE services (id INT PRIMARY KEY, name_ar VARCHAR(100), name_en VARCHAR(100))');
    $db->exec("INSERT INTO services VALUES (1,'خدمة تجريبية','Test Service')");
    $db->exec('CREATE TABLE locations (id INT PRIMARY KEY, name_ar VARCHAR(100), name_en VARCHAR(100))');
    $db->exec("INSERT INTO locations VALUES (1,'عدن','Aden')");
    $details = $model->fullDetails($id);
    check((float)$details['quoted_price'] === 120.0 && $details['quote_status'] === 'approved', 'Request detail payload contains the current agreement');
    check(!empty($details['quote_created_at']) && !empty($details['quote_decided_at']), 'Agreement timestamps support the mobile timeline');
    check(count($model->forProvider(20)) >= 1 && count($model->forCustomer(1)) >= 1, 'Request lists remain queryable with quote data');
    require __DIR__ . '/workflow_api_contract.php';
    runWorkflowApiContract($db, $model);
    $users = new User();
    $users->registerFcmToken(1, 'shared-device-token');
    $users->registerFcmToken(2, 'shared-device-token');
    check($users->find(1)['fcm_token'] === null && $users->find(2)['fcm_token'] === 'shared-device-token', 'Switching accounts transfers device ownership');
    $users->unregisterFcmToken(1, 'shared-device-token');
    check($users->find(2)['fcm_token'] === 'shared-device-token', 'Logout cannot remove another account token');
    $users->unregisterFcmToken(2, 'wrong-token');
    check($users->find(2)['fcm_token'] === 'shared-device-token', 'Unregistering a different device preserves the current token');
    $users->unregisterFcmToken(2, 'shared-device-token');
    check($users->find(2)['fcm_token'] === null, 'Logout removes the matching device token');
    echo "All workflow regression tests passed.\n";
} finally {
    $db->exec('DROP DATABASE mihan_workflow_test');
}
