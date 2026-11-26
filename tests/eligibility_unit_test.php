<?php
/**
 * Test granular eligibility diagnostics and hierarchical location coverage.
 */
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/ServiceRequest.php';
require_once __DIR__ . '/../app/models/Location.php';
require_once __DIR__ . '/../app/models/Service.php';
require_once __DIR__ . '/../app/models/ProviderProfile.php';

function check(bool $ok, string $label): void {
    if (!$ok) {
        throw new RuntimeException("FAILED: $label");
    }
    echo "PASS: $label\n";
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
Database::setInstance($pdo);

// Create SQLite schema
$pdo->exec("
CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT,
    phone TEXT,
    role TEXT,
    is_active INTEGER DEFAULT 1
);

CREATE TABLE locations (
    id INTEGER PRIMARY KEY,
    parent_id INTEGER NULL,
    name_ar TEXT,
    name_en TEXT,
    level TEXT,
    is_active INTEGER DEFAULT 1
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY,
    name_ar TEXT,
    name_en TEXT,
    is_active INTEGER DEFAULT 1
);

CREATE TABLE provider_profiles (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    profession TEXT,
    specialization TEXT,
    is_verified INTEGER DEFAULT 0,
    is_available INTEGER DEFAULT 1,
    avg_rating REAL DEFAULT 0,
    experience_years INTEGER DEFAULT 0
);

CREATE TABLE provider_services (
    provider_id INTEGER,
    service_id INTEGER
);

CREATE TABLE provider_locations (
    provider_id INTEGER,
    location_id INTEGER
);

CREATE TABLE provider_availability (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    provider_id INTEGER,
    day_of_week INTEGER,
    start_time TEXT,
    end_time TEXT
);

CREATE TABLE service_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER,
    service_id INTEGER,
    provider_id INTEGER NULL,
    location_id INTEGER NULL,
    status TEXT,
    description TEXT,
    preferred_date TEXT NULL,
    preferred_time TEXT NULL
);
");

// Insert seed data
// Locations: 1 (Sana'a), 5 (Shu'aub child of 1), 6 (Sabain child of 1), 2 (Aden)
$pdo->exec("
INSERT INTO locations (id, parent_id, name_ar, level) VALUES
(1, NULL, 'أمانة العاصمة', 'governorate'),
(2, NULL, 'عدن', 'governorate'),
(5, 1, 'شعوب', 'district'),
(6, 1, 'السبعين', 'district'),
(8, 2, 'المعلا', 'district');

INSERT INTO services (id, name_ar) VALUES
(10, 'صيانة ثلاجات'),
(20, 'سباكة منازل');

-- Users & Providers
-- User 100: Active, Provider 100 -> NOT VERIFIED (is_verified = 0)
INSERT INTO users (id, name, phone, role, is_active) VALUES (100, 'صالح علي', '775214896', 'provider', 1);
INSERT INTO provider_profiles (id, user_id, profession, is_verified, is_available) VALUES (100, 100, 'صيانة ثلاجات', 0, 1);
INSERT INTO provider_services (provider_id, service_id) VALUES (100, 10);
INSERT INTO provider_locations (provider_id, location_id) VALUES (100, 1);

-- User 200: Active, Provider 200 -> VERIFIED, offers service 10, covers governorate 1 (أمانة العاصمة), available
INSERT INTO users (id, name, phone, role, is_active) VALUES (200, 'محمد أحمد', '771234567', 'provider', 1);
INSERT INTO provider_profiles (id, user_id, profession, is_verified, is_available) VALUES (200, 200, 'فني تبريد وتكييف', 1, 1);
INSERT INTO provider_services (provider_id, service_id) VALUES (200, 10);
INSERT INTO provider_locations (provider_id, location_id) VALUES (200, 1); -- covers governorate 1

-- User 300: Active, Provider 300 -> VERIFIED, offers service 10, covers district 6 (السبعين), unavailable
INSERT INTO users (id, name, phone, role, is_active) VALUES (300, 'علي قائد', '779876543', 'provider', 1);
INSERT INTO provider_profiles (id, user_id, profession, is_verified, is_available) VALUES (300, 300, 'صيانة ثلاجات', 1, 0); -- is_available = 0
INSERT INTO provider_services (provider_id, service_id) VALUES (300, 10);
INSERT INTO provider_locations (provider_id, location_id) VALUES (300, 6);

-- User 400: Active, Provider 400 -> VERIFIED, offers service 20 (سباكة), covers Aden (2)
INSERT INTO users (id, name, phone, role, is_active) VALUES (400, 'ياسر عدنان', '773333333', 'provider', 1);
INSERT INTO provider_profiles (id, user_id, profession, is_verified, is_available) VALUES (400, 400, 'سباك معتمد', 1, 1);
INSERT INTO provider_services (provider_id, service_id) VALUES (400, 20);
INSERT INTO provider_locations (provider_id, location_id) VALUES (400, 2);
");

$model = new ServiceRequest();
$locModel = new Location();

// TEST 1: Hierarchical location coverage
$coverageIdsGov = $locModel->getCoverageLocationIds(1);
check(in_array(1, $coverageIdsGov) && in_array(5, $coverageIdsGov) && in_array(6, $coverageIdsGov), 'Governorate includes child districts in coverage IDs');

$coverageIdsDist = $locModel->getCoverageLocationIds(6);
check(in_array(6, $coverageIdsDist) && in_array(1, $coverageIdsDist), 'District includes parent governorate in coverage IDs');

// Provider 200 covers Governorate 1 (أمانة العاصمة).
// Should cover District 6 (السبعين) hierarchically!
check($locModel->providerCoversLocation(200, 6) === true, 'Provider covering governorate hierarchically covers its district');
check($locModel->providerCoversLocation(200, 8) === false, 'Provider covering Sanaa does not cover Aden district');

// Provider 300 covers District 6 (السبعين).
// Should cover parent Governorate 1 (أمانة العاصمة) hierarchically!
check($locModel->providerCoversLocation(300, 1) === true, 'Provider covering district covers parent governorate');

// TEST 2: Check unverified provider (Case of Salah from screenshot)
$reqForSalah = [
    'service_id' => 10,
    'location_id' => 1, // Same area!
];
$resUnverified = $model->checkProviderEligibility(100, $reqForSalah);
check($resUnverified['eligible'] === false, 'Unverified provider is not eligible');
check($resUnverified['reason_code'] === 'not_verified', 'Reason code is precisely not_verified');
check(str_contains($resUnverified['message'], 'بانتظار الاعتماد والتوثيق الرسمي'), 'Message clearly states waiting for admin verification');
echo "Result message for unverified: " . $resUnverified['message'] . "\n";

// TEST 3: Check verified provider with service mismatch
$reqWrongService = [
    'service_id' => 20, // plumbing, but provider 200 is refrigeration
    'location_id' => 6,
];
$resSvcMismatch = $model->checkProviderEligibility(200, $reqWrongService);
check($resSvcMismatch['eligible'] === false, 'Provider with wrong service is not eligible');
check($resSvcMismatch['reason_code'] === 'service_mismatch', 'Reason code is precisely service_mismatch');
check(str_contains($resSvcMismatch['message'], 'غير مسجل لتقديم خدمة'), 'Message clearly explains service mismatch');
echo "Result message for service mismatch: " . $resSvcMismatch['message'] . "\n";

// TEST 4: Check verified provider with location mismatch (Provider 200 in Sana'a, Request in Aden 8)
$reqWrongLoc = [
    'service_id' => 10,
    'location_id' => 8, // Aden
];
$resLocMismatch = $model->checkProviderEligibility(200, $reqWrongLoc);
check($resLocMismatch['eligible'] === false, 'Provider in different governorate is not eligible');
check($resLocMismatch['reason_code'] === 'location_mismatch', 'Reason code is precisely location_mismatch');
check(str_contains($resLocMismatch['message'], 'لا يغطي منطقة'), 'Message clearly explains location mismatch');
echo "Result message for location mismatch: " . $resLocMismatch['message'] . "\n";

// TEST 5: Check unavailable provider (Provider 300 has is_available = 0)
$reqUnavailable = [
    'service_id' => 10,
    'location_id' => 6,
];
$resUnavailable = $model->checkProviderEligibility(300, $reqUnavailable);
check($resUnavailable['eligible'] === false, 'Provider with is_available=0 is not eligible');
check($resUnavailable['reason_code'] === 'unavailable', 'Reason code is precisely unavailable');
check(str_contains($resUnavailable['message'], 'غير متاح'), 'Message clearly explains provider is unavailable');
echo "Result message for unavailable: " . $resUnavailable['message'] . "\n";

// TEST 6: Check perfectly eligible provider with hierarchical location (Provider 200 covers Gov 1, Request is District 6)
$reqValid = [
    'service_id' => 10,
    'location_id' => 6,
];
$resValid = $model->checkProviderEligibility(200, $reqValid);
check($resValid['eligible'] === true, 'Provider covering governorate is eligible for district request');
check($resValid['reason_code'] === 'eligible', 'Reason code is eligible');
echo "Result message for eligible: " . $resValid['message'] . "\n";

// TEST 7: Boolean wrapper compatibility
check($model->providerEligibleForRequest(100, $reqForSalah) === false, 'Legacy boolean wrapper returns false for unverified');
check($model->providerEligibleForRequest(200, $reqValid) === true, 'Legacy boolean wrapper returns true for eligible');

echo "\n>>> ALL 7 GRANULAR ELIGIBILITY TESTS PASSED! <<<\n";
