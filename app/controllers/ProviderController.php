<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Payout.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/AI/AIServiceFactory.php';

class ProviderController extends Controller
{
    private ProviderProfile $providerModel;
    private array $profile;

    public function __construct()
    {
        Auth::requireRole('provider');
        $this->providerModel = new ProviderProfile();
        $this->profile = $this->providerModel->findByUser(Auth::id());
    }

    public function dashboard()
    {
        $reqModel = new ServiceRequest();
        $requests = $reqModel->forProvider($this->profile['id']);
        $stats = [
            'total'     => count($requests),
            'pending'   => count(array_filter($requests, fn($r) => $r['status'] === 'pending')),
            'accepted'  => count(array_filter($requests, fn($r) => in_array($r['status'], ['accepted','in_progress']))),
            'completed' => count(array_filter($requests, fn($r) => $r['status'] === 'completed')),
        ];
        require_once __DIR__ . '/../services/FinanceService.php';
        $finSummary = (new FinanceService())->providerSummary((int)$this->profile['id']);
        $financeStats = [
            'gross' => $finSummary['gross_amount'],
            'collected_net' => $finSummary['provider_net_amount'],
            'pending_net' => round($finSummary['outstanding_amount'] * (1 - (new Invoice())->commissionRate()), 2),
            'received_amount' => $finSummary['paid_amount'],
            'invoices' => $finSummary['invoices_count'],
        ];
        $this->view('provider/dashboard', [
            'title' => __('dashboard'), 'profile' => $this->profile, 'stats' => $stats,
            'requests' => array_slice($requests, 0, 5),
            'finance' => $financeStats,
        ], 'dashboard');
    }

public function finance()
{
    $db=Database::getInstance(); $pid=(int)$this->profile['id']; $currency=setting('currency','ريال');
    $q=$db->prepare("SELECT i.id,i.invoice_number,i.request_id,i.service_price,i.commission_rate,i.commission_amount,i.provider_net,i.total_amount,i.payment_status,
        sr.request_number,sr.status request_status,cu.name customer_name,
        COALESCE(pp.amount_paid,0) amount_paid,
        GREATEST(i.total_amount-COALESCE(pp.amount_paid,0),0) balance_due
        FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id
        LEFT JOIN (SELECT invoice_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) amount_paid FROM payments GROUP BY invoice_id) pp ON pp.invoice_id=i.id
        WHERE sr.provider_id=:pid ORDER BY i.id DESC");
    $q->execute(['pid'=>$pid]); $invoices=$q->fetchAll(PDO::FETCH_ASSOC);
    require_once __DIR__ . '/../services/FinanceService.php';
    $finSummary=(new FinanceService())->providerSummary($pid);
    $payoutModel=new Payout(); $paidOut=$payoutModel->paidTotalForProvider($pid); $available=$payoutModel->availableForProvider($pid);
    $stats=['invoices'=>$finSummary['invoices_count'],'gross'=>$finSummary['gross_amount'],'collected'=>$finSummary['paid_amount'],'outstanding'=>$finSummary['outstanding_amount'],'commission_on_collected'=>$finSummary['commission_amount'],'provider_net_collected'=>$finSummary['provider_net_amount'],'paid_out'=>$paidOut,'available_payout'=>$available];
    $paymentsQ=$db->prepare("SELECT p.*, i.invoice_number, sr.request_number, cu.name customer_name FROM payments p JOIN invoices i ON i.id=p.invoice_id JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id WHERE sr.provider_id=:pid AND p.status='paid' ORDER BY p.paid_at DESC,p.id DESC LIMIT 100");
    $paymentsQ->execute(['pid'=>$pid]); $payments=$paymentsQ->fetchAll(PDO::FETCH_ASSOC);
    $payouts=$payoutModel->forProvider($pid);
    $this->view('provider/finance',compact('invoices','stats','payments','payouts','currency')+['title'=>'المالية والمدفوعات'],'dashboard');
}

    public function exportFinanceCsv()
    {
        $db=Database::getInstance(); $pid=(int)$this->profile['id'];
        $q=$db->prepare("SELECT i.invoice_number,sr.request_number,cu.name customer_name,i.service_price,i.total_amount,i.commission_amount,i.provider_net,i.payment_status,
            COALESCE(p.paid_amount,0) paid_amount,GREATEST(i.total_amount-COALESCE(p.paid_amount,0),0) balance_due
            FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id
            LEFT JOIN (SELECT invoice_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) paid_amount FROM payments GROUP BY invoice_id) p ON p.invoice_id=i.id
            WHERE sr.provider_id=:pid ORDER BY i.id DESC");
        $q->execute(['pid'=>$pid]); $rows=$q->fetchAll(PDO::FETCH_ASSOC);
        $filename='mihan-provider-finance-'.date('Y-m-d').'.csv';
        header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="'.$filename.'"');
        $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
        fputcsv($out,['الفاتورة','الطلب','العميل','قيمة الخدمة','الإجمالي','عمولة المنصة','صافي مقدم الخدمة','الحالة','المدفوع','المتبقي']);
        foreach($rows as $r) fputcsv($out,$r);
        fclose($out); exit;
    }

    public function requests()
    {
        $requests = (new ServiceRequest())->forProvider($this->profile['id']);
        $this->view('provider/requests', ['title' => 'الطلبات الواردة', 'requests' => $requests], 'dashboard');
    }

    public function requestDetails($id)
    {
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        if (!$request || !$reqModel->providerCanAccess((int)$this->profile['id'], $request)) {
            Session::flash('error', 'هذا الطلب غير متاح لك');
            $this->redirect('/provider/requests');
            return;
        }
        $history = $reqModel->history((int)$id);
        $invoice = (new Invoice())->forRequest((int)$id);
        $payments = $invoice ? (new Invoice())->payments((int)$invoice['id']) : [];
        $this->view('provider/request_details', compact('request', 'history', 'invoice', 'payments') + ['title' => $request['request_number']], 'dashboard');
    }

    public function acceptRequest($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('provide_service');
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if (!$request || !$reqModel->providerCanAccess((int)$this->profile['id'], $request) || $request['status'] !== 'pending') {
            Session::flash('error', 'هذا الطلب غير متاح لك أو لم يعد متاحًا للقبول');
            $this->redirect('/provider/requests');
            return;
        }
        if ($reqModel->changeStatus((int)$id, 'accepted', Auth::id(), $this->profile['id'])) {
            (new Notification())->notify($request['customer_id'], 'تم قبول طلبك', 'قام مقدم الخدمة بقبول طلبك وسيتواصل معك قريبًا', '/customer/requests/' . $id);
            ActivityLog::log(Auth::id(), 'accept_request', "قبول الطلب #$id");
            Session::flash('success', 'تم قبول الطلب');
        } else {
            Session::flash('error', 'لا يمكن قبول هذا الطلب في حالته الحالية');
        }
        $this->redirect('/provider/requests');
    }

    public function rejectRequest($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('provide_service');
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if (!$request || !$reqModel->providerCanAccess((int)$this->profile['id'], $request) || $request['status'] !== 'pending') { Session::flash('error', 'هذا الطلب غير متاح لك أو لم يعد متاحًا للرفض'); $this->redirect('/provider/requests'); return; }
        if ($reqModel->rejectAndRequeue((int)$id, Auth::id(), (int)$this->profile['id'])) {
            (new Notification())->notify($request['customer_id'], 'تم رفض الطلب من مقدم الخدمة', 'اعتذر مقدم الخدمة عن تنفيذ الطلب، وأصبح الطلب متاحًا لاختيار مقدم خدمة آخر', '/customer/requests/' . $id . '/recommendations');
            ActivityLog::log(Auth::id(), 'reject_request', "رفض وإعادة طرح الطلب #$id");
            Session::flash('success', 'تم رفض الطلب وإعادته لقائمة الطلبات المتاحة');
        } else {
            Session::flash('error', 'تعذر رفض الطلب؛ ربما تغيرت حالته بالفعل');
        }
        $this->redirect('/provider/requests');
    }

    public function startRequest($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('provide_service');
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if (!$request || (int)$request['provider_id'] !== (int)$this->profile['id']) { Session::flash('error', 'هذا الطلب غير متاح لك'); $this->redirect('/provider/requests'); return; }
        if ($request && $reqModel->changeStatus((int)$id, 'in_progress', Auth::id())) {
            (new Notification())->notify($request['customer_id'], 'بدأ تنفيذ الطلب', 'بدأ مقدم الخدمة بتنفيذ طلبك', '/customer/requests/' . $id);
            Session::flash('success', 'تم تحديث الحالة إلى قيد التنفيذ');
        }
        $this->redirect('/provider/requests/' . $id);
    }

    public function recordCashPayment($id)
    {
        Session::flash('error','الدفع النقدي المباشر لمقدم الخدمة غير مسموح. يجب أن يتم الدفع عبر منصة مِهَن.');
        $this->redirect('/provider/requests/' . (int)$id);
    }

    public function completeRequest($id)
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('provide_service');
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if (!$request) { $this->redirect('/provider/requests'); return; }
        if ((int)$request['provider_id'] !== (int)$this->profile['id']) {
            Session::flash('error', 'هذا الطلب غير متاح لك');
            $this->redirect('/provider/requests'); return;
        }

        $rawFinalPrice = $this->input('final_price', $request['budget'] ?: 0);
        if ($rawFinalPrice === '' || !is_numeric($rawFinalPrice)) {
            Session::flash('error', 'يرجى إدخال سعر نهائي صحيح');
            $this->redirect('/provider/requests/' . $id);
            return;
        }
        $finalPrice = round((float)$rawFinalPrice, 2);
        if ($finalPrice < 0 || $finalPrice > 9999999999.99) {
            Session::flash('error', 'السعر النهائي خارج النطاق المسموح');
            $this->redirect('/provider/requests/' . $id);
            return;
        }
        $result = $reqModel->completeWithInvoice((int)$id, (int)$this->profile['id'], $finalPrice, (int)Auth::id());
        if ($result['ok']) {
            if (settingBool('notify_on_completion', true)) {
                (new Notification())->notify(
                    $result['customer_id'],
                    'تم إكمال الخدمة وإصدار الفاتورة',
                    'تم إكمال طلبك بنجاح وصدرت الفاتورة ' . $result['invoice_number'] . '، يمكنك الآن تقييم مقدم الخدمة.',
                    '/customer/requests/' . $id
                );
            }
            ActivityLog::log(Auth::id(), 'complete_request', "إكمال الطلب #$id وإصدار الفاتورة " . $result['invoice_number']);
            Session::flash('success', 'تم إكمال الطلب وإصدار الفاتورة بنجاح');
        } else {
            Session::flash('error', 'تعذر إكمال الطلب: ' . ($result['error'] ?? 'خطأ غير متوقع'));
        }
        $this->redirect('/provider/requests/' . $id);
    }


public function conversation($id)
{
    if (!settingBool('internal_messaging_enabled', true)) { Session::flash('error','التواصل الداخلي متوقف حاليًا من إدارة المنصة.'); $this->redirect('/provider/requests/'.$id); return; }
    $reqModel=new ServiceRequest(); $request=$reqModel->fullDetails((int)$id);
    if(!$request || !$reqModel->providerCanAccess((int)$this->profile['id'],$request) || empty($request['provider_id'])) { Session::flash('error','هذا الطلب غير متاح لك.'); $this->redirect('/provider/requests'); return; }
    $conversationModel=new Conversation(); $conversation=$conversationModel->ensureForRequest($request);
    if(!$conversation){Session::flash('error','تعذر تجهيز قناة التواصل لهذا الطلب.');$this->redirect('/provider/requests/'.$id);return;}
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $this->verifyCsrfOrFail(); $message=trim((string)$this->input('message',''));
        if(!$conversationModel->send((int)$conversation['id'],(int)Auth::id(),$message)) Session::flash('error','تعذر إرسال الرسالة.');
        else (new Notification())->notify((int)$request['customer_id'],'رسالة من مقدم الخدمة','لديك رسالة جديدة مرتبطة بالطلب '.$request['request_number'],'/customer/requests/'.$id.'/conversation');
        $this->redirect('/provider/requests/'.$id.'/conversation'); return;
    }
    $conversationModel->markRead((int)$conversation['id'],(int)Auth::id()); $messages=$conversationModel->messages((int)$conversation['id']);
    $this->view('shared/conversation',compact('request','conversation','messages')+['title'=>'التواصل مع العميل','backUrl'=>'/provider/requests/'.$id,'audience'=>'مقدم الخدمة'], 'dashboard');
}

    public function fieldAssistant($id)
    {
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        if (!$request || (int)$request['provider_id'] !== (int)$this->profile['id']) {
            Session::flash('error', 'هذا الطلب غير متاح لك');
            $this->redirect('/provider/requests'); return;
        }
        if (!in_array($request['status'], ['accepted','in_progress'], true)) {
            Session::flash('error', 'المساعد المهني متاح بعد قبول الطلب وحتى إكماله');
            $this->redirect('/provider/requests/' . $id); return;
        }
        $answer=null; $meta=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $this->verifyCsrfOrFail();
            $message=trim((string)$this->input('message',''));
            if ($message==='') { Session::flash('error','صف الحالة التي وجدتها في الموقع'); }
            else {
                $system='أنت MIHAN Field Assistant، مساعد مهني لمقدم الخدمة أثناء تنفيذ طلب حقيقي. استخدم سياق الطلب ورسالة الفني فقط. قدّم تشخيصًا احتماليًا، خطوات فحص آمنة، ترتيب العمل، أدوات/مواد محتملة، وما الذي يجب عدم فعله. لا تخترع قياسات أو صورًا. في الكهرباء والغاز والمياه حذّر من المخاطر: عزل مصدر الخطر أولًا وعدم تنفيذ عمل غير آمن. لا تستبدل حكم الفني ولا تعطي ضمانًا للنتيجة. اجعل الإجابة عملية ومختصرة بعناوين: التقييم الأولي، الخطوات، الأدوات المحتملة، متى أتوقف/أطلب مختصًا.';
                $res=AIServiceFactory::chat([['role'=>'system','content'=>$system],['role'=>'user','content'=>'[MIHAN_FIELD]
الخدمة: '.($request['service_name_ar']??'')."\nوصف العميل: ".($request['description']??'')."\nالمنطقة: ".($request['location_name_ar']??'')."\nحالة الطلب: ".($request['status']??'')."\nملاحظة الفني: ".$message]], ['temperature'=>0.2,'max_tokens'=>900]);
                $answer=$res['text']; $meta=$res;
            }
        }
        $this->view('provider/field_assistant', ['title'=>'المساعد المهني أثناء التنفيذ','request'=>$request,'answer'=>$answer,'meta'=>$meta], 'dashboard');
    }

    public function reviews()
    {
        $reviews = (new Review())->forProvider($this->profile['id']);
        $this->view('provider/reviews', ['title' => __('reviews'), 'reviews' => $reviews], 'dashboard');
    }

    public function replyReview($id)
    {
        $this->verifyCsrfOrFail();
        $reviewModel = new Review();
        $review = $reviewModel->find((int)$id);
        $reply = trim((string)$this->input('reply', ''));
        if (!$review || (int)$review['provider_id'] !== (int)$this->profile['id']) {
            Session::flash('error', 'التقييم غير متاح لك');
            $this->redirect('/provider/reviews');
            return;
        }
        if ($reply === '') {
            Session::flash('error', 'اكتب ردًا قبل الإرسال');
            $this->redirect('/provider/reviews');
            return;
        }
        if (mb_strlen($reply) > 1000) $reply = mb_substr($reply, 0, 1000);
        $reviewModel->update((int)$id, ['provider_reply' => $reply]);
        Session::flash('success', 'تم إرسال ردك على التقييم بنجاح');
        $this->redirect('/provider/reviews');
    }

    public function notifications()
    {
        $notifModel = new Notification();
        $notifModel->markAllRead(Auth::id());
        $notifications = $notifModel->forUser(Auth::id(), 50);
        $this->view('provider/notifications', ['title' => __('notifications'), 'notifications' => $notifications], 'dashboard');
    }

    public function profile()
    {
        $services = $this->providerModel->servicesFor($this->profile['id']);
        $locations = $this->providerModel->locationsFor($this->profile['id']);
        $portfolio = $this->providerModel->portfolioFor($this->profile['id']);
        $availability = $this->providerModel->availabilityFor($this->profile['id']);
        $allServices = (new Service())->withCategory();
        $allLocations = (new Location())->active();
        $categories = (new Category())->active();
        $this->view('provider/profile', compact('services', 'locations', 'portfolio', 'availability', 'allServices', 'allLocations', 'categories')
            + ['title' => __('profile'), 'profile' => $this->profile, 'user' => Auth::user()], 'dashboard');
    }

    public function updateProfile()
    {
        $this->verifyCsrfOrFail();
        $this->providerModel->update($this->profile['id'], [
            'category_id'       => $this->input('category_id') ?: null,
            'profession'        => $this->input('profession'),
            'specialization'    => $this->input('specialization'),
            'bio'               => $this->input('bio'),
            'experience_years'  => (int) $this->input('experience_years', 0),
            'is_available'      => $this->input('is_available') ? 1 : 0,
        ]);
        (new User())->update(Auth::id(), ['name' => $this->input('name'), 'phone' => $this->input('phone')]);
        Session::set('user_name', $this->input('name'));
        Session::flash('success', 'تم تحديث ملفك المهني');
        $this->redirect('/provider/profile');
    }

    public function addService()
    {
        $this->verifyCsrfOrFail();
        $this->providerModel->execute(
            "INSERT INTO provider_services (provider_id, service_id, price_from, price_to) VALUES (:p,:s,:f,:t)
             ON DUPLICATE KEY UPDATE price_from = :f2, price_to = :t2",
            ['p' => $this->profile['id'], 's' => $this->input('service_id'),
             'f' => $this->input('price_from', 0), 't' => $this->input('price_to', 0),
             'f2' => $this->input('price_from', 0), 't2' => $this->input('price_to', 0)]
        );
        Session::flash('success', 'تم إضافة/تحديث الخدمة');
        $this->redirect('/provider/profile');
    }

    public function addLocation()
    {
        $this->verifyCsrfOrFail();
        $this->providerModel->execute(
            "INSERT IGNORE INTO provider_locations (provider_id, location_id) VALUES (:p,:l)",
            ['p' => $this->profile['id'], 'l' => $this->input('location_id')]
        );
        Session::flash('success', 'تم إضافة منطقة العمل');
        $this->redirect('/provider/profile');
    }

    public function addAvailability()
    {
        $this->verifyCsrfOrFail();

        $day = (int)$this->input('day_of_week', -1);
        $start = trim((string)$this->input('start_time', ''));
        $end = trim((string)$this->input('end_time', ''));

        if ($day < 0 || $day > 6 || $start === '' || $end === '' || $start >= $end) {
            Session::flash('error', 'يرجى تحديد يوم ووقت بداية ونهاية صحيحين');
            $this->redirect('/provider/profile');
            return;
        }

        if (!$this->providerModel->addAvailability((int)$this->profile['id'], $day, $start, $end)) {
            Session::flash('error', 'تعذر إضافة فترة التوفر؛ تأكد من البيانات أو أنها غير مكررة');
            $this->redirect('/provider/profile');
            return;
        }

        Session::flash('success', 'تمت إضافة فترة التوفر بنجاح');
        $this->redirect('/provider/profile');
    }

    public function deleteAvailability($id)
    {
        $this->verifyCsrfOrFail();
        $deleted = $this->providerModel->deleteAvailability((int)$this->profile['id'], (int)$id);
        Session::flash($deleted ? 'success' : 'error', $deleted ? 'تم حذف فترة التوفر' : 'فترة التوفر غير موجودة');
        $this->redirect('/provider/profile');
    }

    public function uploadPortfolio()
    {
        $this->verifyCsrfOrFail();
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'الرجاء اختيار صورة صحيحة');
            $this->redirect('/provider/profile'); return;
        }
        $path = $this->handleUpload($_FILES['image'], 'portfolio');
        if ($path) {
            $this->providerModel->execute(
                "INSERT INTO provider_portfolio (provider_id, image_path, caption) VALUES (:p, :img, :c)",
                ['p' => $this->profile['id'], 'img' => $path, 'c' => $this->input('caption', '')]
            );
            Session::flash('success', 'تم رفع الصورة بنجاح');
        } else {
            Session::flash('error', 'نوع الملف غير مسموح به');
        }
        $this->redirect('/provider/profile');
    }

    public function uploadPhoto()
    {
        $this->verifyCsrfOrFail();
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'الرجاء اختيار صورة صحيحة');
            $this->redirect('/provider/profile'); return;
        }
        $path = $this->handleUpload($_FILES['photo'], 'profiles');
        if ($path) {
            (new User())->update(Auth::id(), ['profile_photo' => $path]);
            Session::flash('success', 'تم تحديث الصورة الشخصية');
        } else {
            Session::flash('error', 'نوع الملف غير مسموح به');
        }
        $this->redirect('/provider/profile');
    }

    /**
     * Secure file upload handler:
     * - validates extension AND real MIME type (finfo, not client-supplied)
     * - enforces max size
     * - generates a random unique filename (never trusts user input)
     * - stores strictly under public/uploads/{profiles|portfolio}, never executable
     */
    private function handleUpload(array $file, string $subfolder): ?string
    {
        if ($file['size'] > UPLOAD_MAX_SIZE) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, UPLOAD_ALLOWED_MIME, true)) return null;

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir = __DIR__ . "/../../public/uploads/$subfolder";
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $destPath = "$destDir/$filename";

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return null;
        return url("/public/uploads/$subfolder/$filename");
    }
}
