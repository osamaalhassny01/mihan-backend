<?php
require_once __DIR__ . '/../core/Model.php';

class MahirProfession extends Model
{
    protected string $table = 'mahir_professions';
    private static bool $tablesChecked = false;

    public function __construct()
    {
        parent::__construct();
        self::ensureTablesExist();
    }

    public static function ensureTablesExist(): void
    {
        if (self::$tablesChecked) {
            return;
        }

        try {
            $db = Database::getInstance();
            $check = $db->query("SHOW TABLES LIKE 'mahir_professions'")->fetch();
            if (!$check) {
                self::createTablesAndSeed($db);
            }
            // Auto-correct invalid Gemini model name if present
            $db->exec("UPDATE ai_provider_settings SET model = 'gemini-1.5-flash' WHERE provider = 'gemini' AND (model LIKE '%3.1%' OR model IS NULL OR model = '')");
            self::$tablesChecked = true;
        } catch (Throwable $e) {
            error_log('Mahir AI table check notice: ' . $e->getMessage());
        }
    }

    private static function createTablesAndSeed(PDO $db): void
    {
        $sqls = [
            "CREATE TABLE IF NOT EXISTS `mahir_professions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(150) NOT NULL,
              `name_en` varchar(150) DEFAULT NULL,
              `category` varchar(100) DEFAULT NULL,
              `icon` varchar(100) DEFAULT NULL,
              `description` text DEFAULT NULL,
              `system_prompt_extra` text DEFAULT NULL,
              `is_active` tinyint(1) NOT NULL DEFAULT 1,
              `sort_order` int(11) DEFAULT 0,
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS `mahir_sessions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `session_token` varchar(64) NOT NULL,
              `user_id` int(11) DEFAULT NULL,
              `profession_id` int(11) DEFAULT NULL,
              `messages` longtext DEFAULT NULL,
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `session_token` (`session_token`),
              KEY `user_id` (`user_id`),
              KEY `profession_id` (`profession_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS `mahir_usage_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `session_id` int(11) DEFAULT NULL,
              `user_id` int(11) DEFAULT NULL,
              `profession_id` int(11) DEFAULT NULL,
              `user_message` text DEFAULT NULL,
              `ai_response` text DEFAULT NULL,
              `tokens_used` int(11) DEFAULT 0,
              `duration_ms` int(11) DEFAULT 0,
              `is_within_scope` tinyint(1) DEFAULT 1,
              `created_at` datetime DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `session_id` (`session_id`),
              KEY `profession_id` (`profession_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        ];

        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        // إدخال المهن الافتراضية
        $count = (int)$db->query("SELECT COUNT(*) FROM mahir_professions")->fetchColumn();
        if ($count === 0) {
            $stmt = $db->prepare("
                INSERT INTO `mahir_professions` (`id`, `name`, `name_en`, `category`, `icon`, `description`, `system_prompt_extra`, `sort_order`)
                VALUES (:id, :name, :name_en, :category, :icon, :description, :prompt, :sort_order)
            ");

            $professions = [
                [1, 'السباكة', 'Plumbing', 'صيانة_منزلية', '🔧', 'تعلم إصلاح الأنابيب والصنابير والمواسير', 'ركز على خطوات السلامة أولاً. تحدث عن الأدوات المطلوبة قبل الخطوات.', 1],
                [2, 'الكهرباء المنزلية', 'Home Electricity', 'صيانة_منزلية', '⚡', 'أساسيات الكهرباء المنزلية الآمنة', 'دائماً أكد على قطع الكهرباء من القاطع الرئيسي قبل أي عمل. ذكّر بخطورة الكهرباء.', 2],
                [3, 'النجارة', 'Carpentry', 'صيانة_منزلية', '🪵', 'تعلم أساسيات النجارة وصنع الأثاث', null, 3],
                [4, 'الدهان والطلاء', 'Painting', 'صيانة_منزلية', '🎨', 'تعلم طلاء الجدران والأسطح', null, 4],
                [5, 'الطهي والطبخ', 'Cooking', 'طعام', '🍳', 'وصفات ومهارات الطهي من الصفر', 'اذكر مكونات الوصفة أولاً ثم الخطوات. أضف نصائح عن درجات الحرارة والتوقيت.', 5],
                [6, 'المخبوزات والحلويات', 'Baking', 'طعام', '🧁', 'فن الخبيز وصناعة الحلويات', null, 6],
                [7, 'صيانة الأجهزة الإلكترونية', 'Electronics Repair', 'تقنية', '📱', 'صيانة الهواتف والأجهزة الإلكترونية', null, 7],
                [8, 'الزراعة المنزلية', 'Home Gardening', 'زراعة', '🌿', 'زراعة النباتات في المنزل والحديقة', null, 8],
                [9, 'صيانة السيارات', 'Car Maintenance', 'سيارات', '🚗', 'أساسيات صيانة السيارة التي يمكنك فعلها بنفسك', null, 9],
                [10, 'الخياطة والتطريز', 'Sewing', 'يدوية', '🧵', 'تعلم الخياطة اليدوية والآلية', null, 10],
            ];

            foreach ($professions as $p) {
                try {
                    $stmt->execute([
                        ':id'          => $p[0],
                        ':name'        => $p[1],
                        ':name_en'     => $p[2],
                        ':category'    => $p[3],
                        ':icon'        => $p[4],
                        ':description' => $p[5],
                        ':prompt'      => $p[6],
                        ':sort_order'  => $p[7],
                    ]);
                } catch (Throwable $ignore) {}
            }
        }
    }

    public function allActive(): array
    {
        try {
            $db = Database::getInstance();
            return $db->query(
                "SELECT * FROM mahir_professions WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            self::ensureTablesExist();
            return [];
        }
    }

    public function byCategory(): array
    {
        $all = $this->allActive();
        $grouped = [];
        foreach ($all as $p) {
            $cat = $p['category'] ?? 'عام';
            $grouped[$cat][] = $p;
        }
        return $grouped;
    }

    public function findById(int $id): ?array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM mahir_professions WHERE id = :id AND is_active = 1 LIMIT 1");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function allForAdmin(): array
    {
        try {
            $db = Database::getInstance();
            return $db->query(
                "SELECT * FROM mahir_professions ORDER BY sort_order ASC, name ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            self::ensureTablesExist();
            return [];
        }
    }
}
