# مِهَن — MIHAN
### منصة ذكية لإدارة الخدمات المهنية المحلية ودعم القرار

مشروع تخرج أكاديمي — قسم **نظم المعلومات الإدارية (MIS)**

> **ملاحظة حول التسمية:** 
الاسم الرسمي والهوية الظاهرة للمستخدم في كل الواجهات هي 
**مِهَن — MIHAN**.
> اسم مجلد المشروع وقاعدة البيانات الافتراضية واسم كوكي الجلسة (`mihan` / `mihan_session`).

---

## 1. نظرة عامة على المشروع (Project Overview)

مِهَن منصة ويب تربط العملاء بمقدمي الخدمات المهنية المحلية (سباكة، كهرباء، تكييف، نجارة...)
من خلال دورة عمل كاملة: بحث → طلب → توصية ذكية → قبول/تنفيذ → تقييم، مع لوحة تحكم إدارية
لدعم اتخاذ القرار (تقارير، تحليلات، إدارة شكاوى، إدارة مستخدمين).

المشروع مبني بالكامل بـ **PHP الخام (Core PHP) بمعمارية MVC مخصصة، بدون أي إطار عمل**، تلبيةً
لمتطلبات المشروع.

## 2. التقنيات المستخدمة (Technologies)

| الطبقة | التقنية |
|---|---|
| اللغة | PHP 8.x (بدون Framework) |
| قاعدة البيانات | MySQL 8.x عبر PDO + Prepared Statements |
| الخادم | Apache (XAMPP) |
| الواجهة | HTML5, CSS3, Bootstrap 5 (RTL/LTR), Font Awesome 6 |
| الرسوم البيانية | Chart.js |
| الجافاسكربت | Vanilla JS (بدون مكتبات إضافية إلزامية) |

## 3. المعمارية (Architecture)

نمط **MVC** مخصص مبني يدويًا (بدون Laravel أو أي framework):

```
Request → index.php (Front Controller) → Router → Controller → Model/Service → Database
                                                        ↓
                                                      View (يُعرض داخل Layout)
```

- **Controller** يستقبل الطلب وينسّق بين Model و View، ولا يحتوي أبدًا على استعلامات SQL مباشرة.
- **Model** يغلّف كل الوصول لقاعدة البيانات عبر PDO Prepared Statements فقط.
- **View** تعرض HTML فقط ولا تحتوي على منطق أعمال أو استعلامات.
- **Services** (`app/services`) تحتوي منطق الأعمال المعقد: محرك التوصية الذكي، تحليل النصوص، والتحليلات.

## 4. هيكل المجلدات (Folder Structure)

```
mihan/
├── app/
│   ├── controllers/     Auth, Home, Customer, Provider, Admin, AdminAI
│   ├── models/          User, ProviderProfile, ServiceRequest, Review, Complaint, Invoice, ...
│   ├── views/           layouts/ partials/ auth/ customer/ provider/ admin/ home/ errors/
│   ├── core/            App, Router, Controller, Model, Database, Session, Auth, CSRF, Validator
│   ├── helpers/         helpers.php (__() ,e() ,asset() ,url() ,currency() ,money() ,json_safe() ...)
│   └── services/
│       ├── AI/          AIServiceFactory, Encryption, providers/{RuleBased,Gemini,OpenAI,Groq,...}
│       ├── FinanceService.php   عقد العمليات والحسابات المالية الموحد
│       └── AnalyticsService.php
├── config/               config.php, database.php
├── database/
│   ├── migrations/      001_... إلى 010_... + migrate.php (تشغيل كل الترحيلات)
│   └── seeds/           seed.sql, seed.php, mihan-demo.sql
├── tests/                lint.php (فحص تركيبي شامل), verify_all.php (اختبارات التحقق والانحدار)
├── lang/                 ar.php, en.php
├── public/               assets/{css,js}, uploads/{profiles,portfolio}
├── routes/web.php        كل الروابط (Routes)
├── database.sql          مخطط قاعدة البيانات الأساسي
├── index.php             Front Controller
├── .htaccess             توجيه الروابط + منع الوصول المباشر لمجلدات حساسة
└── .env.example          نموذج إعدادات آمن وموثق
```

## 5. قاعدة البيانات (Database Structure)

أهم الجداول (راجع `database.sql` للتفاصيل الكاملة و`database/seeds/seed.sql` للبيانات التجريبية):

- **المستخدمون والصلاحيات:** `users`, `permissions`, `role_permissions`
- **الخدمات:** `categories`, `services`
- **مقدمو الخدمة:** `provider_profiles`, `provider_services`, `provider_locations`, `provider_availability`, `provider_portfolio`
- **الطلبات:** `service_requests`, `request_status_history`
- **الثقة:** `reviews`
- **الدعم:** `complaints`, `complaint_messages`
- **الإشعارات:** `notifications`
- **المالية:** `invoices`, `payments`, `platform_commissions`
- **الذكاء الاصطناعي:** `ai_recommendations`, `ai_interactions`
- **النظام:** `system_settings`, `activity_logs`, `locations`

جميع العلاقات مربوطة بمفاتيح خارجية (Foreign Keys) مع فهارس (Indexes) على الأعمدة
الأكثر استخدامًا في الاستعلامات (status, customer_id, provider_id...).

## 6. أدوار المستخدمين (User Roles)

| الدور | الصلاحيات الأساسية |
|---|---|
| **Customer** | بحث، إنشاء طلب، متابعة، تقييم، شكوى |
| **Provider** | إدارة ملف مهني، قبول/رفض/تنفيذ الطلبات، الرد على التقييمات |
| **Admin** | إدارة شاملة: مستخدمون، تصنيفات، مناطق، طلبات، شكاوى، مالية، تقارير، إعدادات |

الحماية بين الأدوار مطبقة عبر `Auth::requireRole()` في مُنشئ (constructor) كل Controller،
بحيث لا يستطيع `customer` أو `provider` الوصول إلى `/admin/*` والعكس صحيح.

## 7. الميزات الرئيسية (Main Features)

- تسجيل/دخول/خروج آمن (bcrypt + CSRF + Session Regeneration)
- بحث وتصفية حقيقية من قاعدة البيانات (تصنيف، خدمة، منطقة، تقييم، توثيق، توفر)
- دورة طلب خدمة كاملة: `new → pending → accepted → in_progress → completed` مع `cancelled/rejected`
  وتسجيل كل انتقال في `request_status_history`
- نظام تقييم (1-5 نجوم) مع منع التكرار ومنع تقييم طلب غير مكتمل، وحساب `avg_rating` تلقائيًا
- نظام شكاوى بحالات متعددة ومحادثة (Complaint Messages) بين العميل والإدارة
- نظام إشعارات داخل المنصة لكل الأطراف
- نظام مالي محاكى: فاتورة تلقائية عند إكمال الطلب، حساب العمولة (10% افتراضيًا)، وحالة دفع محاكاة
- تعدد لغات كامل (عربي RTL / إنجليزي LTR) عبر ملفات `lang/*.php` ودالة `__()`
- سجل نشاطات (Activity Log) لكل العمليات الحساسة
- توثيق مقدمي الخدمة (Verified Badge) من لوحة الإدارة
- رفع صور آمن (صورة شخصية + معرض أعمال) بفحص الامتداد وMIME الحقيقي وحجم الملف

## 8. ميزات الذكاء الاصطناعي (AI Features)

المحرك المحلي يعمل بدون 
API Key , 
ويمكن من لوحة الإدارة تفعيل مزود 
LLM خارجي (Gemini/OpenAI/Groq/OpenAI Compatible) 
عند الحاجة:

1. **محرك التوصية الذكي (Rule-Based Recommendation Engine)** — `app/services/AI/RecommendationEngine.php`
   يحسب 
   Recommendation Score 
   لكل مقدم خدمة مناسب للطلب 
   اعتمادًا على:
   Service Match + Location Match + Rating + Experience + Availability + Reliability،
   ويُخزَّن الترتيب في 
   `ai_recommendations` 
   لأغراض التدقيق.

2. **مساعد فهم مشكلة العميل** — `app/services/AI/RuleBasedAIService.php` + `ProblemAnalyzer.php`
   يحلل نص العميل الحر (مثال: "المكيف يشتغل بس ما يبرد") ويقترح التصنيف/الخدمة المناسبة مع درجة
   ثقة وخدمات بديلة، مع تسجيل كل تفاعل في `ai_interactions`. متاح من صفحة إنشاء الطلب، ومن
   **مساعد مِهَن الذكي** (`/customer/ai/assistant`) وهو واجهة محادثة (chat) مبنية فوق نفس المحرك.

3. **تحليلات ودعم القرار** — `app/services/AnalyticsService.php`
   يغذي لوحة تحكم الإدارة بمؤشرات حقيقية من قاعدة البيانات (لا أرقام وهمية):
   الخدمات الأكثر طلبًا، أفضل مقدمي الخدمة، المناطق الأكثر نشاطًا، الإيرادات الشهرية...
   (`/admin/reports`، ومُشار إليها من بطاقة "تحليلات ذكية" في الصفحة الرئيسية لحسابات الإدارة).

**تصميم قابل للتوسع:** `AIProviderInterface` يفصل المنصة عن المزود. إعدادات المزود تُدار من `/admin/ai`، والمفتاح يُشفّر على الخادم. عند فشل مزود خارجي يعود النظام إلى المحرك المحلي (Fallback آمن).

## 9. الأمان (Security Measures)

- **SQL Injection:** جميع الاستعلامات عبر PDO Prepared Statements فقط، بدون أي دمج نصي للمدخلات.
- **XSS:** كل مخرجات المستخدم تمر عبر `e()` (htmlspecialchars) قبل العرض.
- **CSRF:** كل نموذج POST يحتوي `csrf_field()` ويُتحقق منه في `Controller::verifyCsrfOrFail()`.
- **Session Security:** `httponly` cookies، `session_regenerate_id()` بعد تسجيل الدخول ومنع Session Fixation.
- **Password Hashing:** `password_hash()`/`password_verify()` — لا كلمات مرور نصية أبدًا.
- **Authorization:** حراسة أدوار صارمة (`Auth::requireRole`) في كل Controller محمي.
- **رفع الملفات:** فحص الامتداد + فحص MIME الحقيقي عبر `finfo` + حجم أقصى + اسم ملف عشوائي +
  `.htaccess` يمنع تنفيذ أي سكربت داخل `public/uploads`.
- **حماية مجلدات النظام:** `.htaccess` الجذري يمنع الوصول المباشر لـ `app/ config/ database/ storage/`.

## 10. التثبيت (Installation — XAMPP)

1. انسخ مجلد المشروع بالكامل إلى: `C:\xampp\htdocs\mihan` (أو المسار المكافئ في Linux/Mac).
   يمكن استخدام اسم مجلد آخر إن رغبت؛ فقط حدّث `APP_URL` في `.env` ليطابقه.
2. شغّل **Apache** و**MySQL** من لوحة تحكم XAMPP.
3. **استيراد قاعدة البيانات:**
   - افتح phpMyAdmin → أنشئ قاعدة بيانات جديدة أو استورد مباشرة ملف `database.sql`
     (يحتوي على `CREATE DATABASE mihan` والمخطط الكامل — اسم قاعدة البيانات معرّف تقني
     داخلي فقط ولا يظهر للمستخدم).
4. **تهيئة قاعدة البيانات وتشغيل الترحيلات (Migrations & Seed):**
   - انسخ `.env.example` إلى `.env` وعدّل بيانات الاتصال بقاعدة البيانات.
   - لتطبيق جميع الترحيلات بالترتيب (من 001 إلى 010):
     ```bash
     php database/migrations/migrate.php
     ```
   - لتشغيل البيانات التجريبية (Seeding):
     ```bash
     php database/seeds/seed.php
     ```
     هذا السكربت يُنشئ التصنيفات والمناطق والمستخدمين التجريبيين بكلمة مرور مُشفّرة بـ bcrypt بشكل صحيح.
5. **تشغيل الاختبارات والفحص الشامل:**
   - فحص التركيب النحوي لجميع ملفات المشروع (PHP Lint):
     ```bash
     php tests/lint.php
     ```
   - فحص واختبار جميع مكونات النظام وقواعد الأعمال والمالية والأمان:
     ```bash
     php tests/verify_all.php
     ```
6. افتح المتصفح على: **http://localhost/mihan/** (أو المسار الذي اخترته في الخطوة 1).

## 11. الإعداد (Configuration)

ملف `.env` (انسخه من `.env.example`):
```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/mihan

DB_HOST=localhost
DB_NAME=mihan
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

# مفتاح تشفير إعدادات الذكاء الاصطناعي (AES-256-GCM) - يتم توليده تلقائيًا
AI_ENCRYPTION_KEY=
```

## 12. حسابات الدخول التجريبية (Test Accounts)

كلمة المرور لجميع الحسابات: **`Passw0rd!`**

| الدور | البريد الإلكتروني |
|---|---|
| Admin | admin@mihan.test |
| Customer | customer1@mihan.test / customer2@mihan.test |
| Provider | provider1@mihan.test / provider2@mihan.test / provider3@mihan.test |

## 13. المعمارية المالية وميثاق العمليات (Finance Architecture)

تم توحيد المنطق المالي عبر خدمة مركزية `app/services/FinanceService.php` تعمل كمصدر حقيقة وحيد (Single Source of Truth) وتطبق العقد المالي الصارم:
- `gross_amount`: إجمالي قيمة الفاتورة.
- `paid_amount`: إجمالي المبالغ المدفوعة فعليًا.
- `refunded_amount`: المبالغ المستردة.
- `net_collected`: الصافي المحصل (`paid_amount - refunded_amount`).
- `outstanding_amount`: الرصيد المتبقي على العميل.
- `commission_amount`: عمولة المنصة الفعلية (تُحسب كنسبة من المبالغ المدفوعة لمنع تكرار العمولة في الدفعات الجزئية).
- `provider_net_amount`: صافي مستحقات مقدم الخدمة.
- `net_platform_revenue`: صافي إيرادات المنصة المحتجزة بعد أي استرداد.

**الحماية من Race Conditions:**
- تنفيذ `SELECT ... FOR UPDATE` مع Transactions في `Invoice::recordPayment()` و `Invoice::refund()`.
- منع الدفع الزائد (Overpayment Guard) برفض أي دفعة تتجاوز الرصيد المتبقي.
- إتمام الطلب وتوليد الفاتورة بشكل ذري (Atomic Transaction) في `ServiceRequest::completeWithInvoice()`.

## 14. بنية الذكاء الاصطناعي (AI Engine & Fail-Safe Fallback)

- يدعم النظام عدة مزودين عبر واجهة موحدة `AIProviderInterface`:
  - `local`: محرك محلي معتمد على القواعد (Rule-Based) مجاني وسريع ولا يتطلب أي اتصال خارجي.
  - `gemini`: تكامل أصيل مع REST API لـ Google AI Studio.
  - `openai`: OpenAI GPT API.
  - `groq`: Groq High-Speed API.
  - `openai_compatible`: أي مزود متوافق مع معيار OpenAI.
- **Fail-Safe Fallback:** أي خطأ خارجي (انتهاء الحصة، 429، مهلة الاتصال، أخطاء الاعتماد) يحوّل الطلب تلقائيًا وبشكل فوري إلى `RuleBasedAIProvider` مع تسجيل تفاصيل الخطأ بأمان (Sanitized Redacted Error) ودون تعطل واجهة المستخدم.

## 15. ملفات مهمة (Important Files)

| الملف | الوظيفة |
|---|---|
| `index.php` | نقطة الدخول الوحيدة (Front Controller) |
| `routes/web.php` | كل مسارات النظام |
| `app/core/Auth.php` | تسجيل الدخول، حراسة الأدوار والصلاحيات (RBAC)، وإبطال الجلسة الفوري |
| `app/services/FinanceService.php` | الحسابات والتقارير المالية الموحدة |
| `app/services/AI/AIServiceFactory.php` | مصنع وموجّه مزودي الذكاء الاصطناعي مع Fallback آمن |
| `app/services/AI/providers/RuleBasedAIProvider.php` | المحرك المحلي المجاني الدائم |
| `database/migrations/migrate.php` | مشغل ترحيلات قاعدة البيانات الموحد (001 إلى 010) |
| `database/seeds/seed.php` | مشغل البيانات التجريبية بكلمات مرور bcrypt حقيقية |
| `tests/verify_all.php` | مجموعة اختبارات التحقق والانحدار الشاملة |

## 16. إشعارات التطبيق (Firebase HTTP v1)

كل إشعار ينشأ عبر `Notification::notify()` أو `FcmService::sendToUser()` يُحفظ مرة واحدة في قاعدة البيانات ثم تُجرّب إرساله إلى جهاز المستخدم. فشل الإرسال لا يحذف سجل الإشعار. زر الجرس في التطبيق يعرض آخر 100 إشعار، ويدعم تعليم الإشعارات كمقروءة وفتح الطلب المرتبط.

الخادم يستخدم Firebase HTTP v1 مع OAuth 2.0. متغير `FCM_SERVER_KEY` القديم لم يعد مستخدمًا. ملف `google-services.json` الخاص بتطبيق Android لا يحتوي اعتماد إرسال الخادم ولا يكفي وحده لتشغيل الإشعارات.

لتفعيل الإرسال على Railway:

1. افتح مشروع Firebase نفسه المستخدم في التطبيق (`mihan-89053`)، ثم إعدادات المشروع → Service accounts، وأنشئ مفتاح حساب خدمة بصلاحية إرسال FCM. فعّل Firebase Cloud Messaging API (V1) للمشروع.
2. احفظ محتوى ملف حساب الخدمة كاملًا في متغير Railway السري `FCM_SERVICE_ACCOUNT_JSON`. بديلًا عن ذلك، ارفع الملف خارج جذر الويب وحدّد مساره في `GOOGLE_APPLICATION_CREDENTIALS`. لا تضع المفتاح في Flutter أو Git.
3. انشر تغييرات الخادم، وطبّق الترحيلات بما فيها 015 و017، ثم ثبّت نسخة التطبيق المحدثة، واسمح بالإشعارات وسجّل الدخول لتسجيل الجهاز.
4. أرسل إشعارًا إلى حساب اختبار محدد من لوحة الإدارة. تُظهر رسالة الإدارة عدد سجلات الإشعار وعدد الإرسالات التي قبلها Firebase؛ القبول من Firebase لا يثبت عرضه على الهاتف.
5. جرّب والتطبيق مفتوح ثم في الخلفية. إذا لم يصل التنبيه، افحص صلاحية الإشعارات وإعدادات البطارية وسجلات الخادم؛ ابحث عن `FCM` دون طباعة رموز الأجهزة أو مفاتيح حساب الخدمة.

التنفيذ الحالي يحتفظ برمز جهاز واحد لكل حساب. عند تبديل الحساب على الجهاز يُنقل ارتباط الرمز إلى الحساب الجديد، وعند الخروج يُلغى تسجيله. إشعارات iOS تحتاج أيضًا ملف إعداد Firebase وإعداد APNs الخاصين بتطبيق iOS؛ هذه الملفات غير موجودة في النسخة الحالية.

اختبارات دون اتصال بـ Firebase أو مفاتيح حقيقية:

```bash
php tests/fcm_delivery_test.php
php tests/lint.php
```

اختبار سير الطلبات `tests/request_workflow_test.php` مخصص لخادم MariaDB مؤقت ومعزول فقط، ولا يقرأ ملف `.env`. يتطلب `MIHAN_DISPOSABLE_DB=1`، ويتصل بـ `127.0.0.1` بحساب `root` دون كلمة مرور، وينشئ ويحذف قاعدة `mihan_workflow_test`. لا تشغله على خادم بيانات فعلي.

## 17. آلية الاتفاق والاستلام للطلبات الجديدة

تطبّق الترحيلة `018_service_agreement_workflow.sql` قبل تشغيل الخادم والتطبيق المحدثين. تُبقي الطلبات الموجودة على `workflow_version=1`، بينما تنشأ الطلبات الجديدة على الإصدار 2. تُحفظ الفواتير والطلبات القديمة دون تحويل الميزانية إلى موافقة مفترضة على سعر.

رحلة الإصدار 2:

1. يحدد العميل الخدمة والعنوان، ويختار أقرب وقت متاح أو موعدًا مفضلًا، ثم يراجع البيانات قبل الإرسال. الميزانية اختيارية وتعبّر عن توقع العميل فقط.
2. يحجز مقدم خدمة مؤهل الطلب، فيصبح مخصصًا له بالحالة `accepted`.
3. يرسل عرضًا يوضح إجمالي السعر، والأعمال والمواد المشمولة والاستثناءات، وموعد زيارة اختياريًا. يُحفظ كل إصدار في `request_quotes` دون مسح العروض السابقة.
4. يوافق العميل على رقم العرض الحالي تحديدًا أو يطلب عرضًا جديدًا. العرض المعتمد لا يمكن تعديله؛ ولا يبدأ التنفيذ قبل اعتماده. رفض عرض قديم أو الموافقة عليه بعد استبداله لا يغير الاتفاق الحالي.
5. بعد التنفيذ يرسل مقدم الخدمة ملخصًا للإنجاز. تنتقل الحالة إلى `awaiting_confirmation` دون إصدار فاتورة.
6. يؤكد العميل الاستلام، فتصدر فاتورة واحدة بالسعر المعتمد داخل معاملة واحدة. أو يرسل ملاحظات لإعادة الطلب إلى التنفيذ بالسعر نفسه.
7. يستمر الدفع والتقييم عبر النظام المالي الحالي. الدفع الحالي محاكاة كما في الإصدار السابق؛ لا تضيف هذه الآلية بوابة دفع فعلية.

يمكن للعميل الإلغاء قبل بدء التنفيذ، ويُشعر مقدم الخدمة المعيّن بالإلغاء. بعد البدء، تتاح المعالجة قبل الاستلام ونظام الشكاوى الموجود. لا يوجد إغلاق تلقائي عند عدم رد العميل، ولا تقدير آلي لسعر أو موعد وصول مضمون.

واجهات الإصدار 2:

| الإجراء | المسار | البيانات |
|---|---|---|
| عرض مقدم الخدمة | `POST /api/provider/requests/{id}/quote` | `total_price`, `details`, `visit_at` اختياري بصيغة `Y-m-d H:i:s` بتوقيت المنصة |
| قرار العميل | `POST /api/customer/requests/{id}/quote` | `quote_id`, `decision=approve/decline` |
| بدء التنفيذ | `POST /api/provider/requests/{id}/start` | لا شيء؛ يشترط عرضًا معتمدًا |
| طلب الاستلام | `POST /api/provider/requests/{id}/complete` | `completion_notes`؛ السعر يؤخذ من الاتفاق |
| تأكيد الاستلام | `POST /api/customer/requests/{id}/confirm-completion` | لا شيء |
| طلب معالجة | `POST /api/customer/requests/{id}/rework` | `notes` |

اختبار قاعدة البيانات المعزول `tests/request_workflow_test.php` يتحقق من الآلية على MariaDB وMySQL 8، ويشمل عقود API عبر `tests/workflow_api_contract.php`. يشترط خادمًا مؤقتًا كما هو موضح أعلاه. اختبارات واجهة Flutter موجودة في `test/service_workflow_test.dart` و`test/create_request_view_test.dart`.
