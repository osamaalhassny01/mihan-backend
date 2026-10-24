<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/ProviderEvaluation.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/SystemSetting.php';
require_once __DIR__ . '/../models/Payout.php';
require_once __DIR__ . '/../services/AnalyticsService.php';
require_once __DIR__ . '/../models/Conversation.php';

class AdminController extends Controller
{
    public function __construct() { Auth::requireRole('admin'); }

    public function dashboard()
    {
        $analytics = new AnalyticsService();
        $overview = $analytics->overview();
        $requestsByMonth = $analytics->requestsByMonth();
        $servicesByCategory = $analytics->servicesByCategory();
        $topProviders = $analytics->topProviders();
        $recentActivity = (new ActivityLog())->recent(10);

        $this->view('admin/dashboard', compact('overview', 'requestsByMonth', 'servicesByCategory', 'topProviders', 'recentActivity')
            + ['title' => __('dashboard')], 'dashboard');
    }

    // ---------------- Users ----------------
    public function users()
    {
        Auth::requirePermission('manage_users');
        $role = $this->input('role');
        $userModel = new User();
        $users = $role ? $userModel->where(['role' => $role], 'created_at DESC') : $userModel->all('created_at DESC');
        $this->view('admin/users', ['title' => 'إدارة المستخدمين', 'users' => $users, 'role' => $role], 'dashboard');
    }

    public function editUser($id)
    {
        Auth::requirePermission('manage_users');
        $id = (int)$id;
        $userModel = new User();
        $user = $userModel->findWithPermissions($id);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin/user_edit', ['title' => 'تعديل المستخدم: ' . $user['name'], 'user' => $user], 'dashboard');
    }

    public function updateUser($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_users');
        $id = (int)$id;

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            $this->redirect('/admin/users');
            return;
        }

        $name = trim((string)$this->input('name', ''));
        $email = trim(strtolower((string)$this->input('email', '')));
        $phone = trim((string)$this->input('phone', ''));
        $role = trim((string)$this->input('role', 'customer'));
        $password = (string)$this->input('password', '');
        $isActive = $this->input('is_active') ? 1 : 0;
        $isVerified = $this->input('is_verified_email') ? 1 : 0;
        $permissions = (array)$this->input('permissions', []);

        if (mb_strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'يرجى إدخال اسم صحيح وبريد إلكتروني صالح.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        if (!in_array($role, ['customer', 'provider', 'employee', 'admin'], true)) {
            Session::flash('error', 'دور المستخدم المحدد غير صالح.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        if ($userModel->emailExistsExcept($email, $id)) {
            Session::flash('error', 'البريد الإلكتروني مستخدم بالفعل لحساب آخر.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        // Safety Guard 1: Admin cannot deactivate self
        if ($id === (int)Auth::id() && $isActive === 0) {
            Session::flash('error', 'لا يمكنك تعطيل حسابك الحالي أثناء تسجيل الدخول به.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        // Safety Guard 2: Protection of the last active admin
        if ($user['role'] === 'admin' && (int)$user['is_active'] === 1) {
            $db = Database::getInstance();
            $adminCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();

            if ($adminCount <= 1) {
                if ($isActive === 0) {
                    Session::flash('error', 'لا يمكن تعطيل آخر مدير نشط في النظام.');
                    $this->redirect('/admin/users/' . $id . '/edit');
                    return;
                }
                if ($role !== 'admin') {
                    Session::flash('error', 'لا يمكن تغيير دور آخر مدير نشط في النظام.');
                    $this->redirect('/admin/users/' . $id . '/edit');
                    return;
                }
            }
        }

        // Update profile & role
        $userModel->updateProfile($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive,
            'is_verified_email' => $isVerified,
        ]);
        $userModel->updateRole($id, $role);

        // Update password if supplied
        if ($password !== '') {
            if (mb_strlen($password) < 6) {
                Session::flash('error', 'كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف.');
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }
            $userModel->updatePassword($id, $password);
        }

        // Save effective permission overrides
        $userModel->replaceUserPermissions($id, $permissions);

        ActivityLog::log(Auth::id(), 'update_user', "تعديل المستخدم #$id وتحديث صلاحياته");
        Session::flash('success', 'تم حفظ بيانات وصلاحيات المستخدم بنجاح.');
        $this->redirect('/admin/users');
    }

    public function toggleUserActive($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_users');
        $id = (int)$id;

        if ($id === (int)Auth::id()) {
            Session::flash('error', 'لا يمكنك تعطيل حسابك الحالي أثناء تسجيل الدخول به.');
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            $this->redirect('/admin/users');
            return;
        }

        if ($user['role'] === 'admin' && (int)$user['is_active'] === 1) {
            $db = Database::getInstance();
            $adminCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
            if ($adminCount <= 1) {
                Session::flash('error', 'لا يمكن تعطيل آخر مدير نشط في النظام.');
                $this->redirect('/admin/users');
                return;
            }
        }

        $newState = (int)$user['is_active'] === 1 ? 0 : 1;
        $userModel->updateProfile($id, ['is_active' => $newState]);
        ActivityLog::log(Auth::id(), 'update_user', ($newState === 0 ? 'تعطيل' : 'تفعيل') . " المستخدم #$id");
        Session::flash('success', 'تم تحديث حالة المستخدم بنجاح');
        $this->redirect('/admin/users');
    }

    public function deleteUser($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_users');
        $id = (int)$id;

        if ($id === (int)Auth::id()) {
            Session::flash('error', 'لا يمكنك حذف حسابك الحالي.');
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            $this->redirect('/admin/users');
            return;
        }

        if ($user['role'] === 'admin') {
            $db = Database::getInstance();
            $adminCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();
            if ($adminCount <= 1 && (int)$user['is_active'] === 1) {
                Session::flash('error', 'لا يمكن حذف آخر مدير نشط في النظام.');
                $this->redirect('/admin/users');
                return;
            }
        }

        $userModel->delete($id);
        ActivityLog::log(Auth::id(), 'delete_user', "حذف المستخدم #$id");
        Session::flash('success', 'تم حذف المستخدم');
        $this->redirect('/admin/users');
    }

    // ---------------- Platform employees ----------------
    public function employees()
    {
        Auth::requirePermission('manage_employees');
        $employees = (new User())->getByRole('employee', 'created_at DESC');
        $this->view('admin/employees', ['title' => 'موظفو المنصة', 'employees' => $employees], 'dashboard');
    }

    public function createEmployee()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_employees');
        $name = trim((string)$this->input('name', ''));
        $email = trim(strtolower((string)$this->input('email', '')));
        $phone = trim((string)$this->input('phone', ''));
        $password = (string)$this->input('password', '');

        if (mb_strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 8) {
            Session::flash('error', 'يرجى إدخال اسم صحيح وبريد صحيح وكلمة مرور لا تقل عن 8 أحرف.');
            $this->redirect('/admin/employees');
            return;
        }

        $u = new User();
        if ($u->emailExists($email)) {
            Session::flash('error', 'البريد الإلكتروني مستخدم بالفعل.');
            $this->redirect('/admin/employees');
            return;
        }

        $id = $u->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => 'employee',
            'is_active' => 1,
            'is_verified_email' => 1,
        ]);

        ActivityLog::log(Auth::id(), 'create_employee', 'إنشاء موظف منصة #' . $id);
        Session::flash('success', 'تم إنشاء موظف المنصة بنجاح.');
        $this->redirect('/admin/employees');
    }

    public function editEmployee($id)
    {
        Auth::requirePermission('manage_employees');
        $id = (int)$id;
        $userModel = new User();
        $employee = $userModel->findWithPermissions($id);
        if (!$employee || $employee['role'] !== 'employee') {
            Session::flash('error', 'الموظف غير موجود.');
            $this->redirect('/admin/employees');
            return;
        }

        $this->view('admin/employee_edit', ['title' => 'تعديل الموظف: ' . $employee['name'], 'employee' => $employee], 'dashboard');
    }

    public function updateEmployee($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_employees');
        $id = (int)$id;

        $userModel = new User();
        $employee = $userModel->find($id);
        if (!$employee || $employee['role'] !== 'employee') {
            Session::flash('error', 'الموظف غير موجود.');
            $this->redirect('/admin/employees');
            return;
        }

        $name = trim((string)$this->input('name', ''));
        $email = trim(strtolower((string)$this->input('email', '')));
        $phone = trim((string)$this->input('phone', ''));
        $password = (string)$this->input('password', '');
        $isActive = $this->input('is_active') ? 1 : 0;
        $permissions = (array)$this->input('permissions', []);

        if (mb_strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'يرجى إدخال اسم صحيح وبريد إلكتروني صالح.');
            $this->redirect('/admin/employees/' . $id . '/edit');
            return;
        }

        if ($userModel->emailExistsExcept($email, $id)) {
            Session::flash('error', 'البريد الإلكتروني مستخدم بالفعل لحساب آخر.');
            $this->redirect('/admin/employees/' . $id . '/edit');
            return;
        }

        $userModel->updateProfile($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive,
        ]);

        if ($password !== '') {
            if (mb_strlen($password) < 8) {
                Session::flash('error', 'كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف.');
                $this->redirect('/admin/employees/' . $id . '/edit');
                return;
            }
            $userModel->updatePassword($id, $password);
        }

        // Save employee permissions
        $userModel->replaceUserPermissions($id, $permissions);

        ActivityLog::log(Auth::id(), 'update_employee', "تعديل موظف المنصة #$id");
        Session::flash('success', 'تم حفظ بيانات وصلاحيات الموظف بنجاح.');
        $this->redirect('/admin/employees');
    }

    public function toggleEmployee($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_employees');
        $id = (int)$id;
        $u = (new User())->find($id);
        if (!$u || $u['role'] !== 'employee') {
            Session::flash('error', 'الموظف غير موجود.');
            $this->redirect('/admin/employees');
            return;
        }
        $new = (int)$u['is_active'] === 1 ? 0 : 1;
        (new User())->updateProfile($id, ['is_active' => $new]);
        ActivityLog::log(Auth::id(), 'toggle_employee', ($new ? 'تفعيل' : 'تعطيل') . ' الموظف #' . $id);
        Session::flash('success', 'تم تحديث حالة الموظف.');
        $this->redirect('/admin/employees');
    }

    // ---------------- Providers / verification ----------------
    public function providers()
    {
        Auth::requirePermission('verify_providers');
        $providerModel = new ProviderProfile();
        $providers = $providerModel->search([]);
        $this->view('admin/providers', ['title' => 'مقدمو الخدمة', 'providers' => $providers], 'dashboard');
    }


    public function providerEvaluation($id)
    {
        Auth::requirePermission('verify_providers');
        $providerModel=new ProviderProfile(); $provider=$providerModel->find((int)$id);
        if(!$provider){ Session::flash('error','مقدم الخدمة غير موجود'); $this->redirect('/admin/providers'); return; }
        $db=Database::getInstance();
        $rows=$db->prepare("SELECT pp.*, u.name, u.email, u.phone FROM provider_profiles pp JOIN users u ON u.id=pp.user_id WHERE pp.id=:id LIMIT 1"); $rows->execute(['id'=>(int)$id]); $provider=$rows->fetch(PDO::FETCH_ASSOC) ?: $provider;
        $evaluationModel=new ProviderEvaluation(); $latest=$evaluationModel->latest((int)$id); $history=$evaluationModel->history((int)$id);
        $objective=$evaluationModel->objectiveScore($provider); $combined=$evaluationModel->combinedScore($provider,$latest);
        $customerStats=$db->prepare("SELECT COUNT(*) review_count, COALESCE(AVG(rating),0) customer_avg FROM reviews WHERE provider_id=:p"); $customerStats->execute(['p'=>(int)$id]); $customerStats=$customerStats->fetch(PDO::FETCH_ASSOC);
        $this->view('admin/provider_evaluation',compact('provider','latest','history','objective','combined','customerStats')+['title'=>'تقييم مقدم الخدمة'],'dashboard');
    }

    public function saveProviderEvaluation($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('verify_providers');
        $provider=(new ProviderProfile())->find((int)$id); if(!$provider){Session::flash('error','مقدم الخدمة غير موجود');$this->redirect('/admin/providers');return;}
        $scores=[]; foreach(['quality_score','reliability_score','communication_score','compliance_score'] as $k){$v=(int)$this->input($k,0);if($v<1||$v>5){Session::flash('error','كل معايير التقييم يجب أن تكون من 1 إلى 5');$this->redirect('/admin/providers/'.$id.'/evaluation');return;}$scores[$k]=$v;}
        $score=round(array_sum($scores)/4,2); $scores['provider_id']=(int)$id; $scores['admin_id']=Auth::id(); $scores['score']=$score; $scores['comment']=mb_substr((string)$this->input('comment',''),0,2000);
        (new ProviderEvaluation())->insert($scores); ActivityLog::log(Auth::id(),'evaluate_provider',"تقييم مقدم الخدمة #$id بدرجة $score/5");
        Session::flash('success','تم حفظ تقييم المنصة لمقدم الخدمة'); $this->redirect('/admin/providers/'.$id.'/evaluation');
    }

    public function verifyProvider($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('verify_providers');
        $providerModel = new ProviderProfile();
        $p = $providerModel->find((int)$id);
        if ($p) {
            $providerModel->update((int)$id, ['is_verified' => 1]);
            (new Notification())->notify($p['user_id'], 'تم توثيق حسابك', 'تهانينا! تم توثيق ملفك المهني من قبل الإدارة', '/provider/profile');
            ActivityLog::log(Auth::id(), 'verify_provider', "توثيق مقدم الخدمة #$id");
            Session::flash('success', 'تم توثيق مقدم الخدمة');
        }
        $this->redirect('/admin/providers');
    }

    public function unverifyProvider($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('verify_providers');
        (new ProviderProfile())->update((int)$id, ['is_verified' => 0]);
        ActivityLog::log(Auth::id(), 'unverify_provider', "إلغاء توثيق مقدم الخدمة #$id");
        Session::flash('success', 'تم إلغاء توثيق مقدم الخدمة');
        $this->redirect('/admin/providers');
    }

    // ---------------- Categories & Services ----------------
    public function categories()
    {
        Auth::requirePermission('manage_categories');
        $categories = (new Category())->all('name_ar');
        $services = (new Service())->withCategory();
        $this->view('admin/categories', ['title' => 'التصنيفات والخدمات', 'categories' => $categories, 'services' => $services], 'dashboard');
    }

    public function storeCategory()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_categories');
        (new Category())->insert([
            'name_ar' => $this->input('name_ar'), 'name_en' => $this->input('name_en'),
            'icon' => $this->input('icon', 'fa-toolbox'),
        ]);
        Session::flash('success', 'تم إضافة التصنيف');
        $this->redirect('/admin/categories');
    }

    public function deleteCategory($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_categories');
        (new Category())->delete((int)$id);
        Session::flash('success', 'تم حذف التصنيف');
        $this->redirect('/admin/categories');
    }

    public function storeService()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_categories');
        (new Service())->insert([
            'category_id' => $this->input('category_id'), 'name_ar' => $this->input('name_ar'),
            'name_en' => $this->input('name_en'), 'description' => $this->input('description'),
        ]);
        Session::flash('success', 'تم إضافة الخدمة');
        $this->redirect('/admin/categories');
    }

    public function deleteService($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_categories');
        (new Service())->delete((int)$id);
        Session::flash('success', 'تم حذف الخدمة');
        $this->redirect('/admin/categories');
    }

    // ---------------- Locations ----------------
    public function locations()
    {
        Auth::requirePermission('manage_locations');
        $locations = (new Location())->all('level, name_ar');
        $this->view('admin/locations', ['title' => 'المناطق', 'locations' => $locations], 'dashboard');
    }

    public function storeLocation()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_locations');
        (new Location())->insert([
            'parent_id' => $this->input('parent_id') ?: null,
            'name_ar' => $this->input('name_ar'), 'name_en' => $this->input('name_en'),
            'level' => $this->input('level'),
        ]);
        Session::flash('success', 'تم إضافة المنطقة');
        $this->redirect('/admin/locations');
    }

    public function deleteLocation($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_locations');
        (new Location())->delete((int)$id);
        Session::flash('success', 'تم حذف المنطقة');
        $this->redirect('/admin/locations');
    }

    // ---------------- Requests ----------------
    public function requests()
    {
        Auth::requirePermission('manage_requests');
        $status = $this->input('status');
        $reqModel = new ServiceRequest();
        $requests = $status ? $reqModel->where(['status' => $status], 'created_at DESC') : $reqModel->all('created_at DESC');
        $this->view('admin/requests', ['title' => 'الطلبات', 'requests' => $requests, 'status' => $status], 'dashboard');
    }

    public function requestDetails($id)
    {
        Auth::requirePermission('manage_requests');
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        $history = $reqModel->history((int)$id);
        $this->view('admin/request_details', compact('request', 'history') + ['title' => $request['request_number'] ?? ''], 'dashboard');
    }

    // ---------------- Complaints ----------------
    public function complaints()
    {
        Auth::requirePermission('manage_complaints');
        $status = $this->input('status');
        $complaintModel = new Complaint();
        $complaints = $status ? $complaintModel->where(['status' => $status], 'created_at DESC') : $complaintModel->all('created_at DESC');
        $this->view('admin/complaints', ['title' => 'الشكاوى', 'complaints' => $complaints, 'status' => $status], 'dashboard');
    }

    public function complaintDetails($id)
    {
        Auth::requirePermission('manage_complaints');
        $complaintModel = new Complaint();
        $complaint = $complaintModel->find((int)$id);
        $messages = $complaintModel->messages((int)$id);
        $this->view('admin/complaint_details', compact('complaint', 'messages') + ['title' => $complaint['complaint_number'] ?? ''], 'dashboard');
    }

    public function replyComplaint($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_complaints');
        $complaintModel = new Complaint();
        $complaintModel->addMessage((int)$id, Auth::id(), $this->input('message'));
        $complaint = $complaintModel->find((int)$id);
        (new Notification())->notify($complaint['created_by'], 'تحديث على شكواك', 'قامت الإدارة بالرد على شكواك', '/customer/complaints/' . $id);
        $this->redirect('/admin/complaints/' . $id);
    }

    public function updateComplaintStatus($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_complaints');
        $status = $this->input('status');
        $allowedStatuses = ['open', 'in_review', 'resolved', 'rejected', 'closed'];
        if (!in_array($status, $allowedStatuses, true)) {
            Session::flash('error', 'حالة الشكوى غير صالحة');
            $this->redirect('/admin/complaints/' . $id);
            return;
        }
        $complaintModel = new Complaint();
        $complaint = $complaintModel->find((int)$id);
        if (!$complaint) {
            Session::flash('error', 'الشكوى غير موجودة');
            $this->redirect('/admin/complaints');
            return;
        }
        $complaintModel->update((int)$id, ['status' => $status]);
        $complaint = $complaintModel->find((int)$id);
        (new Notification())->notify($complaint['created_by'], 'تحديث حالة الشكوى', 'تم تحديث حالة شكواك إلى: ' . $status, '/customer/complaints/' . $id);
        ActivityLog::log(Auth::id(), 'resolve_complaint', "تغيير حالة الشكوى #$id إلى $status");
        Session::flash('success', 'تم تحديث حالة الشكوى');
        $this->redirect('/admin/complaints/' . $id);
    }

    // ---------------- Finance ----------------
    public function finance()
    {
        Auth::requirePermission('manage_finance');
        $invoiceModel = new Invoice();
        $db = Database::getInstance();
        $status = $this->input('status');
        $from = $this->input('date_from');
        $to = $this->input('date_to');
        $where=[]; $params=[];
        if(in_array($status,['unpaid','paid','refunded'],true)){ $where[]='i.payment_status=:status'; $params['status']=$status; }
        if($from){ $where[]='DATE(i.created_at)>=:from'; $params['from']=$from; }
        if($to){ $where[]='DATE(i.created_at)<=:to'; $params['to']=$to; }
        $w=$where?'WHERE '.implode(' AND ',$where):'';
        $stmt=$db->prepare("SELECT i.*,sr.request_number,sr.final_price,sr.provider_id,cu.name customer_name,pu.name provider_name,
            COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id=i.id AND p.status='paid'),0) paid_amount,
            GREATEST(i.total_amount-COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id=i.id AND p.status='paid'),0),0) balance,
            (SELECT COUNT(*) FROM payments p WHERE p.invoice_id=i.id AND p.status='paid') payment_count,
            GREATEST(i.provider_net - COALESCE((SELECT SUM(pp2.amount) FROM provider_payouts pp2 WHERE pp2.invoice_id=i.id AND pp2.status='paid'),0), 0) available_payout
            FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id $w ORDER BY i.created_at DESC");
        $stmt->execute($params); $invoices=$stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../services/FinanceService.php';
        $finSummary = (new FinanceService())->platformSummary($from, $to, $status);
        $totals = [
            'invoices_count' => $finSummary['invoices_count'],
            'gross'          => $finSummary['gross_amount'],
            'collected'      => $finSummary['net_collected'],
            'commission'     => $finSummary['commission_amount'],
            'provider_net'   => $finSummary['provider_net_amount'],
            'unpaid'         => $finSummary['outstanding_amount'],
        ];

        $paymentTotals = $db->query("
            SELECT COUNT(*) count,
                   COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) paid,
                   COALESCE(SUM(CASE WHEN status='refunded' THEN amount ELSE 0 END),0) refunded
            FROM payments
        ")->fetch(PDO::FETCH_ASSOC);

        $methods = $db->query("
            SELECT method, COUNT(*) count, COALESCE(SUM(amount), 0) amount
            FROM payments
            WHERE status = 'paid'
            GROUP BY method
            ORDER BY amount DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $monthly = $db->query("
            SELECT DATE_FORMAT(i.created_at,'%Y-%m') AS month,
                   COALESCE(SUM(i.total_amount),0) AS gross,
                   COALESCE(SUM(CASE WHEN i.payment_status='refunded' THEN 0 ELSE p.collected_month END), 0) AS collected,
                   COALESCE(SUM(CASE WHEN i.payment_status='refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p.collected_month, 0)) * i.commission_rate, 2) END), 0) AS commission
            FROM invoices i
            LEFT JOIN (
                SELECT invoice_id, SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) AS collected_month
                FROM payments GROUP BY invoice_id
            ) p ON p.invoice_id = i.id
            GROUP BY DATE_FORMAT(i.created_at,'%Y-%m')
            ORDER BY month DESC LIMIT 12
        ")->fetchAll(PDO::FETCH_ASSOC);

        $providers = $db->query("
            SELECT sr.provider_id,
                   COALESCE(u.name, 'غير محدد') AS provider_name,
                   COUNT(i.id) AS invoices,
                   COALESCE(SUM(i.total_amount), 0) AS gross,
                   COALESCE(SUM(CASE WHEN i.payment_status='refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p.paid_amount, 0)) * i.commission_rate, 2) END), 0) AS commission,
                   COALESCE(SUM(CASE WHEN i.payment_status='refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p.paid_amount, 0)) * (1 - i.commission_rate), 2) END), 0) AS net,
                   COALESCE(SUM(CASE WHEN i.payment_status='unpaid' THEN GREATEST(i.total_amount - COALESCE(p.paid_amount, 0), 0) * (1 - i.commission_rate) ELSE 0 END), 0) AS pending_net
            FROM invoices i
            JOIN service_requests sr ON sr.id = i.request_id
            LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
            LEFT JOIN users u ON u.id = pp.user_id
            LEFT JOIN (
                SELECT invoice_id, SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) AS paid_amount
                FROM payments GROUP BY invoice_id
            ) p ON p.invoice_id = i.id
            GROUP BY sr.provider_id, u.name
            ORDER BY net DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $openRequests = $db->query("
            SELECT sr.id, sr.request_number, cu.name customer_name, pu.name provider_name, sr.final_price
            FROM service_requests sr
            JOIN users cu ON cu.id = sr.customer_id
            LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
            LEFT JOIN users pu ON pu.id = pp.user_id
            WHERE sr.status = 'completed' AND sr.final_price IS NOT NULL AND sr.final_price > 0
              AND NOT EXISTS (SELECT 1 FROM invoices i WHERE i.request_id = sr.id)
            ORDER BY sr.updated_at DESC LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);

        $today = $db->query("
            SELECT COALESCE(SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END), 0) AS collected_today,
                   COUNT(CASE WHEN p.status='paid' THEN 1 END) AS payments_today
            FROM payments p
            WHERE DATE(p.paid_at) = CURDATE()
        ")->fetch(PDO::FETCH_ASSOC) ?: ['collected_today' => 0, 'payments_today' => 0];

        $todayCommission = $db->query("
            SELECT COALESCE(SUM(ROUND(p.amount * i.commission_rate, 2)), 0)
            FROM payments p
            JOIN invoices i ON i.id = p.invoice_id
            WHERE p.status = 'paid' AND DATE(p.paid_at) = CURDATE()
        ")->fetchColumn();

        $recentPayments = $db->query("
            SELECT p.*, i.invoice_number, i.total_amount, cu.name customer_name, pu.name provider_name
            FROM payments p
            JOIN invoices i ON i.id = p.invoice_id
            JOIN service_requests sr ON sr.id = i.request_id
            JOIN users cu ON cu.id = sr.customer_id
            LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
            LEFT JOIN users pu ON pu.id = pp.user_id
            ORDER BY p.paid_at DESC, p.id DESC LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        $rate = (new Invoice())->commissionRate();
        $this->view('admin/finance', compact('invoices','totals','paymentTotals','methods','monthly','providers','openRequests','rate','status','from','to','today','recentPayments') + ['todayCommission'=>(float)$todayCommission,'title'=>'المالية'], 'dashboard');
    }

    public function createInvoice()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_finance');
        $requestId=(int)$this->input('request_id'); $price=(float)$this->input('service_price');
        if($requestId<=0 || $price<=0){ Session::flash('error','يجب تحديد الطلب وسعر خدمة صحيح.'); $this->redirect('/admin/finance'); return; }
        $id=(new Invoice())->generateForRequest($requestId,$price); ActivityLog::log(Auth::id(),'create_invoice','إنشاء/اعتماد فاتورة #'.$id.' للطلب #'.$requestId); Session::flash('success','تم إنشاء الفاتورة واعتماد السعر.'); $this->redirect('/admin/finance');
    }

    public function recordPayment()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_finance');
        $id=(int)$this->input('invoice_id'); $method=$this->input('method','simulated_wallet'); $amount=(float)$this->input('amount'); $reference=mb_substr((string)$this->input('reference',''),0,100); $notes=mb_substr((string)$this->input('notes',''),0,500);
        if(!in_array($method,['simulated_card','simulated_wallet'],true)){ Session::flash('error','طريقة الدفع غير صحيحة.'); $this->redirect('/admin/finance'); return; }
        if(!(new Invoice())->recordPayment($id,$method,$amount,$reference?:null,$notes?:null)){ Session::flash('error','تعذر تسجيل الدفعة. تحقق من الفاتورة والمبلغ المتبقي.'); $this->redirect('/admin/finance'); return; }
        if (settingBool('notify_on_payment', true)) {
            $invoice=(new Invoice())->details($id);
            if ($invoice) {
                (new Notification())->notify((int)$invoice['customer_id'],'تسجيل دفعة','تم تسجيل دفعة على الفاتورة '.$invoice['invoice_number'],'/customer/requests/'.$invoice['request_id'],'payment');
                if (!empty($invoice['provider_id'])) {
                    $provider=(new ProviderProfile())->find((int)$invoice['provider_id']);
                    if ($provider) (new Notification())->notify((int)$provider['user_id'],'تحديث مالي','تم تسجيل دفعة على الفاتورة '.$invoice['invoice_number'],'/provider/requests/'.$invoice['request_id'],'payment');
                }
            }
        }
        ActivityLog::log(Auth::id(),'record_payment','تسجيل دفعة بقيمة '.number_format($amount,2).' على الفاتورة #'.$id); Session::flash('success','تم تسجيل الدفعة وتحديث حالة الفاتورة.'); $this->redirect('/admin/finance');
    }

public function recordPayout()
{
    $this->verifyCsrfOrFail(); Auth::requirePermission('manage_payouts');
    $providerId=(int)$this->input('provider_id'); $invoiceId=(int)$this->input('invoice_id'); $amount=(float)$this->input('amount');
    $reference=mb_substr((string)$this->input('reference',''),0,100); $notes=mb_substr((string)$this->input('notes',''),0,500);
    if($providerId<=0 || $invoiceId<=0 || $amount<=0 || !(new Payout())->createPaid($providerId,$invoiceId,$amount,(int)Auth::id(),$reference?:null,$notes?:null)){
        Session::flash('error','تعذر تسجيل التحويل. تحقق من مستحقات مقدم الخدمة وحالة الفاتورة والمبلغ المتاح.'); $this->redirect('/admin/finance'); return;
    }
    $provider=(new ProviderProfile())->find($providerId); if($provider)(new Notification())->notify((int)$provider['user_id'],'تم تحويل مستحقاتك','تم تسجيل تحويل مالي بقيمة '.number_format($amount,2).' '.setting('currency','ريال'),'/provider/finance');
    ActivityLog::log(Auth::id(),'provider_payout','تحويل مستحقات مقدم الخدمة #'.$providerId.' بقيمة '.number_format($amount,2)); Session::flash('success','تم تسجيل تحويل مستحقات مقدم الخدمة.'); $this->redirect('/admin/finance');
}

    public function refundInvoice()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_finance');
        $id=(int)$this->input('invoice_id'); $notes=mb_substr((string)$this->input('notes',''),0,500);
        if(!(new Invoice())->refund($id,$notes?:null)){ Session::flash('error','لا يمكن استرداد هذه الفاتورة لأنها لا تحتوي على مبلغ مدفوع.'); $this->redirect('/admin/finance'); return; }
        ActivityLog::log(Auth::id(),'refund_invoice','استرداد الفاتورة #'.$id); Session::flash('success','تم تسجيل الاسترداد.'); $this->redirect('/admin/finance');
    }

    public function exportFinanceCsv()
    {
        Auth::requirePermission('manage_finance');
        $db=Database::getInstance();
        $rows=$db->query("SELECT i.invoice_number,sr.request_number,cu.name customer_name,pu.name provider_name,i.service_price,i.commission_amount,i.provider_net,i.total_amount,i.payment_status,i.created_at,COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id=i.id AND p.status='paid'),0) paid_amount FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id ORDER BY i.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="mihan-finance-report-'.date('Y-m-d').'.csv"');
        echo "\xEF\xBB\xBF";
        $out=fopen('php://output','w');
        fputcsv($out,['الفاتورة','الطلب','العميل','مقدم الخدمة','سعر الخدمة','عمولة المنصة','صافي المقدم','الإجمالي','المدفوع','حالة الدفع','التاريخ']);
        foreach($rows as $r) fputcsv($out,[$r['invoice_number'],$r['request_number'],$r['customer_name'],$r['provider_name'],$r['service_price'],$r['commission_amount'],$r['provider_net'],$r['total_amount'],$r['paid_amount'],$r['payment_status'],$r['created_at']]);
        fclose($out); exit;
    }

    public function updateFinanceSettings()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_finance');
        $rate=(float)$this->input('commission_rate');
        if($rate<0 || $rate>100){ Session::flash('error','نسبة العمولة يجب أن تكون بين 0 و100%.'); $this->redirect('/admin/finance'); return; }
        $db=Database::getInstance(); $s=$db->prepare("INSERT INTO system_settings (`key`,`value`) VALUES ('platform_commission_rate',:v) ON DUPLICATE KEY UPDATE value=:v2"); $s->execute(['v'=>$rate/100,'v2'=>$rate/100]);
        ActivityLog::log(Auth::id(),'update_finance_settings','تحديث عمولة المنصة إلى '.$rate.'%'); Session::flash('success','تم حفظ نسبة عمولة المنصة، وستطبق على الفواتير الجديدة.'); $this->redirect('/admin/finance');
    }

    // ---------------- Reports ----------------
    public function reports()
    {
        Auth::requirePermission('view_reports');
        $analytics = new AnalyticsService();
        $filters = [
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'category_id' => $this->input('category_id'),
            'location_id' => $this->input('location_id'),
            'status' => $this->input('status'),
        ];

        $requestsReport = $analytics->reportRequests($filters);
        $categories = (new Category())->active();
        $locations = (new Location())->active();

        if ($this->input('export') === 'csv') {
            $this->exportCsv($requestsReport);
            return;
        }

        $this->view('admin/reports', [
            'title' => 'التقارير والتحليلات',
            'requestsReport' => $requestsReport,
            'filters' => $filters,
            'categories' => $categories,
            'locations' => $locations,
            'summary' => $analytics->decisionSummary($filters),
            'statusBreakdown' => $analytics->statusBreakdown($filters),
            'mostRequested' => $analytics->mostRequestedServices($filters, 8),
            'leastRequested' => $analytics->leastRequestedServices($filters, 8),
            'topProviders' => $analytics->providerPerformance($filters, 8),
            'mostActiveAreas' => $analytics->mostActiveAreas(8, $filters),
            'areaProviderNeed' => $analytics->areaProviderNeed($filters, 8),
            'ratingsByProfession' => $analytics->ratingsByProfession($filters, 8),
            'peakHours' => $analytics->peakHours($filters, 6),
            'newUsers' => $analytics->newUsers($filters),
            'rejectedRequests' => $analytics->rejectedRequests($filters),
            'decisionInsights' => $analytics->decisionInsights($filters),
            'revenueByMonth' => $analytics->revenueByMonth(),
            'requestsByMonth' => $analytics->requestsByMonth(6),
        ], 'dashboard');
    }

    private function exportCsv(array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mihan_requests_report.csv"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['رقم الطلب', 'العميل', 'التصنيف', 'الخدمة', 'الموقع', 'الحالة', 'التاريخ']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['request_number'], $r['customer_name'], $r['category_name'], $r['service_name_ar'], $r['location_name'] ?? '', $r['status'], $r['created_at']]);
        }
        fclose($out);
        exit;
    }

    // ---------------- Reviews moderation ----------------
    public function reviews()
    {
        $filters=['rating'=>$this->input('rating'),'provider_id'=>$this->input('provider_id')];
        $reviews=(new Review())->allForAdmin($filters);
        $providers=(new ProviderProfile())->search([]);
        $this->view('admin/reviews', ['title'=>'إدارة تقييمات العملاء','reviews'=>$reviews,'providers'=>$providers,'filters'=>$filters], 'dashboard');
    }

    public function deleteReview($id)
    {
        $this->verifyCsrfOrFail();
        $reviewModel=new Review();
        $review=$reviewModel->find((int)$id);
        if(!$review){ Session::flash('error','التقييم غير موجود'); $this->redirect('/admin/reviews'); return; }
        $reviewModel->delete((int)$id);
        (new ProviderProfile())->recalculateRating((int)$review['provider_id']);
        ActivityLog::log(Auth::id(),'moderate_review',"حذف تقييم #$id لمقدم الخدمة #".$review['provider_id']);
        Session::flash('success','تم حذف التقييم وتحديث متوسط تقييم مقدم الخدمة');
        $this->redirect('/admin/reviews');
    }

    // ---------------- Settings ----------------
    public function settings()
    {
        Auth::requirePermission('manage_settings');
        $model = new SystemSetting();
        $values = $model->allKeyed();
        $groups = $this->settingsGroups($values);
        $db = Database::getInstance();
        $health = [
            'users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'providers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='provider'")->fetchColumn(),
            'requests' => (int)$db->query("SELECT COUNT(*) FROM service_requests")->fetchColumn(),
            'database' => 'متصل',
            'environment' => defined('APP_ENV') ? APP_ENV : 'unknown',
            'php' => PHP_VERSION,
        ];
        $this->view('admin/settings', [
            'title' => 'إعدادات النظام',
            'values' => $values,
            'groups' => $groups,
            'health' => $health,
        ], 'dashboard');
    }

    private function settingsGroups(array $v): array
    {
        $field = static function(string $key, string $label, string $type='text', $default='', string $help='', array $options=[]): array {
            return compact('key','label','type','default','help','options');
        };
        return [
            ['id'=>'general','icon'=>'fa-sliders','title'=>'عام المنصة','description'=>'هوية المنصة ومظهرها ونصوصها الأساسية.','fields'=>[
                $field('site_name','اسم المنصة','text','مِهَن','يظهر في عنوان الموقع والواجهة.'),
                $field('site_tagline','الوصف المختصر','text','منصة الخدمات المهنية','عبارة تعريفية قصيرة.'),
                $field('default_language','اللغة الافتراضية','select','ar','لغة الجلسات الجديدة.',['ar'=>'العربية','en'=>'English']),
                $field('timezone','المنطقة الزمنية','select','Asia/Aden','تؤثر على التاريخ والوقت في النظام.',['Asia/Aden'=>'Asia/Aden','Asia/Riyadh'=>'Asia/Riyadh','UTC'=>'UTC']),
            ]],
            ['id'=>'access','icon'=>'fa-user-shield','title'=>'الوصول والحسابات','description'=>'تحكم في التسجيل وحالة المنصة العامة.','fields'=>[
                $field('allow_customer_registration','تسجيل العملاء','toggle','1','السماح بإنشاء حسابات عملاء جديدة.'),
                $field('allow_provider_registration','تسجيل مقدمي الخدمة','toggle','1','السماح بإنشاء حسابات مقدمي الخدمات.'),
                $field('maintenance_mode','وضع الصيانة','toggle','0','يوقف الوصول العام مع إبقاء الإدارة متاحة.'),
                $field('maintenance_message','رسالة الصيانة','textarea','المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.','الرسالة التي تظهر للزوار أثناء الصيانة.'),
            ]],
            ['id'=>'workflow','icon'=>'fa-diagram-project','title'=>'إدارة سير العمل','description'=>'قواعد التشغيل التي تؤثر على الطلبات ومقدمي الخدمة.','fields'=>[
                $field('auto_provider_available','إتاحة مقدم الخدمة تلقائيًا','toggle','1','إبقاء مقدم الخدمة متاحًا افتراضيًا عند إنشاء ملفه.'),
                $field('reviews_enabled','التقييمات','toggle','1','تمكين العملاء من تقييم الطلبات المكتملة.'),
                $field('complaints_enabled','الشكاوى','toggle','1','تمكين إنشاء الشكاوى ومتابعتها.'),
                $field('notify_on_request','إشعارات الطلبات','toggle','1','تفعيل الإشعارات المرتبطة بتغيّر الطلبات.'),
            ]],
            ['id'=>'finance','icon'=>'fa-coins','title'=>'السياسات المالية','description'=>'الإعدادات العامة للعمولة والعملة وعرض الأسعار.','fields'=>[
                $field('currency','العملة','text','ريال يمني','اسم العملة الظاهر بجانب المبالغ.'),
                $field('currency_code','رمز العملة','text','YER','رمز العملة المختصر.'),
                $field('invoice_prefix','بادئة الفواتير','text','INV','بادئة رقم الفاتورة.'),
                $field('platform_commission_rate','عمولة المنصة (%)','number','10','تطبق على الفواتير الجديدة. يمكن ضبطها أيضًا من المالية.'),
            ]],
            ['id'=>'notifications','icon'=>'fa-bell','title'=>'الإشعارات','description'=>'سياسة الإشعارات الافتراضية للمستخدمين.','fields'=>[
                $field('notifications_enabled','الإشعارات داخل المنصة','toggle','1','تشغيل نظام الإشعارات الداخلي.'),
                $field('notify_on_completion','إشعار إكمال الخدمة','toggle','1','إشعار العميل عند إكمال الطلب.'),
                $field('notify_on_payment','إشعار الدفع','toggle','1','إشعار الأطراف عند تسجيل دفعة.'),
                $field('notify_on_complaint','إشعارات الشكاوى','toggle','1','إشعار الإدارة والعميل بتحديثات الشكوى.'),
            ]],
            ['id'=>'security','icon'=>'fa-lock','title'=>'الأمان والخصوصية','description'=>'خيارات حماية وتشغيل يمكن تفعيلها تدريجيًا.','fields'=>[
                $field('force_https','فرض HTTPS','toggle','0','للاستخدام عند تشغيل المشروع على نطاق HTTPS فعلي.'),
                $field('activity_log_enabled','سجل النشاط','toggle','1','تسجيل عمليات الإدارة والمستخدمين المهمة.'),
                $field('show_system_errors','عرض أخطاء النظام','toggle','0','لا يُنصح بتفعيله في الإنتاج.'),
            ]],
        ];
    }

    public function updateSettings()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_settings');
        $model = new SystemSetting();
        $groups = $this->settingsGroups([]);
        $allowed = [];
        foreach ($groups as $group) foreach ($group['fields'] as $f) $allowed[$f['key']] = $f;

        $values = [];
        foreach ($allowed as $key => $f) {
            if ($f['type'] === 'toggle') {
                $values[$key] = isset($_POST[$key]) ? '1' : '0';
                continue;
            }
            $value = trim((string)($_POST[$key] ?? $f['default']));
            if ($key === 'platform_commission_rate') {
                $n = (float)$value;
                if ($n < 0 || $n > 100) { Session::flash('error','نسبة العمولة يجب أن تكون بين 0 و100%.'); $this->redirect('/admin/settings'); return; }
                $value = (string)$n;
            }
            if ($key === 'default_language' && !in_array($value,['ar','en'],true)) $value='ar';
            if ($key === 'timezone' && !in_array($value,['Asia/Aden','Asia/Riyadh','UTC'],true)) $value='Asia/Aden';
            $values[$key] = mb_substr($value, 0, 2000);
        }
        // Keep the financial module's canonical decimal representation.
        if (isset($values['platform_commission_rate'])) {
            $model->set('platform_commission_rate', ((float)$values['platform_commission_rate']) / 100);
            unset($values['platform_commission_rate']);
        }
        $model->setMany($values);
        ActivityLog::log(Auth::id(), 'update_settings', 'تحديث إعدادات النظام العامة والتشغيلية');
        Session::flash('success', 'تم حفظ إعدادات المنصة بنجاح');
        $this->redirect('/admin/settings');
    }

    public function resetSettings()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('manage_settings');
        $model = new SystemSetting();
        foreach ($this->settingsGroups([]) as $group) foreach ($group['fields'] as $f) {
            $value = $f['default'];
            if ($f['key'] === 'platform_commission_rate') $value = ((float)$value)/100;
            $model->set($f['key'], $value);
        }
        ActivityLog::log(Auth::id(), 'reset_settings', 'إعادة إعدادات المنصة إلى القيم الافتراضية');
        Session::flash('success', 'تمت إعادة الإعدادات الافتراضية');
        $this->redirect('/admin/settings');
    }

    public function notifications()
    {
        $notifModel = new Notification();
        $notifications = $notifModel->forUser(Auth::id(), 50);
        $db=Database::getInstance();
        $users=$db->query("SELECT id,name,email,role FROM users WHERE is_active=1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->view('admin/notifications', ['title'=>__('notifications'),'notifications'=>$notifications,'users'=>$users], 'dashboard');
    }

    public function sendNotification()
    {
        $this->verifyCsrfOrFail();
        $title=trim((string)$this->input('title',''));
        $message=trim((string)$this->input('message',''));
        $target=$this->input('target','all');
        $link=trim((string)$this->input('link',''));
        if($title==='' || mb_strlen($title)>200 || $message==='' || mb_strlen($message)>500){
            Session::flash('error','العنوان والرسالة مطلوبان وبحد أقصى 200 و500 حرف.');
            $this->redirect('/admin/notifications'); return;
        }
        $db=Database::getInstance();
        $params=[];
        if($target==='customers') $sql="SELECT id FROM users WHERE is_active=1 AND role='customer'";
        elseif($target==='providers') $sql="SELECT id FROM users WHERE is_active=1 AND role='provider'";
        elseif($target==='admins') $sql="SELECT id FROM users WHERE is_active=1 AND role='admin'";
        elseif(str_starts_with((string)$target,'user:')) { $uid=(int)substr($target,5); $sql="SELECT id FROM users WHERE is_active=1 AND id=:uid"; $params=['uid'=>$uid]; }
        else $sql="SELECT id FROM users WHERE is_active=1";
        $stmt=$db->prepare($sql); $stmt->execute($params); $recipients=$stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!$recipients){ Session::flash('error','لم يتم العثور على مستلمين صالحين.'); $this->redirect('/admin/notifications'); return; }
        $insert=$db->prepare("INSERT INTO notifications (user_id,title,message,link) VALUES (:uid,:title,:message,:link)");
        $db->beginTransaction();
        try { foreach($recipients as $uid){ $insert->execute(['uid'=>(int)$uid,'title'=>$title,'message'=>$message,'link'=>$link!==''?$link:null]); } $db->commit(); }
        catch(Throwable $e){ if($db->inTransaction()) $db->rollBack(); throw $e; }
        ActivityLog::log(Auth::id(),'send_notification',"إرسال إشعار إلى ".count($recipients)." مستخدم");
        Session::flash('success','تم إرسال الإشعار إلى '.count($recipients).' مستخدم.');
        $this->redirect('/admin/notifications');
    }

    // ---------------- Conversations (admin oversight) ----------------
    public function conversations()
    {
        Auth::requirePermission('view_conversations');
        $db = Database::getInstance();
        $conversations = $db->query("
            SELECT c.id, c.created_at,
                   sr.request_number, sr.id AS request_id,
                   cu.name AS customer_name,
                   pu.name AS provider_name,
                   (SELECT COUNT(*) FROM conversation_messages cm WHERE cm.conversation_id = c.id) AS message_count,
                   (SELECT MAX(cm2.created_at) FROM conversation_messages cm2 WHERE cm2.conversation_id = c.id) AS last_message_at
            FROM conversations c
            JOIN service_requests sr ON sr.id = c.request_id
            JOIN users cu ON cu.id = c.customer_id
            LEFT JOIN provider_profiles pp ON pp.id = c.provider_id
            LEFT JOIN users pu ON pu.id = pp.user_id
            ORDER BY last_message_at DESC, c.created_at DESC
            LIMIT 100
        ")->fetchAll(PDO::FETCH_ASSOC);
        $this->view('admin/conversations', ['title' => 'المحادثات', 'conversations' => $conversations], 'dashboard');
    }

    public function conversationView($id)
    {
        Auth::requirePermission('view_conversations');
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.id, c.created_at,
                   sr.request_number, sr.id AS request_id,
                   cu.name AS customer_name, cu.id AS customer_user_id,
                   pu.name AS provider_name,
                   pp.id AS provider_profile_id
            FROM conversations c
            JOIN service_requests sr ON sr.id = c.request_id
            JOIN users cu ON cu.id = c.customer_id
            LEFT JOIN provider_profiles pp ON pp.id = c.provider_id
            LEFT JOIN users pu ON pu.id = pp.user_id
            WHERE c.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => (int)$id]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conversation) { Session::flash('error', 'المحادثة غير موجودة'); $this->redirect('/admin/conversations'); return; }
        $messages = (new Conversation())->messages((int)$id);
        $this->view('admin/conversation_view', compact('conversation', 'messages') + ['title' => 'محادثة: ' . $conversation['request_number']], 'dashboard');
    }
}
