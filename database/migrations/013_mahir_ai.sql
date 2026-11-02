-- ============================================================
-- Migration 013: Mahir AI Vocational Learning System
-- ============================================================

CREATE TABLE IF NOT EXISTS `mahir_professions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL COMMENT 'اسم المهنة بالعربية',
  `name_en` varchar(150) DEFAULT NULL COMMENT 'اسم المهنة بالإنجليزية',
  `category` varchar(100) DEFAULT NULL COMMENT 'الفئة: صيانة_منزلية، طعام، تقنية، زراعة، سيارات، يدوية',
  `icon` varchar(100) DEFAULT NULL COMMENT 'رمز تعبيري للمهنة',
  `description` text DEFAULT NULL COMMENT 'وصف مختصر للمهنة',
  `system_prompt_extra` text DEFAULT NULL COMMENT 'تعليمات إضافية خاصة بهذه المهنة تُضاف لـ system prompt',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mahir_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_token` varchar(64) NOT NULL COMMENT 'رمز فريد للجلسة',
  `user_id` int(11) DEFAULT NULL COMMENT 'المستخدم المسجل (NULL للزوار)',
  `profession_id` int(11) DEFAULT NULL COMMENT 'المهنة المختارة في الجلسة',
  `messages` longtext DEFAULT NULL COMMENT 'تاريخ المحادثة JSON',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`),
  KEY `profession_id` (`profession_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mahir_usage_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `profession_id` int(11) DEFAULT NULL,
  `user_message` text DEFAULT NULL,
  `ai_response` text DEFAULT NULL,
  `tokens_used` int(11) DEFAULT 0,
  `duration_ms` int(11) DEFAULT 0,
  `is_within_scope` tinyint(1) DEFAULT 1 COMMENT 'هل السؤال ضمن المهن المسموحة',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `profession_id` (`profession_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `mahir_professions` (`id`, `name`, `name_en`, `category`, `icon`, `description`, `system_prompt_extra`, `sort_order`) VALUES
(1, 'السباكة', 'Plumbing', 'صيانة_منزلية', '🔧', 'تعلم إصلاح الأنابيب والصنابير والمواسير', 'ركز على خطوات السلامة أولاً. تحدث عن الأدوات المطلوبة قبل الخطوات.', 1),
(2, 'الكهرباء المنزلية', 'Home Electricity', 'صيانة_منزلية', '⚡', 'أساسيات الكهرباء المنزلية الآمنة', 'دائماً أكد على قطع الكهرباء من القاطع الرئيسي قبل أي عمل. ذكّر بخطورة الكهرباء.', 2),
(3, 'النجارة', 'Carpentry', 'صيانة_منزلية', '🪵', 'تعلم أساسيات النجارة وصنع الأثاث', NULL, 3),
(4, 'الدهان والطلاء', 'Painting', 'صيانة_منزلية', '🎨', 'تعلم طلاء الجدران والأسطح', NULL, 4),
(5, 'الطهي والطبخ', 'Cooking', 'طعام', '🍳', 'وصفات ومهارات الطهي من الصفر', 'اذكر مكونات الوصفة أولاً ثم الخطوات. أضف نصائح عن درجات الحرارة والتوقيت.', 5),
(6, 'المخبوزات والحلويات', 'Baking', 'طعام', '🧁', 'فن الخبيز وصناعة الحلويات', NULL, 6),
(7, 'صيانة الأجهزة الإلكترونية', 'Electronics Repair', 'تقنية', '📱', 'صيانة الهواتف والأجهزة الإلكترونية', NULL, 7),
(8, 'الزراعة المنزلية', 'Home Gardening', 'زراعة', '🌿', 'زراعة النباتات في المنزل والحديقة', NULL, 8),
(9, 'صيانة السيارات', 'Car Maintenance', 'سيارات', '🚗', 'أساسيات صيانة السيارة التي يمكنك فعلها بنفسك', NULL, 9),
(10, 'الخياطة والتطريز', 'Sewing', 'يدوية', '🧵', 'تعلم الخياطة اليدوية والآلية', NULL, 10);

-- Update Gemini default model to gemini-1.5-flash
UPDATE `ai_provider_settings`
SET `model` = 'gemini-1.5-flash',
    `base_url` = 'https://generativelanguage.googleapis.com/v1beta'
WHERE `provider` = 'gemini' AND (`model` LIKE '%3.1%' OR `model` IS NULL OR `model` = '');

