<?php
require_once __DIR__ . '/../core/Model.php';

class ProviderProfile extends Model
{
    protected string $table = 'provider_profiles';

    public function findByUser(int $userId)
    {
        return $this->first(['user_id' => $userId]);
    }

    public function fullProfile(int $providerId)
    {
        $sql = "SELECT pp.*, u.name, u.email, u.phone, u.profile_photo, c.name_ar as category_name_ar, c.name_en as category_name_en
                FROM provider_profiles pp
                JOIN users u ON u.id = pp.user_id
                LEFT JOIN categories c ON c.id = pp.category_id
                WHERE pp.id = :id LIMIT 1";
        $rows = $this->query($sql, ['id' => $providerId]);
        return $rows[0] ?? null;
    }

    /**
     * Search providers with structured filters. When a keyword is supplied,
     * results are ranked by relevance (exact service match > category match
     * > profession match > specialization match > name match > bio match)
     * instead of a single unordered LIKE across unrelated columns.
     */
    public function search(array $filters): array
    {
        $keyword = trim($filters['keyword'] ?? '');

        // Arabic feminine nouns pluralize by dropping the tied-ta (ة) and
        // adding ات (e.g. غسالة → غسالات, سيارة → سيارات, ثلاجة → ثلاجات).
        // A plain substring search for "غسالة" would therefore NOT match a
        // service called "صيانة غسالات". Stripping the trailing ة gives a
        // stem ("غسال") that is a substring of BOTH the singular and plural
        // form, without weakening matches for words that don't end in ة.
        $searchTerm = $keyword;
        if ($keyword !== '' && mb_substr($keyword, -1) === 'ة' && mb_strlen($keyword) > 1) {
            $searchTerm = mb_substr($keyword, 0, -1);
        }

        $params = [];

        // Relevance score is only meaningful when a keyword is present.
        // Each term below mirrors the priority ladder from the spec:
        // 1 service name, 2 category name, 3 specialization, 4 profession,
        // 5 provider name, 6 bio/description.
        $scoreSelect = '0 AS relevance';
        $joinKeywordService = '';
        if ($keyword !== '') {
            $params['kw'] = "%{$searchTerm}%";
            $joinKeywordService = "LEFT JOIN provider_services kws ON kws.provider_id = pp.id
                LEFT JOIN services ks ON ks.id = kws.service_id AND ks.name_ar LIKE :kw";
            $scoreSelect = "(
                CASE WHEN ks.id IS NOT NULL THEN 100 ELSE 0 END +
                CASE WHEN c.name_ar LIKE :kw_cat THEN 60 ELSE 0 END +
                CASE WHEN pp.specialization LIKE :kw_spec THEN 40 ELSE 0 END +
                CASE WHEN pp.profession LIKE :kw_prof THEN 30 ELSE 0 END +
                CASE WHEN u.name LIKE :kw_name THEN 20 ELSE 0 END +
                CASE WHEN pp.bio LIKE :kw_bio THEN 5 ELSE 0 END
            ) AS relevance";
            $params['kw_cat'] = "%{$searchTerm}%";
            $params['kw_spec'] = "%{$searchTerm}%";
            $params['kw_prof'] = "%{$searchTerm}%";
            $params['kw_name'] = "%{$searchTerm}%";
            $params['kw_bio'] = "%{$searchTerm}%";
        }

        $sql = "SELECT DISTINCT pp.*, u.name, u.profile_photo, c.name_ar as category_name_ar, c.name_en as category_name_en, $scoreSelect
                FROM provider_profiles pp
                JOIN users u ON u.id = pp.user_id
                LEFT JOIN categories c ON c.id = pp.category_id
                $joinKeywordService
                WHERE u.is_active = 1";

        if (!empty($filters['category_id'])) {
            $sql .= " AND pp.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['service_id'])) {
            $sql .= " AND pp.id IN (SELECT provider_id FROM provider_services WHERE service_id = :service_id)";
            $params['service_id'] = $filters['service_id'];
        }
        if (!empty($filters['location_id'])) {
            $sql .= " AND pp.id IN (SELECT provider_id FROM provider_locations WHERE location_id = :location_id)";
            $params['location_id'] = $filters['location_id'];
        }
        if (!empty($filters['min_rating'])) {
            $sql .= " AND pp.avg_rating >= :min_rating";
            $params['min_rating'] = $filters['min_rating'];
        }
        if (!empty($filters['verified_only'])) {
            $sql .= " AND pp.is_verified = 1";
        }
        if (!empty($filters['available_only'])) {
            $sql .= " AND pp.is_available = 1";
        }
        if ($keyword !== '') {
            $sql .= " AND (
                ks.id IS NOT NULL OR c.name_ar LIKE :kw2 OR pp.specialization LIKE :kw3
                OR pp.profession LIKE :kw4 OR u.name LIKE :kw5 OR pp.bio LIKE :kw6
            )";
            $params['kw2'] = "%{$searchTerm}%";
            $params['kw3'] = "%{$searchTerm}%";
            $params['kw4'] = "%{$searchTerm}%";
            $params['kw5'] = "%{$searchTerm}%";
            $params['kw6'] = "%{$searchTerm}%";
        }

        $sql .= $keyword !== ''
            ? " ORDER BY relevance DESC, pp.avg_rating DESC, pp.completed_jobs DESC"
            : " ORDER BY pp.avg_rating DESC, pp.completed_jobs DESC";

        return $this->query($sql, $params);
    }

    public function recalculateRating(int $providerId): void
    {
        $sql = "SELECT AVG(rating) avg_r, COUNT(*) cnt FROM reviews WHERE provider_id = :id";
        $row = $this->query($sql, ['id' => $providerId])[0];
        $this->update($providerId, [
            'avg_rating' => round((float)($row['avg_r'] ?? 0), 2),
            'rating_count' => (int) $row['cnt'],
        ]);
    }

    public function incrementCompletedJobs(int $providerId): void
    {
        $this->execute("UPDATE provider_profiles SET completed_jobs = completed_jobs + 1 WHERE id = :id", ['id' => $providerId]);
    }

    public function servicesFor(int $providerId): array
    {
        return $this->query(
            "SELECT ps.*, s.name_ar, s.name_en FROM provider_services ps JOIN services s ON s.id = ps.service_id WHERE ps.provider_id = :id",
            ['id' => $providerId]
        );
    }

    public function locationsFor(int $providerId): array
    {
        return $this->query(
            "SELECT pl.*, l.name_ar, l.name_en FROM provider_locations pl JOIN locations l ON l.id = pl.location_id WHERE pl.provider_id = :id",
            ['id' => $providerId]
        );
    }

    /**
     * Return the provider's weekly availability schedule.
     * day_of_week: 0=Sunday ... 6=Saturday.
     */
    public function availabilityFor(int $providerId): array
    {
        return $this->query(
            "SELECT * FROM provider_availability
             WHERE provider_id = :id
             ORDER BY day_of_week ASC, start_time ASC",
            ['id' => $providerId]
        );
    }

    /**
     * Add a weekly availability interval after validating its boundaries.
     */
    public function addAvailability(int $providerId, int $dayOfWeek, string $startTime, string $endTime): bool
    {
        if ($dayOfWeek < 0 || $dayOfWeek > 6) return false;
        if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/', $startTime)) return false;
        if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/', $endTime)) return false;
        if ($startTime >= $endTime) return false;

        // Prevent exact duplicate intervals. Overlapping intervals are allowed
        // so a provider can split a day into several working periods.
        $exists = $this->query(
            "SELECT id FROM provider_availability
             WHERE provider_id = :p AND day_of_week = :d
               AND start_time = :s AND end_time = :e LIMIT 1",
            ['p' => $providerId, 'd' => $dayOfWeek, 's' => $startTime, 'e' => $endTime]
        );
        if ($exists) return false;

        return (bool)$this->insert([
            'provider_id' => $providerId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    public function availableForRequest(int $providerId, array $request): bool
    {
        $profile = $this->find($providerId);
        if (!$profile || (int)$profile['is_available'] !== 1) return false;

        $scheduled = $this->scheduledAvailability(
            $providerId,
            $request['preferred_date'] ?? null,
            $request['preferred_time'] ?? null
        );

        return $scheduled === null ? true : $scheduled;
    }

    public function deleteAvailability(int $providerId, int $availabilityId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM provider_availability WHERE id = :id AND provider_id = :provider_id"
        );
        $stmt->execute(['id' => $availabilityId, 'provider_id' => $providerId]);
        return $stmt->rowCount() === 1;
    }

    /**
     * Check whether the provider is scheduled for a given date/time.
     * If no weekly schedule has been configured, return null so callers can
     * fall back to the provider's master is_available flag for backward compatibility.
     */
    public function scheduledAvailability(int $providerId, ?string $date = null, ?string $time = null): ?bool
    {
        $rows = $this->query(
            "SELECT day_of_week, start_time, end_time FROM provider_availability
             WHERE provider_id = :id ORDER BY day_of_week, start_time",
            ['id' => $providerId]
        );
        if (!$rows) return null;

        if ($date === null) $date = date('Y-m-d');
        $timestamp = strtotime($date);
        if ($timestamp === false) return false;
        $day = (int)date('w', $timestamp);

        // If only a date was requested, being scheduled at any point that day
        // is considered a match. If a time is supplied, the interval must contain it.
        if ($time === null || $time === '') {
            foreach ($rows as $row) {
                if ((int)$row['day_of_week'] === $day) return true;
            }
            return false;
        }

        $time = strlen($time) === 5 ? $time . ':00' : $time;
        foreach ($rows as $row) {
            if ((int)$row['day_of_week'] === $day && $time >= $row['start_time'] && $time < $row['end_time']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Portfolio images live in `provider_portfolio`, a *different* table than
     * this model's own $table ('provider_profiles'). The old implementation
     * called $this->where(...) which always queries $this->table, so it ran
     * "SELECT * FROM provider_profiles WHERE provider_id = ..." — but
     * provider_profiles has no provider_id column (its own PK is `id`),
     * causing SQLSTATE[42S22]: Unknown column 'provider_id'.
     * Fixed by querying provider_portfolio directly, the same pattern already
     * used by servicesFor()/locationsFor() above.
     */
    public function portfolioFor(int $providerId): array
    {
        return $this->query(
            "SELECT * FROM provider_portfolio WHERE provider_id = :id ORDER BY created_at DESC",
            ['id' => $providerId]
        );
    }
}
