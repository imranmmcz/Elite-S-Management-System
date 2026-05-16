<?php
declare(strict_types=1);
/**
 * Elite School Management - Authentication Controller
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../config/app.php';

$action = $_GET['action'] ?? 'login';

// ===================================================================
// LOGIN ACTION
// ===================================================================
if ($action === 'login') {
    // Show login form if GET request
    if (isGet()) {
        // Redirect if already logged in
        if (!empty($_SESSION['user_id'])) {
            redirect(getDashboardUrl());
        }
        
        include ROOT . '/views/auth/login.php';
        exit;
    }
    
    // Process login if POST request
    if (isPost()) {
        // Verify CSRF
        if (!verify_csrf()) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/login');
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validation
        if (empty($email) || empty($password)) {
            flash('error', 'Email এবং password প্রদান করুন।');
            redirect('/login');
        }
        
        // Check rate limiting
        if (!checkLoginAttempts($email)) {
            flash('error', 'অনেক বার ভুল চেষ্টা। ১৫ মিনিট পরে আবার চেষ্টা করুন।');
            redirect('/login');
        }
        
        // Fetch user
        $user = Database::fetchOne(
            'SELECT u.*, r.name as role_name, r.permissions 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = ? AND u.is_active = 1',
            [$email]
        );
        
        // Verify password
        if (!$user || !verifyPassword($password, $user['password'])) {
            recordFailedLogin($email);
            flash('error', 'ভুল email অথবা password।');
            redirect('/login');
        }
        
        // Check if password needs rehash
        if (needsRehash($user['password'])) {
            $newHash = hashPassword($password);
            Database::update('users', ['password' => $newHash], 'id = ?', [$user['id']]);
        }
        
        // Clear failed attempts
        clearLoginAttempts($email);
        
        // Log activity
        try {
            Database::insert('activity_logs', [
                'user_id'    => $user['id'],
                'action'     => 'login',
                'module'     => 'auth',
                'description' => 'User logged in',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $e) {
            // Log error but don't fail login
            error_log('Activity log error: ' . $e->getMessage());
        }
        
        // Login user
        loginUser($user);
        
        // Set remember me cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
            // In production, store token hash in database
        }
        
        flash('success', 'স্বাগতম, ' . e($user['full_name']) . '!');
        redirect(getDashboardUrl());
    }
}

// ===================================================================
// LOGOUT ACTION
// ===================================================================
elseif ($action === 'logout') {
    // Log activity
    if (!empty($_SESSION['user_id'])) {
        try {
            Database::insert('activity_logs', [
                'user_id'    => $_SESSION['user_id'],
                'action'     => 'logout',
                'module'     => 'auth',
                'description' => 'User logged out',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('Activity log error: ' . $e->getMessage());
        }
    }
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Logout
    logoutUser();
    
    flash('success', 'সফলভাবে লগআউট হয়েছে।');
    redirect('/login');
}

// Invalid action
else {
    http_response_code(404);
    echo 'Invalid action';
}
