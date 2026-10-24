<?php
require_once __DIR__ . '/../../core/Model.php';

/**
 * RecommendationEngine - Rule-Based provider ranking.
 *
 * Recommendation Score = ServiceMatch + LocationMatch + RatingScore
 *                       + ExperienceScore + AvailabilityScore + ReliabilityScore
 *
 * Runs entirely on local DB data; no external API required.
 */
class RecommendationEngine
{
    private const W_SERVICE = 30;
    private const W_LOCATION = 20;
    private const W_RATING = 20;
    private const W_EXPERIENCE = 10;
    private const W_AVAILABILITY = 10;
    private const W_RELIABILITY = 10;

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * @return array List of providers with 'score' and 'breakdown', sorted desc.
     */
    public function recommend(int $serviceId, ?int $locationId, int $limit = 10, ?string $preferredDate = null, ?string $preferredTime = null): array
    {
        $sql = "SELECT pp.*, u.name, u.profile_photo
                FROM provider_profiles pp
                JOIN users u ON u.id = pp.user_id
                JOIN provider_services ps ON ps.provider_id = pp.id AND ps.service_id = :service_id
                WHERE u.is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['service_id' => $serviceId]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($candidates as $p) {
            $breakdown = $this->scoreProvider($p, $serviceId, $locationId, $preferredDate, $preferredTime);
            $p['score'] = $breakdown['total'];
            $p['score_breakdown'] = $breakdown;
            $p['recommendation_reasons'] = $this->buildReasons($breakdown, $p);
            $results[] = $p;
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($results, 0, $limit);
    }

    private function scoreProvider(array $provider, int $serviceId, ?int $locationId, ?string $preferredDate = null, ?string $preferredTime = null): array
    {
        // 1. Service Match: provider already filtered to offer this service => full weight
        $serviceMatch = self::W_SERVICE;

        // 2. Location Match
        $locationMatch = 0;
        if ($locationId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) c FROM provider_locations WHERE provider_id = :p AND location_id = :l");
            $stmt->execute(['p' => $provider['id'], 'l' => $locationId]);
            $locationMatch = ((int)$stmt->fetch()['c'] > 0) ? self::W_LOCATION : self::W_LOCATION * 0.3;
        } else {
            $locationMatch = self::W_LOCATION * 0.5; // neutral if customer gave no location
        }

        // 3. Rating Score (normalized out of 5)
        $ratingScore = ((float)$provider['avg_rating'] / 5) * self::W_RATING;

        // 4. Experience Score (capped at 10 years for normalization)
        $experienceScore = min((int)$provider['experience_years'], 10) / 10 * self::W_EXPERIENCE;

        // 5. Availability Score
        // is_available is the provider's master switch. If a weekly schedule
        // exists, also check the requested slot (or the current slot when no
        // date/time was supplied). Providers without a configured schedule keep
        // the legacy full score when their master switch is enabled.
        $availabilityScore = 0;
        if ((int)$provider['is_available'] === 1) {
            require_once __DIR__ . '/../../models/ProviderProfile.php';
            $providerModel = new ProviderProfile();
            $scheduled = $providerModel->scheduledAvailability(
                (int)$provider['id'],
                $preferredDate,
                $preferredTime
            );
            if ($scheduled === null) {
                $availabilityScore = self::W_AVAILABILITY;
            } elseif ($scheduled === true) {
                $availabilityScore = self::W_AVAILABILITY;
            } else {
                // Keep the provider visible, but clearly rank scheduled matches higher.
                $availabilityScore = 0;
            }
        }

        // 6. Reliability Score = weighted mix of completed jobs & response rate
        $completedFactor = min((int)$provider['completed_jobs'], 50) / 50;
        $responseFactor = ((float)$provider['response_rate']) / 100;
        $reliabilityScore = (($completedFactor * 0.5) + ($responseFactor * 0.5)) * self::W_RELIABILITY;

        $total = $serviceMatch + $locationMatch + $ratingScore + $experienceScore + $availabilityScore + $reliabilityScore;

        return [
            'service_match'     => round($serviceMatch, 2),
            'location_match'    => round($locationMatch, 2),
            'rating_score'      => round($ratingScore, 2),
            'experience_score'  => round($experienceScore, 2),
            'availability_score'=> round($availabilityScore, 2),
            'availability_checked' => $preferredDate || $preferredTime ? true : false,
            'reliability_score' => round($reliabilityScore, 2),
            'verified'          => (int)$provider['is_verified'] === 1,
            'total'             => round($total, 2),
        ];
    }


    /** Explain the recommendation in human-readable Arabic for the UI. */
    private function buildReasons(array $b, array $provider): array
    {
        $reasons = [];
        if (($b['service_match'] ?? 0) >= self::W_SERVICE) $reasons[] = 'متخصص في الخدمة المطلوبة';
        if (($b['location_match'] ?? 0) >= self::W_LOCATION) $reasons[] = 'يغطي موقع الخدمة';
        elseif (($b['location_match'] ?? 0) > 0) $reasons[] = 'تغطية الموقع غير مؤكدة بالكامل';
        if (($b['rating_score'] ?? 0) >= 16) $reasons[] = 'تقييم مرتفع';
        if (($b['experience_score'] ?? 0) >= 7) $reasons[] = 'خبرة جيدة';
        if (($b['availability_score'] ?? 0) >= self::W_AVAILABILITY) $reasons[] = 'متاح في الوقت المطلوب';
        elseif (($b['availability_checked'] ?? false)) $reasons[] = 'التوفر في الموعد المطلوب غير مؤكد';
        if (($b['reliability_score'] ?? 0) >= 7) $reasons[] = 'سجل موثوق في تنفيذ الطلبات';
        if (!empty($b['verified'])) $reasons[] = 'مقدم خدمة موثّق';
        return array_slice($reasons, 0, 5);
    }

    /** Persist a recommendation snapshot for reporting/audit (ai_recommendations table) */
    public function logRecommendations(int $customerId, array $ranked, ?int $requestId = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO ai_recommendations (request_id, customer_id, provider_id, score, score_breakdown) VALUES (:r, :c, :p, :s, :b)"
        );
        foreach ($ranked as $p) {
            $stmt->execute([
                'r' => $requestId,
                'c' => $customerId,
                'p' => $p['id'],
                's' => $p['score'],
                'b' => json_encode($p['score_breakdown'], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
