<?php
// Included by request_workflow_test.php against its disposable database only.
require_once __DIR__ . '/../app/controllers/ApiController.php';
function env($name, $default = null) { return $name === 'APP_KEY' ? 'isolated-workflow-test-secret' : $default; }
class WorkflowApiResult extends RuntimeException {
    public function __construct(public int $status, public mixed $data = null) { parent::__construct('API response'); }
}
class WorkflowApiProbe extends ApiController {
    public array $payload = [];
    protected function getPayload(): array { return $this->payload; }
    protected function json(mixed $data, int $status = 200, string $message = ''): void { throw new WorkflowApiResult($status, $data); }
    protected function error(string $message, int $status = 400, mixed $errors = null): void { throw new WorkflowApiResult($status, $message); }
}
function workflowApiCall(PDO $db, int $userId, string $method, int $id, array $payload = []): WorkflowApiResult {
    unset($_SERVER['HTTP_AUTHORIZATION']);
    if ($userId > 0) {
        $user = $db->query('SELECT * FROM users WHERE id = ' . $userId)->fetch();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . ApiController::createToken($user + ['email' => 'test@example.invalid']);
    }
    $api = new WorkflowApiProbe();
    $api->payload = $payload;
    try { $api->$method($id); }
    catch (WorkflowApiResult $result) { return $result; }
    throw new RuntimeException('Endpoint did not return an API response');
}
function runWorkflowApiContract(PDO $db, ServiceRequest $model): void {
    $id = $model->createRequest(['customer_id' => 1, 'service_id' => 1, 'location_id' => 1, 'description' => 'API end-to-end request']);
    $model->changeStatus($id, 'accepted', 2, 20);
    check(workflowApiCall($db, 0, 'quoteProviderRequest', $id)->status === 401, 'Quote API requires authentication');
    check(workflowApiCall($db, 1, 'quoteProviderRequest', $id)->status === 403, 'Quote API rejects the customer role');
    check(workflowApiCall($db, 2, 'quoteProviderRequest', $id, ['total_price' => 200, 'details' => 'Replacement parts and labor included'])->status === 200, 'Mobile quote payload is accepted');
    $quoteId = (int)$model->currentQuote($id)['id'];
    check(workflowApiCall($db, 1, 'decideCustomerQuote', $id, ['quote_id' => $quoteId, 'decision' => 'invalid'])->status === 422, 'Decision API validates the action');
    check(workflowApiCall($db, 1, 'decideCustomerQuote', $id, ['quote_id' => $quoteId, 'decision' => 'approve'])->status === 200, 'Mobile approval payload is accepted');
    check(workflowApiCall($db, 2, 'startProviderRequest', $id)->status === 200, 'Mobile start obeys the approved quote');
    check(workflowApiCall($db, 2, 'completeProviderRequest', $id, ['completion_notes' => 'Replaced parts and verified operation'])->status === 200, 'Mobile completion requests handover without a price field');
    check((int)$db->query('SELECT COUNT(*) FROM invoices WHERE request_id = ' . $id)->fetchColumn() === 0, 'Completion endpoint does not issue a premature invoice');
    check(workflowApiCall($db, 1, 'reworkCustomerRequest', $id, ['notes' => 'Please check the vibration again'])->status === 200, 'Mobile rework payload returns the request to execution');
    check(workflowApiCall($db, 2, 'completeProviderRequest', $id, ['completion_notes' => 'Adjusted vibration and checked again'])->status === 200, 'Provider can request receipt again through the API');
    check(workflowApiCall($db, 2, 'confirmCustomerCompletion', $id)->status === 403, 'Provider cannot impersonate customer receipt');
    check(workflowApiCall($db, 1, 'confirmCustomerCompletion', $id)->status === 200, 'Mobile customer receipt issues the agreed invoice');
    check((float)$db->query('SELECT total_amount FROM invoices WHERE request_id = ' . $id)->fetchColumn() === 200.0, 'API invoice total equals the approved price');
    check((int)$db->query('SELECT COUNT(*) FROM notifications WHERE user_id = 1')->fetchColumn() >= 3, 'Workflow events create customer inbox notifications');
    check((int)$db->query('SELECT COUNT(*) FROM notifications WHERE user_id = 2')->fetchColumn() >= 3, 'Workflow events notify the assigned provider');
    $cancelId = $model->createRequest(['customer_id' => 1, 'service_id' => 1, 'location_id' => 1, 'description' => 'Request cancelled before work']);
    $model->changeStatus($cancelId, 'accepted', 2, 20);
    check(workflowApiCall($db, 1, 'cancelCustomerRequest', $cancelId)->status === 200, 'Customer can cancel before execution');
    check((int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id = 2 AND title = 'ألغى العميل الطلب'")->fetchColumn() === 1, 'Cancellation notifies the assigned provider once');
    unset($_SERVER['HTTP_AUTHORIZATION']);
}
