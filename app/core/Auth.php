<?php
require_once __DIR__ . '/../models/User.php';

/** Auth - login/logout/registration + role guards */
class Auth
{
    private static ?array $permissionsCache = null;
    private static ?array $userCache = null;

    public static function attempt(string $email, string $password): bool
    {
        $email = trim(strtolower($email));
        $userModel = new User();
        $user = $userModel->first(['email' => $email]);
        if (!$user) return false;
        if ((int)$user['is_active'] === 0) return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['name']);
        self::$permissionsCache = null;
        self::$userCache = null;
        self::$userOverridesCache = null;
        return true;
    }

    public static function clearCache(?int $userId = null): void
    {
        self::$permissionsCache = null;
        if ($userId === null) {
            self::$userCache = null;
            self::$userOverridesCache = null;
        } else {
            if (self::$userCache !== null && (int)(self::$userCache['id'] ?? 0) === $userId) {
                self::$userCache = null;
            }
            if (self::$userOverridesCache !== null && isset(self::$userOverridesCache[$userId])) {
                unset(self::$userOverridesCache[$userId]);
            }
        }
    }

    public static function logout(): void
    {
        self::clearCache();
        Session::destroy();
    }

    public static function check(): bool
    {
        if (!Session::has('user_id')) {
            return false;
        }
        $u = self::user();
        return ($u !== null && (int)($u['is_active'] ?? 0) === 1);
    }

    public static function id() { return Session::get('user_id'); }

    public static function role(): ?string
    {
        $u = self::user();
        return $u ? ($u['role'] ?? null) : null;
    }

    public static function name(): ?string
    {
        $u = self::user();
        return $u ? ($u['name'] ?? null) : null;
    }

    public static function user(): ?array
    {
        if (!Session::has('user_id')) return null;
        if (self::$userCache === null) {
            $user = (new User())->find(Session::get('user_id'));
            if (!$user || (int)($user['is_active'] ?? 0) === 0) {
                self::logout();
                self::$userCache = null;
                return null;
            }
            // Sync session if role or name changed
            if (Session::get('user_role') !== $user['role']) {
                Session::set('user_role', $user['role']);
            }
            if (Session::get('user_name') !== $user['name']) {
                Session::set('user_name', $user['name']);
            }
            self::$userCache = $user;
        }
        return self::$userCache;
    }

    public static function requireLogin(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'الرجاء تسجيل الدخول أولاً');
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }

        $user = self::user();
        if (!$user || (int)($user['is_active'] ?? 0) === 0) {
            self::logout();
            Session::flash('error', 'تم تعطيل حسابك أو حذفه من قبل الإدارة.');
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    private static ?array $userOverridesCache = null;

    /**
     * Central RBAC and Effective User Permission check.
     * Precedence:
     * 1. Direct user override in user_permissions table (is_allowed = 1 or 0)
     * 2. Role permissions from role_permissions table
     * 3. Fallback defaults if table records are empty
     */
    public static function can(string $permission, ?int $userId = null): bool
    {
        $user = null;
        if ($userId !== null) {
            $user = (new User())->find($userId);
        } else {
            $user = self::user();
        }

        if (!$user || (int)($user['is_active'] ?? 0) === 0) {
            return false;
        }

        $uid = (int)$user['id'];
        $role = $user['role'] ?? '';
        if ($role === '') {
            return false;
        }

        // 1. Check direct user override
        if (self::$userOverridesCache === null || !isset(self::$userOverridesCache[$uid])) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT p.`key` as perm_key, up.is_allowed
                    FROM user_permissions up
                    JOIN permissions p ON p.id = up.permission_id
                    WHERE up.user_id = :uid
                ");
                $stmt->execute(['uid' => $uid]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                self::$userOverridesCache[$uid] = [];
                foreach ($rows as $r) {
                    self::$userOverridesCache[$uid][$r['perm_key']] = (int)$r['is_allowed'];
                }
            } catch (Throwable $e) {
                self::$userOverridesCache[$uid] = [];
            }
        }

        if (isset(self::$userOverridesCache[$uid][$permission])) {
            return self::$userOverridesCache[$uid][$permission] === 1;
        }

        // 2. Check role permissions
        if (self::$permissionsCache === null) {
            self::$permissionsCache = [];
            try {
                $db = Database::getInstance();
                $rows = $db->query("
                    SELECT rp.role, p.`key` as perm_key
                    FROM role_permissions rp
                    JOIN permissions p ON p.id = rp.permission_id
                ")->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $r) {
                    self::$permissionsCache[$r['role']][$r['perm_key']] = true;
                }
            } catch (Throwable $e) {
                self::$permissionsCache = [];
            }
        }

        // Fallback defaults if table records are empty
        if (empty(self::$permissionsCache[$role])) {
            $defaults = [
                'admin' => [
                    'manage_users' => true, 'manage_categories' => true, 'manage_locations' => true,
                    'verify_providers' => true, 'manage_complaints' => true, 'view_reports' => true,
                    'manage_requests' => true, 'manage_finance' => true, 'manage_ai' => true,
                    'manage_settings' => true, 'manage_employees' => true, 'create_request' => true, 'provide_service' => true,
                    'manage_payouts' => true, 'view_finance' => true, 'view_conversations' => true,
                    'manage_reviews' => true, 'view_dashboard' => true, 'view_providers' => true,
                ],
                'customer' => ['create_request' => true],
                'provider' => ['provide_service' => true],
                'employee' => [
                    'view_providers' => true, 'manage_reviews' => true, 'view_finance' => true,
                    'view_conversations' => true, 'manage_complaints' => true,
                    'manage_requests' => true, 'view_reports' => true, 'view_dashboard' => true,
                ],
            ];
            return !empty($defaults[$role][$permission]);
        }

        // Admin has access to standard administrative functions
        if ($role === 'admin' && empty(self::$permissionsCache['admin'][$permission])) {
            $standardAdmin = [
                'manage_users', 'manage_categories', 'manage_locations', 'verify_providers',
                'manage_complaints', 'view_reports', 'manage_requests', 'manage_finance',
                'manage_ai', 'manage_settings', 'create_request', 'provide_service',
                'manage_employees', 'manage_payouts', 'view_finance', 'view_conversations',
                'manage_reviews', 'view_dashboard', 'view_providers',
            ];
            if (in_array($permission, $standardAdmin, true)) {
                return true;
            }
        }

        return !empty(self::$permissionsCache[$role][$permission]);
    }

    public static function requirePermission(string $permission): void
    {
        self::requireLogin();
        if (!self::can($permission)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
