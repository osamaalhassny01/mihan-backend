<?php
require_once __DIR__ . '/../core/Model.php';

class Invoice extends Model
{
    protected string $table = 'invoices';

    public function commissionRate(): float
    {
        $row = $this->db->query("SELECT value FROM system_settings WHERE `key`='platform_commission_rate' LIMIT 1")->fetch();
        $rate = $row ? (float)$row['value'] : (float)PLATFORM_COMMISSION_RATE;
        return $rate > 1 ? $rate / 100 : $rate;
    }

    public function generateForRequest(int $requestId, float $servicePrice): int
    {
        $existing = $this->forRequest($requestId);
        if ($existing) return (int)$existing['id'];
        $rate = $this->commissionRate();
        $commission = round($servicePrice * $rate, 2);
        $net = round($servicePrice - $commission, 2);
        $id = (int)$this->insert([
            'invoice_number'=>'TEMP','request_id'=>$requestId,'service_price'=>$servicePrice,
            'commission_rate'=>$rate,'commission_amount'=>$commission,'provider_net'=>$net,
            'total_amount'=>$servicePrice,'payment_status'=>'unpaid'
        ]);
        $prefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string)setting('invoice_prefix', 'INV'));
        $prefix = $prefix !== '' ? $prefix : 'INV';
        $this->update($id, ['invoice_number'=>$prefix.'-'.date('Y').'-'.str_pad($id,6,'0',STR_PAD_LEFT)]);
        $this->execute("INSERT INTO platform_commissions (invoice_id, amount) VALUES (:i,:a)", ['i'=>$id,'a'=>$commission]);
        return $id;
    }

    public function recordPayment(int $invoiceId, string $method, float $amount, ?string $reference = null, ?string $notes = null): bool
    {
        if (!in_array($method, ['simulated_card','simulated_wallet'], true)) {
            return false;
        }
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id, total_amount, payment_status FROM invoices WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $invoiceId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice || $invoice['payment_status'] === 'refunded') {
                $this->db->rollBack();
                return false;
            }

            $payStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = :id AND status = 'paid'");
            $payStmt->execute(['id' => $invoiceId]);
            $alreadyPaid = round((float)$payStmt->fetchColumn(), 2);

            $totalAmount = round((float)$invoice['total_amount'], 2);
            $remainingBalance = max(0, round($totalAmount - $alreadyPaid, 2));

            // Prevent overpayment
            if ($amount > ($remainingBalance + 0.0001)) {
                $this->db->rollBack();
                return false;
            }

            $insertStmt = $this->db->prepare("
                INSERT INTO payments (invoice_id, method, amount, status, paid_at, is_simulated, reference, notes)
                VALUES (:i, :m, :a, 'paid', NOW(), 1, :r, :n)
            ");
            $insertStmt->execute([
                'i' => $invoiceId,
                'm' => $method,
                'a' => $amount,
                'r' => $reference ?: null,
                'n' => $notes ?: null
            ]);

            $newPaid = round($alreadyPaid + $amount, 2);
            $newBalance = max(0, round($totalAmount - $newPaid, 2));
            $newStatus = ($newBalance <= 0.0001) ? 'paid' : 'unpaid';

            $updateStmt = $this->db->prepare("UPDATE invoices SET payment_status = :st WHERE id = :id");
            $updateStmt->execute(['st' => $newStatus, 'id' => $invoiceId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function balance(int $invoiceId): float
    {
        $stmt = $this->db->prepare("
            SELECT i.total_amount,
                   COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id AND status = 'paid'), 0) AS paid_amount
            FROM invoices i
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $invoiceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return 0.0;
        return max(0.0, round((float)$row['total_amount'] - (float)$row['paid_amount'], 2));
    }

    public function refund(int $invoiceId, ?string $notes = null): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id, total_amount, payment_status FROM invoices WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $invoiceId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice || $invoice['payment_status'] === 'refunded') {
                $this->db->rollBack();
                return false;
            }

            $payStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = :id AND status = 'paid'");
            $payStmt->execute(['id' => $invoiceId]);
            $paidTotal = round((float)$payStmt->fetchColumn(), 2);
            $payoutStmt = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM provider_payouts WHERE invoice_id=:id AND status='paid'");
            $payoutStmt->execute(['id'=>$invoiceId]);
            if ((float)$payoutStmt->fetchColumn() > 0) {
                $this->db->rollBack();
                return false;
            }

            if ($paidTotal <= 0) {
                $this->db->rollBack();
                return false;
            }

            $refStmt = $this->db->prepare("
                INSERT INTO payments (invoice_id, method, amount, status, paid_at, is_simulated, reference, notes)
                VALUES (:i, 'simulated_wallet', :a, 'refunded', NOW(), 1, :r, :n)
            ");
            $refStmt->execute([
                'i' => $invoiceId,
                'a' => $paidTotal,
                'r' => 'REF-' . date('YmdHis'),
                'n' => $notes ?: 'استرداد كامل المبلغ المدفوع'
            ]);

            $upStmt = $this->db->prepare("UPDATE invoices SET payment_status = 'refunded' WHERE id = :id");
            $upStmt->execute(['id' => $invoiceId]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function forRequest(int $requestId) { return $this->first(['request_id'=>$requestId]); }

    public function details(int $invoiceId) {
        $s=$this->db->prepare("SELECT i.*,sr.request_number,sr.customer_id,sr.provider_id,cu.name customer_name,pu.name provider_name FROM invoices i JOIN service_requests sr ON sr.id=i.request_id JOIN users cu ON cu.id=sr.customer_id LEFT JOIN provider_profiles pp ON pp.id=sr.provider_id LEFT JOIN users pu ON pu.id=pp.user_id WHERE i.id=:id LIMIT 1");
        $s->execute(['id'=>$invoiceId]); return $s->fetch(PDO::FETCH_ASSOC)?:null;
    }

    public function payments(int $invoiceId): array {
        $s=$this->db->prepare("SELECT * FROM payments WHERE invoice_id=:i ORDER BY paid_at DESC,id DESC"); $s->execute(['i'=>$invoiceId]); return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}
