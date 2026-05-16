<?php
declare(strict_types=1);
/**
 * Elite School Management - Attendance Controller
 * Handles attendance marking and reports
 */

require_once __DIR__ . '/../config/app.php';

requireLogin();
requireRole(['super_admin', 'admin', 'teacher']);

$action = $_GET['action'] ?? 'index';

// ===================================================================
// ATTENDANCE INDEX
// ===================================================================
if ($action === 'index') {
    
    $pageTitle = 'Attendance Management';
    
    // Get filter parameters
    $date = $_GET['date'] ?? date('Y-m-d');
    $classId = $_GET['class_id'] ?? '';
    $sectionId = $_GET['section_id'] ?? '';
    
    // Fetch classes for filter
    $classes = Database::fetchAll("SELECT * FROM classes ORDER BY numeric_name");
    
    // Fetch sections if class selected
    $sections = [];
    if ($classId) {
        $sections = Database::fetchAll("SELECT * FROM sections WHERE class_id = ? ORDER BY name", [$classId]);
    }
    
    // Fetch students if both class and section selected
    $students = [];
    $attendanceRecords = [];
    
    if ($classId && $sectionId) {
        $students = Database::fetchAll(
            "SELECT id, admission_no, CONCAT(first_name, ' ', last_name) as full_name, roll_no
             FROM students 
             WHERE class_id = ? AND section_id = ? AND status = 'active'
             ORDER BY roll_no",
            [$classId, $sectionId]
        );
        
        // Check if attendance already marked for this date
        $attendanceRecords = Database::fetchAll(
            "SELECT student_id, status, remarks
             FROM attendance
             WHERE date = ? AND class_id = ? AND section_id = ?",
            [$date, $classId, $sectionId]
        );
        
        // Convert to associative array for easy lookup
        $attendanceMap = [];
        foreach ($attendanceRecords as $record) {
            $attendanceMap[$record['student_id']] = $record;
        }
    }
    
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/attendance/index.php';
    include ROOT . '/views/layouts/footer.php';
}

// ===================================================================
// MARK ATTENDANCE
// ===================================================================
elseif ($action === 'mark') {
    
    if (!isPost() || !verify_csrf()) {
        flash('error', 'Invalid request');
        redirect('/attendance');
    }
    
    try {
        $date = $_POST['date'];
        $classId = (int)$_POST['class_id'];
        $sectionId = (int)$_POST['section_id'];
        $attendance = $_POST['attendance'] ?? [];
        
        if (empty($attendance)) {
            flash('error', 'No attendance data submitted');
            redirect('/attendance');
        }
        
        Database::beginTransaction();
        
        // Delete existing attendance for this date/class/section
        Database::query(
            "DELETE FROM attendance WHERE date = ? AND class_id = ? AND section_id = ?",
            [$date, $classId, $sectionId]
        );
        
        // Insert new attendance records
        $markedCount = 0;
        foreach ($attendance as $studentId => $status) {
            Database::insert('attendance', [
                'student_id' => (int)$studentId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'date' => $date,
                'status' => $status,
                'remarks' => $_POST['remarks'][$studentId] ?? '',
                'marked_by' => $_SESSION['user_id'],
            ]);
            $markedCount++;
        }
        
        // Log activity
        Database::insert('activity_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'mark_attendance',
            'module' => 'attendance',
            'description' => "Marked attendance for $markedCount students on $date",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        
        Database::commit();
        
        flash('success', "Attendance marked successfully for $markedCount students!");
        redirect('/attendance?date=' . $date . '&class_id=' . $classId . '&section_id=' . $sectionId);
        
    } catch (Exception $e) {
        Database::rollback();
        error_log('Attendance marking error: ' . $e->getMessage());
        flash('error', 'Failed to mark attendance. Please try again.');
        redirect('/attendance');
    }
}

// Invalid action
else {
    http_response_code(404);
    echo 'Invalid action';
}
