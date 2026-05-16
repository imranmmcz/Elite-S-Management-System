<?php
declare(strict_types=1);
/**
 * Elite School Management - Exam Controller
 * Placeholder for exam management
 */

require_once __DIR__ . '/../config/app.php';

requireLogin();

$action = $_GET['action'] ?? 'index';

if ($action === 'index') {
    $pageTitle = 'Examinations';
    
    // Fetch exams
    $exams = Database::fetchAll(
        "SELECT e.*, ay.name as academic_year 
         FROM exams e 
         JOIN academic_years ay ON e.academic_year_id = ay.id 
         ORDER BY e.id DESC 
         LIMIT 20"
    );
    
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/exams/index.php';
    include ROOT . '/views/layouts/footer.php';
}
else {
    http_response_code(404);
    echo 'Action not implemented yet';
}
