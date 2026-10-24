<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    protected string $table = 'users';

    public function register(array $data): int
    {
        return (int) $this->insert([
            'name' => trim((string)$data['name']),
            'email' => trim(strtolower((string)$data['email'])),
            'phone' => !empty($data['phone']) ? trim((string)$data['phone']) : null,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'] ?? 'customer',
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'is_verified_email' => isset($data['is_verified_email']) ? (int)$data['is_verified_email'] : 0,
        ]);
    }

    public function emailExists(string $email): bool
    {
        return $this->first(['email' => trim(strtolower($email))]) !== null;
    }

    public function emailExistsExcept(string $email, int $excludeId): bool
    {
        $email = trim(strtolower($email));
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :e AND id <> :id LIMIT 1");
        $stmt->execute(['e' => $email, 'id' => $excludeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function countByRole(string $role): int
    {
        return $this->count(['role' => $role]);
    }

    public function getByRole(string $role, string $orderBy = 'created_at DESC'): array
    {
        return $this->where(['role' => $role], $orderBy);
    }

    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = trim((string)$data['name']);
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = trim(strtolower((string)$data['email']));
        }
        if (array_key_exists('phone', $data)) {
            $fields[] = 'phone = :phone';
            $params['phone'] = !empty($data['phone']) ? trim((string)$data['phone']) : null;
        }
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = :is_active';
            $params['is_active'] = (int)$data['is_active'];
        }
        if (isset($data['is_verified_email'])) {
            $fields[] = 'is_verified_email = :is_verified_email';
            $params['is_verified_email'] = (int)$data['is_verified_email'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute($params);
        if ($ok) {
            Auth::clearCache($id);
        }
        return $ok;
    }

    public function updateRole(int $id, string $role): bool
    {
        $allowed = ['customer', 'provider', 'employee', 'admin'];
        if (!in_array($role, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET role = :r WHERE id = :id");
        $ok = $stmt->execute(['r' => $role, 'id' => $id]);
        if ($ok) {
            Auth::clearCache($id);
        }
        return $ok;
    }

    public function updatePassword(int $id, string $password): bool
    {
        if (mb_strlen($password) < 6) {
            return false;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :p WHERE id = :id");
        return $stmt->execute(['p' => $hash, 'id' => $id]);
    }

    /**
     * Get direct permission overrides from user_permissions table.
     * Returns array keyed by permission key: ['manage_requests' => 1, 'manage_users' => 0]
     */
    public function getPermissions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.`key`, up.is_allowed, up.permission_id
            FROM user_permissions up
            JOIN permissions p ON p.id = up.permission_id
            WHERE up.user_id = :uid
        ");
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[$r['key']] = (int)$r['is_allowed'];
        }
        return $result;
    }

    /**
     * Calculates full effective permissions for a user based on:
     * 1. User overrides in user_permissions table (highest precedence)
     * 2. Role defaults in role_permissions table
     */
    public function getEffectivePermissions(int $userId): array
    {
        $user = $this->find($userId);
        if (!$user) {
            return [];
        }

        // 1. Load all permissions catalogue
        $allPerms = $this->db->query("SELECT id, `key` FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Load role defaults
        $roleStmt = $this->db->prepare("
            SELECT p.`key`
            FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role = :r
        ");
        $roleStmt->execute(['r' => $user['role']]);
        $rolePermKeys = $roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $rolePermMap = array_fill_keys($rolePermKeys, true);

        // 3. Load user overrides
        $userOverrides = $this->getPermissions($userId);

        $effective = [];
        foreach ($allPerms as $p) {
            $k = $p['key'];
            if (isset($userOverrides[$k])) {
                $effective[$k] = ($userOverrides[$k] === 1);
            } else {
                $effective[$k] = !empty($rolePermMap[$k]);
            }
        }
        return $effective;
    }

    /**
     * Sets or updates individual permission overrides.
     * $permissions is array of ['permission_key' => bool|int] or ['perm_id' => bool|int]
     */
    public function setUserPermissions(int $userId, array $permissions): bool
    {
        $this->db->beginTransaction();
        try {
            $selectStmt = $this->db->prepare("SELECT id FROM permissions WHERE `key` = :k LIMIT 1");
            $upsertStmt = $this->db->prepare("
                INSERT INTO user_permissions (user_id, permission_id, is_allowed)
                VALUES (:u, :p, :a)
                ON DUPLICATE KEY UPDATE is_allowed = :a2
            ");

            foreach ($permissions as $key => $isAllowed) {
                if (is_numeric($key)) {
                    $permId = (int)$key;
                } else {
                    $selectStmt->execute(['k' => $key]);
                    $permId = (int)$selectStmt->fetchColumn();
                }

                if ($permId > 0) {
                    $allowedVal = ($isAllowed === true || $isAllowed === 1 || $isAllowed === '1') ? 1 : 0;
                    $upsertStmt->execute([
                        'u' => $userId,
                        'p' => $permId,
                        'a' => $allowedVal,
                        'a2' => $allowedVal,
                    ]);
                }
            }
            $this->db->commit();
            Auth::clearCache($userId);
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Replaces all user overrides with a new explicit set of permissions.
     * When saving user edit form:
     * - We compare submitted permissions against the user's role defaults.
     * - If a checked permission is already default for role, no override is needed.
     * - If a permission differs from role default, an explicit override is recorded.
     */
    public function replaceUserPermissions(int $userId, array $grantedKeys): bool
    {
        $user = $this->find($userId);
        if (!$user) return false;

        $allPerms = $this->db->query("SELECT id, `key` FROM permissions")->fetchAll(PDO::FETCH_ASSOC);
        $permIdMap = [];
        foreach ($allPerms as $p) {
            $permIdMap[$p['key']] = (int)$p['id'];
        }

        // Role defaults
        $roleStmt = $this->db->prepare("
            SELECT p.`key` FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role = :r
        ");
        $roleStmt->execute(['r' => $user['role']]);
        $roleDefaults = array_fill_keys($roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [], true);

        $grantedMap = array_fill_keys($grantedKeys, true);

        $this->db->beginTransaction();
        try {
            // Delete existing overrides for clean state
            $delStmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = :uid");
            $delStmt->execute(['uid' => $userId]);

            $insStmt = $this->db->prepare("INSERT INTO user_permissions (user_id, permission_id, is_allowed) VALUES (:u, :p, :a)");

            foreach ($allPerms as $p) {
                $k = $p['key'];
                $pid = (int)$p['id'];
                $isGranted = !empty($grantedMap[$k]);
                $isDefault = !empty($roleDefaults[$k]);

                // Only store override if different from role default
                if ($isGranted && !$isDefault) {
                    // Explicit grant override
                    $insStmt->execute(['u' => $userId, 'p' => $pid, 'a' => 1]);
                } elseif (!$isGranted && $isDefault) {
                    // Explicit revoke override
                    $insStmt->execute(['u' => $userId, 'p' => $pid, 'a' => 0]);
                }
            }
            $this->db->commit();
            Auth::clearCache($userId);
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function clearUserOverrides(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = :uid");
        $ok = $stmt->execute(['uid' => $userId]);
        if ($ok) {
            Auth::clearCache($userId);
        }
        return $ok;
    }

    /**
     * Returns full user record plus all permissions with flags for rendering edit forms.
     */
    public function findWithPermissions(int $id): ?array
    {
        $user = $this->find($id);
        if (!$user) return null;

        $allPerms = $this->db->query("SELECT id, `key`, description FROM permissions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Role defaults
        $roleStmt = $this->db->prepare("
            SELECT p.`key` FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role = :r
        ");
        $roleStmt->execute(['r' => $user['role']]);
        $roleDefaults = array_fill_keys($roleStmt->fetchAll(PDO::FETCH_COLUMN) ?: [], true);

        // Overrides
        $overrides = $this->getPermissions($id);

        $permList = [];
        foreach ($allPerms as $p) {
            $k = $p['key'];
            $isDefault = !empty($roleDefaults[$k]);
            $hasOverride = isset($overrides[$k]);
            $effective = $hasOverride ? ($overrides[$k] === 1) : $isDefault;

            $permList[] = [
                'id' => (int)$p['id'],
                'key' => $k,
                'description' => $p['description'],
                'is_role_default' => $isDefault,
                'has_override' => $hasOverride,
                'override_value' => $hasOverride ? $overrides[$k] : null,
                'is_allowed' => $effective,
            ];
        }

        $user['permissions_catalogue'] = $permList;
        return $user;
    }
}
