<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payout.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../services/FinanceService.php';
require_once __DIR__ . '/../services/AnalyticsService.php';

class EmployeeController extends Controller
{
    public function __construct(){ Auth::requireRole('employee'); }

    public function dashboard()
    {
        Auth::requirePermission('view_dashboard'); $db=Database::getInstance(); $analytics=new AnalyticsService();
        $stats=[
            'requests'=>(int)$db->query("SELECT COUNT(*) FROM service_requests")->fetchColumn(),
            'pending'=>(int)$db->query("SELECT COUNT(*) FROM service_requests WHERE status IN ('new','pending')")->fetchColumn(),
            'providers'=>(int)$db->query("SELECT COUNT(*) FROM users WHERE role='provider' AND is_active=1")->fetchColumn(),
            'reviews'=>(int)$db->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
            'open_complaints'=>(int)$db->query("SELECT COUNT(*) FROM complaints WHERE status IN ('open','in_review')")->fetchColumn(),
        ];
        $this->view('employee/dashboard',['title'=>'لوحة موظف المنصة','stats'=>$stats,'recentRequests'=>(new ServiceRequest())->query("SELECT sr.*, cu.name customer_name FROM service_requests sr JOIN users cu ON cu.id=sr.customer_id ORDER BY sr.created_at DESC LIMIT 10"),'overview'=>$analytics->overview()],'dashboard');
    }

    public function requests(){Auth::requirePermission('manage_requests');$status=$this->input('status');$m=new ServiceRequest();$requests=$status?$m->where(['status'=>$status],'created_at DESC'):$m->query("SELECT sr.*,cu.name customer_name,pu.name provider_name,s.name_ar service_name_ar FROM service_requests sr JOIN users cu ON cu.id=sr.customer_id LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id JOIN services s ON s.id=sr.service_id ORDER BY sr.created_at DESC");$this->view('employee/requests',['title'=>'متابعة الطلبات','requests'=>$requests,'status'=>$status],'dashboard');}

    public function requestDetails($id){Auth::requirePermission('manage_requests');$m=new ServiceRequest();$request=$m->fullDetails((int)$id);if(!$request){$this->redirect('/employee/requests');return;}$history=$m->history((int)$id);$conversation=(new Conversation())->forRequest((int)$id);$this->view('employee/request_details',compact('request','history','conversation')+['title'=>$request['request_number']],'dashboard');}

    public function providers(){Auth::requirePermission('view_providers');$providers=(new ProviderProfile())->search([]);$this->view('employee/providers',['title'=>'مقدمو الخدمة','providers'=>$providers],'dashboard');}

    public function reviews(){Auth::requirePermission('manage_reviews');$db=Database::getInstance();$rows=$db->query("SELECT r.*,cu.name customer_name,pu.name provider_name,sr.request_number FROM reviews r JOIN users cu ON cu.id=r.customer_id JOIN provider_profiles pp ON pp.id=r.provider_id JOIN users pu ON pu.id=pp.user_id JOIN service_requests sr ON sr.id=r.request_id ORDER BY r.created_at DESC");$this->view('employee/reviews',['title'=>'تقييمات مقدمي الخدمة','reviews'=>$rows],'dashboard');}

    public function complaints(){Auth::requirePermission('manage_complaints');$rows=$this->queryComplaints();$this->view('employee/complaints',['title'=>'الشكاوى','complaints'=>$rows],'dashboard');}
    private function queryComplaints(){return (new Complaint())->query("SELECT c.*,u.name created_by_name,au.name against_name FROM complaints c JOIN users u ON u.id=c.created_by LEFT JOIN users au ON au.id=c.against_user_id ORDER BY c.created_at DESC");}
    public function complaintDetails($id){Auth::requirePermission('manage_complaints');$m=new Complaint();$complaint=$m->find((int)$id);if(!$complaint){$this->redirect('/employee/complaints');return;}$messages=$m->messages((int)$id);$this->view('employee/complaint_details',compact('complaint','messages')+['title'=>$complaint['complaint_number']],'dashboard');}
    public function replyComplaint($id){$this->verifyCsrfOrFail();Auth::requirePermission('manage_complaints');$m=new Complaint();$m->addMessage((int)$id,(int)Auth::id(),$this->input('message'));$c=$m->find((int)$id);if($c)(new Notification())->notify((int)$c['created_by'],'تحديث على شكواك','تمت إضافة رد من موظف منصة مِهَن','/customer/complaints/'.$id);ActivityLog::log(Auth::id(),'employee_reply_complaint','الرد على الشكوى #'.$id);$this->redirect('/employee/complaints/'.$id);}
    public function updateComplaintStatus($id){$this->verifyCsrfOrFail();Auth::requirePermission('manage_complaints');$status=$this->input('status');if(!in_array($status,['open','in_review','resolved','rejected','closed'],true)){Session::flash('error','حالة غير صالحة');$this->redirect('/employee/complaints/'.$id);return;}$m=new Complaint();$m->update((int)$id,['status'=>$status]);ActivityLog::log(Auth::id(),'employee_complaint_status','تغيير حالة الشكوى #'.$id.' إلى '.$status);Session::flash('success','تم تحديث حالة الشكوى.');$this->redirect('/employee/complaints/'.$id);}

    public function finance(){Auth::requirePermission('view_finance');$db=Database::getInstance();$summary=(new FinanceService())->platformSummary();$rows=$db->query("SELECT i.invoice_number,sr.request_number,cu.name customer_name,pu.name provider_name,i.total_amount,i.commission_amount,i.provider_net,i.payment_status,COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id=i.id AND p.status='paid'),0) paid_amount FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id ORDER BY i.created_at DESC LIMIT 100");$this->view('employee/finance',['title'=>'المتابعة المالية','summary'=>$summary,'invoices'=>$rows,'canPayout'=>Auth::can('manage_payouts')],'dashboard');}

    public function notifications(){ $notifications=(new Notification())->forUser((int)Auth::id(),50); $this->view('employee/notifications',['title'=>'إشعارات الموظف','notifications'=>$notifications],'dashboard'); }

    public function conversations(){Auth::requirePermission('view_conversations');$conversations=(new Conversation())->listForEmployee();$this->view('employee/conversations',['title'=>'محادثات الطلبات','conversations'=>$conversations],'dashboard');}
    public function conversation($id){Auth::requirePermission('view_conversations');$cm=new Conversation();$conversation=$cm->forRequest((int)$id);if(!$conversation){$this->redirect('/employee/conversations');return;}$messages=$cm->messages((int)$conversation['id']);$this->view('shared/conversation', ['title'=>'متابعة المحادثة','request'=>['id'=>$conversation['request_id'],'request_number'=>$conversation['request_number']],'conversation'=>$conversation,'messages'=>$messages,'backUrl'=>'/employee/conversations','audience'=>'موظف المنصة','readOnly'=>true], 'dashboard');}
}
