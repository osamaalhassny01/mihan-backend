<?php
require_once __DIR__ . '/../core/Model.php';

class ServiceRequest extends Model
{
    protected string $table = 'service_requests';

    private array $allowedTransitions = [
        'new'         => ['pending', 'cancelled'],
        'pending'     => ['accepted', 'rejected', 'cancelled'],
        'accepted'    => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => [],
        'cancelled'   => [],
        'rejected'    => [],
    ];

    public function createRequest(array $data): int
    {
        $id = (int) $this->insert([
            'request_number' => 'TEMP',
            'customer_id'    => $data['customer_id'],
            'service_id'     => $data['service_id'],
            'location_id'    => $data['location_id'] ?? null,
            'description'    => $data['description'],
            'preferred_date' => $data['preferred_date'] ?: null,
            'preferred_time' => $data['preferred_time'] ?: null,
            'budget'         => $data['budget'] ?: null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'pending',
        ]);
        $this->update($id, ['request_number' => generateRequestNumber($id)]);
        $this->logStatus($id, $data['customer_id'], null, 'pending');
        return $id;
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedTransitions[$from] ?? [], true);
    }

    /**
     * Verify that a provider is an active provider who offers the requested
     * service and, when a request has a location, serves that location.
     */
    public function providerEligibleForRequest(int $providerId, array $request): bool
    {
        $sql = "SELECT pp.id
                FROM provider_profiles pp
                JOIN users u ON u.id = pp.user_id
                JOIN provider_services ps ON ps.provider_id = pp.id
                WHERE pp.id = :provider_id
                  AND u.role = 'provider'
                  AND u.is_active = 1
                  AND ps.service_id = :service_id
                  AND (
                        :location_id IS NULL
                        OR EXISTS (
                            SELECT 1 FROM provider_locations pl
                            WHERE pl.provider_id = pp.id
                              AND pl.location_id = :location_id_2
                        )
                  )
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $locationId = !empty($request['location_id']) ? (int)$request['location_id'] : null;
        $stmt->bindValue(':provider_id', $providerId, PDO::PARAM_INT);
        $stmt->bindValue(':service_id', (int)$request['service_id'], PDO::PARAM_INT);
        $stmt->bindValue(':location_id', $locationId, $locationId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':location_id_2', $locationId, $locationId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetch()) return false;

        require_once __DIR__ . '/ProviderProfile.php';
        return (new ProviderProfile())->availableForRequest($providerId, $request);
    }

    /**
     * Provider can view/act on a request only if it is assigned to them,
     * or it is an unassigned pending request they are eligible to receive.
     */
    public function providerCanAccess(int $providerId, array $request): bool
    {
        if (!empty($request['provider_id'])) {
            return (int)$request['provider_id'] === $providerId;
        }

        return $request['status'] === 'pending' && $this->providerEligibleForRequest($providerId, $request);
    }

    public function changeStatus(int $requestId, string $newStatus, int $changedBy, ?int $providerId = null): bool
    {
        $request = $this->find($requestId);
        if (!$request) return false;

        $oldStatus = (string)$request['status'];
        if (!$this->canTransition($oldStatus, $newStatus)) return false;

        // Provider actions must always belong to the authenticated provider.
        if ($providerId !== null) {
            if (!$this->providerCanAccess($providerId, $request)) return false;
            if (!$this->providerEligibleForRequest($providerId, $request)) return false;
        }

        // Compare-and-set on the old status prevents two simultaneous requests
        // from both changing the same workflow state.
        if ($providerId !== null && empty($request['provider_id']) && $newStatus === 'accepted') {
            $stmt = $this->db->prepare(
                "UPDATE service_requests
                 SET status = 'accepted', provider_id = :provider_id
                 WHERE id = :id AND status = :old_status AND provider_id IS NULL"
            );
            $stmt->execute([
                'provider_id' => $providerId,
                'id' => $requestId,
                'old_status' => $oldStatus,
            ]);
        } else {
            $sql = "UPDATE service_requests SET status = :new_status";
            $params = ['new_status' => $newStatus, 'id' => $requestId, 'old_status' => $oldStatus];
            if ($providerId !== null) {
                $sql .= " WHERE id = :id AND status = :old_status AND provider_id = :provider_id";
                $params['provider_id'] = $providerId;
            } else {
                $sql .= " WHERE id = :id AND status = :old_status";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }

        if ($stmt->rowCount() !== 1) return false;

        $this->logStatus($requestId, $changedBy, $oldStatus, $newStatus);
        return true;
    }

    /**
     * Reject a provider-assigned or unassigned pending request and return it
     * to the public pending queue so the customer can choose another provider.
     */
    public function rejectAndRequeue(int $requestId, int $changedBy, int $providerId): bool
    {
        $request = $this->find($requestId);
        if (!$request || $request['status'] !== 'pending') return false;
        if (!$this->providerCanAccess($providerId, $request)) return false;
        if (!$this->providerEligibleForRequest($providerId, $request)) return false;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE service_requests
                 SET status = 'rejected', provider_id = NULL
                 WHERE id = :id AND status = 'pending'
                   AND (provider_id IS NULL OR provider_id = :provider_id)"
            );
            $stmt->execute(['id' => $requestId, 'provider_id' => $providerId]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }
            $this->logStatus($requestId, $changedBy, 'pending', 'rejected');

            $stmt = $this->db->prepare(
                "UPDATE service_requests SET status = 'pending'
                 WHERE id = :id AND status = 'rejected' AND provider_id IS NULL"
            );
            $stmt->execute(['id' => $requestId]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }
            $this->logStatus($requestId, $changedBy, 'rejected', 'pending');
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    /**
     * Atomically assign an eligible provider to an unassigned pending request.
     */
    public function assignProvider(int $requestId, int $customerId, int $providerId): bool
    {
        $request = $this->find($requestId);
        if (!$request || (int)$request['customer_id'] !== $customerId) return false;
        if ($request['status'] !== 'pending' || !empty($request['provider_id'])) return false;
        if (!$this->providerEligibleForRequest($providerId, $request)) return false;

        $stmt = $this->db->prepare(
            "UPDATE service_requests
             SET provider_id = :provider_id
             WHERE id = :id AND customer_id = :customer_id
               AND status = 'pending' AND provider_id IS NULL"
        );
        $stmt->execute([
            'provider_id' => $providerId,
            'id' => $requestId,
            'customer_id' => $customerId,
        ]);

        return $stmt->rowCount() === 1;
    }

    private function logStatus(int $requestId, ?int $changedBy, ?string $old, string $new): void
    {
        $this->execute(
            "INSERT INTO request_status_history (request_id, changed_by, old_status, new_status) VALUES (:r, :c, :o, :n)",
            ['r' => $requestId, 'c' => $changedBy, 'o' => $old, 'n' => $new]
        );
    }

    public function history(int $requestId): array
    {
        return $this->query(
            "SELECT h.*, u.name as changed_by_name FROM request_status_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.request_id = :id ORDER BY h.changed_at ASC",
            ['id' => $requestId]
        );
    }

    public function fullDetails(int $id)
    {
        $sql = "SELECT sr.*, s.name_ar as service_name_ar, s.name_en as service_name_en,
                       c.name as customer_name, c.phone as customer_phone,
                       l.name_ar as location_name_ar, l.name_en as location_name_en,
                       pp.id as provider_profile_id, pu.name as provider_name, pu.phone as provider_phone
                FROM service_requests sr
                JOIN services s ON s.id = sr.service_id
                JOIN users c ON c.id = sr.customer_id
                LEFT JOIN locations l ON l.id = sr.location_id
                LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
                LEFT JOIN users pu ON pu.id = pp.user_id
                WHERE sr.id = :id LIMIT 1";
        $rows = $this->query($sql, ['id' => $id]);
        return $rows[0] ?? null;
    }

    public function forCustomer(int $customerId): array
    {
        return $this->query(
            "SELECT sr.*, s.name_ar as service_name_ar, s.name_en as service_name_en, pu.name as provider_name
             FROM service_requests sr
             JOIN services s ON s.id = sr.service_id
             LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
             LEFT JOIN users pu ON pu.id = pp.user_id
             WHERE sr.customer_id = :id ORDER BY sr.created_at DESC",
            ['id' => $customerId]
        );
    }

    public function forProvider(int $providerId): array
    {
        return $this->query(
            "SELECT sr.*, s.name_ar as service_name_ar, s.name_en as service_name_en, cu.name as customer_name
             FROM service_requests sr
             JOIN services s ON s.id = sr.service_id
             JOIN users cu ON cu.id = sr.customer_id
             WHERE sr.provider_id = :id
                OR (sr.provider_id IS NULL AND sr.status = 'pending'
                    AND sr.service_id IN (SELECT service_id FROM provider_services WHERE provider_id = :id2))
             ORDER BY sr.created_at DESC",
            ['id' => $providerId, 'id2' => $providerId]
        );
    }

    public function pendingUnassigned(): array
    {
        return $this->where(['status' => 'pending'], 'created_at DESC');
    }

    /**
     * Atomically complete request, assign final price, generate invoice, record commission, and update statistics.
     */
    public function completeWithInvoice(int $requestId, int $providerProfileId, float $finalPrice, int $userId): array
    {
        $finalPrice = round($finalPrice, 2);
        if ($finalPrice < 0) {
            return ['ok' => false, 'error' => 'السعر النهائي غير صالح'];
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM service_requests WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $requestId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'الطلب غير موجود'];
            }
            if ((int)$request['provider_id'] !== $providerProfileId) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'الطلب غير مخصص لهذا المقدم'];
            }
            if (!in_array($request['status'], ['accepted', 'in_progress'], true)) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'لا يمكن إكمال الطلب في حالته الحالية'];
            }

            // 1. Update request status and final price
            $upReq = $this->db->prepare("UPDATE service_requests SET status = 'completed', final_price = :fp WHERE id = :id");
            $upReq->execute(['fp' => $finalPrice, 'id' => $requestId]);

            // 2. Status history
            $hist = $this->db->prepare("INSERT INTO request_status_history (request_id, changed_by, old_status, new_status) VALUES (:r, :c, :old, 'completed')");
            $hist->execute(['r' => $requestId, 'c' => $userId, 'old' => $request['status']]);

            // 3. Generate Invoice
            require_once __DIR__ . '/Invoice.php';
            $invoiceModel = new Invoice();
            $rate = $invoiceModel->commissionRate();
            $commission = round($finalPrice * $rate, 2);
            $net = round($finalPrice - $commission, 2);

            $invStmt = $this->db->prepare("
                INSERT INTO invoices (invoice_number, request_id, service_price, commission_rate, commission_amount, provider_net, total_amount, payment_status)
                VALUES ('TEMP', :req, :sp, :cr, :ca, :pn, :ta, 'unpaid')
            ");
            $invStmt->execute([
                'req' => $requestId,
                'sp' => $finalPrice,
                'cr' => $rate,
                'ca' => $commission,
                'pn' => $net,
                'ta' => $finalPrice
            ]);
            $invoiceId = (int)$this->db->lastInsertId();

            $prefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string)setting('invoice_prefix', 'INV'));
            $prefix = $prefix !== '' ? $prefix : 'INV';
            $invNumber = $prefix . '-' . date('Y') . '-' . str_pad($invoiceId, 6, '0', STR_PAD_LEFT);

            $upInvNum = $this->db->prepare("UPDATE invoices SET invoice_number = :num WHERE id = :id");
            $upInvNum->execute(['num' => $invNumber, 'id' => $invoiceId]);

            // 4. Platform commission record
            $commStmt = $this->db->prepare("INSERT INTO platform_commissions (invoice_id, amount) VALUES (:i, :a)");
            $commStmt->execute(['i' => $invoiceId, 'a' => $commission]);

            // 5. Update provider completed jobs
            $provStmt = $this->db->prepare("UPDATE provider_profiles SET completed_jobs = completed_jobs + 1 WHERE id = :pid");
            $provStmt->execute(['pid' => $providerProfileId]);

            $this->db->commit();
            return [
                'ok' => true,
                'invoice_id' => $invoiceId,
                'invoice_number' => $invNumber,
                'customer_id' => (int)$request['customer_id']
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
