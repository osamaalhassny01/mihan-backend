<?php
require_once __DIR__ . '/../core/Model.php';

/**
 * AnalyticsService - real-data analytics and decision-support indicators.
 * No fabricated/demo figures are generated here.
 */
class AnalyticsService
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    private function scalar(string $sql, array $params = [], $default = 0)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return $default;
        return array_values($row)[0] ?? $default;
    }

    public function overview(): array
    {
        return [
            'total_users' => (int)$this->scalar("SELECT COUNT(*) FROM users"),
            'customers' => (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role='customer'"),
            'providers' => (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role='provider'"),
            'verified_providers' => (int)$this->scalar("SELECT COUNT(*) FROM provider_profiles WHERE is_verified=1"),
            'pending_requests' => (int)$this->scalar("SELECT COUNT(*) FROM service_requests WHERE status='pending'"),
            'completed_requests' => (int)$this->scalar("SELECT COUNT(*) FROM service_requests WHERE status='completed'"),
            'cancelled_requests' => (int)$this->scalar("SELECT COUNT(*) FROM service_requests WHERE status='cancelled'"),
            'open_complaints' => (int)$this->scalar("SELECT COUNT(*) FROM complaints WHERE status IN ('open','in_review')"),
            'revenue' => (float)$this->scalar("SELECT COALESCE(SUM(total_amount),0) FROM invoices WHERE payment_status='paid'"),
            'commission' => (float)$this->scalar("SELECT COALESCE(SUM(amount),0) FROM platform_commissions"),
        ];
    }

    public function decisionSummary(array $filters = []): array
    {
        [$where, $params] = $this->requestFilter($filters, 'sr');
        $total = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where", $params);
        $completed = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='completed'", $params);
        $cancelled = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='cancelled'", $params);
        $rejected = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='rejected'", $params);
        $pending = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='pending'", $params);
        $inProgress = (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='in_progress'", $params);
        $rating = (float)$this->scalar("SELECT COALESCE(AVG(r.rating),0) FROM reviews r JOIN service_requests sr ON sr.id=r.request_id WHERE $where", $params);
        $revenue = (float)$this->scalar("SELECT COALESCE(SUM(i.total_amount),0) FROM invoices i JOIN service_requests sr ON sr.id=i.request_id WHERE i.payment_status='paid' AND $where", $params);
        $commission = (float)$this->scalar("SELECT COALESCE(SUM(i.commission_amount),0) FROM invoices i JOIN service_requests sr ON sr.id=i.request_id WHERE i.payment_status='paid' AND $where", $params);

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completion_rate' => $total ? round($completed / $total * 100, 1) : 0,
            'cancellation_rate' => $total ? round($cancelled / $total * 100, 1) : 0,
            'rejected' => $rejected,
            'rejection_rate' => $total ? round($rejected / $total * 100, 1) : 0,
            'average_rating' => round($rating, 2),
            'revenue' => round($revenue, 2),
            'commission' => round($commission, 2),
        ];
    }

    public function requestsByMonth(int $months = 6): array
    {
        $months = max(1, min(24, $months));
        return $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c FROM service_requests
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
             GROUP BY ym ORDER BY ym"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function servicesByCategory(): array
    {
        return $this->db->query(
            "SELECT c.name_ar, COUNT(sr.id) c FROM categories c
             JOIN services s ON s.category_id=c.id
             LEFT JOIN service_requests sr ON sr.service_id=s.id
             GROUP BY c.id, c.name_ar ORDER BY c DESC LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mostRequestedServices(array $filters = [], int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        $sql = "SELECT s.id, s.name_ar, s.name_en, c.name_ar category_name, c.name_en category_name_en, COUNT(sr.id) c
                FROM services s JOIN categories c ON c.id=s.category_id
                LEFT JOIN service_requests sr ON sr.service_id=s.id AND $where
                GROUP BY s.id, s.name_ar, s.name_en, c.name_ar, c.name_en ORDER BY c DESC, s.name_ar LIMIT $limit";
        return $this->query($sql, $params);
    }

    public function leastRequestedServices(array $filters = [], int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT s.id, s.name_ar, s.name_en, c.name_ar category_name, c.name_en category_name_en, COUNT(sr.id) c
             FROM services s JOIN categories c ON c.id=s.category_id
             LEFT JOIN service_requests sr ON sr.service_id=s.id AND $where
             WHERE s.is_active=1
             GROUP BY s.id, s.name_ar, s.name_en, c.name_ar, c.name_en
             ORDER BY c ASC, s.name_ar ASC LIMIT $limit", $params
        );
    }

    public function newUsers(array $filters = []): int
    {
        $parts=['1=1']; $params=[];
        if (!empty($filters['date_from'])) { $parts[]='created_at >= :udf'; $params['udf']=$filters['date_from'].' 00:00:00'; }
        if (!empty($filters['date_to'])) { $parts[]='created_at <= :udt'; $params['udt']=$filters['date_to'].' 23:59:59'; }
        return (int)$this->scalar("SELECT COUNT(*) FROM users WHERE ".implode(' AND ',$parts), $params);
    }

    public function peakHours(array $filters = [], int $limit = 6): array
    {
        $limit=max(1,min(24,$limit));
        [$where,$params]=$this->requestFilter($filters,'sr');
        return $this->query(
            "SELECT HOUR(sr.created_at) hour, COUNT(*) c FROM service_requests sr WHERE $where
             GROUP BY HOUR(sr.created_at) ORDER BY c DESC, hour ASC LIMIT $limit", $params
        );
    }

    public function ratingsByProfession(array $filters = [], int $limit = 20): array
    {
        $limit=max(1,min(50,$limit));
        [$where,$params]=$this->requestFilter($filters,'sr');
        return $this->query(
            "SELECT pp.profession, COUNT(r.id) review_count, ROUND(AVG(r.rating),2) avg_rating
             FROM reviews r JOIN provider_profiles pp ON pp.id=r.provider_id
             JOIN service_requests sr ON sr.id=r.request_id
             WHERE $where AND pp.profession IS NOT NULL AND pp.profession <> ''
             GROUP BY pp.profession ORDER BY avg_rating DESC, review_count DESC LIMIT $limit", $params
        );
    }

    public function rejectedRequests(array $filters = []): int
    {
        [$where,$params]=$this->requestFilter($filters,'sr');
        return (int)$this->scalar("SELECT COUNT(*) FROM service_requests sr WHERE $where AND sr.status='rejected'",$params);
    }

    public function areaProviderNeed(array $filters = [], int $limit = 10): array
    {
        $limit=max(1,min(50,$limit));
        [$where,$params]=$this->requestFilter($filters,'sr');
        return $this->query(
            "SELECT l.id, l.name_ar, l.name_en, COUNT(DISTINCT sr.id) request_count,
                    COUNT(DISTINCT pp.id) provider_count,
                    ROUND(COUNT(DISTINCT sr.id) / NULLIF(COUNT(DISTINCT pp.id),0),1) demand_per_provider
             FROM locations l
             LEFT JOIN service_requests sr ON sr.location_id=l.id AND $where
             LEFT JOIN provider_locations pl ON pl.location_id=l.id
             LEFT JOIN provider_profiles pp ON pp.id=pl.provider_id AND pp.is_available=1
             WHERE l.is_active=1
             GROUP BY l.id,l.name_ar,l.name_en
             HAVING request_count > 0
             ORDER BY provider_count ASC, request_count DESC, demand_per_provider DESC LIMIT $limit", $params
        );
    }

    public function mostActiveAreas(int $limit = 10, array $filters = []): array
    {
        $limit = max(1, min(50, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT l.id, l.name_ar, COUNT(sr.id) c FROM locations l
             JOIN service_requests sr ON sr.location_id=l.id
             WHERE $where GROUP BY l.id, l.name_ar ORDER BY c DESC LIMIT $limit", $params
        );
    }

    public function topProviders(int $limit = 5, array $filters = []): array
    {
        $limit = max(1, min(50, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT pp.id, u.name, pp.avg_rating, pp.completed_jobs, COUNT(sr.id) request_count,
                    SUM(CASE WHEN sr.status='completed' THEN 1 ELSE 0 END) completed_count
             FROM provider_profiles pp JOIN users u ON u.id=pp.user_id
             LEFT JOIN service_requests sr ON sr.provider_id=pp.id AND $where
             GROUP BY pp.id, u.name, pp.avg_rating, pp.completed_jobs
             ORDER BY completed_count DESC, pp.avg_rating DESC LIMIT $limit", $params
        );
    }

    public function providerPerformance(array $filters = [], int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT pp.id, u.name, pp.avg_rating, pp.completed_jobs,
                    COUNT(sr.id) total_requests,
                    SUM(CASE WHEN sr.status='completed' THEN 1 ELSE 0 END) completed_requests,
                    SUM(CASE WHEN sr.status='cancelled' THEN 1 ELSE 0 END) cancelled_requests,
                    SUM(CASE WHEN sr.status='rejected' THEN 1 ELSE 0 END) rejected_requests,
                    ROUND(100 * SUM(CASE WHEN sr.status='completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(sr.id),0),1) completion_rate,
                    ROUND(100 * SUM(CASE WHEN sr.status='rejected' THEN 1 ELSE 0 END) / NULLIF(COUNT(sr.id),0),1) rejection_rate
             FROM provider_profiles pp JOIN users u ON u.id=pp.user_id
             LEFT JOIN service_requests sr ON sr.provider_id=pp.id AND $where
             GROUP BY pp.id, u.name, pp.avg_rating, pp.completed_jobs
             ORDER BY completion_rate DESC, pp.avg_rating DESC LIMIT $limit", $params
        );
    }

    public function revenueByMonth(int $months = 6): array
    {
        $months = max(1, min(24, $months));
        return $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COALESCE(SUM(total_amount),0) total,
                    COALESCE(SUM(commission_amount),0) commission
             FROM invoices WHERE payment_status='paid'
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)
             GROUP BY ym ORDER BY ym"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statusBreakdown(array $filters = []): array
    {
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT sr.status, COUNT(*) c FROM service_requests sr WHERE $where GROUP BY sr.status ORDER BY c DESC", $params
        );
    }

    /** Human-readable, data-derived recommendations for administrators. */
    public function decisionInsights(array $filters = []): array
    {
        $summary = $this->decisionSummary($filters);
        $services = $this->mostRequestedServices($filters, 3);
        $areas = $this->mostActiveAreas(3, $filters);
        $insights = [];

        if ($summary['total'] === 0) {
            return ['لا توجد بيانات كافية ضمن الفترة أو الفلاتر المحددة لإنتاج توصيات إدارية.'];
        }
        if ($summary['completion_rate'] < 60) {
            $insights[] = 'نسبة إتمام الطلبات أقل من 60%؛ يوصى بمراجعة أسباب التعثر والإلغاء وأداء مقدمي الخدمة.';
        } elseif ($summary['completion_rate'] >= 80) {
            $insights[] = 'نسبة إتمام الطلبات مرتفعة؛ يوصى بالحفاظ على آلية التشغيل الحالية ومراقبة النمو.';
        }
        if ($summary['cancellation_rate'] >= 15) {
            $insights[] = 'نسبة الإلغاء مرتفعة نسبيًا؛ يوصى بتحليل أسباب الإلغاء وتحسين المطابقة والتوفر.';
        }
        if ($summary['average_rating'] > 0 && $summary['average_rating'] < 3.5) {
            $insights[] = 'متوسط التقييم منخفض؛ يوصى بمراجعة الشكاوى وأداء مقدمي الخدمات ذوي التقييمات المنخفضة.';
        }
        if (!empty($services)) {
            $insights[] = 'أعلى خدمة طلبًا حاليًا: ' . $services[0]['name_ar'] . ' (' . $services[0]['c'] . ' طلب).';
        }
        if (!empty($areas)) {
            $insights[] = 'أكثر منطقة نشاطًا: ' . $areas[0]['name_ar'] . ' (' . $areas[0]['c'] . ' طلب).';
        }
        if ($summary['pending'] > $summary['completed'] && $summary['total'] >= 10) {
            $insights[] = 'الطلبات المعلقة تتجاوز الطلبات المكتملة ضمن الفترة؛ يوصى بزيادة مقدمي الخدمة المتاحين أو تحسين التوزيع.';
        }
        return $insights ?: ['مؤشرات الأداء ضمن نطاق مستقر وفق البيانات المتاحة؛ استمر في المراقبة الدورية.'];
    }

    public function reportRequests(array $filters = [], int $limit = 500): array
    {
        $limit = max(1, min(2000, $limit));
        [$where, $params] = $this->requestFilter($filters, 'sr');
        return $this->query(
            "SELECT sr.*, s.name_ar service_name_ar, s.name_en service_name_en, c.name_ar category_name, c.name_en category_name_en, u.name customer_name,
                    l.name_ar location_name, l.name_en location_name_en
             FROM service_requests sr
             JOIN services s ON s.id=sr.service_id
             JOIN categories c ON c.id=s.category_id
             JOIN users u ON u.id=sr.customer_id
             LEFT JOIN locations l ON l.id=sr.location_id
             WHERE $where ORDER BY sr.created_at DESC LIMIT $limit", $params
        );
    }

    private function requestFilter(array $filters, string $alias = 'sr'): array
    {
        $parts = ['1=1']; $params = [];
        if (!empty($filters['date_from'])) { $parts[] = "$alias.created_at >= :df"; $params['df'] = $filters['date_from'].' 00:00:00'; }
        if (!empty($filters['date_to'])) { $parts[] = "$alias.created_at <= :dt"; $params['dt'] = $filters['date_to'].' 23:59:59'; }
        if (!empty($filters['category_id'])) { $parts[] = "$alias.service_id IN (SELECT id FROM services WHERE category_id=:cat)"; $params['cat']=(int)$filters['category_id']; }
        if (!empty($filters['location_id'])) { $parts[] = "$alias.location_id=:loc"; $params['loc']=(int)$filters['location_id']; }
        if (!empty($filters['status'])) { $parts[] = "$alias.status=:st"; $params['st']=$filters['status']; }
        return [implode(' AND ', $parts), $params];
    }

    private function query(string $sql, array $params = []): array
    {
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
