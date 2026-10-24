USE mihan;

-- ---------------------------------------------------------
-- Permissions (illustrative RBAC catalogue)
-- ---------------------------------------------------------
INSERT INTO permissions (`key`, description) VALUES
('manage_users', 'إدارة المستخدمين'),
('manage_categories', 'إدارة التصنيفات والخدمات'),
('manage_locations', 'إدارة المناطق'),
('verify_providers', 'توثيق مقدمي الخدمة'),
('manage_complaints', 'إدارة الشكاوى'),
('view_reports', 'مشاهدة التقارير'),
('manage_requests', 'إدارة الطلبات'),
('manage_finance', 'إدارة المالية والفواتير'),
('manage_settings', 'إدارة إعدادات النظام'),
('manage_ai', 'إدارة مزودي ونماذج الذكاء الاصطناعي'),
('create_request', 'إنشاء طلب خدمة'),
('provide_service', 'تقديم الخدمات'),
('manage_payouts', 'إدارة مستحقات مقدمي الخدمة'),
('manage_employees', 'إدارة موظفي المنصة'),
('view_providers', 'عرض مقدمي الخدمة'),
('view_finance', 'عرض التقارير المالية'),
('view_conversations', 'عرض المحادثات'),
('manage_reviews', 'إدارة التقييمات'),
('view_dashboard', 'عرض لوحة التحكم');

-- Admin gets ALL permissions
INSERT INTO role_permissions (role, permission_id) SELECT 'admin', id FROM permissions;
-- Customer: create_request only
INSERT INTO role_permissions (role, permission_id) SELECT 'customer', id FROM permissions WHERE `key` = 'create_request';
-- Provider: provide_service only
INSERT INTO role_permissions (role, permission_id) SELECT 'provider', id FROM permissions WHERE `key` = 'provide_service';
-- Employee: operational permissions (no admin-exclusive ones)
INSERT INTO role_permissions (role, permission_id)
SELECT 'employee', id FROM permissions WHERE `key` IN (
  'view_providers', 'manage_reviews', 'view_finance', 'view_conversations',
  'manage_complaints', 'manage_requests', 'view_reports', 'view_dashboard'
);

-- ---------------------------------------------------------
-- Locations: Yemen governorates -> sample cities/districts
-- ---------------------------------------------------------
INSERT INTO locations (id, parent_id, name_ar, name_en, level) VALUES
(1, NULL, 'أمانة العاصمة', 'Sana\'a Capital', 'governorate'),
(2, NULL, 'عدن', 'Aden', 'governorate'),
(3, NULL, 'تعز', 'Taiz', 'governorate'),
(4, NULL, 'حضرموت', 'Hadhramaut', 'governorate');

INSERT INTO locations (parent_id, name_ar, name_en, level) VALUES
(1, 'شعوب', 'Shu\'aub', 'district'),
(1, 'السبعين', 'As-Sabain', 'district'),
(1, 'معين', 'Ma\'in', 'district'),
(2, 'المعلا', 'Al-Mualla', 'district'),
(2, 'كريتر', 'Crater', 'district'),
(2, 'خورمكسر', 'Khormaksar', 'district'),
(3, 'صالة', 'Salah', 'district'),
(3, 'المظفر', 'Al-Mudhaffar', 'district'),
(4, 'المكلا', 'Al-Mukalla', 'city');

-- ---------------------------------------------------------
-- Categories & Services
-- ---------------------------------------------------------
INSERT INTO categories (id, name_ar, name_en, icon) VALUES
(1, 'سباكة', 'Plumbing', 'fa-faucet'),
(2, 'كهرباء', 'Electrical', 'fa-bolt'),
(3, 'نجارة', 'Carpentry', 'fa-hammer'),
(4, 'تكييف وتبريد', 'Air Conditioning', 'fa-fan'),
(5, 'صيانة أجهزة منزلية', 'Home Appliance Repair', 'fa-blender'),
(6, 'دهان', 'Painting', 'fa-paint-roller'),
(7, 'تنظيف', 'Cleaning', 'fa-broom'),
(8, 'صيانة سيارات', 'Car Maintenance', 'fa-car'),
(9, 'صيانة هواتف وحواسيب', 'Phone & Computer Repair', 'fa-laptop'),
(10, 'حدادة وألمنيوم', 'Blacksmith & Aluminum', 'fa-industry'),
(11, 'بلاط وسيراميك', 'Tiling', 'fa-th');

INSERT INTO services (category_id, name_ar, name_en, description) VALUES
(1, 'إصلاح تسريب المياه', 'Water Leak Repair', 'الكشف عن تسريبات المياه وإصلاحها'),
(1, 'تركيب أدوات صحية', 'Sanitary Fixture Installation', 'تركيب مغاسل ومراحيض وخلاطات'),
(2, 'إصلاح أعطال كهربائية', 'Electrical Fault Repair', 'تشخيص وإصلاح الأعطال الكهربائية المنزلية'),
(2, 'تمديدات كهربائية جديدة', 'New Wiring', 'تمديدات كهربائية للمنازل والمكاتب'),
(3, 'تصنيع أثاث خشبي', 'Custom Furniture', 'تصميم وتصنيع أثاث حسب الطلب'),
(3, 'إصلاح أبواب ونوافذ خشبية', 'Door & Window Repair', 'صيانة الأبواب والنوافذ الخشبية'),
(4, 'صيانة مكيفات', 'AC Maintenance', 'تنظيف وصيانة وإصلاح المكيفات'),
(4, 'تركيب مكيفات', 'AC Installation', 'تركيب وحدات تكييف جديدة'),
(5, 'صيانة غسالات', 'Washing Machine Repair', 'إصلاح أعطال الغسالات'),
(5, 'صيانة ثلاجات', 'Refrigerator Repair', 'إصلاح أعطال الثلاجات'),
(6, 'دهان داخلي', 'Interior Painting', 'دهان جدران وأسقف داخلية'),
(6, 'دهان خارجي', 'Exterior Painting', 'دهان واجهات خارجية'),
(7, 'تنظيف منازل', 'Home Cleaning', 'تنظيف شامل للمنازل'),
(7, 'تنظيف مكاتب', 'Office Cleaning', 'تنظيف دوري للمكاتب'),
(8, 'صيانة عامة للسيارات', 'General Car Maintenance', 'فحص وصيانة دورية للسيارات'),
(9, 'إصلاح شاشات الهواتف', 'Phone Screen Repair', 'استبدال وإصلاح شاشات الهواتف'),
(10, 'أعمال حدادة عامة', 'General Blacksmith Work', 'تصنيع بوابات وشبابيك حديدية'),
(11, 'تركيب بلاط', 'Tile Installation', 'تركيب بلاط الأرضيات والحمامات');

-- ---------------------------------------------------------
-- Users: 1 admin, 2 customers, 3 providers
-- Password for ALL demo accounts: Passw0rd!  (bcrypt hash below)
-- ---------------------------------------------------------
-- NOTE: '__DEMO_HASH__' is replaced at seed time by database/seeds/seed.php
-- with a real bcrypt hash of the demo password (Passw0rd!). This guarantees
-- the hash is valid regardless of PHP/OpenSSL version on the target machine.

INSERT INTO users (id, name, email, phone, password_hash, role, is_active, is_verified_email) VALUES
(1, 'مدير النظام', 'admin@mihan.test', '777000001', '__DEMO_HASH__', 'admin', 1, 1),
(2, 'سالم أحمد', 'customer1@mihan.test', '777000002', '__DEMO_HASH__', 'customer', 1, 1),
(3, 'منى علي', 'customer2@mihan.test', '777000003', '__DEMO_HASH__', 'customer', 1, 1),
(4, 'أحمد الفني', 'provider1@mihan.test', '777000004', '__DEMO_HASH__', 'provider', 1, 1),
(5, 'خالد الصانع', 'provider2@mihan.test', '777000005', '__DEMO_HASH__', 'provider', 1, 1),
(6, 'ياسر المقاول', 'provider3@mihan.test', '777000006', '__DEMO_HASH__', 'provider', 1, 1),
(7, 'نورة العتيبي', 'employee@mihan.test', '777000007', '__DEMO_HASH__', 'employee', 1, 1);

-- ---------------------------------------------------------
-- Provider profiles
-- ---------------------------------------------------------
INSERT INTO provider_profiles (id, user_id, category_id, profession, specialization, bio, experience_years, is_verified, is_available, avg_rating, rating_count, completed_jobs, response_rate) VALUES
(1, 4, 4, 'فني تكييف', 'صيانة وتركيب مكيفات', 'خبرة واسعة في صيانة جميع أنواع المكيفات', 8, 1, 1, 4.8, 12, 40, 96.5),
(2, 5, 1, 'سباك محترف', 'إصلاح تسريبات وتمديدات', 'سباك معتمد بخبرة طويلة في المنازل والمنشآت', 6, 1, 1, 4.5, 9, 25, 92.0),
(3, 6, 2, 'كهربائي منازل', 'تمديدات وإصلاح أعطال', 'كهربائي متخصص في الأعطال المنزلية والتمديدات الجديدة', 5, 0, 1, 4.2, 5, 14, 88.0);

INSERT INTO provider_services (provider_id, service_id, price_from, price_to) VALUES
(1, 7, 3000, 8000), (1, 8, 15000, 40000),
(2, 1, 2000, 6000), (2, 2, 5000, 15000),
(3, 3, 2500, 7000), (3, 4, 10000, 30000);

INSERT INTO provider_locations (provider_id, location_id) VALUES
(1, 1), (1, 6), (2, 2), (2, 1), (3, 3), (3, 8);

INSERT INTO provider_availability (provider_id, day_of_week, start_time, end_time) VALUES
(1, 0, '08:00:00', '18:00:00'), (1, 1, '08:00:00', '18:00:00'),
(2, 0, '09:00:00', '17:00:00'), (2, 2, '09:00:00', '17:00:00'),
(3, 1, '10:00:00', '20:00:00'), (3, 3, '10:00:00', '20:00:00');

-- ---------------------------------------------------------
-- Portfolio samples (demonstrates the fixed provider_portfolio relation)
-- ---------------------------------------------------------
INSERT INTO provider_portfolio (provider_id, image_path, caption) VALUES
(1, 'https://picsum.photos/seed/mihan-ac-1/600/400', 'تركيب مكيف سبليت لعميل في صنعاء'),
(1, 'https://picsum.photos/seed/mihan-ac-2/600/400', 'صيانة دورية لوحدة تكييف مركزي'),
(2, 'https://picsum.photos/seed/mihan-plumb-1/600/400', 'إصلاح تسريب مواسير مطبخ'),
(3, 'https://picsum.photos/seed/mihan-elec-1/600/400', 'تمديد كهربائي جديد لشقة سكنية');

-- ---------------------------------------------------------
-- Sample service requests across different statuses
-- ---------------------------------------------------------
INSERT INTO service_requests (id, request_number, customer_id, provider_id, service_id, location_id, description, preferred_date, preferred_time, budget, status) VALUES
(1, 'REQ-2026-000001', 2, 1, 7, 1, 'المكيف يعمل لكنه لا يبرد بشكل جيد', '2026-09-15', '10:00:00', 6000, 'completed'),
(2, 'REQ-2026-000002', 3, 2, 1, 2, 'تسريب مياه أسفل المغسلة', '2026-09-16', '14:00:00', 4000, 'in_progress'),
(3, 'REQ-2026-000003', 2, NULL, 3, 1, 'يوجد عطل متكرر في الكهرباء بالمطبخ', '2026-09-20', '09:00:00', 5000, 'pending'),
(4, 'REQ-2026-000004', 3, 1, 8, 6, 'أحتاج تركيب مكيف جديد في غرفة النوم', '2026-09-18', '11:00:00', 25000, 'accepted');

INSERT INTO request_status_history (request_id, changed_by, old_status, new_status) VALUES
(1, 2, NULL, 'new'), (1, 2, 'new', 'pending'), (1, 4, 'pending', 'accepted'),
(1, 4, 'accepted', 'in_progress'), (1, 4, 'in_progress', 'completed'),
(2, 3, NULL, 'new'), (2, 3, 'new', 'pending'), (2, 5, 'pending', 'accepted'), (2, 5, 'accepted', 'in_progress'),
(3, 2, NULL, 'new'), (3, 2, 'new', 'pending'),
(4, 3, NULL, 'new'), (4, 3, 'new', 'pending'), (4, 1, 'pending', 'accepted');

-- ---------------------------------------------------------
-- Review for the completed request
-- ---------------------------------------------------------
INSERT INTO reviews (request_id, customer_id, provider_id, rating, comment) VALUES
(1, 2, 1, 5, 'خدمة ممتازة وسريعة، شكرًا لك');

-- ---------------------------------------------------------
-- Invoice + simulated payment for the completed request
-- ---------------------------------------------------------
INSERT INTO invoices (id, invoice_number, request_id, service_price, commission_rate, commission_amount, provider_net, total_amount, payment_status) VALUES
(1, 'INV-2026-000001', 1, 6000.00, 0.10, 600.00, 5400.00, 6000.00, 'paid');

INSERT INTO payments (invoice_id, method, amount, status, is_simulated) VALUES
(1, 'simulated_wallet', 6000.00, 'paid', 1);

INSERT INTO platform_commissions (invoice_id, amount) VALUES (1, 600.00);

-- ---------------------------------------------------------
-- Sample complaint
-- ---------------------------------------------------------
INSERT INTO complaints (complaint_number, created_by, against_user_id, request_id, subject, description, status) VALUES
('CMP-2026-000001', 3, 5, 2, 'تأخر في الوصول', 'مقدم الخدمة تأخر عن الموعد المتفق عليه بساعة', 'in_review');

INSERT INTO complaint_messages (complaint_id, sender_id, message) VALUES
(1, 3, 'تأخر الفني عن الموعد المحدد بدون إشعار مسبق'),
(1, 1, 'تم التواصل مع مقدم الخدمة وسيتم المتابعة');

-- ---------------------------------------------------------
-- Notifications sample
-- ---------------------------------------------------------
INSERT INTO notifications (user_id, title, message, link, is_read) VALUES
(4, 'طلب جديد', 'لديك طلب خدمة جديد بانتظار الرد', '/provider/requests', 0),
(2, 'تم قبول طلبك', 'قام مقدم الخدمة بقبول طلبك', '/customer/requests', 1),
(2, 'تم إكمال الخدمة', 'تم إكمال طلبك بنجاح، يمكنك الآن تقييم مقدم الخدمة', '/customer/requests', 1);

-- ---------------------------------------------------------
-- System settings
-- ---------------------------------------------------------
INSERT INTO system_settings (`key`, `value`) VALUES
('platform_commission_rate', '0.10'),
('site_name', 'مِهَن'),
('site_tagline', 'منصة الخدمات المهنية'),
('default_language', 'ar'),
('timezone', 'Asia/Aden'),
('allow_customer_registration', '1'),
('allow_provider_registration', '1'),
('maintenance_mode', '0'),
('maintenance_message', 'المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.'),
('auto_provider_available', '1'),
('reviews_enabled', '1'),
('complaints_enabled', '1'),
('currency', 'ريال يمني'),
('currency_code', 'YER'),
('invoice_prefix', 'INV'),
('notifications_enabled', '1'),
('notify_on_request', '1'),
('notify_on_completion', '1'),
('notify_on_payment', '1'),
('notify_on_complaint', '1'),
('force_https', '0'),
('activity_log_enabled', '1'),
('show_system_errors', '0')
ON DUPLICATE KEY UPDATE value=VALUES(value);

-- ---------------------------------------------------------
-- Activity log sample
-- ---------------------------------------------------------
INSERT INTO activity_logs (user_id, action, description) VALUES
(1, 'login', 'تسجيل دخول المدير'),
(4, 'accept_request', 'قبول الطلب REQ-2026-000001');
