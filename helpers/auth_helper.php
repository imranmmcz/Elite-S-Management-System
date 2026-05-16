<?php
declare(strict_types=1);
/**
 * Elite School Management - Authentication & Authorization Helpers
 * RBAC (Role-Based Access Control) implementation
 */

/**
 * Require user to be logged in
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        flash('error', 'অনুগ্রহ করে লগইন করুন।');
        redirect('/login');
    }
}

/**
 * Require specific role(s)
 * @param string|array $roles Single role or array of allowed roles
 */
function requireRole(string|array $roles): void {
    requireLogin();
    
    $userRole = $_SESSION['role'] ?? '';
    $allowed = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($userRole, $allowed, true)) {
        if (isAjax()) {
            jsonResponse(['error' => 'Access denied. Insufficient permissions.'], 403);
        }
        http_response_code(403);
        include ROOT . '/views/errors/403.php';
        exit;
    }
}

/**
 * Check if user has specific permission
 * @param string $permission Permission string (e.g., 'student.create', 'fee.edit')
 * @return bool
 */
function hasPermission(string $permission): bool {
    $perms = $_SESSION['permissions'] ?? [];
    return in_array($permission, $perms, true) || in_array('*', $perms, true);
}

/**
 * Get current logged-in user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    
    // Return cached user data if available
    if (isset($_SESSION['user_cache'])) {
        return $_SESSION['user_cache'];
    }
    
    // Fetch from database
    $user = Database::fetchOne(
        'SELECT u.*, r.name as role_name, r.permissions 
         FROM users u 
         JOIN roles r ON u.role_id = r.id 
         WHERE u.id = ? AND u.is_active = 1',
        [$_SESSION['user_id']]
    );
    
    if (!$user) {
        // User not found or inactive - logout
        session_destroy();
        redirect('/login');
    }
    
    // Cache user data in session
    $_SESSION['user_cache'] = $user;
    $_SESSION['permissions'] = json_decode($user['permissions'] ?? '[]', true);
    
    return $user;
}

/**
 * Check if current user is specific role
 */
function isRole(string $role): bool {
    return ($_SESSION['role'] ?? '') === $role;
}

/**
 * Check if super admin
 */
function isSuperAdmin(): bool {
    return isRole('super_admin');
}

/**
 * Check if admin (super_admin or admin)
 */
function isAdmin(): bool {
    return in_array($_SESSION['role'] ?? '', ['super_admin', 'admin'], true);
}

/**
 * Check if teacher
 */
function isTeacher(): bool {
    return isRole('teacher');
}

/**
 * Check if student
 */
function isStudent(): bool {
    return isRole('student');
}

/**
 * Check if parent
 */
function isParent(): bool {
    return isRole('parent');
}

/**
 * Check if accountant
 */
function isAccountant(): bool {
    return isRole('accountant');
}

/**
 * Check if librarian
 */
function isLibrarian(): bool {
    return isRole('librarian');
}

/**
 * Get menu items based on user role
 * @return array Menu items array
 */
function getMenuItems(): array {
    $role = $_SESSION['role'] ?? 'guest';
    
    $allMenus = [
        [
            'url'   => '/dashboard',
            'icon'  => 'fa-home',
            'label' => 'ড্যাশবোর্ড',
            'roles' => ['super_admin', 'admin', 'teacher', 'accountant', 'librarian', 'student', 'parent']
        ],
        [
            'url'   => '/students',
            'icon'  => 'fa-user-graduate',
            'label' => 'শিক্ষার্থী',
            'roles' => ['super_admin', 'admin', 'teacher']
        ],
        [
            'url'   => '/attendance',
            'icon'  => 'fa-calendar-check',
            'label' => 'উপস্থিতি',
            'roles' => ['super_admin', 'admin', 'teacher']
        ],
        [
            'url'   => '/exams',
            'icon'  => 'fa-file-alt',
            'label' => 'পরীক্ষা ও ফলাফল',
            'roles' => ['super_admin', 'admin', 'teacher', 'student', 'parent']
        ],
        [
            'url'   => '/fees',
            'icon'  => 'fa-money-bill',
            'label' => 'ফি ব্যবস্থাপনা',
            'roles' => ['super_admin', 'admin', 'accountant']
        ],
        [
            'url'   => '/payments',
            'icon'  => 'fa-credit-card',
            'label' => 'অনলাইন পেমেন্ট',
            'roles' => ['super_admin', 'admin', 'accountant', 'student', 'parent']
        ],
        [
            'url'   => '/hr',
            'icon'  => 'fa-users',
            'label' => 'মানব সম্পদ',
            'roles' => ['super_admin', 'admin']
        ],
        [
            'url'   => '/library',
            'icon'  => 'fa-book',
            'label' => 'লাইব্রেরি',
            'roles' => ['super_admin', 'admin', 'librarian', 'teacher', 'student']
        ],
        [
            'url'   => '/notifications',
            'icon'  => 'fa-bell',
            'label' => 'নোটিফিকেশন',
            'roles' => ['super_admin', 'admin', 'teacher']
        ],
        [
            'url'   => '/reports',
            'icon'  => 'fa-chart-bar',
            'label' => 'রিপোর্ট',
            'roles' => ['super_admin', 'admin', 'accountant']
        ],
        [
            'url'   => '/certificates',
            'icon'  => 'fa-award',
            'label' => 'সার্টিফিকেট',
            'roles' => ['super_admin', 'admin', 'student', 'parent']
        ],
        [
            'url'   => '/academic',
            'icon'  => 'fa-graduation-cap',
            'label' => 'একাডেমিক',
            'roles' => ['super_admin', 'admin']
        ],
        [
            'url'   => '/settings',
            'icon'  => 'fa-cog',
            'label' => 'সেটিংস',
            'roles' => ['super_admin']
        ],
    ];
    
    // Filter menu items by role
    return array_filter($allMenus, fn($item) => in_array($role, $item['roles'], true));
}

/**
 * Get dashboard URL based on role
 */
function getDashboardUrl(): string {
    return match ($_SESSION['role'] ?? 'guest') {
        'student'    => '/student/dashboard',
        'parent'     => '/parent/dashboard',
        'teacher'    => '/teacher/dashboard',
        'accountant' => '/accountant/dashboard',
        'librarian'  => '/librarian/dashboard',
        default      => '/dashboard',
    };
}

/**
 * Check login attempts (rate limiting)
 * @param string $identifier Email or username
 * @return bool True if allowed, false if too many attempts
 */
function checkLoginAttempts(string $identifier): bool {
    $key = 'login_attempts_' . md5($identifier);
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
    
    // Reset after 15 minutes
    if (time() - $attempts['time'] > 900) {
        unset($_SESSION[$key]);
        return true;
    }
    
    // Block after 5 failed attempts
    if ($attempts['count'] >= 5) {
        return false;
    }
    
    return true;
}

/**
 * Record failed login attempt
 */
function recordFailedLogin(string $identifier): void {
    $key = 'login_attempts_' . md5($identifier);
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
    $attempts['count']++;
    $_SESSION[$key] = $attempts;
}

/**
 * Clear login attempts
 */
function clearLoginAttempts(string $identifier): void {
    $key = 'login_attempts_' . md5($identifier);
    unset($_SESSION[$key]);
}

/**
 * Hash password
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Check if password needs rehash (if algorithm improved)
 */
function needsRehash(string $hash): bool {
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Login user (set session data)
 */
function loginUser(array $user): void {
    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['role']       = $user['role_name'] ?? 'user';
    $_SESSION['role_id']    = $user['role_id'];
    $_SESSION['permissions'] = json_decode($user['permissions'] ?? '[]', true);
    $_SESSION['logged_in_at'] = time();
    
    // Update last login
    Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
}

/**
 * Logout user
 */
function logoutUser(): void {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Get role color for badges
 */
function getRoleColor(string $role): string {
    return match ($role) {
        'super_admin' => 'danger',
        'admin'       => 'primary',
        'teacher'     => 'success',
        'student'     => 'info',
        'parent'      => 'warning',
        'accountant'  => 'secondary',
        'librarian'   => 'dark',
        default       => 'secondary',
    };
}

/**
 * Get role display name (Bengali)
 */
function getRoleDisplayName(string $role): string {
    return match ($role) {
        'super_admin' => 'সুপার অ্যাডমিন',
        'admin'       => 'অ্যাডমিন',
        'teacher'     => 'শিক্ষক',
        'student'     => 'শিক্ষার্থী',
        'parent'      => 'অভিভাবক',
        'accountant'  => 'হিসাবরক্ষক',
        'librarian'   => 'গ্রন্থাগারিক',
        default       => $role,
    };
}
