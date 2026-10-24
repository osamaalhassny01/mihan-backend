<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AuthController extends Controller
{
    public function showLogin()
    {
        // Preserve a "come back here after login" target, e.g. a provider
        // profile's "Request this service" button for a logged-out visitor.
        // Only same-site relative paths are accepted to avoid open redirects.
        $redirect = $this->safeRedirect($this->input('redirect'));
        if (Auth::check()) $this->redirectByRole($redirect);
        $this->view('auth/login', ['title' => __('login'), 'redirect' => $redirect]);
    }

    public function login()
    {
        $this->verifyCsrfOrFail();
        $email = $this->input('email');
        $password = $this->input('password');
        $redirect = $this->safeRedirect($this->input('redirect'));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $throttleKey = "login:{$ip}:" . hash('sha256', strtolower(trim((string)$email)));

        if (class_exists('RateLimiter') && RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int)ceil($seconds / 60));
            Session::flash('error', "لقد تجاوزت الحد المسموح به لمحاولات تسجيل الدخول. يرجى الانتظار {$minutes} دقيقة والمحاولة مجددًا.");
            $this->view('auth/login', ['title' => __('login'), 'redirect' => $redirect]);
            return;
        }

        $v = new Validator($_POST);
        $v->validate(['email' => 'required|email', 'password' => 'required']);
        if ($v->fails()) {
            Session::set('_old_input', $_POST);
            Session::flash('error', 'الرجاء تصحيح الأخطاء أدناه');
            $this->view('auth/login', ['title' => __('login'), 'errors' => $v->errors(), 'redirect' => $redirect]);
            return;
        }

        if (Auth::attempt($email, $password)) {
            if (class_exists('RateLimiter')) RateLimiter::clear($throttleKey);
            if (class_exists('CSRF')) CSRF::rotateToken();
            ActivityLog::log(Auth::id(), 'login', 'تسجيل دخول ناجح');
            $this->redirectByRole($redirect);
        } else {
            if (class_exists('RateLimiter')) {
                RateLimiter::hit($throttleKey, 900);
                $remaining = max(0, 5 - RateLimiter::attempts($throttleKey));
                $msg = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                if ($remaining > 0 && $remaining <= 3) {
                    $msg .= " (متبقي {$remaining} محاولات قبل القفل المؤقت)";
                }
            } else {
                $msg = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            }
            Session::flash('error', $msg);
            $this->view('auth/login', ['title' => __('login'), 'redirect' => $redirect]);
        }
    }

    /** Only allow same-site, relative paths ("/customer/...") to prevent open-redirect abuse */
    private function safeRedirect(?string $path): ?string
    {
        if (!$path) return null;
        if (strpos($path, '/') !== 0 || strpos($path, '//') === 0) return null;
        return $path;
    }

    public function showRegister()
    {
        if (Auth::check()) $this->redirectByRole();
        if (!settingBool('allow_customer_registration', true) && !settingBool('allow_provider_registration', true)) { Session::flash('error','التسجيل مغلق حاليًا من إدارة المنصة.'); $this->redirect('/login'); return; }
        $this->view('auth/register', ['title' => __('register')]);
    }

    public function register()
    {
        $this->verifyCsrfOrFail();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $regThrottleKey = 'register:' . $ip;
        if (class_exists('RateLimiter') && RateLimiter::tooManyAttempts($regThrottleKey, 10, 3600)) {
            $seconds = RateLimiter::availableIn($regThrottleKey);
            $minutes = max(1, (int)ceil($seconds / 60));
            Session::flash('error', "تم تجاوز الحد الأقصى لمحاولات التسجيل من عنوانك. يرجى الانتظار {$minutes} دقيقة قبل المحاولة مجددًا.");
            $this->redirect('/register');
            return;
        }

        $v = new Validator($_POST);
        $v->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:customer,provider',
        ]);

        $userModel = new User();
        if (!$v->fails() && $userModel->emailExists($this->input('email'))) {
            $v = new Validator($_POST);
            $v->validate([]);
            Session::flash('error', 'هذا البريد الإلكتروني مستخدم بالفعل');
            Session::set('_old_input', $_POST);
            $this->view('auth/register', ['title' => __('register')]);
            return;
        }

        if ($v->fails()) {
            Session::set('_old_input', $_POST);
            $this->view('auth/register', ['title' => __('register'), 'errors' => $v->errors()]);
            return;
        }

        $role = $this->input('role');
        if ($role === 'customer' && !settingBool('allow_customer_registration', true)) { Session::flash('error','تسجيل حسابات العملاء مغلق حاليًا.'); $this->redirect('/register'); return; }
        if ($role === 'provider' && !settingBool('allow_provider_registration', true)) { Session::flash('error','تسجيل مقدمي الخدمة مغلق حاليًا.'); $this->redirect('/register'); return; }
        $userId = $userModel->register([
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'password' => $this->input('password'),
            'role' => $role,
        ]);

        if ($role === 'provider') {
            (new ProviderProfile())->insert([
                'user_id' => $userId,
                'profession' => $this->input('profession', ''),
                'experience_years' => 0,
                'is_verified' => 0,
                'is_available' => settingBool('auto_provider_available', true) ? 1 : 0,
            ]);
        }

        ActivityLog::log($userId, 'register', 'تسجيل حساب جديد كـ ' . $role);
        if (class_exists('RateLimiter')) RateLimiter::hit($regThrottleKey, 3600);
        Auth::attempt($this->input('email'), $this->input('password'));
        Session::flash('success', 'تم إنشاء الحساب بنجاح! مرحبًا بك في مِهَن');
        $this->redirectByRole();
    }

    public function logout()
    {
        $this->verifyCsrfOrFail();
        $userId = Auth::id();
        if ($userId) {
            ActivityLog::log($userId, 'logout', 'تسجيل خروج');
        }
        Auth::logout();
        if (class_exists('CSRF')) CSRF::rotateToken();
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }

    private function redirectByRole(?string $redirect = null): void
    {
        if ($redirect) { $this->redirect($redirect); return; }
        switch (Auth::role()) {
            case 'admin': $this->redirect('/admin/dashboard'); break;
            case 'provider': $this->redirect('/provider/dashboard'); break;
            case 'employee': $this->redirect('/employee/dashboard'); break;
            default: $this->redirect('/customer/dashboard');
        }
    }
}
