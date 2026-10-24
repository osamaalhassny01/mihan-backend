<?php
require_once __DIR__ . '/../core/Model.php';

class Payout extends Model
{
    protected string $table = 'provider_payouts';

    public function forProvider(int $providerId): array
    {
        return $this->query("SELECT po.*, i.invoice_number, sr.request_number, u.name provider_name
            FROM provider_payouts po
            JOIN invoices i ON i.id=po.invoice_id
            JOIN service_requests sr ON sr.id=i.request_id
            JOIN provider_profiles pp ON pp.id=po.provider_id
            JOIN users u ON u.id=pp.user_id
            WHERE po.provider_id=:p ORDER BY po.created_at DESC", ['p'=>$providerId]);
    }

    public function paidTotalForProvider(int $providerId): float
    {
        $r=$this->query("SELECT COALESCE(SUM(amount),0) total FROM provider_payouts WHERE provider_id=:p AND status='paid'",['p'=>$providerId]);
        return round((float)($r[0]['total']??0),2);
    }

    public function availableForProvider(int $providerId): float
    {
        $r=$this->query("SELECT COALESCE(SUM(LEAST(i.provider_net, COALESCE(paid.paid_amount,0))),0) earned
            FROM invoices i JOIN service_requests sr ON sr.id=i.request_id
            LEFT JOIN (SELECT invoice_id,SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) paid_amount FROM payments GROUP BY invoice_id) paid ON paid.invoice_id=i.id
            WHERE sr.provider_id=:p AND i.payment_status='paid'",['p'=>$providerId]);
        $earned=round((float)($r[0]['earned']??0),2);
        return max(0,round($earned-$this->paidTotalForProvider($providerId),2));
    }

    public function availableForInvoice(int $invoiceId): float
    {
        $r=$this->query("SELECT i.provider_net, COALESCE((SELECT SUM(amount) FROM provider_payouts WHERE invoice_id=i.id AND status='paid'),0) paid_out FROM invoices i WHERE i.id=:i LIMIT 1",['i'=>$invoiceId]);
        if(!$r) return 0.0;
        return max(0,round((float)$r[0]['provider_net']-(float)$r[0]['paid_out'],2));
    }

    public function createPaid(int $providerId,int $invoiceId,float $amount,int $createdBy,?string $reference=null,?string $notes=null): bool
    {
        $amount=round($amount,2); if($amount<=0) return false;
        $db=Database::getInstance(); $db->beginTransaction();
        try{
            $stmt=$db->prepare("SELECT i.provider_net, i.payment_status, sr.provider_id FROM invoices i JOIN service_requests sr ON sr.id=i.request_id WHERE i.id=:i FOR UPDATE");
            $stmt->execute(['i'=>$invoiceId]); $inv=$stmt->fetch(PDO::FETCH_ASSOC);
            if(!$inv || (int)$inv['provider_id']!==$providerId || $inv['payment_status']!=='paid'){ $db->rollBack(); return false; }
            $paidOutStmt=$db->prepare("SELECT COALESCE(SUM(amount),0) FROM provider_payouts WHERE invoice_id=:i AND status='paid'");
            $paidOutStmt->execute(['i'=>$invoiceId]); $already=(float)$paidOutStmt->fetchColumn();
            $available=max(0,round((float)$inv['provider_net']-$already,2));
            if($amount>$available+0.0001){$db->rollBack();return false;}
            $ins=$db->prepare("INSERT INTO provider_payouts(provider_id,invoice_id,amount,status,reference,notes,paid_at,created_by) VALUES(:p,:i,:a,'paid',:r,:n,NOW(),:u)");
            $ins->execute(['p'=>$providerId,'i'=>$invoiceId,'a'=>$amount,'r'=>$reference?:null,'n'=>$notes?:null,'u'=>$createdBy]);
            $db->commit(); return true;
        }catch(Throwable $e){if($db->inTransaction())$db->rollBack();return false;}
    }
}
