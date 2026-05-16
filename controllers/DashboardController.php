<?php
declare(strict_types=1);
/**
 * Elite School Management - Dashboard Controller
 * Role-based dashboard with statistics
 */

require_once __DIR__ . '/../config/app.php';

// Require login
requireLogin();

$action = $_GET['action'] ?? 'index';

// ===================================================================
// DASHBOARD INDEX
// ===================================================================
if ($action === 'index') {
    $user = getCurrentUser();
    $role = $_SESSION['role'] ?? '';
    
    // Fetch statistics based on role
    $stats = [];
    
    try {
        // Common statistics for all roles
        $currentYear = Database::fetchOne('SELECT id, name FROM academic_years WHERE is_active = 1');
        
        if ($role === 'super_admin' || $role === 'admin') {
            // Admin dashboard statistics
            $stats['total_students'] = Database::fetchOne('SELECT COUNT(*) as count FROM students WHERE status = ?', ['active'])['count'] ?? 0;
            $stats['total_staff'] = Database::fetchOne('SELECT COUNT(*) as count FROM staff WHERE status = ?', ['active'])['count'] ?? 0;
            $stats['total_classes'] = Database::fetchOne('SELECT COUNT(*) as count FROM classes')['count'] ?? 0;
            
            // Today's attendance
            $stats['today_attendance'] = Database::fetchOne(
                'SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent
                 FROM attendance WHERE date = CURDATE()'
            );
            
            // Fee collection statistics (current month)
            $stats['fee_collection'] = Database::fetchOne(
                'SELECT 
                    COUNT(*) as total_invoices,
                    SUM(total_amount) as total_amount,
                    SUM(paid_amount) as paid_amount,
                    SUM(due_amount) as due_amount
                 FROM invoices 
                 WHERE YEAR(created_at) = YEAR(CURDATE()) 
                   AND MONTH(created_at) = MONTH(CURDATE())'
            );
            
            // Recent activities
            $stats['recent_activities'] = Database::fetchAll(
                'SELECT a.*, u.full_name 
                 FROM activity_logs a 
                 LEFT JOIN users u ON a.user_id = u.id 
                 ORDER BY a.created_at DESC 
                 LIMIT 10'
            );
            
            // Pending approvals
            $stats['pending_certificates'] = Database::fetchOne(
                'SELECT COUNT(*) as count FROM certificate_requests WHERE status = ?',
                ['pending']
            )['count'] ?? 0;
            
            $stats['pending_leaves'] = Database::fetchOne(
                'SELECT COUNT(*) as count FROM leaves WHERE status = ?',
                ['pending']
            )['count'] ?? 0;
            
        } elseif ($role === 'teacher') {
            // Teacher dashboard statistics
            $stats['my_classes'] = Database::fetchAll(
                'SELECT DISTINCT c.name as class_name, s.name as section_name
                 FROM class_subjects cs
                 JOIN classes c ON cs.class_id = c.id
                 JOIN sections s ON c.id = s.class_id
                 WHERE cs.teacher_id = ?',
                [$user['id']]
            );
            
            $stats['total_students'] = Database::fetchOne(
                'SELECT COUNT(DISTINCT st.id) as count
                 FROM students st
                 JOIN class_subjects cs ON st.class_id = cs.class_id
                 WHERE cs.teacher_id = ? AND st.status = ?',
                [$user['id'], 'active']
            )['count'] ?? 0;
            
        } elseif ($role === 'accountant') {
            // Accountant dashboard statistics
            $stats['today_collection'] = Database::fetchOne(
                'SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE() AND status = ?',
                ['success']
            )['total'] ?? 0;
            
            $stats['month_collection'] = Database::fetchOne(
                'SELECT SUM(amount) as total FROM payments 
                 WHERE YEAR(payment_date) = YEAR(CURDATE()) 
                   AND MONTH(payment_date) = MONTH(CURDATE()) 
                   AND status = ?',
                ['success']
            )['total'] ?? 0;
            
            $stats['pending_invoices'] = Database::fetchOne(
                'SELECT COUNT(*) as count FROM invoices WHERE status IN (?, ?)',
                ['unpaid', 'overdue']
            )['count'] ?? 0;
            
        } elseif ($role === 'student' || $role === 'parent') {
            // Student/Parent dashboard
            $studentId = $_SESSION['student_id'] ?? null; // Set this during login for student role
            
            if ($studentId) {
                $stats['student_info'] = Database::fetchOne(
                    'SELECT * FROM v_current_students WHERE id = ?',
                    [$studentId]
                );
                
                // Recent exam results
                $stats['recent_exams'] = Database::fetchAll(
                    'SELECT e.name, em.marks_obtained, em.full_marks, em.grade, em.gpa
                     FROM exam_marks em
                     JOIN exams e ON em.exam_id = e.id
                     WHERE em.student_id = ?
                     ORDER BY e.created_at DESC
                     LIMIT 5',
                    [$studentId]
                );
                
                // Pending fees
                $stats['pending_fees'] = Database::fetchAll(
                    'SELECT * FROM invoices WHERE student_id = ? AND status != ? ORDER BY due_date',
                    [$studentId, 'paid']
                );
            }
        }
        
    } catch (Exception $e) {
        error_log('Dashboard stats error: ' . $e->getMessage());
        $stats['error'] = 'Failed to load statistics';
    }
    
    // Include dashboard view based on role
    $dashboardViews = [
        'super_admin' => 'admin_dashboard',
        'admin'       => 'admin_dashboard',
        'teacher'     => 'teacher_dashboard',
        'accountant'  => 'accountant_dashboard',
        'student'     => 'student_dashboard',
        'parent'      => 'student_dashboard',
        'librarian'   => 'librarian_dashboard',
    ];
    
    $viewFile = $dashboardViews[$role] ?? 'admin_dashboard';
    
    // Include layout
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/dashboard/' . $viewFile . '.php';
    include ROOT . '/views/layouts/footer.php';
}

// Invalid action
else {
    http_response_code(404);
    echo 'Invalid action';
}
