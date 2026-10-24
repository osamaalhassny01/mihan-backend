<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Invoice.php';

/**
 * FinanceService - The single source of truth for all financial operations and calculations.
 *
 * Implements the unified Financial Contract:
 * [
 *     'gross_amount'         => float, // Total invoiced volume
 *     'paid_amount'          => float, // Gross collections from paid payments
 *     'refunded_amount'      => float, // Total refunded amount
 *     'net_collected'        => float, // paid_amount - refunded_amount
 *     'outstanding_amount'   => float, // Remaining balance due from customer
 *     'commission_amount'    => float, // Platform commission
 *     'provider_net_amount'  => float, // Provider net share
 *     'net_platform_revenue' => float, // Platform retained revenue after refunds
 * ]
 */
class FinanceService
{
    private ?PDO $db = null;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db;
    }

    private function db(): PDO
    {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }

    /**
     * Compute financial contract for a single invoice.
     */
    public function invoiceSummary(int $invoiceId): array
    {
        $stmt = $this->db()->prepare("
            SELECT i.*,
                   COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) AS paid_total,
                   COALESCE(SUM(CASE WHEN p.status = 'refunded' THEN p.amount ELSE 0 END), 0) AS refunded_total
            FROM invoices i
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.id = :id
            GROUP BY i.id
        ");
        $stmt->execute(['id' => $invoiceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $this->emptyContract();
        }

        return $this->formatContractRow($row);
    }

    /**
     * Provider financial overview.
     */
    public function providerSummary(int $providerProfileId): array
    {
        $stmt = $this->db()->prepare("
            SELECT
                COUNT(DISTINCT i.id) AS invoices_count,
                COALESCE(SUM(i.total_amount), 0) AS gross_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE p_totals.paid_amount END), 0) AS paid_total,
                COALESCE(SUM(p_totals.refunded_amount), 0) AS refunded_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE GREATEST(i.total_amount - COALESCE(p_totals.paid_amount, 0), 0) END), 0) AS outstanding_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p_totals.paid_amount, 0)) * i.commission_rate, 2) END), 0) AS commission_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p_totals.paid_amount, 0)) * (1 - i.commission_rate), 2) END), 0) AS provider_net_total
            FROM invoices i
            JOIN service_requests sr ON sr.id = i.request_id
            LEFT JOIN (
                SELECT invoice_id,
                       SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS paid_amount,
                       SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) AS refunded_amount
                FROM payments
                GROUP BY invoice_id
            ) p_totals ON p_totals.invoice_id = i.id
            WHERE sr.provider_id = :pid
        ");
        $stmt->execute(['pid' => $providerProfileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $gross = round((float)($row['gross_total'] ?? 0), 2);
        $paid = round((float)($row['paid_total'] ?? 0), 2);
        $refunded = round((float)($row['refunded_total'] ?? 0), 2);
        $netCollected = max(0, round($paid - $refunded, 2));
        $outstanding = round((float)($row['outstanding_total'] ?? 0), 2);
        $commission = round((float)($row['commission_total'] ?? 0), 2);
        $providerNet = round((float)($row['provider_net_total'] ?? 0), 2);
        $payoutStmt=$this->db()->prepare("SELECT COALESCE(SUM(amount),0) FROM provider_payouts WHERE provider_id=:p AND status='paid'");
        $payoutStmt->execute(['p'=>$providerProfileId]);
        $paidOut=round((float)$payoutStmt->fetchColumn(),2);
        $available=max(0,round($providerNet-$paidOut,2));

        return [
            'invoices_count'       => (int)($row['invoices_count'] ?? 0),
            'gross_amount'         => $gross,
            'paid_amount'          => $paid,
            'refunded_amount'      => $refunded,
            'net_collected'        => $netCollected,
            'outstanding_amount'   => $outstanding,
            'commission_amount'    => $commission,
            'provider_net_amount'  => $providerNet,
            'net_platform_revenue' => $commission,
            'paid_out_amount'      => $paidOut,
            'available_payout'     => $available,
        ];
    }

    /**
     * Customer financial overview.
     */
    public function customerSummary(int $customerId): array
    {
        $stmt = $this->db()->prepare("
            SELECT
                COUNT(DISTINCT i.id) AS invoices_count,
                COALESCE(SUM(i.total_amount), 0) AS gross_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE p_totals.paid_amount END), 0) AS paid_total,
                COALESCE(SUM(p_totals.refunded_amount), 0) AS refunded_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE GREATEST(i.total_amount - COALESCE(p_totals.paid_amount, 0), 0) END), 0) AS outstanding_total
            FROM invoices i
            JOIN service_requests sr ON sr.id = i.request_id
            LEFT JOIN (
                SELECT invoice_id,
                       SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS paid_amount,
                       SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) AS refunded_amount
                FROM payments
                GROUP BY invoice_id
            ) p_totals ON p_totals.invoice_id = i.id
            WHERE sr.customer_id = :cid
        ");
        $stmt->execute(['cid' => $customerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $gross = round((float)($row['gross_total'] ?? 0), 2);
        $paid = round((float)($row['paid_total'] ?? 0), 2);
        $refunded = round((float)($row['refunded_total'] ?? 0), 2);
        $netCollected = max(0, round($paid - $refunded, 2));
        $outstanding = round((float)($row['outstanding_total'] ?? 0), 2);

        return [
            'invoices_count'       => (int)($row['invoices_count'] ?? 0),
            'gross_amount'         => $gross,
            'paid_amount'          => $paid,
            'refunded_amount'      => $refunded,
            'net_collected'        => $netCollected,
            'outstanding_amount'   => $outstanding,
        ];
    }

    /**
     * Platform-wide financial overview for Admin and Analytics.
     */
    public function platformSummary(?string $dateFrom = null, ?string $dateTo = null, ?string $status = null): array
    {
        $where = [];
        $params = [];

        if ($dateFrom) {
            $where[] = 'DATE(i.created_at) >= :from';
            $params['from'] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = 'DATE(i.created_at) <= :to';
            $params['to'] = $dateTo;
        }
        if ($status && in_array($status, ['unpaid', 'paid', 'refunded'], true)) {
            $where[] = 'i.payment_status = :status';
            $params['status'] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT
                COUNT(DISTINCT i.id) AS invoices_count,
                COALESCE(SUM(i.total_amount), 0) AS gross_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE p_totals.paid_amount END), 0) AS paid_total,
                COALESCE(SUM(p_totals.refunded_amount), 0) AS refunded_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE GREATEST(i.total_amount - COALESCE(p_totals.paid_amount, 0), 0) END), 0) AS outstanding_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p_totals.paid_amount, 0)) * i.commission_rate, 2) END), 0) AS commission_total,
                COALESCE(SUM(CASE WHEN i.payment_status = 'refunded' THEN 0 ELSE ROUND(LEAST(i.total_amount, COALESCE(p_totals.paid_amount, 0)) * (1 - i.commission_rate), 2) END), 0) AS provider_net_total
            FROM invoices i
            LEFT JOIN (
                SELECT invoice_id,
                       SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS paid_amount,
                       SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) AS refunded_amount
                FROM payments
                GROUP BY invoice_id
            ) p_totals ON p_totals.invoice_id = i.id
            $whereClause
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $gross = round((float)($row['gross_total'] ?? 0), 2);
        $paid = round((float)($row['paid_total'] ?? 0), 2);
        $refunded = round((float)($row['refunded_total'] ?? 0), 2);
        $netCollected = max(0, round($paid - $refunded, 2));
        $outstanding = round((float)($row['outstanding_total'] ?? 0), 2);
        $commission = round((float)($row['commission_total'] ?? 0), 2);
        $providerNet = round((float)($row['provider_net_total'] ?? 0), 2);

        return [
            'invoices_count'       => (int)($row['invoices_count'] ?? 0),
            'gross_amount'         => $gross,
            'paid_amount'          => $paid,
            'refunded_amount'      => $refunded,
            'net_collected'        => $netCollected,
            'outstanding_amount'   => $outstanding,
            'commission_amount'    => $commission,
            'provider_net_amount'  => $providerNet,
            'net_platform_revenue' => $commission,
        ];
    }

    /**
     * Standardized calculation contract for an invoice with payments.
     */
    public function calculateContract(array $invoiceData, array $payments = []): array
    {
        $paidTotal = 0.0;
        $refundedTotal = 0.0;
        foreach ($payments as $p) {
            $amt = (float)($p['amount'] ?? 0);
            $st = $p['status'] ?? 'paid';
            if ($st === 'paid') $paidTotal += $amt;
            elseif ($st === 'refunded') $refundedTotal += $amt;
        }
        $row = array_merge($invoiceData, [
            'paid_total' => $paidTotal,
            'refunded_total' => $refundedTotal,
        ]);
        return $this->formatContractRow($row);
    }

    /**
     * Format a database row into the standardized financial contract array.
     */
    public function formatContractRow(array $row): array
    {
        $isRefunded = ($row['payment_status'] ?? '') === 'refunded';
        $gross = round((float)($row['total_amount'] ?? 0), 2);
        $paid = round((float)($row['paid_total'] ?? 0), 2);
        $refunded = round((float)($row['refunded_total'] ?? 0), 2);
        $rate = (float)($row['commission_rate'] ?? 0.10);

        if ($isRefunded) {
            $outstanding = 0.00;
            $commission = 0.00;
            $providerNet = 0.00;
            $netCollected = 0.00;
        } else {
            $netCollected = (float)max(0.0, round($paid - $refunded, 2));
            $outstanding = (float)max(0.0, round($gross - $paid, 2));
            $commission = (float)round(min($gross, $paid) * $rate, 2);
            $providerNet = (float)round(min($gross, $paid) * (1 - $rate), 2);
        }

        return [
            'invoice_id'           => (int)($row['id'] ?? 0),
            'gross_amount'         => $gross,
            'paid_amount'          => $paid,
            'refunded_amount'      => $refunded,
            'net_collected'        => $netCollected,
            'outstanding_amount'   => $outstanding,
            'commission_amount'    => $commission,
            'provider_net_amount'  => $providerNet,
            'net_platform_revenue' => $commission,
            'payment_status'       => $row['payment_status'] ?? 'unpaid',
        ];
    }

    private function emptyContract(): array
    {
        return [
            'gross_amount'         => 0.00,
            'paid_amount'          => 0.00,
            'refunded_amount'      => 0.00,
            'net_collected'        => 0.00,
            'outstanding_amount'   => 0.00,
            'commission_amount'    => 0.00,
            'provider_net_amount'  => 0.00,
            'net_platform_revenue' => 0.00,
        ];
    }
}
