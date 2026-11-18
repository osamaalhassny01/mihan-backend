<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AnalyticsService.php';
require_once __DIR__ . '/AI/AIServiceFactory.php';

/**
 * AdminDecisionSupportService
 * 
 * Provides end-to-end data aggregation, statistical KPI evaluation,
 * and AI-driven administrative decision recommendations for platform managers.
 * 
 * Covers the 4 core business pillars:
 * 1. Geographic Supply vs Demand Deficit Detection (نقص مقدمي الخدمات في المناطق)
 * 2. Provider Multi-Metric Performance Matrix (أداء مقدمي الخدمات)
 * 3. Most & Least Demanded Services & Specializations (الخدمات الأكثر والأقل طلباً)
 * 4. Complaints Hotspots & Quality Risk Radar (متابعة الشكاوى ومؤشرات الجودة)
 */
class AdminDecisionSupportService
{
    private PDO $db;
    private AnalyticsService $analytics;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->analytics = new AnalyticsService();
    }

    /**
     * Complete Decision Support Payload
     */
    public function getFullDashboardData(array $filters = []): array
    {
        $kpis = $this->getExecutiveKpis($filters);
        $supplyDemandDeficit = $this->getSupplyDemandDeficit($filters);
        $providerPerformance = $this->getProviderPerformanceMatrix($filters);
        $serviceDemand = $this->getServiceDemandAnalysis($filters);
        $complaintsHotspots = $this->getComplaintsHotspots($filters);
        
        // Generate AI-driven interpretations and actionable recommendations
        $aiRecommendations = $this->generateRecommendations([
            'kpis' => $kpis,
            'supply_demand' => $supplyDemandDeficit,
            'provider_performance' => $providerPerformance,
            'service_demand' => $serviceDemand,
            'complaints' => $complaintsHotspots,
        ]);

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'kpis' => $kpis,
            'supply_demand_deficit' => $supplyDemandDeficit,
            'provider_performance' => $providerPerformance,
            'service_demand' => $serviceDemand,
            'complaints_hotspots' => $complaintsHotspots,
            'ai_recommendations' => $aiRecommendations,
        ];
    }

    /**
     * Pillar 1: Executive KPI Aggregation
     */
    public function getExecutiveKpis(array $filters = []): array
    {
        $summary = $this->analytics->decisionSummary($filters);
        $overview = $this->analytics->overview();

        // Calculate complaints resolution rate
        $totalComplaints = (int)$this->scalar("SELECT COUNT(*) FROM complaints");
        $resolvedComplaints = (int)$this->scalar("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'");
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 100.0;

        // Active vs Verified Providers
        $totalProviders = (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role = 'provider'");
        $verifiedProviders = (int)$this->scalar("SELECT COUNT(*) FROM provider_profiles WHERE is_verified = 1");
        $availableProviders = (int)$this->scalar("SELECT COUNT(*) FROM provider_profiles WHERE is_available = 1");

        return [
            'total_requests' => $summary['total'],
            'completed_requests' => $summary['completed'],
            'completion_rate' => $summary['completion_rate'],
            'cancelled_requests' => $summary['cancelled'],
            'cancellation_rate' => $summary['cancellation_rate'],
            'rejected_requests' => $summary['rejected'],
            'rejection_rate' => $summary['rejection_rate'],
            'pending_requests' => $summary['pending'],
            'in_progress_requests' => $summary['in_progress'],
            'average_rating' => $summary['average_rating'],
            'total_revenue' => $summary['revenue'] > 0 ? $summary['revenue'] : $overview['revenue'],
            'platform_commission' => $summary['commission'] > 0 ? $summary['commission'] : $overview['commission'],
            'total_providers' => $totalProviders,
            'verified_providers' => $verifiedProviders,
            'available_providers' => $availableProviders,
            'open_complaints' => $overview['open_complaints'],
            'total_complaints' => $totalComplaints,
            'complaint_resolution_rate' => $resolutionRate,
        ];
    }

    /**
     * Pillar 2: Example 1 - Geographic Supply vs Demand Deficit Detection
     * Identifies areas with high request counts and zero or few registered/available providers.
     */
    public function getSupplyDemandDeficit(array $filters = []): array
    {
        $sql = "
            SELECT 
                l.id AS location_id,
                l.name_ar,
                l.name_en,
                l.city,
                COUNT(DISTINCT sr.id) AS requests_count,
                COUNT(DISTINCT pp.id) AS total_providers,
                COUNT(DISTINCT CASE WHEN pp.is_available = 1 THEN pp.id ELSE NULL END) AS available_providers,
                COUNT(DISTINCT CASE WHEN pp.is_verified = 1 THEN pp.id ELSE NULL END) AS verified_providers
            FROM locations l
            LEFT JOIN service_requests sr ON sr.location_id = l.id
            LEFT JOIN provider_locations pl ON pl.location_id = l.id
            LEFT JOIN provider_profiles pp ON pp.id = pl.provider_id
            WHERE l.is_active = 1
            GROUP BY l.id, l.name_ar, l.name_en, l.city
            ORDER BY requests_count DESC, available_providers ASC
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $r) {
            $req = (int)$r['requests_count'];
            $avail = (int)$r['available_providers'];
            $verified = (int)$r['verified_providers'];
            $totalProv = (int)$r['total_providers'];

            // Determine Deficit Level & Ratio
            $ratio = $avail > 0 ? round($req / $avail, 1) : (float)$req;
            
            $status = 'balanced';
            $statusLabel = 'متوازن';
            $badgeColor = 'success';
            $recommendation = 'توازن جيد بين الطلب والمهنيين المتاحين.';

            if ($req > 0 && $avail === 0) {
                $status = 'critical';
                $statusLabel = 'عجز حرج';
                $badgeColor = 'danger';
                $recommendation = "لا يوجد أي مهني متاح في هذه المنطقة حالياً رغم وجود {$req} طلب. يوصى باستقطاب وتفعيل مزودي خدمات فوراً.";
            } elseif ($ratio >= 3.0 && $req >= 3) {
                $status = 'high_deficit';
                $statusLabel = 'عجز مرتفع';
                $badgeColor = 'warning';
                $recommendation = "معدل الطلب ({$ratio} طلب لكل مهني) يفوق القدرة الاستيعابية. يوصى بتكثيف استقطاب مهنيين.";
            } elseif ($req === 0 && $totalProv > 0) {
                $status = 'idle_capacity';
                $statusLabel = 'طاقة راكدة';
                $badgeColor = 'info';
                $recommendation = "يوجد مهنيون مسجلون مع انعدام الطلبات. يوصى بتوجيه حملات تسويقية للمنطقة.";
            }

            $result[] = [
                'location_id' => (int)$r['location_id'],
                'name_ar' => $r['name_ar'],
                'name_en' => $r['name_en'] ?? $r['name_ar'],
                'city' => $r['city'] ?? '',
                'requests_count' => $req,
                'total_providers' => $totalProv,
                'available_providers' => $avail,
                'verified_providers' => $verified,
                'demand_to_supply_ratio' => $ratio,
                'status' => $status,
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor,
                'recommendation' => $recommendation,
            ];
        }

        // Sort so critical deficits appear first
        usort($result, function ($a, $b) {
            $weights = ['critical' => 3, 'high_deficit' => 2, 'idle_capacity' => 1, 'balanced' => 0];
            $wA = $weights[$a['status']] ?? 0;
            $wB = $weights[$b['status']] ?? 0;
            if ($wA !== $wB) return $wB <=> $wA;
            return $b['requests_count'] <=> $a['requests_count'];
        });

        return $result;
    }

    /**
     * Pillar 3: Example 2 - Provider Performance Matrix
     * Evaluates ratings, completion rates, rejections, availability, and verification.
     */
    public function getProviderPerformanceMatrix(array $filters = []): array
    {
        $sql = "
            SELECT 
                pp.id AS provider_id,
                pp.user_id,
                u.name AS provider_name,
                u.phone AS provider_phone,
                u.email AS provider_email,
                pp.profession,
                pp.years_exp,
                pp.avg_rating,
                pp.total_reviews,
                pp.completed_jobs,
                pp.is_verified,
                pp.is_available,
                COUNT(sr.id) AS total_assigned_requests,
                SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) AS completed_requests,
                SUM(CASE WHEN sr.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_requests,
                SUM(CASE WHEN sr.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_requests,
                COUNT(c.id) AS complaints_count
            FROM provider_profiles pp
            JOIN users u ON u.id = pp.user_id
            LEFT JOIN service_requests sr ON sr.provider_id = pp.id
            LEFT JOIN complaints c ON c.provider_id = pp.id
            GROUP BY pp.id, pp.user_id, u.name, u.phone, u.email, pp.profession, pp.years_exp,
                     pp.avg_rating, pp.total_reviews, pp.completed_jobs, pp.is_verified, pp.is_available
            ORDER BY pp.completed_jobs DESC, pp.avg_rating DESC
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $topPerformers = [];
        $needsAttention = [];
        $all = [];

        foreach ($rows as $r) {
            $totalAssigned = (int)$r['total_assigned_requests'];
            $completed = (int)$r['completed_requests'];
            $cancelled = (int)$r['cancelled_requests'];
            $rejected = (int)$r['rejected_requests'];
            $complaints = (int)$r['complaints_count'];
            $rating = (float)$r['avg_rating'];
            $isVerified = (int)$r['is_verified'] === 1;
            $isAvailable = (int)$r['is_available'] === 1;

            $completionRate = $totalAssigned > 0 ? round(($completed / $totalAssigned) * 100, 1) : 100.0;
            $rejectionRate = $totalAssigned > 0 ? round((($cancelled + $rejected) / $totalAssigned) * 100, 1) : 0.0;

            // Calculate Composite Score (0 - 100)
            // Rating (40%), Completion (35%), Verification (15%), Availability (10%)
            $ratingNorm = ($rating / 5.0) * 40.0;
            $completionNorm = ($completionRate / 100.0) * 35.0;
            $verifiedNorm = $isVerified ? 15.0 : 0.0;
            $availableNorm = $isAvailable ? 10.0 : 0.0;
            $compositeScore = round($ratingNorm + $completionNorm + $verifiedNorm + $availableNorm, 1);

            $tier = 'normal';
            $tierLabel = 'أداء جيد';
            $badgeColor = 'primary';
            $adminAction = 'الحفاظ على مستوى الخدمة ومتابعة دورية.';

            if ($compositeScore >= 80 && ($completed >= 1 || $rating >= 4.5)) {
                $tier = 'top_performer';
                $tierLabel = 'أداء مرتفع (متميز)';
                $badgeColor = 'success';
                $adminAction = 'منح أولوية الإسناد والإبراز في نتائج البحث كمهني موثوق.';
            } elseif ($complaints >= 2 || $rejectionRate >= 40 || ($rating > 0 && $rating < 3.0) || (!$isVerified && $totalAssigned > 0)) {
                $tier = 'needs_attention';
                $tierLabel = 'يحتاج متابعة إدارية';
                $badgeColor = 'danger';
                
                $reasons = [];
                if ($complaints >= 2) $reasons[] = "{$complaints} شكاوى مسجلة";
                if ($rejectionRate >= 40) $reasons[] = "نسبة رفض/إلغاء {$rejectionRate}%";
                if ($rating > 0 && $rating < 3.0) $reasons[] = "تقييم منخفض ({$rating}/5)";
                if (!$isVerified) $reasons[] = 'غير موثق إدارياً';
                
                $adminAction = 'يوصى بالتواصل مع المهني والتحقق من: ' . implode('، ', $reasons);
            }

            $providerItem = [
                'provider_id' => (int)$r['provider_id'],
                'user_id' => (int)$r['user_id'],
                'name' => $r['provider_name'],
                'phone' => $r['provider_phone'] ?? '',
                'email' => $r['provider_email'] ?? '',
                'profession' => $r['profession'] ?? 'فني خدمات',
                'years_exp' => (int)$r['years_exp'],
                'avg_rating' => $rating,
                'total_reviews' => (int)$r['total_reviews'],
                'completed_jobs' => (int)$r['completed_jobs'],
                'total_assigned' => $totalAssigned,
                'completion_rate' => $completionRate,
                'rejection_rate' => $rejectionRate,
                'complaints_count' => $complaints,
                'is_verified' => $isVerified,
                'is_available' => $isAvailable,
                'composite_score' => $compositeScore,
                'tier' => $tier,
                'tier_label' => $tierLabel,
                'badge_color' => $badgeColor,
                'admin_action' => $adminAction,
            ];

            $all[] = $providerItem;
            if ($tier === 'top_performer') {
                $topPerformers[] = $providerItem;
            } elseif ($tier === 'needs_attention') {
                $needsAttention[] = $providerItem;
            }
        }

        // Sort Top Performers by Score DESC
        usort($topPerformers, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);
        // Sort Needs Attention by Complaints DESC, Composite ASC
        usort($needsAttention, function ($a, $b) {
            if ($a['complaints_count'] !== $b['complaints_count']) {
                return $b['complaints_count'] <=> $a['complaints_count'];
            }
            return $a['composite_score'] <=> $b['composite_score'];
        });

        return [
            'total_evaluated' => count($all),
            'top_performers_count' => count($topPerformers),
            'needs_attention_count' => count($needsAttention),
            'top_performers' => array_slice($topPerformers, 0, 10),
            'needs_attention' => array_slice($needsAttention, 0, 10),
            'all_providers' => $all,
        ];
    }

    /**
     * Pillar 4: Example 3 - Most & Least Demanded Services
     * Pinpoints which specializations require recruiting more providers.
     */
    public function getServiceDemandAnalysis(array $filters = []): array
    {
        $sql = "
            SELECT 
                s.id AS service_id,
                s.name_ar,
                s.name_en,
                s.base_price,
                c.id AS category_id,
                c.name_ar AS category_name,
                COUNT(DISTINCT sr.id) AS requests_count,
                COUNT(DISTINCT pl.provider_id) AS active_providers_count,
                SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN sr.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(i.total_amount), 0) AS total_revenue
            FROM services s
            JOIN categories c ON c.id = s.category_id
            LEFT JOIN service_requests sr ON sr.service_id = s.id
            LEFT JOIN provider_services pl ON pl.service_id = s.id
            LEFT JOIN invoices i ON i.request_id = sr.id AND i.payment_status = 'paid'
            WHERE s.is_active = 1
            GROUP BY s.id, s.name_ar, s.name_en, s.base_price, c.id, c.name_ar
            ORDER BY requests_count DESC, s.name_ar ASC
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $mostDemanded = [];
        $leastDemanded = [];
        $specializationDeficit = [];

        $totalRequests = array_sum(array_column($rows, 'requests_count')) ?: 1;

        foreach ($rows as $r) {
            $req = (int)$r['requests_count'];
            $providers = (int)$r['active_providers_count'];
            $share = round(($req / $totalRequests) * 100, 1);

            $item = [
                'service_id' => (int)$r['service_id'],
                'name_ar' => $r['name_ar'],
                'name_en' => $r['name_en'] ?? $r['name_ar'],
                'category_name' => $r['category_name'],
                'base_price' => (float)$r['base_price'],
                'requests_count' => $req,
                'active_providers_count' => $providers,
                'completed_count' => (int)$r['completed_count'],
                'cancelled_count' => (int)$r['cancelled_count'],
                'total_revenue' => (float)$r['total_revenue'],
                'market_share_percent' => $share,
            ];

            // If demand is high (>2) and providers <= 1, it's a specialization deficit!
            if ($req >= 2 && $providers <= 1) {
                $specializationDeficit[] = array_merge($item, [
                    'deficit_urgency' => $providers === 0 ? 'حرجة' : 'متوسطة',
                    'recommendation' => "طلب مرتفع ({$req} طلب) مع نقص بالمهنيين ({$providers} فني فقط). يوصى باستقطاب مزودي خدمة جدد في هذا التخصص فوراً.",
                ]);
            }

            if ($req > 0) {
                $mostDemanded[] = $item;
            } else {
                $leastDemanded[] = $item;
            }
        }

        // Sort Most Demanded DESC
        usort($mostDemanded, fn($a, $b) => $b['requests_count'] <=> $a['requests_count']);
        // Sort Least Demanded ASC (zero requests first)
        usort($leastDemanded, fn($a, $b) => $a['requests_count'] <=> $b['requests_count']);

        return [
            'most_demanded' => array_slice($mostDemanded, 0, 8),
            'least_demanded' => array_slice($leastDemanded, 0, 8),
            'specialization_deficit' => $specializationDeficit,
        ];
    }

    /**
     * Pillar 5: Example 4 - Complaints Hotspots & Quality Radar
     * Identifies frequent complaint triggers by service, region, and provider.
     */
    public function getComplaintsHotspots(array $filters = []): array
    {
        $totalComplaints = (int)$this->scalar("SELECT COUNT(*) FROM complaints");

        // Complaints by Category / Service
        $byService = $this->db->query("
            SELECT 
                s.name_ar AS service_name,
                c.name_ar AS category_name,
                COUNT(cmp.id) AS complaints_count
            FROM complaints cmp
            JOIN service_requests sr ON sr.id = cmp.request_id
            JOIN services s ON s.id = sr.service_id
            JOIN categories c ON c.id = s.category_id
            GROUP BY s.id, s.name_ar, c.name_ar
            ORDER BY complaints_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Complaints by Location
        $byLocation = $this->db->query("
            SELECT 
                l.name_ar AS location_name,
                COUNT(cmp.id) AS complaints_count
            FROM complaints cmp
            JOIN service_requests sr ON sr.id = cmp.request_id
            JOIN locations l ON l.id = sr.location_id
            GROUP BY l.id, l.name_ar
            ORDER BY complaints_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Complaints by Provider (Repeated offenders)
        $byProvider = $this->db->query("
            SELECT 
                pp.id AS provider_id,
                u.name AS provider_name,
                pp.profession,
                COUNT(cmp.id) AS complaints_count,
                ROUND(AVG(pp.avg_rating), 1) AS rating
            FROM complaints cmp
            JOIN provider_profiles pp ON pp.id = cmp.provider_id
            JOIN users u ON u.id = pp.user_id
            GROUP BY pp.id, u.name, pp.profession
            HAVING complaints_count > 0
            ORDER BY complaints_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Status Breakdown
        $statusBreakdown = $this->db->query("
            SELECT status, COUNT(*) AS count 
            FROM complaints 
            GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Recent Complaints with details
        $recentComplaints = $this->db->query("
            SELECT 
                cmp.id,
                cmp.request_id,
                cmp.status,
                cmp.description,
                cmp.created_at,
                u.name AS customer_name,
                pu.name AS provider_name,
                s.name_ar AS service_name
            FROM complaints cmp
            LEFT JOIN users u ON u.id = cmp.user_id
            LEFT JOIN provider_profiles pp ON pp.id = cmp.provider_id
            LEFT JOIN users pu ON pu.id = pp.user_id
            LEFT JOIN service_requests sr ON sr.id = cmp.request_id
            LEFT JOIN services s ON s.id = sr.service_id
            ORDER BY cmp.created_at DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_complaints' => $totalComplaints,
            'status_breakdown' => $statusBreakdown,
            'hotspots_by_service' => $byService,
            'hotspots_by_location' => $byLocation,
            'repeated_providers' => $byProvider,
            'recent_complaints' => $recentComplaints,
        ];
    }

    /**
     * Pillar 6: AI-Driven Decision & Executive Action Generator
     * Formulates structured, actionable recommendations using LLM or smart algorithmic fallback.
     */
    public function generateRecommendations(array $data): array
    {
        $recommendations = [];

        // 1. Deficit Check (Example 1)
        foreach ($data['supply_demand'] as $area) {
            if ($area['status'] === 'critical') {
                $recommendations[] = [
                    'id' => 'rec_deficit_' . $area['location_id'],
                    'pillar' => 'supply_demand',
                    'severity' => 'urgent',
                    'title' => 'عجز حرج في مقدمي الخدمات بمنطقة ' . $area['name_ar'],
                    'explanation' => "سجلت المنطقة {$area['requests_count']} طلبات بينما لا يتوفر فيها أي مهني معتمد متاح حالياً، مما يهدد بتأخير تنفيذ الطلبات وإلغائها.",
                    'recommendation' => "استقطاب فوري لـ 3-5 مزودي خدمات وتوجيه طلبات التوثيق الجديدة لهذه المنطقة، مع إتاحة حوافز تشجيعية.",
                    'action_label' => 'عرض مهنيي المنطقة',
                    'action_type' => 'view_location',
                    'target_id' => $area['location_id'],
                ];
            } elseif ($area['status'] === 'high_deficit' && count($recommendations) < 4) {
                $recommendations[] = [
                    'id' => 'rec_deficit_' . $area['location_id'],
                    'pillar' => 'supply_demand',
                    'severity' => 'warning',
                    'title' => 'ضغط طلبات مرتفع في ' . $area['name_ar'],
                    'explanation' => "نسبة الطلب بلغت {$area['demand_to_supply_ratio']} طلب لكل فني، مما قد يسبب تأخر الاستجابة وزيادة أوقات الانتظار.",
                    'recommendation' => "توسيع نطاق تغطية الفنيين في المناطق المجاورة لتشمل هذه المنطقة ريثما يتم اعتماد مهنيين إضافيين.",
                    'action_label' => 'إدارة مناطق التغطية',
                    'action_type' => 'view_location',
                    'target_id' => $area['location_id'],
                ];
            }
        }

        // 2. Specialization Deficit Check (Example 3)
        foreach ($data['service_demand']['specialization_deficit'] as $spec) {
            $recommendations[] = [
                'id' => 'rec_spec_' . $spec['service_id'],
                'pillar' => 'service_demand',
                'severity' => 'warning',
                'title' => 'تخصص يحتاج استقطاب: ' . $spec['name_ar'],
                'explanation' => "هذه الخدمة تشهد إقبالاً بـ ({$spec['requests_count']} طلب) بينما لا يوجد سوى ({$spec['active_providers_count']} فني) مؤهل لتقديمها.",
                'recommendation' => "الإعلان عن فتح باب التسجيل وتسهيل اعتماد الفنيين المتخصصين في {$spec['name_ar']} لرفع الطاقة الاستيعابية.",
                'action_label' => 'عرض تفاصيل الخدمة',
                'action_type' => 'view_service',
                'target_id' => $spec['service_id'],
            ];
        }

        // 3. Provider Watchlist Check (Example 2)
        foreach ($data['provider_performance']['needs_attention'] as $prov) {
            if ($prov['complaints_count'] >= 1 || $prov['rejection_rate'] >= 40) {
                $recommendations[] = [
                    'id' => 'rec_prov_' . $prov['provider_id'],
                    'pillar' => 'provider_performance',
                    'severity' => 'warning',
                    'title' => 'متابعة أداء المهني: ' . $prov['name'],
                    'explanation' => "لوحظ ارتفاع في نسبة الرفض/الإلغاء ({$prov['rejection_rate']}%) ووجود ({$prov['complaints_count']} شكوى)، مع تقييم عام ({$prov['avg_rating']}/5).",
                    'recommendation' => "مراجعة جودة تنفيذ الأعمال مع المهني، وتقديم إشعار تنبيهي أو إعادة توجيهه لرفع نسبة الالتزام.",
                    'action_label' => 'مراجعة ملف المهني',
                    'action_type' => 'view_provider',
                    'target_id' => $prov['provider_id'],
                ];
                break; // Limit to highest priority watchlist item
            }
        }

        // 4. Complaints Hotspot Check (Example 4)
        if (!empty($data['complaints']['hotspots_by_service'])) {
            $topComplaint = $data['complaints']['hotspots_by_service'][0];
            if ((int)$topComplaint['complaints_count'] >= 2) {
                $recommendations[] = [
                    'id' => 'rec_comp_' . md5($topComplaint['service_name']),
                    'pillar' => 'complaints',
                    'severity' => 'urgent',
                    'title' => 'بؤرة شكاوى في خدمة: ' . $topComplaint['service_name'],
                    'explanation' => "تركزت الشكاوى على هذه الخدمة بـ ({$topComplaint['complaints_count']} شكوى)، مما يشير إلى خلل في التسعير أو آلية التنفيذ.",
                    'recommendation' => "مراجعة بنود ومعايير تنفيذ الخدمة وضبط آلية التسعير بين العملاء ومقدمي الخدمة لتلافي النزاعات.",
                    'action_label' => 'متابعة الشكاوى',
                    'action_type' => 'view_complaints',
                    'target_id' => 0,
                ];
            }
        }

        // 5. High Performance Commendation
        if (!empty($data['provider_performance']['top_performers'])) {
            $star = $data['provider_performance']['top_performers'][0];
            $recommendations[] = [
                'id' => 'rec_star_' . $star['provider_id'],
                'pillar' => 'provider_performance',
                'severity' => 'opportunity',
                'title' => 'مهني متميز عالي الأداء: ' . $star['name'],
                'explanation' => "حقق تقييماً ممتازاً ({$star['avg_rating']}/5) مع إتمام ({$star['completed_jobs']} عمل) بنسبة نجاح ({$star['completion_rate']}%).",
                'recommendation' => "منح المهني شارة 'مهني الشهر المعتمد' وأولوية الإسناد لتعزيز رضا العملاء وتحفيز باقي الفريق.",
                'action_label' => 'عرض الملف المهني',
                'action_type' => 'view_provider',
                'target_id' => $star['provider_id'],
            ];
        }

        // If no high-risk items, provide general healthy optimization recommendation
        if (empty($recommendations)) {
            $recommendations[] = [
                'id' => 'rec_healthy',
                'pillar' => 'overview',
                'severity' => 'opportunity',
                'title' => 'استقرار تشغيلي عام',
                'explanation' => 'كافة المؤشرات التشغيلية ونسب الإتمام وتوازن العرض والطلب ضمن النطاقات المقبولة حالياً.',
                'recommendation' => 'الاستمرار في المراقبة الدورية للشكاوى وتكثيف التسويق في المناطق ذات الطلب المنخفض.',
                'action_label' => 'عرض تقرير الأداء',
                'action_type' => 'refresh',
                'target_id' => 0,
            ];
        }

        return $recommendations;
    }

    /**
     * Ask AI Advisor: Allows the admin to ask ad-hoc questions about platform data
     */
    public function askAdvisor(string $question): array
    {
        $question = trim($question);
        if ($question === '') {
            $question = 'قدم ملخصاً تحليلياً شاملاً لوضع المنصة وأهم القرارات الإدارية الواجب اتخاذها.';
        }

        $analyticsData = $this->getFullDashboardData();

        $systemPrompt = "أنت 'المستشار الذكي لدعم القرار الإداري' في منصة مِهَن (Mihan Platform).
مهمتك مساعدة مدير النظام في اتخاذ قرارات تشغيلية واستراتيجية مبنية على البيانات الفعلية فقط.
تلتزم بما يلي:
1. استند بحزم ودقة إلى أرقام وإحصائيات JSON المزودة فقط، ولا تختلق أي أرقام أو أسماء غير موجودة.
2. نسق إجابتك بلغة عربية إدارية راقية واحترافية باستخدام نقاط محددة:
   - 📌 التشخيص والوضع الراهن.
   - ⚠️ التحديات والمخاطر المكتشفة (العجز الجغرافي، تدني الأداء، الشكاوى).
   - 💡 التوصيات والقرارات الإدارية المقترحة (خطوات عملية قابلة للتنفيذ فوراً).
3. كن موجزاً ومباشراً وموجهاً لصانع القرار.";

        $userPrompt = "بيانات المنصة الحالية بالكامل:\n" . 
            json_encode($analyticsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . 
            "\n\nسؤال مدير النظام:\n" . $question;

        try {
            $aiResponse = AIServiceFactory::chat([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ], [
                'temperature' => 0.2,
                'max_tokens' => 1200,
            ]);

            return [
                'success' => true,
                'question' => $question,
                'answer' => $aiResponse['text'] ?? '',
                'provider' => $aiResponse['provider'] ?? 'local',
                'fallback_used' => !empty($aiResponse['fallback']),
            ];
        } catch (Throwable $e) {
            // Algorithmic fallback response
            return [
                'success' => true,
                'question' => $question,
                'answer' => $this->generateAlgorithmicAdvisorSummary($analyticsData),
                'provider' => 'local_algorithmic',
                'fallback_used' => true,
            ];
        }
    }

    private function generateAlgorithmicAdvisorSummary(array $data): string
    {
        $kpis = $data['kpis'];
        $lines = [];
        $lines[] = "### 📌 التقرير التحليلي الإداري الذكي";
        $lines[] = "- **إجمالي الطلبات:** {$kpis['total_requests']} طلب (مكتملة: {$kpis['completed_requests']} بنسبة {$kpis['completion_rate']}%)";
        $lines[] = "- **مقدمو الخدمات:** {$kpis['total_providers']} مسجلون ({$kpis['verified_providers']} موثقون، {$kpis['available_providers']} متاحون)";
        $lines[] = "- **الشكاوى المفتوحة:** {$kpis['open_complaints']} شكوى (نسبة الحل: {$kpis['complaint_resolution_rate']}%)";
        $lines[] = "- **الإيرادات الإجمالية:** " . number_format($kpis['total_revenue']) . " ريال (العمولات: " . number_format($kpis['platform_commission']) . " ريال)\n";

        $lines[] = "### ⚠️ أبرز الملاحظات التشغيلية:";
        foreach (array_slice($data['ai_recommendations'], 0, 3) as $rec) {
            $lines[] = "- **{$rec['title']}:** {$rec['explanation']}";
        }

        $lines[] = "\n### 💡 التوصيات الإدارية المقترحة:";
        foreach (array_slice($data['ai_recommendations'], 0, 3) as $rec) {
            $lines[] = "1. {$rec['recommendation']}";
        }

        return implode("\n", $lines);
    }

    private function scalar(string $sql, array $params = [], $default = 0)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return $default;
        return array_values($row)[0] ?? $default;
    }
}
