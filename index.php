<?php
declare(strict_types=1);
/**
 * Elite School Management System - Main Entry Point
 * Handles all routing and request processing
 */

// Load configuration
require_once __DIR__ . '/config/app.php';

// Get request URI and method
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Remove base path from URI
if ($base_path && str_starts_with($request_uri, $base_path)) {
    $request_uri = substr($request_uri, strlen($base_path));
}
$request_uri = '/' . trim($request_uri, '/');

// Simple routing
$routes = [
    'GET /'                    => 'views/welcome.php',
    'GET /login'               => 'views/auth/login.php',
    'POST /login'              => 'controllers/AuthController.php?action=login',
    'GET /logout'              => 'controllers/AuthController.php?action=logout',
    
    'GET /dashboard'           => 'controllers/DashboardController.php?action=index',
    
    'GET /students'            => 'controllers/StudentController.php?action=index',
    'GET /students/create'     => 'controllers/StudentController.php?action=create',
    'POST /students/store'     => 'controllers/StudentController.php?action=store',
    'GET /students/(\d+)'      => 'controllers/StudentController.php?action=show&id=$1',
    'GET /students/(\d+)/edit' => 'controllers/StudentController.php?action=edit&id=$1',
    'POST /students/(\d+)/update' => 'controllers/StudentController.php?action=update&id=$1',
    
    'GET /attendance'          => 'controllers/AttendanceController.php?action=index',
    'POST /attendance/mark'    => 'controllers/AttendanceController.php?action=mark',
    
    'GET /exams'               => 'controllers/ExamController.php?action=index',
    'GET /exams/create'        => 'controllers/ExamController.php?action=create',
    'GET /exams/(\d+)'         => 'controllers/ExamController.php?action=show&id=$1',
    'GET /exams/(\d+)/marks'   => 'controllers/ExamController.php?action=marks&id=$1',
    'POST /exams/(\d+)/save-marks' => 'controllers/ExamController.php?action=saveMarks&id=$1',
    
    'GET /fees'                => 'controllers/FeeController.php?action=index',
    'GET /fees/structures'     => 'controllers/FeeController.php?action=structures',
    'GET /fees/generate'       => 'controllers/FeeController.php?action=generate',
    'POST /fees/generate'      => 'controllers/FeeController.php?action=generate',
    'GET /fees/(\d+)'          => 'controllers/FeeController.php?action=show&id=$1',
    
    'GET /payments'            => 'controllers/PaymentController.php?action=index',
    'GET /payments/create'     => 'controllers/PaymentController.php?action=create',
    'POST /payments/store'     => 'controllers/PaymentController.php?action=store',
    'GET /payments/(\d+)'      => 'controllers/PaymentController.php?action=show&id=$1',
    
    'GET /reports'             => 'controllers/ReportController.php?action=index',
    'GET /reports/students'    => 'controllers/ReportController.php?action=students',
    'GET /reports/attendance'  => 'controllers/ReportController.php?action=attendance',
    'GET /reports/exams'       => 'controllers/ReportController.php?action=exams',
    'GET /reports/financial'   => 'controllers/ReportController.php?action=financial',
    
    'GET /certificates'        => 'controllers/CertificateController.php?action=index',
    'GET /certificates/create' => 'controllers/CertificateController.php?action=create',
    'POST /certificates/store' => 'controllers/CertificateController.php?action=store',
    'GET /certificates/(\d+)'  => 'controllers/CertificateController.php?action=show&id=$1',
    'GET /certificates/(\d+)/download' => 'controllers/CertificateController.php?action=download&id=$1',
    
    'GET /library'             => 'controllers/LibraryController.php?action=index',
    'GET /library/books'       => 'controllers/LibraryController.php?action=books',
    'GET /library/issue'       => 'controllers/LibraryController.php?action=issue',
    'POST /library/issue'      => 'controllers/LibraryController.php?action=issue',
    'GET /library/return'      => 'controllers/LibraryController.php?action=return',
    'POST /library/return'     => 'controllers/LibraryController.php?action=return',
    
    'GET /settings'            => 'controllers/SettingsController.php?action=index',
    'GET /settings/general'    => 'controllers/SettingsController.php?action=general',
    'POST /settings/general'   => 'controllers/SettingsController.php?action=general',
    'GET /settings/academic'   => 'controllers/SettingsController.php?action=academic',
    'POST /settings/academic'  => 'controllers/SettingsController.php?action=academic',
    'GET /settings/classes'    => 'controllers/SettingsController.php?action=classes',
    'GET /settings/users'      => 'controllers/SettingsController.php?action=users',
    'GET /settings/notifications' => 'controllers/SettingsController.php?action=notifications',
    'POST /settings/notifications' => 'controllers/SettingsController.php?action=notifications',
];

// Match route
$matched = false;
foreach ($routes as $route => $handler) {
    [$method, $pattern] = explode(' ', $route, 2);
    
    // Check if method matches
    if ($method !== $request_method) {
        continue;
    }
    
    // Convert pattern to regex
    $regex = '#^' . preg_replace('/\\\\\(([^)]+)\\\\\)/', '($1)', preg_quote($pattern, '#')) . '$#';
    
    // Check if URI matches pattern
    if (preg_match($regex, $request_uri, $matches)) {
        array_shift($matches); // Remove full match
        
        // Parse handler
        if (str_contains($handler, '?')) {
            [$file, $query] = explode('?', $handler, 2);
            parse_str($query, $params);
            
            // Replace placeholders with captured groups
            foreach ($params as $key => $value) {
                if (preg_match('/\$(\d+)/', $value, $m)) {
                    $index = (int)$m[1] - 1;
                    $params[$key] = $matches[$index] ?? '';
                }
            }
            
            $_GET = array_merge($_GET, $params);
        } else {
            $file = $handler;
        }
        
        // Include file
        $full_path = ROOT . '/' . $file;
        if (file_exists($full_path)) {
            require $full_path;
            $matched = true;
            break;
        }
    }
}

// If no route matched, show 404
if (!$matched) {
    http_response_code(404);
    if (file_exists(ROOT . '/views/errors/404.php')) {
        require ROOT . '/views/errors/404.php';
    } else {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body style="font-family:sans-serif;text-align:center;padding:50px"><h1>404</h1><p>Page not found</p></body></html>';
    }
}
