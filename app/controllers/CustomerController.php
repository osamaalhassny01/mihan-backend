<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/AI/ProblemAnalyzer.php';
require_once __DIR__ . '/../services/AI/RecommendationEngine.php';
require_once __DIR__ . '/../services/AI/AIServiceFactory.php';

class CustomerController extends Controller
{
    public function __construct() { Auth::requireRole('customer'); }

    public function dashboard()
    {
        $reqModel = new ServiceRequest();
        $requests = $reqModel->forCustomer(Auth::id());
        $stats = [
            'active'    => count(array_filter($requests, fn($r) => in_array($r['status'], ['new','pending','accepted','in_progress']))),
            'completed' => count(array_filter($requests, fn($r) => $r['status'] === 'completed')),
            'cancelled' => count(array_filter($requests, fn($r) => $r['status'] === 'cancelled')),
        ];
        $notifModel = new Notification();
        $notifications = $notifModel->forUser(Auth::id(), 5);

        $this->view('customer/dashboard', [
            'title' => __('dashboard'), 'requests' => array_slice($requests, 0, 5),
            'stats' => $stats, 'notifications' => $notifications,
        ], 'dashboard');
    }

    public function finance()
    {
        $db=Database::getInstance(); $uid=(int)Auth::id(); $currency=setting('currency','ريال');
        $q=$db->prepare("SELECT i.id,i.invoice_number,i.request_id,i.service_price,i.total_amount,i.commission_rate,i.payment_status,
            i.created_at,sr.request_number,sr.status request_status,
            COALESCE(pp.amount_paid,0) amount_paid,
            GREATEST(i.total_amount-COALESCE(pp.amount_paid,0),0) balance_due
            FROM invoices i JOIN service_requests sr ON sr.id=i.request_id
            JOIN users cu ON cu.id=sr.customer_id
            LEFT JOIN (SELECT invoice_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) amount_paid FROM payments GROUP BY invoice_id) pp ON pp.invoice_id=i.id
            WHERE sr.customer_id=:uid ORDER BY i.id DESC");
        $q->execute(['uid'=>$uid]); $invoices=$q->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../services/FinanceService.php';
        $finService = new FinanceService();
        $finSummary = $finService->customerSummary($uid);
        $stats = [
            'invoices'    => $finSummary['invoices_count'],
            'billed'      => $finSummary['gross_amount'],
            'paid'        => $finSummary['paid_amount'],
            'outstanding' => $finSummary['outstanding_amount'],
                    ];
        $payQ=$db->prepare("SELECT p.*,i.invoice_number,sr.request_number,pu.name provider_name FROM payments p
            JOIN invoices i ON i.id=p.invoice_id JOIN service_requests sr ON sr.id=i.request_id
            LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id
            WHERE sr.customer_id=:uid ORDER BY p.paid_at DESC,p.id DESC LIMIT 100");
        $payQ->execute(['uid'=>$uid]); $payments=$payQ->fetchAll(PDO::FETCH_ASSOC);
        $this->view('customer/finance',compact('invoices','stats','payments','currency')+['title'=>'المالية والفواتير'],'dashboard');
    }

    public function requests()
    {
        $requests = (new ServiceRequest())->forCustomer(Auth::id());
        $this->view('customer/requests', ['title' => __('my_requests'), 'requests' => $requests], 'dashboard');
    }

    public function createRequestForm()
    {
        Auth::requirePermission('create_request');
        $services = (new Service())->withCategory();
        $locations = (new Location())->active();
        $providerId = $this->input('provider_id');
        $targetProvider = null;

        if ($providerId) {
            $providerModel = new ProviderProfile();
            $targetProvider = $providerModel->fullProfile((int)$providerId);
            if ($targetProvider) {
                // Requesting a specific professional: narrow the service list
                // down to the services that professional actually offers.
                $offered = $providerModel->servicesFor((int)$providerId);
                $offeredIds = array_column($offered, 'service_id');
                if ($offeredIds) {
                    $services = array_values(array_filter($services, fn($s) => in_array($s['id'], $offeredIds)));
                }
            } else {
                $providerId = null; // invalid id in the URL, ignore silently
            }
        }

        $preselectedServiceId = $this->input('service_id');

        $this->view('customer/request_create', [
            'title' => __('new_request'), 'services' => $services, 'locations' => $locations,
            'providerId' => $providerId, 'targetProvider' => $targetProvider,
            'preselectedServiceId' => $preselectedServiceId,
        ], 'dashboard');
    }

    public function analyzeProblem()
    {
        $this->verifyCsrfOrFail();
        $text = $this->input('problem_text', '');
        if (trim($text) === '') $this->json(['matched' => false]);
        $result = ProblemAnalyzer::analyze($text, Auth::id());
        $this->json($result);
    }

    /**
     * AI Feature 3 - "مساعد مِهَن الذكي": a simple offline chat assistant.
     * Runs entirely on RuleBasedAIService (via ProblemAnalyzer) through the
     * same /customer/requests/analyze endpoint used by the request form, so
     * there is a single source of truth for the AI logic. No external API
     * (OpenAI) is required for this to work.
     */
    public function aiAssistant()
    {
        $this->view('customer/ai_assistant', ['title' => 'مساعد مِهَن الذكي'], 'dashboard');
    }

    public function aiChat()
    {
        $this->verifyCsrfOrFail();
        $text = trim((string)$this->input('message', ''));
        if ($text === '') {
            $this->json(['ok' => false, 'message' => 'اكتب رسالة أولًا.'], 422);
            return;
        }

        if (mb_strlen($text) > 800) {
            $this->json(['ok' => false, 'message' => 'الرسالة طويلة جدًا، الحد الأقصى 800 حرف.'], 422);
            return;
        }

        $injectionPatterns = [
            '/(?:ignore|disregard|forget)\s+(?:all\s+)?(?:previous|prior|system)\s+(?:instructions?|directives?|rules?)/i',
            '/(?:system\s*prompt|system\s*instruction|\<\<sys\>\>|\[inst\]|###\s*system)/i',
            '/you\s+are\s+now\s+(?:a|an|the)\b/i',
        ];
        $sanitized = preg_replace($injectionPatterns, '[FILTERED]', $text);

        $rateKey = 'aichat:' . (Auth::id() ?? $_SERVER['REMOTE_ADDR'] ?? 'guest');
        if (class_exists('RateLimiter') && RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->json(['ok' => false, 'message' => 'لقد تجاوزت معدل الأسئلة المسموح به مؤقتًا. يرجى الانتظار قليلًا.'], 429);
            return;
        }
        if (class_exists('RateLimiter')) {
            RateLimiter::hit($rateKey, 60);
        }

        $result = AIServiceFactory::chat([
            ['role' => 'system', 'content' => 'أنت مساعد مِهَن لخدمات مهنية محلية. كن مختصرًا وعمليًا. لا تخترع أسماء مقدمي خدمة أو أسعارًا أو أرقامًا. إذا كان السؤال يحتاج بيانات المنصة، اطلب استخدام وظائف المنصة المناسبة. ردودك استرشادية فقط ولا تمثل التزامًا تعاقديًا أو ماليًا.'],
            ['role' => 'user', 'content' => $sanitized]
        ], ['temperature' => 0.3, 'max_tokens' => 700]);

        $this->json(['ok' => true] + $result);
    }

    public function storeRequest()
    {
        $this->verifyCsrfOrFail();
        Auth::requirePermission('create_request');
        $v = new Validator($_POST);
        $v->validate([
            'service_id'  => 'required|numeric',
            'description' => 'required|min:5|max:1000',
        ]);
        if ($v->fails()) {
            Session::flash('error', 'الرجاء تعبئة الحقول المطلوبة بشكل صحيح');
            $this->redirect('/customer/requests/create');
            return;
        }

        $serviceModel = new Service();
        $service = $serviceModel->find((int)$this->input('service_id'));
        if (!$service || (int)$service['is_active'] !== 1) {
            Session::flash('error', 'الخدمة المختارة غير متاحة حاليًا');
            $this->redirect('/customer/requests/create');
            return;
        }

        $preferredDate = trim((string)$this->input('preferred_date', ''));
        $preferredTime = trim((string)$this->input('preferred_time', ''));
        if ($preferredDate !== '') {
            $dateObj = DateTime::createFromFormat('Y-m-d', $preferredDate);
            $dateErrors = DateTime::getLastErrors();
            if (!$dateObj || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $dateObj->format('Y-m-d') !== $preferredDate || $preferredDate < date('Y-m-d')) {
                Session::flash('error', 'تاريخ الموعد غير صالح أو يقع في الماضي');
                $this->redirect('/customer/requests/create');
                return;
            }
        }
        if ($preferredTime !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $preferredTime)) {
            Session::flash('error', 'وقت الموعد غير صالح');
            $this->redirect('/customer/requests/create');
            return;
        }

        $locationId = $this->input('location_id') ?: null;
        if ($locationId !== null && !(new Location())->find((int)$locationId)) {
            Session::flash('error', 'الموقع المختار غير صالح');
            $this->redirect('/customer/requests/create');
            return;
        }

        $reqModel = new ServiceRequest();
        $id = $reqModel->createRequest([
            'customer_id'    => Auth::id(),
            'service_id'     => (int)$service['id'],
            'location_id'    => $locationId,
            'description'    => $this->input('description'),
            'preferred_date' => $preferredDate ?: null,
            'preferred_time' => $preferredTime ?: null,
            'budget'         => $this->input('budget') ?: null,
            'notes'          => $this->input('notes'),
        ]);

        // AI Feature 1: rank providers for this request (rule-based) and notify top matches
        $engine = new RecommendationEngine();
        $ranked = $engine->recommend((int)$service['id'], $locationId, 5, $preferredDate ?: null, $preferredTime ?: null);
        $engine->logRecommendations(Auth::id(), $ranked, $id);

        $notifModel = new Notification();
        foreach (array_slice($ranked, 0, 3) as $p) {
            if (!empty($p['user_id'])) {
                $notifModel->notify($p['user_id'], 'طلب خدمة جديد', 'لديك طلب خدمة جديد قد يناسب تخصصك', '/provider/requests');
            }
        }

        ActivityLog::log(Auth::id(), 'create_request', 'إنشاء طلب خدمة جديد #' . $id);

        // If the customer came from a specific provider's profile ("طلب هذه
        // الخدمة"), try to send the request directly to that professional
        // instead of the generic ranked list — same eligibility checks as
        // the manual "assign" action, so nothing bypasses IDOR/coverage rules.
        $requestedProviderId = (int)($this->input('provider_id') ?: 0);
        if ($requestedProviderId > 0) {
            $freshRequest = $reqModel->find($id);
            if ($reqModel->providerEligibleForRequest($requestedProviderId, $freshRequest)) {
                $providerModel = new ProviderProfile();
                $provider = $providerModel->find($requestedProviderId);
                if ($provider && $reqModel->assignProvider($id, Auth::id(), $requestedProviderId)) {
                    $notifModel->notify($provider['user_id'], 'طلب خدمة موجّه إليك', 'اختارك أحد العملاء لتنفيذ طلب خدمة، الرجاء المراجعة', '/provider/requests');
                    Session::flash('success', 'تم إرسال طلبك مباشرة إلى مقدم الخدمة الذي اخترته، بانتظار رده');
                    $this->redirect('/customer/requests');
                    return;
                }
            }
            // Not eligible (e.g. doesn't cover that service/area) — fall back
            // to the normal ranked-recommendations flow below instead of failing.
        }

        Session::flash('success', 'تم إنشاء طلبك بنجاح، سيتم اقتراح أفضل مقدمي الخدمة لك');
        $this->redirect('/customer/requests/' . $id . '/recommendations');
    }

    public function recommendations($id)
    {
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        if (!$request || $request['customer_id'] != Auth::id()) { $this->redirect('/customer/requests'); return; }

        $engine = new RecommendationEngine();
        $ranked = $engine->recommend($request['service_id'], $request['location_id'], 10, $request['preferred_date'], $request['preferred_time']);

        $this->view('customer/recommendations', ['title' => 'أفضل مقدمي الخدمة لك', 'request' => $request, 'ranked' => $ranked], 'dashboard');
    }

    public function assignProvider($id)
    {
        $this->verifyCsrfOrFail();
        $providerId = (int) $this->input('provider_id');
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if (!$request || (int)$request['customer_id'] !== (int)Auth::id()) {
            Session::flash('error', 'هذا الطلب غير متاح لك');
            $this->redirect('/customer/requests');
            return;
        }

        if ($request['status'] !== 'pending' || !empty($request['provider_id'])) {
            Session::flash('error', 'لا يمكن اختيار مقدم خدمة لهذا الطلب في حالته الحالية');
            $this->redirect('/customer/requests');
            return;
        }

        if ($providerId <= 0 || !$reqModel->providerEligibleForRequest($providerId, $request)) {
            Session::flash('error', 'مقدم الخدمة المختار غير مؤهل لتنفيذ هذه الخدمة أو لا يغطي موقعها');
            $this->redirect('/customer/requests/' . $id . '/recommendations');
            return;
        }

        $providerModel = new ProviderProfile();
        $provider = $providerModel->find($providerId);
        if (!$provider) {
            Session::flash('error', 'مقدم الخدمة غير موجود');
            $this->redirect('/customer/requests/' . $id . '/recommendations');
            return;
        }

        if (!$reqModel->assignProvider((int)$id, (int)Auth::id(), $providerId)) {
            Session::flash('error', 'تعذر تعيين مقدم الخدمة؛ ربما تم تعيين الطلب بالفعل');
            $this->redirect('/customer/requests/' . $id . '/recommendations');
            return;
        }

        (new Notification())->notify($provider['user_id'], 'طلب خدمة موجّه إليك', 'اختارك أحد العملاء لتنفيذ طلب خدمة، الرجاء المراجعة', '/provider/requests');

        Session::flash('success', 'تم إرسال طلبك إلى مقدم الخدمة المختار بانتظار رده');
        $this->redirect('/customer/requests');
    }

    public function requestDetails($id)
    {
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        if (!$request || $request['customer_id'] != Auth::id()) { $this->redirect('/customer/requests'); return; }
        $history = $reqModel->history((int)$id);
        $review = (new Review())->first(['request_id' => (int)$id]);
        $invoice = (new Invoice())->forRequest((int)$id);
        $payments = $invoice ? (new Invoice())->payments((int)$invoice['id']) : [];
        $paidAmount = $invoice ? max(0, (float)$invoice['total_amount'] - (new Invoice())->balance((int)$invoice['id'])) : 0;
        $balanceAmount = $invoice ? (new Invoice())->balance((int)$invoice['id']) : 0;
        $this->view('customer/request_details', compact('request', 'history', 'review', 'invoice', 'payments', 'paidAmount', 'balanceAmount') + ['title' => $request['request_number']], 'dashboard');
    }

    public function cancelRequest($id)
    {
        $this->verifyCsrfOrFail();
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        if ($request && $request['customer_id'] == Auth::id()) {
            if ($reqModel->changeStatus((int)$id, 'cancelled', Auth::id())) {
                Session::flash('success', 'تم إلغاء الطلب');
            } else {
                Session::flash('error', 'لا يمكن إلغاء الطلب في حالته الحالية');
            }
        }
        $this->redirect('/customer/requests');
    }

    public function submitReview($id)
    {
        $this->verifyCsrfOrFail();
        if (!settingBool('reviews_enabled', true)) { Session::flash('error','التقييمات مغلقة حاليًا من إدارة المنصة.'); $this->redirect('/customer/requests/' . $id); return; }
        $reqModel = new ServiceRequest();
        $request = $reqModel->find((int)$id);
        $reviewModel = new Review();

        if (!$request || $request['customer_id'] != Auth::id()) { $this->redirect('/customer/requests'); return; }
        if ($request['status'] !== 'completed') { Session::flash('error', 'لا يمكن تقييم طلب غير مكتمل'); $this->redirect('/customer/requests/' . $id); return; }
        if ($reviewModel->existsForRequest((int)$id)) { Session::flash('error', 'لقد قمت بتقييم هذا الطلب مسبقًا'); $this->redirect('/customer/requests/' . $id); return; }

        // Require a paid invoice before allowing a review
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->forRequest((int)$id);
        if (!$invoice) { Session::flash('error', 'لا يمكن التقييم: لا توجد فاتورة لهذا الطلب بعد.'); $this->redirect('/customer/requests/' . $id); return; }
        if ($invoice['payment_status'] !== 'paid') { Session::flash('error', 'يجب سداد الفاتورة أولاً قبل تقييم الخدمة.'); $this->redirect('/customer/requests/' . $id); return; }

        $rawRating = $this->input('rating');
        if (!is_numeric($rawRating) || (int)$rawRating < 1 || (int)$rawRating > 5) {
            Session::flash('error', 'التقييم يجب أن يكون رقمًا بين 1 و 5 نجوم');
            $this->redirect('/customer/requests/' . $id);
            return;
        }
        $rating = (int)$rawRating;
        $reviewModel->insert([
            'request_id'  => $id,
            'customer_id' => Auth::id(),
            'provider_id' => $request['provider_id'],
            'rating'      => $rating,
            'comment'     => $this->input('comment'),
        ]);
        (new ProviderProfile())->recalculateRating((int)$request['provider_id']);
        (new Notification())->notify(
            (new ProviderProfile())->find($request['provider_id'])['user_id'],
            'تقييم جديد', 'قام عميل بتقييم خدمتك', '/provider/reviews'
        );
        ActivityLog::log(Auth::id(), 'submit_review', "تقييم الطلب #$id");
        Session::flash('success', 'شكرًا لتقييمك');
        $this->redirect('/customer/requests/' . $id);
    }


public function conversation($id)
{
    if (!settingBool('internal_messaging_enabled', true)) { Session::flash('error','التواصل الداخلي متوقف حاليًا من إدارة المنصة.'); $this->redirect('/customer/requests/'.$id); return; }
    $reqModel=new ServiceRequest(); $request=$reqModel->fullDetails((int)$id);
    if(!$request || (int)$request['customer_id'] !== (int)Auth::id() || empty($request['provider_id'])) { Session::flash('error','لا يمكن فتح محادثة لهذا الطلب حاليًا.'); $this->redirect('/customer/requests'); return; }
    $conversationModel=new Conversation(); $conversation=$conversationModel->ensureForRequest($request);
    if(!$conversation){Session::flash('error','تعذر تجهيز قناة التواصل لهذا الطلب.');$this->redirect('/customer/requests/'.$id);return;}
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $this->verifyCsrfOrFail();
        $message=trim((string)$this->input('message',''));
        if(!$conversationModel->send((int)$conversation['id'],(int)Auth::id(),$message)) Session::flash('error','تعذر إرسال الرسالة.');
        else {
            $provider=(new ProviderProfile())->find((int)$request['provider_id']);
            if($provider) (new Notification())->notify((int)$provider['user_id'],'رسالة جديدة من العميل','لديك رسالة جديدة مرتبطة بالطلب '.$request['request_number'],'/provider/requests/'.$id.'/conversation');
        }
        $this->redirect('/customer/requests/'.$id.'/conversation'); return;
    }
    $conversationModel->markRead((int)$conversation['id'],(int)Auth::id()); $messages=$conversationModel->messages((int)$conversation['id']);
    $this->view('shared/conversation',compact('request','conversation','messages')+['title'=>'التواصل مع مقدم الخدمة','backUrl'=>'/customer/requests/'.$id,'audience'=>'العميل'], 'dashboard');
}

public function payInvoice($id)
{
    $this->verifyCsrfOrFail();
    if(!settingBool('platform_payment_required', true)){Session::flash('error','الدفع عبر المنصة غير متاح حاليًا.');$this->redirect('/customer/finance');return;}
    $invoice=(new Invoice())->details((int)$id);
    if(!$invoice || (int)$invoice['customer_id']!==(int)Auth::id()){Session::flash('error','الفاتورة غير متاحة لك.');$this->redirect('/customer/finance');return;}
    $method=$this->input('method','simulated_wallet');
    if(!in_array($method,['simulated_card','simulated_wallet'],true)){Session::flash('error','طريقة الدفع غير صالحة.');$this->redirect('/customer/requests/'.$invoice['request_id']);return;}
    $balance=(new Invoice())->balance((int)$id); if($balance<=0.0001){Session::flash('success','الفاتورة مدفوعة بالفعل.');$this->redirect('/customer/requests/'.$invoice['request_id']);return;}
    if(!(new Invoice())->recordPayment((int)$id,$method,$balance,'MIHAN-PAY-'.date('YmdHis'),'دفع العميل عبر منصة مِهَن')){Session::flash('error','تعذر إتمام الدفع المحاكى.');$this->redirect('/customer/requests/'.$invoice['request_id']);return;}
    if(!empty($invoice['provider_id'])){ $provider=(new ProviderProfile())->find((int)$invoice['provider_id']); if($provider)(new Notification())->notify((int)$provider['user_id'],'تم الدفع عبر المنصة','تم سداد الفاتورة '.$invoice['invoice_number'].' عبر منصة مِهَن','/provider/finance','payment'); }
    ActivityLog::log(Auth::id(),'customer_payment','دفع الفاتورة #'.$id.' عبر المنصة'); Session::flash('success','تم تسجيل الدفع عبر منصة مِهَن واحتساب مستحق مقدم الخدمة.'); $this->redirect('/customer/requests/'.$invoice['request_id']);
}

    public function preArrivalAssistant($id)
    {
        $reqModel = new ServiceRequest();
        $request = $reqModel->fullDetails((int)$id);
        if (!$request || (int)$request['customer_id'] !== (int)Auth::id()) {
            Session::flash('error', 'الطلب غير موجود أو لا تملك صلاحية الوصول إليه');
            $this->redirect('/customer/requests'); return;
        }
        if (!in_array($request['status'], ['pending','accepted'], true)) {
            Session::flash('error', 'المساعد الوقائي قبل الوصول متاح أثناء انتظار مقدم الخدمة فقط');
            $this->redirect('/customer/requests/' . $id); return;
        }
        $answer = null; $meta = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfOrFail();
            $message = trim((string)$this->input('message',''));
            if ($message === '') {
                Session::flash('error', 'اكتب وصفًا للمشكلة أولًا');
            } else {
                $system = 'أنت مساعد وقائي فوري لمنصة مِهَن. مهمتك مساعدة العميل على تقليل الضرر مؤقتًا إلى أن يصل مقدم الخدمة. أعطِ خطوات بسيطة وآمنة وقابلة للتنفيذ، مرتبة 1-2-3. في تسرب الماء ركّز أولًا على إغلاق محبس الماء المناسب إن كان ذلك آمنًا، إبعاد الأجهزة والكهرباء عن الماء، احتواء الماء بالمناشف/الوعاء، وعدم محاولة إصلاح الكسر بأدوات خطرة. لا تطلب من العميل فك تمديدات أو تنفيذ أعمال كهرباء أو غاز. إذا كان هناك خطر صعق أو غاز أو فيضان شديد فالأولوية لإخلاء المكان وطلب الطوارئ/مختص. لا تدّعِ أنك ترى المكان. لا تعطِ رقم طوارئ غير معروف. اختم بما يجب إبلاغ السباك به. هذه المساعدة مؤقتة وليست بديلًا عن الفني.';
                $res = AIServiceFactory::chat([['role'=>'system','content'=>$system],['role'=>'user','content'=>'[MIHAN_PREARRIVAL]
الخدمة: '.($request['service_name_ar']??'')."\nوصف الطلب: ".($request['description']??'')."\nرسالة العميل الآن: ".$message]], ['temperature'=>0.15,'max_tokens'=>700]);
                $answer=$res['text']; $meta=$res;
            }
        }
        $this->view('customer/pre_arrival', ['title'=>'مساعد ما قبل وصول الفني','request'=>$request,'answer'=>$answer,'meta'=>$meta], 'dashboard');
    }

    public function complaints()
    {
        $complaints = (new Complaint())->forUser(Auth::id());
        $this->view('customer/complaints', ['title' => __('complaints'), 'complaints' => $complaints], 'dashboard');
    }

    public function createComplaintForm()
    {
        $requests = (new ServiceRequest())->forCustomer(Auth::id());
        $againstUserId=(int)$this->input('against_user_id',0);
        $againstProvider=null;
        if($againstUserId>0){
            $db=Database::getInstance();
            $stmt=$db->prepare("SELECT u.id user_id,u.name,pp.id provider_id FROM users u JOIN provider_profiles pp ON pp.user_id=u.id WHERE u.id=:id AND u.role='provider' LIMIT 1");
            $stmt->execute(['id'=>$againstUserId]);
            $againstProvider=$stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        $this->view('customer/complaint_create', [
            'title'=>'شكوى جديدة','requests'=>$requests,'againstProvider'=>$againstProvider,
            'prefillSubject'=>(string)$this->input('subject','')
        ], 'dashboard');
    }

    public function storeComplaint()
    {
        $this->verifyCsrfOrFail();
        if (!settingBool('complaints_enabled', true)) { Session::flash('error','نظام الشكاوى مغلق حاليًا من إدارة المنصة.'); $this->redirect('/customer/complaints'); return; }
        $v = new Validator($_POST);
        $v->validate(['subject' => 'required|min:5|max:200', 'description' => 'required|min:10|max:1000']);
        if ($v->fails()) { Session::flash('error', 'الرجاء تعبئة الحقول بشكل صحيح'); $this->redirect('/customer/complaints/create'); return; }

        $requestId = $this->input('request_id') ?: null;
        $againstUser = null;
        $againstUserId=(int)$this->input('against_user_id',0);
        if($againstUserId>0){
            $db=Database::getInstance();
            $stmt=$db->prepare("SELECT id FROM users WHERE id=:id AND role='provider' AND is_active=1 LIMIT 1");
            $stmt->execute(['id'=>$againstUserId]);
            $againstUser=$stmt->fetchColumn() ?: null;
            if(!$againstUser){ Session::flash('error','مقدم الخدمة المحدد غير صالح'); $this->redirect('/customer/complaints/create'); return; }
        }
        if ($requestId) {
            $req = (new ServiceRequest())->find((int)$requestId);
            if (!$req || (int)$req['customer_id'] !== (int)Auth::id()) {
                Session::flash('error', 'لا يمكنك إنشاء شكوى مرتبطة بطلب لا يخصك');
                $this->redirect('/customer/complaints/create');
                return;
            }
            if ($req['provider_id']) {
                $providerRow = (new ProviderProfile())->find($req['provider_id']);
                $againstUser = $providerRow['user_id'] ?? null;
            }
        }

        $id = (new Complaint())->createComplaint([
            'created_by' => Auth::id(), 'against_user_id' => $againstUser, 'request_id' => $requestId,
            'subject' => $this->input('subject'), 'description' => $this->input('description'),
        ]);
        ActivityLog::log(Auth::id(), 'create_complaint', "إنشاء شكوى #$id");
        Session::flash('success', 'تم إرسال شكواك وسيتم مراجعتها من قبل الإدارة');
        $this->redirect('/customer/complaints');
    }

    public function complaintDetails($id)
    {
        $complaintModel = new Complaint();
        $complaint = $complaintModel->find((int)$id);
        if (!$complaint || $complaint['created_by'] != Auth::id()) { $this->redirect('/customer/complaints'); return; }
        $messages = $complaintModel->messages((int)$id);
        $this->view('customer/complaint_details', compact('complaint', 'messages') + ['title' => $complaint['complaint_number']], 'dashboard');
    }

    public function replyComplaint($id)
    {
        $this->verifyCsrfOrFail();
        $complaintModel = new Complaint();
        $complaint = $complaintModel->find((int)$id);
        if ($complaint && $complaint['created_by'] == Auth::id()) {
            $complaintModel->addMessage((int)$id, Auth::id(), $this->input('message'));
        }
        $this->redirect('/customer/complaints/' . $id);
    }

    public function notifications()
    {
        $notifModel = new Notification();
        $notifModel->markAllRead(Auth::id());
        $notifications = $notifModel->forUser(Auth::id(), 50);
        $this->view('customer/notifications', ['title' => __('notifications'), 'notifications' => $notifications], 'dashboard');
    }

    public function profile()
    {
        $user = Auth::user();
        $this->view('customer/profile', ['title' => __('profile'), 'user' => $user], 'dashboard');
    }

    public function updateProfile()
    {
        $this->verifyCsrfOrFail();
        $userModel = new User();
        $userModel->update(Auth::id(), [
            'name' => $this->input('name'),
            'phone' => $this->input('phone'),
        ]);
        Session::set('user_name', $this->input('name'));
        Session::flash('success', 'تم تحديث الملف الشخصي');
        $this->redirect('/customer/profile');
    }
}
