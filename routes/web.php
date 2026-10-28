<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

$router = new Router();

// ---------------- Public ----------------
$router->get('/', 'HomeController@index');
$router->get('/services', 'HomeController@services');
$router->get('/providers', 'HomeController@providers');
$router->get('/providers/{id}', 'HomeController@providerProfile');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// ---------------- Customer ----------------
$router->get('/customer/dashboard', 'CustomerController@dashboard');
$router->get('/customer/requests', 'CustomerController@requests');
$router->get('/customer/finance', 'CustomerController@finance');
$router->get('/customer/requests/create', 'CustomerController@createRequestForm');
$router->post('/customer/requests/analyze', 'CustomerController@analyzeProblem');
$router->get('/customer/ai/assistant', 'CustomerController@aiAssistant');
$router->post('/customer/ai/chat', 'CustomerController@aiChat');
$router->post('/customer/requests', 'CustomerController@storeRequest');
$router->get('/customer/requests/{id}/recommendations', 'CustomerController@recommendations');
$router->post('/customer/requests/{id}/assign', 'CustomerController@assignProvider');
$router->get('/customer/requests/{id}/pre-arrival', 'CustomerController@preArrivalAssistant');
$router->post('/customer/requests/{id}/pre-arrival', 'CustomerController@preArrivalAssistant');
$router->get('/customer/requests/{id}', 'CustomerController@requestDetails');
$router->post('/customer/requests/{id}/cancel', 'CustomerController@cancelRequest');
$router->post('/customer/requests/{id}/review', 'CustomerController@submitReview');
$router->get('/customer/requests/{id}/conversation', 'CustomerController@conversation');
$router->post('/customer/requests/{id}/conversation', 'CustomerController@conversation');
$router->post('/customer/invoices/{id}/pay', 'CustomerController@payInvoice');

$router->get('/customer/complaints', 'CustomerController@complaints');
$router->get('/customer/complaints/create', 'CustomerController@createComplaintForm');
$router->post('/customer/complaints', 'CustomerController@storeComplaint');
$router->get('/customer/complaints/{id}', 'CustomerController@complaintDetails');
$router->post('/customer/complaints/{id}/reply', 'CustomerController@replyComplaint');

$router->get('/customer/notifications', 'CustomerController@notifications');
$router->get('/customer/profile', 'CustomerController@profile');
$router->post('/customer/profile', 'CustomerController@updateProfile');

// ---------------- Provider ----------------
$router->get('/provider/dashboard', 'ProviderController@dashboard');
$router->get('/provider/requests', 'ProviderController@requests');
$router->get('/provider/finance', 'ProviderController@finance');
$router->get('/provider/finance/export', 'ProviderController@exportFinanceCsv');
$router->get('/provider/requests/{id}/assistant', 'ProviderController@fieldAssistant');
$router->post('/provider/requests/{id}/assistant', 'ProviderController@fieldAssistant');
$router->get('/provider/requests/{id}', 'ProviderController@requestDetails');
$router->post('/provider/requests/{id}/accept', 'ProviderController@acceptRequest');
$router->post('/provider/requests/{id}/reject', 'ProviderController@rejectRequest');
$router->post('/provider/requests/{id}/start', 'ProviderController@startRequest');
$router->post('/provider/requests/{id}/complete', 'ProviderController@completeRequest');
$router->get('/provider/requests/{id}/conversation', 'ProviderController@conversation');
$router->post('/provider/requests/{id}/conversation', 'ProviderController@conversation');

$router->get('/provider/reviews', 'ProviderController@reviews');
$router->post('/provider/reviews/{id}/reply', 'ProviderController@replyReview');

$router->get('/provider/profile', 'ProviderController@profile');
$router->post('/provider/profile', 'ProviderController@updateProfile');
$router->post('/provider/profile/services', 'ProviderController@addService');
$router->post('/provider/profile/locations', 'ProviderController@addLocation');
$router->post('/provider/profile/availability', 'ProviderController@addAvailability');
$router->post('/provider/profile/availability/{id}/delete', 'ProviderController@deleteAvailability');
$router->post('/provider/profile/portfolio', 'ProviderController@uploadPortfolio');
$router->post('/provider/profile/photo', 'ProviderController@uploadPhoto');

$router->get('/provider/notifications', 'ProviderController@notifications');

// ---------------- Admin ----------------
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/ai', 'AdminAIController@index');
$router->post('/admin/ai/save', 'AdminAIController@save');
$router->post('/admin/ai/test', 'AdminAIController@test');
$router->post('/admin/ai/test-all', 'AdminAIController@testAll');
$router->get('/admin/ai/logs', 'AdminAIController@logs');
$router->get('/admin/ai/decision-support', 'AdminAIController@decisionSupport');
$router->get('/admin/ai/provider/{id}', 'AdminAIController@providerAnalysis');
$router->post('/admin/ai/decision-support', 'AdminAIController@decisionSupport');

$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/{id}/edit', 'AdminController@editUser');
$router->post('/admin/users/{id}/update', 'AdminController@updateUser');
$router->post('/admin/users/{id}/toggle', 'AdminController@toggleUserActive');
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser');

$router->get('/admin/employees', 'AdminController@employees');
$router->post('/admin/employees', 'AdminController@createEmployee');
$router->get('/admin/employees/{id}/edit', 'AdminController@editEmployee');
$router->post('/admin/employees/{id}/update', 'AdminController@updateEmployee');
$router->post('/admin/employees/{id}/toggle', 'AdminController@toggleEmployee');

$router->get('/admin/providers', 'AdminController@providers');
$router->post('/admin/providers/{id}/verify', 'AdminController@verifyProvider');
$router->post('/admin/providers/{id}/unverify', 'AdminController@unverifyProvider');
$router->get('/admin/providers/{id}/evaluation', 'AdminController@providerEvaluation');
$router->get('/admin/reviews', 'AdminController@reviews');
$router->post('/admin/reviews/{id}/delete', 'AdminController@deleteReview');
$router->post('/admin/providers/{id}/evaluation', 'AdminController@saveProviderEvaluation');

$router->get('/admin/categories', 'AdminController@categories');
$router->post('/admin/categories', 'AdminController@storeCategory');
$router->post('/admin/categories/{id}/delete', 'AdminController@deleteCategory');
$router->post('/admin/services', 'AdminController@storeService');
$router->post('/admin/services/{id}/delete', 'AdminController@deleteService');

$router->get('/admin/locations', 'AdminController@locations');
$router->post('/admin/locations', 'AdminController@storeLocation');
$router->post('/admin/locations/{id}/delete', 'AdminController@deleteLocation');

$router->get('/admin/requests', 'AdminController@requests');
$router->get('/admin/requests/{id}', 'AdminController@requestDetails');

$router->get('/admin/complaints', 'AdminController@complaints');
$router->get('/admin/complaints/{id}', 'AdminController@complaintDetails');
$router->post('/admin/complaints/{id}/reply', 'AdminController@replyComplaint');
$router->post('/admin/complaints/{id}/status', 'AdminController@updateComplaintStatus');

$router->get('/admin/finance', 'AdminController@finance');
$router->get('/admin/finance/export', 'AdminController@exportFinanceCsv');
$router->post('/admin/finance/invoice', 'AdminController@createInvoice');
$router->post('/admin/finance/payment', 'AdminController@recordPayment');
$router->post('/admin/finance/refund', 'AdminController@refundInvoice');
$router->post('/admin/finance/payout', 'AdminController@recordPayout');
$router->post('/admin/finance/settings', 'AdminController@updateFinanceSettings');
$router->get('/admin/reports', 'AdminController@reports');
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@updateSettings');
$router->post('/admin/settings/reset', 'AdminController@resetSettings');
$router->get('/admin/notifications', 'AdminController@notifications');
$router->post('/admin/notifications/send', 'AdminController@sendNotification');
$router->get('/admin/conversations', 'AdminController@conversations');
$router->get('/admin/conversations/{id}', 'AdminController@conversationView');


// ---------------- Platform Employee ----------------
$router->get('/employee/dashboard', 'EmployeeController@dashboard');
$router->get('/employee/requests', 'EmployeeController@requests');
$router->get('/employee/requests/{id}', 'EmployeeController@requestDetails');
$router->get('/employee/providers', 'EmployeeController@providers');
$router->get('/employee/reviews', 'EmployeeController@reviews');
$router->get('/employee/complaints', 'EmployeeController@complaints');
$router->get('/employee/complaints/{id}', 'EmployeeController@complaintDetails');
$router->post('/employee/complaints/{id}/reply', 'EmployeeController@replyComplaint');
$router->post('/employee/complaints/{id}/status', 'EmployeeController@updateComplaintStatus');
$router->get('/employee/finance', 'EmployeeController@finance');
$router->get('/employee/notifications', 'EmployeeController@notifications');
$router->get('/employee/conversations', 'EmployeeController@conversations');
$router->get('/employee/conversations/{id}', 'EmployeeController@conversation');

// ---------------- REST API (Mobile App & External Clients) ----------------
$router->get('/api/ping', 'ApiController@ping');
$router->post('/api/login', 'ApiController@login');
$router->post('/api/register', 'ApiController@register');
$router->get('/api/me', 'ApiController@me');

// Public catalog lookups
$router->get('/api/categories', 'ApiController@categories');
$router->get('/api/services', 'ApiController@services');
$router->get('/api/locations', 'ApiController@locations');
$router->get('/api/providers', 'ApiController@providers');

// Customer endpoints
$router->get('/api/customer/dashboard', 'ApiController@customerDashboard');
$router->get('/api/customer/requests', 'ApiController@customerRequests');
$router->get('/api/customer/requests/{id}', 'ApiController@customerRequestDetails');
$router->post('/api/customer/requests', 'ApiController@createCustomerRequest');
$router->post('/api/customer/requests/{id}/cancel', 'ApiController@cancelCustomerRequest');
$router->post('/api/customer/invoices/{id}/pay', 'ApiController@payCustomerInvoice');
$router->post('/api/customer/requests/{id}/review', 'ApiController@submitCustomerReview');

// Provider endpoints
$router->get('/api/provider/dashboard', 'ApiController@providerDashboard');
$router->get('/api/provider/requests', 'ApiController@providerRequests');
$router->get('/api/provider/requests/{id}', 'ApiController@providerRequestDetails');
$router->post('/api/provider/requests/{id}/accept', 'ApiController@acceptProviderRequest');
$router->post('/api/provider/requests/{id}/reject', 'ApiController@rejectProviderRequest');
$router->post('/api/provider/requests/{id}/start', 'ApiController@startProviderRequest');
$router->post('/api/provider/requests/{id}/complete', 'ApiController@completeProviderRequest');
$router->get('/api/provider/profile', 'ApiController@providerProfile');
$router->post('/api/provider/availability', 'ApiController@toggleAvailability');
$router->post('/api/provider/locations', 'ApiController@updateCoverageLocations');
$router->get('/api/provider/finance', 'ApiController@providerFinance');

// Employee API endpoints
$router->get('/api/employee/dashboard', 'ApiController@employeeDashboard');
$router->get('/api/employee/requests', 'ApiController@employeeRequests');
$router->get('/api/employee/complaints', 'ApiController@employeeComplaints');

// Admin API endpoints
$router->get('/api/admin/dashboard', 'ApiController@adminDashboard');
$router->get('/api/admin/users', 'ApiController@adminUsers');

// Chunked Upload API (Supports large multipart files up to 300MB)
$router->post('/api/upload/chunk', 'ApiController@uploadChunk');

return $router;
