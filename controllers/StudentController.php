<?php
declare(strict_types=1);
/**
 * Elite School Management - Student Controller
 * Complete CRUD operations for student management
 */

require_once __DIR__ . '/../config/app.php';

// Require login
requireLogin();

$action = $_GET['action'] ?? 'index';

// ===================================================================
// LIST STUDENTS
// ===================================================================
if ($action === 'index') {
    requireRole(['super_admin', 'admin', 'teacher']);
    
    // Pagination
    $page = (int)($_GET['page'] ?? 1);
    $perPage = 20;
    
    // Search & Filter
    $search = $_GET['search'] ?? '';
    $classId = $_GET['class_id'] ?? '';
    $status = $_GET['status'] ?? 'active';
    
    // Build query
    $where = ['s.status = ?'];
    $params = [$status];
    
    if ($search) {
        $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_no LIKE ? OR s.email LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    if ($classId) {
        $where[] = "s.class_id = ?";
        $params[] = $classId;
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Count total
    $total = Database::fetchOne(
        "SELECT COUNT(*) as count FROM students s WHERE $whereClause",
        $params
    )['count'];
    
    // Fetch students
    $pagination = paginate($total, $page, $perPage);
    
    $students = Database::fetchAll(
        "SELECT s.*, c.name as class_name, sec.name as section_name,
                CONCAT(s.first_name, ' ', s.last_name) as full_name
         FROM students s
         LEFT JOIN classes c ON s.class_id = c.id
         LEFT JOIN sections sec ON s.section_id = sec.id
         WHERE $whereClause
         ORDER BY c.numeric_name, sec.name, s.roll_no
         LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
        $params
    );
    
    // Fetch classes for filter
    $classes = Database::fetchAll("SELECT * FROM classes ORDER BY numeric_name");
    
    // Include view
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/students/index.php';
    include ROOT . '/views/layouts/footer.php';
}

// ===================================================================
// SHOW CREATE FORM
// ===================================================================
elseif ($action === 'create') {
    requireRole(['super_admin', 'admin']);
    
    // Fetch data for form
    $academicYears = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");
    $classes = Database::fetchAll("SELECT * FROM classes ORDER BY numeric_name");
    
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/students/create.php';
    include ROOT . '/views/layouts/footer.php';
}

// ===================================================================
// STORE STUDENT
// ===================================================================
elseif ($action === 'store') {
    requireRole(['super_admin', 'admin']);
    
    if (!isPost() || !verify_csrf()) {
        flash('error', 'Invalid request');
        redirect('/students');
    }
    
    try {
        Database::beginTransaction();
        
        // Generate admission number
        $year = date('Y');
        $lastStudent = Database::fetchOne(
            "SELECT id FROM students WHERE YEAR(admission_date) = ? ORDER BY id DESC LIMIT 1",
            [$year]
        );
        $admissionNo = generateAdmissionNo($year, $lastStudent['id'] ?? 0);
        
        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $validation = validateUpload($_FILES['photo'], 2097152, ['image/jpeg', 'image/png']);
            if ($validation['valid']) {
                $photoPath = 'students/' . $validation['filename'];
                move_uploaded_file($_FILES['photo']['tmp_name'], ROOT . '/uploads/' . $photoPath);
            }
        }
        
        // Insert student
        $studentId = Database::insert('students', [
            'academic_year_id' => (int)$_POST['academic_year_id'],
            'admission_no' => $admissionNo,
            'roll_no' => sanitize($_POST['roll_no']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'gender' => $_POST['gender'],
            'blood_group' => sanitize($_POST['blood_group'] ?? ''),
            'religion' => sanitize($_POST['religion'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'photo' => $photoPath,
            'present_address' => sanitize($_POST['present_address'] ?? ''),
            'permanent_address' => sanitize($_POST['permanent_address'] ?? ''),
            'father_name' => sanitize($_POST['father_name'] ?? ''),
            'father_phone' => sanitize($_POST['father_phone'] ?? ''),
            'father_occupation' => sanitize($_POST['father_occupation'] ?? ''),
            'mother_name' => sanitize($_POST['mother_name'] ?? ''),
            'mother_phone' => sanitize($_POST['mother_phone'] ?? ''),
            'mother_occupation' => sanitize($_POST['mother_occupation'] ?? ''),
            'guardian_name' => sanitize($_POST['guardian_name'] ?? ''),
            'guardian_phone' => sanitize($_POST['guardian_phone'] ?? ''),
            'guardian_relation' => sanitize($_POST['guardian_relation'] ?? ''),
            'class_id' => (int)$_POST['class_id'],
            'section_id' => (int)$_POST['section_id'],
            'status' => 'active',
            'admission_date' => $_POST['admission_date'] ?? date('Y-m-d'),
        ]);
        
        // Log activity
        Database::insert('activity_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'create',
            'module' => 'students',
            'record_id' => $studentId,
            'description' => "Added new student: $admissionNo",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        
        Database::commit();
        
        flash('success', "Student admitted successfully! Admission No: $admissionNo");
        redirect('/students/' . $studentId);
        
    } catch (Exception $e) {
        Database::rollback();
        error_log('Student creation error: ' . $e->getMessage());
        flash('error', 'Failed to add student. Please try again.');
        redirect('/students/create');
    }
}

// ===================================================================
// SHOW STUDENT DETAILS
// ===================================================================
elseif ($action === 'show') {
    requireRole(['super_admin', 'admin', 'teacher']);
    
    $id = (int)$_GET['id'];
    
    $student = Database::fetchOne(
        "SELECT s.*, c.name as class_name, sec.name as section_name, ay.name as academic_year,
                CONCAT(s.first_name, ' ', s.last_name) as full_name,
                TIMESTAMPDIFF(YEAR, s.date_of_birth, CURDATE()) as age
         FROM students s
         LEFT JOIN classes c ON s.class_id = c.id
         LEFT JOIN sections sec ON s.section_id = sec.id
         LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
         WHERE s.id = ?",
        [$id]
    );
    
    if (!$student) {
        flash('error', 'Student not found');
        redirect('/students');
    }
    
    // Fetch additional data
    $attendanceStats = Database::fetchOne(
        "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
         FROM attendance WHERE student_id = ? AND YEAR(date) = YEAR(CURDATE())",
        [$id]
    );
    
    $feeStatus = Database::fetchAll(
        "SELECT i.*, ft.name as fee_type
         FROM invoices i
         JOIN fee_types ft ON i.fee_type_id = ft.id
         WHERE i.student_id = ? AND i.status != 'paid'
         ORDER BY i.due_date",
        [$id]
    );
    
    $recentExams = Database::fetchAll(
        "SELECT e.name as exam_name, em.marks_obtained, em.full_marks, em.grade, em.gpa
         FROM exam_marks em
         JOIN exams e ON em.exam_id = e.id
         WHERE em.student_id = ?
         ORDER BY e.id DESC LIMIT 5",
        [$id]
    );
    
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/students/show.php';
    include ROOT . '/views/layouts/footer.php';
}

// ===================================================================
// SHOW EDIT FORM
// ===================================================================
elseif ($action === 'edit') {
    requireRole(['super_admin', 'admin']);
    
    $id = (int)$_GET['id'];
    
    $student = Database::fetchOne("SELECT * FROM students WHERE id = ?", [$id]);
    
    if (!$student) {
        flash('error', 'Student not found');
        redirect('/students');
    }
    
    $academicYears = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");
    $classes = Database::fetchAll("SELECT * FROM classes ORDER BY numeric_name");
    $sections = Database::fetchAll("SELECT * FROM sections WHERE class_id = ?", [$student['class_id']]);
    
    include ROOT . '/views/layouts/header.php';
    include ROOT . '/views/students/edit.php';
    include ROOT . '/views/layouts/footer.php';
}

// ===================================================================
// UPDATE STUDENT
// ===================================================================
elseif ($action === 'update') {
    requireRole(['super_admin', 'admin']);
    
    if (!isPost() || !verify_csrf()) {
        flash('error', 'Invalid request');
        redirect('/students');
    }
    
    $id = (int)$_GET['id'];
    
    try {
        Database::beginTransaction();
        
        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $validation = validateUpload($_FILES['photo'], 2097152, ['image/jpeg', 'image/png']);
            if ($validation['valid']) {
                // Delete old photo
                $oldStudent = Database::fetchOne("SELECT photo FROM students WHERE id = ?", [$id]);
                if ($oldStudent && $oldStudent['photo']) {
                    @unlink(ROOT . '/uploads/' . $oldStudent['photo']);
                }
                
                $photoPath = 'students/' . $validation['filename'];
                move_uploaded_file($_FILES['photo']['tmp_name'], ROOT . '/uploads/' . $photoPath);
            }
        }
        
        // Build update data
        $updateData = [
            'roll_no' => sanitize($_POST['roll_no']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'gender' => $_POST['gender'],
            'blood_group' => sanitize($_POST['blood_group'] ?? ''),
            'religion' => sanitize($_POST['religion'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'present_address' => sanitize($_POST['present_address'] ?? ''),
            'permanent_address' => sanitize($_POST['permanent_address'] ?? ''),
            'father_name' => sanitize($_POST['father_name'] ?? ''),
            'father_phone' => sanitize($_POST['father_phone'] ?? ''),
            'father_occupation' => sanitize($_POST['father_occupation'] ?? ''),
            'mother_name' => sanitize($_POST['mother_name'] ?? ''),
            'mother_phone' => sanitize($_POST['mother_phone'] ?? ''),
            'mother_occupation' => sanitize($_POST['mother_occupation'] ?? ''),
            'guardian_name' => sanitize($_POST['guardian_name'] ?? ''),
            'guardian_phone' => sanitize($_POST['guardian_phone'] ?? ''),
            'guardian_relation' => sanitize($_POST['guardian_relation'] ?? ''),
            'class_id' => (int)$_POST['class_id'],
            'section_id' => (int)$_POST['section_id'],
            'status' => $_POST['status'],
        ];
        
        if ($photoPath) {
            $updateData['photo'] = $photoPath;
        }
        
        Database::update('students', $updateData, 'id = ?', [$id]);
        
        // Log activity
        Database::insert('activity_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'update',
            'module' => 'students',
            'record_id' => $id,
            'description' => "Updated student information",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        
        Database::commit();
        
        flash('success', 'Student information updated successfully!');
        redirect('/students/' . $id);
        
    } catch (Exception $e) {
        Database::rollback();
        error_log('Student update error: ' . $e->getMessage());
        flash('error', 'Failed to update student. Please try again.');
        redirect('/students/' . $id . '/edit');
    }
}

// Invalid action
else {
    http_response_code(404);
    echo 'Invalid action';
}
