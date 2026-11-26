<?php
require_once __DIR__ . '/../core/Model.php';

class ServiceRequest extends Model
{
    protected string $table = 'service_requests';

    private array $allowedTransitions = [
        'new'         => ['pending', 'accepted', 'rejected', 'cancelled'],
        'pending'     => ['accepted', 'rejected', 'cancelled'],
        'accepted'    => ['in_progress', 'cancelled', 'rejected'],
        'in_progress' => ['awaiting_confirmation', 'cancelled'],
        'awaiting_confirmation' => ['in_progress', 'completed'],
        'completed'   => [],
        'cancelled'   => [],
        'rejected'    => ['pending', 'cancelled'],
    ];

    public function createRequest(array $data): int
    {
        $insertData = [
            'request_number' => 'TMP-' . bin2hex(random_bytes(8)),
            'workflow_version' => 2,
            'customer_id'    => $data['customer_id'],
            'service_id'     => $data['service_id'],
            'location_id'    => $data['location_id'] ?? null,
            'description'    => $data['description'],
            'preferred_date' => ($data['preferred_date'] ?? null) ?: null,
            'preferred_time' => ($data['preferred_time'] ?? null) ?: null,
            'budget'         => ($data['budget'] ?? null) ?: null,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'pending',
        ];

        if (!empty($data['provider_id'])) {
            $insertData['provider_id'] = (int)$data['provider_id'];
        }

        $this->db->beginTransaction();
        try {
            $id = (int)$this->insert($insertData);
            $this->update($id, ['request_number' => generateRequestNumber($id)]);
            $this->logStatus($id, $data['customer_id'], null, 'pending');
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedTransitions[$from] ?? [], true);
    }

    /**
     * Check provider eligibility with granular diagnostic reasons and human-readable Arabic messages.
     * Separates reasons clearly:
     * - not_found_or_inactive
     * - not_verified (account awaiting admin vetting/approval)
     * - service_mismatch (provider does not offer this service)
     * - location_mismatch (provider does not cover this location directly or hierarchically)
     * - unavailable (master is_available toggle is off)
     * - schedule_conflict (outside weekly working hours)
     */
    public function checkProviderEligibility(int $providerId, array $request): array
    {
        if ($providerId <= 0) {
            return [
                'eligible' => false,
                'reason_code' => 'invalid_provider',
                'message' => 'لم يتم تحديد مقدم خدمة صالح.',
                'details' => ['provider_id' => $providerId],
            ];
        }

        // 1. Account existence, active status and role
        $sql = "SELECT pp.*, u.name as user_name, u.phone as user_phone, u.is_active as user_active, u.role as user_role
                FROM provider_profiles pp
                JOIN users u ON u.id = pp.user_id
                WHERE pp.id = :provider_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['provider_id' => $providerId]);
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider || (int)$provider['user_active'] !== 1 || $provider['user_role'] !== 'provider') {
            return [
                'eligible' => false,
                'reason_code' => 'not_found_or_inactive',
                'message' => 'حساب مقدم الخدمة غير موجود أو غير نشط في المنصة حالياً.',
                'details' => ['provider_id' => $providerId],
            ];
        }

        $providerName = trim((string)($provider['user_name'] ?? 'مقدم الخدمة'));
        $profession = trim((string)($provider['profession'] ?? ($provider['specialization'] ?? 'المهنة')));

        // 2. Official administrative verification (is_verified)
        if ((int)($provider['is_verified'] ?? 0) !== 1) {
            return [
                'eligible' => false,
                'reason_code' => 'not_verified',
                'message' => "مقدم الخدمة ({$providerName}) لا يزال بانتظار الاعتماد والتوثيق الرسمي من إدارة المنصة، ولا يمكنه استقبال طلبات جديدة حتى اكتمال تفعيل حسابه.",
                'details' => [
                    'provider_id' => $providerId,
                    'provider_name' => $providerName,
                    'is_verified' => 0,
                ],
            ];
        }

        // 3. Service specialty matching
        $serviceId = (int)($request['service_id'] ?? 0);
        if ($serviceId > 0) {
            $stmt = $this->db->prepare("SELECT 1 FROM provider_services WHERE provider_id = :p AND service_id = :s LIMIT 1");
            $stmt->execute(['p' => $providerId, 's' => $serviceId]);
            $hasService = (bool)$stmt->fetchColumn();

            require_once __DIR__ . '/Service.php';
            $svc = (new Service())->find($serviceId);
            $serviceName = $svc['name_ar'] ?? 'الخدمة المطلوبة';

            // Also check if provider's profession text matches this service
            if (!$hasService && !empty($serviceName) && !empty($profession)) {
                $normProf = trim($profession);
                $normSvc = trim($serviceName);
                if ($normProf === $normSvc || mb_strpos($normProf, $normSvc) !== false || mb_strpos($normSvc, $normProf) !== false) {
                    $hasService = true;
                    try {
                        $this->db->prepare("INSERT IGNORE INTO provider_services (provider_id, service_id) VALUES (:p, :s)")
                             ->execute(['p' => $providerId, 's' => $serviceId]);
                    } catch (\Throwable $e) {}
                }
            }

            if (!$hasService) {
                return [
                    'eligible' => false,
                    'reason_code' => 'service_mismatch',
                    'message' => "مقدم الخدمة ({$providerName}) غير مسجل لتقديم خدمة ({$serviceName}) ضمن قائمة تخصصاته المعتمدة (تخصصه الحالي: {$profession}).",
                    'details' => [
                        'provider_id' => $providerId,
                        'service_id' => $serviceId,
                        'service_name' => $serviceName,
                        'profession' => $profession,
                    ],
                ];
            }
        }

        // 4. Geographic coverage matching (Hierarchical via Location model)
        $locationId = !empty($request['location_id']) ? (int)$request['location_id'] : null;
        if ($locationId !== null && $locationId > 0) {
            require_once __DIR__ . '/Location.php';
            $locModel = new Location();
            if (!$locModel->providerCoversLocation($providerId, $locationId)) {
                $reqLoc = $locModel->find($locationId);
                $reqLocName = $reqLoc['name_ar'] ?? 'المنطقة المحددة';

                $coveredLocs = $this->query(
                    "SELECT l.name_ar FROM provider_locations pl JOIN locations l ON l.id = pl.location_id WHERE pl.provider_id = :p",
                    ['p' => $providerId]
                );
                $coveredNames = !empty($coveredLocs) ? implode('، ', array_column($coveredLocs, 'name_ar')) : 'لم يحدد مناطق تغطية بعد';

                return [
                    'eligible' => false,
                    'reason_code' => 'location_mismatch',
                    'message' => "مقدم الخدمة ({$providerName}) لا يغطي منطقة ({$reqLocName}). نطاق تغطيته الحالي هو: ({$coveredNames}).",
                    'details' => [
                        'provider_id' => $providerId,
                        'location_id' => $locationId,
                        'location_name' => $reqLocName,
                        'covered_locations' => $coveredNames,
                    ],
                ];
            }
        }

        // 5. Master availability toggle
        if ((int)($provider['is_available'] ?? 0) !== 1) {
            return [
                'eligible' => false,
                'reason_code' => 'unavailable',
                'message' => "مقدم الخدمة ({$providerName}) مضبوط حالياً في وضع 'غير متاح' لاستقبال طلبات مؤقتاً.",
                'details' => [
                    'provider_id' => $providerId,
                    'is_available' => 0,
                ],
            ];
        }

        // 6. Scheduled working hours availability
        require_once __DIR__ . '/ProviderProfile.php';
        $prefDate = $request['preferred_date'] ?? null;
        $prefTime = $request['preferred_time'] ?? null;
        $scheduled = (new ProviderProfile())->scheduledAvailability($providerId, $prefDate, $prefTime);
        if ($scheduled === false) {
            $timeSlot = trim(($prefDate ?? '') . ' ' . ($prefTime ?? ''));
            return [
                'eligible' => false,
                'reason_code' => 'schedule_conflict',
                'message' => "مقدم الخدمة ({$providerName}) غير متاح في الموعد المطلوب ({$timeSlot}) وفق جدول ساعات عمله المعتمدة.",
                'details' => [
                    'provider_id' => $providerId,
                    'preferred_date' => $prefDate,
                    'preferred_time' => $prefTime,
                ],
            ];
        }

        return [
            'eligible' => true,
            'reason_code' => 'eligible',
            'message' => "مقدم الخدمة ({$providerName}) مؤهل ومتاح لتنفيذ الطلب.",
            'details' => [
                'provider_id' => $providerId,
                'provider_name' => $providerName,
            ],
        ];
    }

    /**
     * Verify that a provider is eligible for the request.
     * Backward-compatible boolean wrapper around checkProviderEligibility.
     */
    public function providerEligibleForRequest(int $providerId, array $request): bool
    {
        $res = $this->checkProviderEligibility($providerId, $request);
        return (bool)($res['eligible'] ?? false);
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

        if (!in_array($request['status'], ['new', 'pending'], true)) return false;
        if (!$this->providerEligibleForRequest($providerId, $request)) return false;
        $stmt = $this->db->prepare('SELECT 1 FROM provider_rejections WHERE request_id = :r AND provider_id = :p');
        $stmt->execute(['r' => (int)$request['id'], 'p' => $providerId]);
        return !$stmt->fetchColumn();
    }

    public function changeStatus(int $requestId, string $newStatus, int $changedBy, ?int $providerId = null): bool
    {
        $request = $this->find($requestId);
        if (!$request) return false;

        if (in_array($newStatus, ['completed', 'awaiting_confirmation'], true)) return false;
        if ($newStatus === 'cancelled' && (int)$request['customer_id'] !== $changedBy) return false;
        if ($newStatus === 'in_progress' && $request['status'] !== 'accepted') return false;
        if ($newStatus === 'in_progress' && (int)($request['workflow_version'] ?? 1) >= 2) {
            $quote = $this->currentQuote($requestId);
            if (!$quote || $quote['status'] !== 'approved') return false;
        }

        $oldStatus = (string)$request['status'];
        if (!$this->canTransition($oldStatus, $newStatus)) return false;

        // Provider actions must always belong to the authenticated provider.
        if ($providerId !== null) {
            if (!$this->providerCanAccess($providerId, $request)) return false;
            if ($newStatus === 'accepted') {
                if (!empty($request['provider_id']) && (int)$request['provider_id'] === $providerId) {
                    $u = (new User())->find((int)$this->scalar("SELECT user_id FROM provider_profiles WHERE id = :p", ['p' => $providerId]));
                    if (!$u || $u['role'] !== 'provider' || (int)$u['is_active'] !== 1) return false;
                } else {
                    if (!$this->providerEligibleForRequest($providerId, $request)) return false;
                }
            }
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
            if ($newStatus === 'in_progress') {
                $sql .= " AND (workflow_version = 1 OR EXISTS (SELECT 1 FROM request_quotes q WHERE q.id = service_requests.quote_id AND q.status = 'approved'))";
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
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM service_requests WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $requestId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$request || !in_array($request['status'], ['new', 'pending'], true)
                || !$this->providerCanAccess($providerId, $request)) {
                $this->db->rollBack();
                return false;
            }
            $stmt = $this->db->prepare("INSERT IGNORE INTO provider_rejections (request_id, provider_id) VALUES (:r, :p)");
            $stmt->execute(['r' => $requestId, 'p' => $providerId]);
            // A refusal affects this provider only, not requests accepted by another provider.
            if (!empty($request['provider_id'])) {
                $stmt = $this->db->prepare("UPDATE service_requests SET provider_id = NULL WHERE id = :id AND status IN ('new', 'pending') AND provider_id = :p");
                $stmt->execute(['id' => $requestId, 'p' => $providerId]);
                $this->logStatus($requestId, $changedBy, $request['status'], 'rejected');
                $this->logStatus($requestId, $changedBy, 'rejected', 'pending');
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Request rejection failed (' . get_class($e) . '): ' . $e->getMessage());
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

    public function currentQuote(int $requestId): ?array
    {
        $rows = $this->query("SELECT q.* FROM request_quotes q JOIN service_requests sr ON sr.quote_id = q.id WHERE sr.id = :id", ['id' => $requestId]);
        return $rows[0] ?? null;
    }

    private function lockedRequest(int $requestId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM service_requests WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function submitQuote(int $requestId, int $providerId, float $price, string $details, ?string $visitAt = null): array
    {
        $price = round($price, 2);
        $details = trim($details);
        if (!is_finite($price) || $price <= 0 || $price > 99999999.99 || mb_strlen($details) < 10 || mb_strlen($details) > 3000) {
            return ['ok' => false, 'error' => 'أدخل سعرًا صحيحًا ووصفًا لما يشمله العرض من 10 إلى 3000 حرف'];
        }
        if ($visitAt !== null && $visitAt !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $visitAt);
            if (!$date || $date->format('Y-m-d H:i:s') !== $visitAt || $date <= new DateTimeImmutable()) {
                return ['ok' => false, 'error' => 'موعد الزيارة يجب أن يكون تاريخًا ووقتًا قادمين'];
            }
        } else $visitAt = null;
        $this->db->beginTransaction();
        try {
            $request = $this->lockedRequest($requestId);
            if (!$request || $request['status'] !== 'accepted' || (int)$request['provider_id'] !== $providerId) {
                throw new DomainException('يمكن إرسال العرض للطلب المقبول المخصص لك فقط');
            }
            $current = $this->currentQuote($requestId);
            if ($current && $current['status'] === 'approved') {
                throw new DomainException('العرض المعتمد ثابت ولا يمكن تغييره بعد موافقة العميل');
            }
            if ($current && $current['status'] === 'pending') {
                $this->execute("UPDATE request_quotes SET status = 'superseded' WHERE id = :id", ['id' => $current['id']]);
            }
            $stmt = $this->db->prepare('INSERT INTO request_quotes (request_id, provider_id, total_price, details, visit_at) VALUES (:r,:p,:price,:details,:visit)');
            $stmt->execute(['r' => $requestId, 'p' => $providerId, 'price' => $price, 'details' => $details, 'visit' => $visitAt]);
            $quoteId = (int)$this->db->lastInsertId();
            $this->update($requestId, ['quote_id' => $quoteId, 'workflow_version' => 2]);
            $this->db->commit();
            return ['ok' => true, 'quote_id' => $quoteId, 'customer_id' => (int)$request['customer_id']];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['ok' => false, 'error' => $e instanceof DomainException ? $e->getMessage() : 'تعذر إرسال العرض، حاول مجددًا'];
        }
    }

    public function decideQuote(int $requestId, int $customerId, int $quoteId, bool $approve): array
    {
        $this->db->beginTransaction();
        try {
            $request = $this->lockedRequest($requestId);
            $quote = $request ? $this->currentQuote($requestId) : null;
            if (!$request || (int)$request['customer_id'] !== $customerId || $request['status'] !== 'accepted') {
                throw new DomainException('هذا الطلب غير متاح للموافقة على عرض');
            }
            if (!$quote || (int)$quote['id'] !== $quoteId || $quote['status'] !== 'pending') {
                throw new DomainException('تغير العرض أو تمت مراجعته. حدّث الصفحة قبل المتابعة');
            }
            if ($approve && !empty($quote['visit_at']) && strtotime($quote['visit_at']) <= time()) {
                throw new DomainException('موعد العرض مضى. اطلب عرضًا بموعد جديد');
            }
            $this->execute('UPDATE request_quotes SET status = :status, decided_at = NOW() WHERE id = :id',
                ['status' => $approve ? 'approved' : 'declined', 'id' => $quoteId]);
            $this->db->commit();
            return ['ok' => true, 'provider_id' => (int)$request['provider_id']];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['ok' => false, 'error' => $e instanceof DomainException ? $e->getMessage() : 'تعذر تسجيل قرار العرض'];
        }
    }

    public function requestCompletion(int $requestId, int $providerId, int $userId, string $notes): array
    {
        $notes = trim($notes);
        if (mb_strlen($notes) < 5 || mb_strlen($notes) > 3000) return ['ok' => false, 'error' => 'اكتب ملخص العمل المنجز من 5 إلى 3000 حرف'];
        $this->db->beginTransaction();
        try {
            $request = $this->lockedRequest($requestId);
            $quote = $request ? $this->currentQuote($requestId) : null;
            if (!$request || (int)$request['provider_id'] !== $providerId || $request['status'] !== 'in_progress'
                || !$quote || $quote['status'] !== 'approved') throw new DomainException('الطلب ليس قيد التنفيذ بعرض معتمد');
            $this->update($requestId, ['status' => 'awaiting_confirmation', 'completion_notes' => $notes]);
            $this->logStatus($requestId, $userId, 'in_progress', 'awaiting_confirmation');
            $this->db->commit();
            return ['ok' => true, 'customer_id' => (int)$request['customer_id']];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['ok' => false, 'error' => $e instanceof DomainException ? $e->getMessage() : 'تعذر إرسال تأكيد الإنجاز'];
        }
    }

    public function requestRework(int $requestId, int $customerId, string $notes): array
    {
        $notes = trim($notes);
        if (mb_strlen($notes) < 5 || mb_strlen($notes) > 3000) return ['ok' => false, 'error' => 'اكتب الملاحظات المطلوب معالجتها من 5 إلى 3000 حرف'];
        $this->db->beginTransaction();
        try {
            $request = $this->lockedRequest($requestId);
            if (!$request || (int)$request['customer_id'] !== $customerId || $request['status'] !== 'awaiting_confirmation') {
                throw new DomainException('يمكن طلب المعالجة قبل تأكيد الاستلام فقط');
            }
            $this->update($requestId, ['status' => 'in_progress', 'rework_notes' => $notes]);
            $this->logStatus($requestId, $customerId, 'awaiting_confirmation', 'in_progress');
            $this->db->commit();
            return ['ok' => true, 'provider_id' => (int)$request['provider_id']];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['ok' => false, 'error' => $e instanceof DomainException ? $e->getMessage() : 'تعذر تسجيل الملاحظات'];
        }
    }

    public function confirmCompletion(int $requestId, int $customerId): array
    {
        $request = $this->find($requestId);
        $quote = $request ? $this->currentQuote($requestId) : null;
        if (!$request || (int)$request['customer_id'] !== $customerId || !$quote) return ['ok' => false, 'error' => 'الطلب غير موجود'];
        return $this->completeWithInvoice($requestId, (int)$request['provider_id'], (float)$quote['total_price'], $customerId);
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
                       pp.id as provider_profile_id, pp.profession as provider_profession,
                       pp.experience_years as provider_experience_years, pp.avg_rating as provider_avg_rating,
                       pp.is_verified as provider_is_verified, pu.name as provider_name, pu.phone as provider_phone,
                       q.total_price AS quoted_price, q.details AS quote_details, q.status AS quote_status, q.visit_at, q.decided_at AS quote_decided_at, q.created_at AS quote_created_at
                FROM service_requests sr
                LEFT JOIN services s ON s.id = sr.service_id
                LEFT JOIN users c ON c.id = sr.customer_id
                LEFT JOIN locations l ON l.id = sr.location_id
                LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
                LEFT JOIN users pu ON pu.id = pp.user_id
                LEFT JOIN request_quotes q ON q.id = sr.quote_id
                WHERE sr.id = :id LIMIT 1";
        $rows = $this->query($sql, ['id' => $id]);
        return $rows[0] ?? null;
    }

    public function forCustomer(int $customerId): array
    {
        return $this->query(
            "SELECT sr.*, s.name_ar as service_name_ar, s.name_en as service_name_en, pu.name as provider_name, q.status AS quote_status, q.total_price AS quoted_price
             FROM service_requests sr
             JOIN services s ON s.id = sr.service_id
             LEFT JOIN provider_profiles pp ON pp.id = sr.provider_id
             LEFT JOIN users pu ON pu.id = pp.user_id
             LEFT JOIN request_quotes q ON q.id = sr.quote_id
             WHERE sr.customer_id = :id ORDER BY sr.created_at DESC",
            ['id' => $customerId]
        );
    }

    public function forProvider(int $providerId): array
    {
        return $this->query(
            "SELECT sr.*, s.name_ar as service_name_ar, s.name_en as service_name_en, cu.name as customer_name, q.status AS quote_status, q.total_price AS quoted_price
             FROM service_requests sr
             JOIN services s ON s.id = sr.service_id
             JOIN users cu ON cu.id = sr.customer_id
             LEFT JOIN request_quotes q ON q.id = sr.quote_id
             WHERE (sr.provider_id = :id AND sr.status != 'rejected')
                OR (
                    sr.provider_id IS NULL 
                    AND sr.status = 'pending'
                    AND sr.service_id IN (SELECT service_id FROM provider_services WHERE provider_id = :id2)
                    AND (
                        sr.location_id IS NULL
                        OR EXISTS (
                            SELECT 1 FROM provider_locations pl 
                            LEFT JOIN locations req_l ON req_l.id = sr.location_id
                            LEFT JOIN locations pl_l ON pl_l.id = pl.location_id
                            WHERE pl.provider_id = :id3 
                              AND (
                                  pl.location_id = sr.location_id
                                  OR pl.location_id = req_l.parent_id
                                  OR pl_l.parent_id = sr.location_id
                              )
                        )
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM provider_rejections pr 
                        WHERE pr.request_id = sr.id AND pr.provider_id = :id4
                    )
                )
             ORDER BY sr.created_at DESC",
            ['id' => $providerId, 'id2' => $providerId, 'id3' => $providerId, 'id4' => $providerId]
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
        if (!is_finite($finalPrice) || $finalPrice <= 0) {
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
            $version2 = (int)($request['workflow_version'] ?? 1) >= 2;
            $quote = $version2 ? $this->currentQuote($requestId) : null;
            if ($version2 && ((int)$request['customer_id'] !== $userId || !$quote || $quote['status'] !== 'approved'
                || abs((float)$quote['total_price'] - $finalPrice) > 0.001)) {
                $this->db->rollBack();
                return ['ok' => false, 'error' => 'الاستلام للعميل فقط وبالسعر المعتمد في العرض'];
            }
            if ($request['status'] !== ($version2 ? 'awaiting_confirmation' : 'in_progress')) {
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
            error_log('Request invoice transaction failed (' . get_class($e) . ')');
            return ['ok' => false, 'error' => 'تعذر إصدار الفاتورة، حاول مجددًا'];
        }
    }
}
