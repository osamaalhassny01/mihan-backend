<?php
require_once __DIR__ . '/../models/ServiceRequest.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/FcmService.php';

/** Shared web/mobile orchestration. Business rules live in ServiceRequest. */
class RequestWorkflowService
{
    public function quote(int $id, int $providerId, int $actor, float $price, string $details, ?string $visitAt): array
    {
        $result = (new ServiceRequest())->submitQuote($id, $providerId, $price, $details, $visitAt);
        if ($result['ok']) {
            $this->notify((int)$result['customer_id'], $id, 'عرض خدمة بانتظار موافقتك', 'راجع السعر وما يشمله العرض قبل الموافقة وبدء التنفيذ.', 'quote_submitted');
            $this->log($actor, 'submit_quote', $id);
        }
        return $result;
    }

    public function decide(int $id, int $customerId, int $quoteId, bool $approve): array
    {
        $result = (new ServiceRequest())->decideQuote($id, $customerId, $quoteId, $approve);
        if ($result['ok']) {
            $this->notifyProvider((int)$result['provider_id'], $id, $approve ? 'وافق العميل على العرض' : 'طلب العميل عرضًا جديدًا',
                $approve ? 'تم اعتماد السعر ونطاق العمل. يمكنك الآن بدء التنفيذ.' : 'لم يوافق العميل على العرض. يمكنك تقديم عرض جديد قبل بدء العمل.', 'quote_decided');
            $this->log($customerId, $approve ? 'approve_quote' : 'decline_quote', $id);
        }
        return $result;
    }

    public function finish(int $id, int $providerId, int $actor, string $notes): array
    {
        $result = (new ServiceRequest())->requestCompletion($id, $providerId, $actor, $notes);
        if ($result['ok']) {
            $this->notify((int)$result['customer_id'], $id, 'الخدمة بانتظار استلامك', 'أعلن مقدم الخدمة إنجاز العمل. راجع النتيجة ثم أكد الاستلام أو أرسل ملاحظاتك.', 'completion_requested');
            $this->log($actor, 'request_completion', $id);
        }
        return $result;
    }

    public function confirm(int $id, int $customerId): array
    {
        $model = new ServiceRequest();
        $result = $model->confirmCompletion($id, $customerId);
        if ($result['ok']) {
            $request = $model->find($id);
            $this->notifyProvider((int)$request['provider_id'], $id, 'أكد العميل استلام الخدمة', 'صدرت الفاتورة بالسعر المتفق عليه. يمكنك متابعة السداد من المستحقات.', 'request_completed');
            $this->notify($customerId, $id, 'تم الاستلام وإصدار الفاتورة', 'يمكنك الآن سداد الفاتورة داخل المنصة، ثم تقييم تجربتك.', 'request_completed');
            $this->log($customerId, 'confirm_completion', $id);
        }
        return $result;
    }

    public function rework(int $id, int $customerId, string $notes): array
    {
        $result = (new ServiceRequest())->requestRework($id, $customerId, $notes);
        if ($result['ok']) {
            $this->notifyProvider((int)$result['provider_id'], $id, 'ملاحظات قبل استلام الخدمة', 'راجع ملاحظات العميل في الطلب وعالجها قبل طلب الاستلام مجددًا. السعر المتفق عليه ثابت.', 'rework_requested');
            $this->log($customerId, 'request_rework', $id);
        }
        return $result;
    }

    public function cancel(int $id, int $customerId): array
    {
        $model = new ServiceRequest();
        if (!$model->changeStatus($id, 'cancelled', $customerId)) {
            return ['ok' => false, 'error' => 'يمكن إلغاء الطلب قبل بدء التنفيذ فقط'];
        }
        $request = $model->find($id);
        if (!empty($request['provider_id'])) {
            $this->notifyProvider((int)$request['provider_id'], $id, 'ألغى العميل الطلب', 'أُلغي الطلب قبل بدء التنفيذ. لا تبدأ العمل على هذا الطلب.', 'request_cancelled');
        }
        return ['ok' => true];
    }

    private function notifyProvider(int $providerId, int $id, string $title, string $body, string $type): void
    {
        $profile = (new ProviderProfile())->find($providerId);
        if ($profile) $this->notify((int)$profile['user_id'], $id, $title, $body, $type);
    }

    private function notify(int $userId, int $id, string $title, string $body, string $type): void
    {
        FcmService::sendToUser($userId, $title, $body, ['request_id' => (string)$id, 'type' => $type]);
    }

    private function log(int $actor, string $action, int $id): void
    {
        try { ActivityLog::log($actor, $action, 'طلب #' . $id); }
        catch (Throwable $e) { error_log('Workflow activity log unavailable'); }
    }
}
