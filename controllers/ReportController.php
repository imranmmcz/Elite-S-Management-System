<?php
/**
 * Report Controller
 * Handles all reporting features - student reports, financial reports, attendance reports, etc.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class ReportController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
    }

    /**
     * Display reports dashboard
     */
    public function index() {
        requireRole(['super_admin', 'admin', 'teacher', 'accountant']);
        
        $reportTypes = [
            'student' => [
                'title' => 'Student Reports',
                'description' => 'Student list, admission reports, student profiles',
                'icon' => 'fa-user-graduate',
                'url' => '/reports/students'
            ],
            'attendance' => [
                'title' => 'Attendance Reports',
                'description' => 'Daily, weekly, monthly attendance reports',
                'icon' => 'fa-clipboard-check',
                'url' => '/reports/attendance'
            ],
            'exam' => [
                'title' => 'Exam Reports',
                'description' => 'Mark sheets, result cards, grade reports',
                'icon' => 'fa-file-alt',
                'url' => '/reports/exams'
            ],
            'financial' => [
                'title' => 'Financial Reports',
                'description' => 'Fee collection, payment reports, due lists',
                'icon' => 'fa-money-bill-wave',
                'url' => '/reports/financial'
            ],
            'certificate' => [
                'title' => 'Certificates',
                'description' => 'Generate student certificates and documents',
                'icon' => 'fa-certificate',
                'url' => '/reports/certificates'
            ]
        ];
        
        require_once __DIR__ . '/../views/reports/index.php';
    }

    /**
     * Student reports
     */
    public function students() {
        requireRole(['super_admin', 'admin', 'teacher']);
        
        $reportType = $_GET['type'] ?? 'list';
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $status = $_GET['status'] ?? 'active';
        
        // Get classes for filter
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        $students = [];
        $title = 'Student List';
        
        if ($reportType === 'list' && $classId) {
            // Build query
            $where = ['s.status = :status'];
            $params = [':status' => $status];
            
            $where[] = 's.class_id = :class_id';
            $params[':class_id'] = $classId;
            
            if ($sectionId) {
                $where[] = 's.section_id = :section_id';
                $params[':section_id'] = $sectionId;
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT s.*, 
                           c.name AS class_name, 
                           sec.name AS section_name,
                           p.first_name AS parent_first_name, 
                           p.last_name AS parent_last_name, 
                           p.phone AS parent_phone
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    LEFT JOIN parents p ON s.parent_id = p.id
                    WHERE $whereClause
                    ORDER BY s.roll_number, s.first_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/../views/reports/students.php';
    }

    /**
     * Attendance reports
     */
    public function attendance() {
        requireRole(['super_admin', 'admin', 'teacher']);
        
        $reportType = $_GET['type'] ?? 'daily';
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Get classes for filter
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        $attendanceData = [];
        
        if ($classId) {
            if ($reportType === 'daily') {
                // Daily attendance report
                $sql = "SELECT a.*, 
                               s.first_name, s.last_name, s.admission_number, s.roll_number,
                               c.name AS class_name, sec.name AS section_name
                        FROM attendance a
                        JOIN students s ON a.student_id = s.id
                        LEFT JOIN classes c ON s.class_id = c.id
                        LEFT JOIN sections sec ON s.section_id = sec.id
                        WHERE s.class_id = :class_id
                        AND a.date BETWEEN :date_from AND :date_to
                        ORDER BY a.date DESC, s.roll_number";
                
                $stmt = $this->db->prepare($sql);
                $params = [
                    ':class_id' => $classId,
                    ':date_from' => $dateFrom,
                    ':date_to' => $dateTo
                ];
                
                if ($sectionId) {
                    $sql = str_replace('WHERE s.class_id', 'WHERE s.section_id = :section_id AND s.class_id', $sql);
                    $params[':section_id'] = $sectionId;
                }
                
                $stmt->execute($params);
                $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($reportType === 'summary') {
                // Summary report - attendance percentage per student
                $sql = "SELECT s.id, s.first_name, s.last_name, s.admission_number, s.roll_number,
                               c.name AS class_name, sec.name AS section_name,
                               COUNT(a.id) AS total_days,
                               SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_days,
                               SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_days,
                               SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_days,
                               ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) AS attendance_percentage
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        LEFT JOIN sections sec ON s.section_id = sec.id
                        LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN :date_from AND :date_to
                        WHERE s.class_id = :class_id
                        GROUP BY s.id
                        ORDER BY s.roll_number";
                
                $stmt = $this->db->prepare($sql);
                $params = [
                    ':class_id' => $classId,
                    ':date_from' => $dateFrom,
                    ':date_to' => $dateTo
                ];
                
                if ($sectionId) {
                    $sql = str_replace('WHERE s.class_id', 'WHERE s.section_id = :section_id AND s.class_id', $sql);
                    $params[':section_id'] = $sectionId;
                }
                
                $stmt->execute($params);
                $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
        require_once __DIR__ . '/../views/reports/attendance.php';
    }

    /**
     * Financial reports
     */
    public function financial() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        $reportType = $_GET['type'] ?? 'collection';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $classId = $_GET['class_id'] ?? '';
        
        // Get classes for filter
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        $reportData = [];
        
        if ($reportType === 'collection') {
            // Fee collection report
            $sql = "SELECT p.payment_date, p.receipt_number, p.amount, p.payment_method,
                           fi.invoice_number,
                           s.first_name, s.last_name, s.admission_number,
                           c.name AS class_name,
                           u.username AS collected_by
                    FROM payments p
                    LEFT JOIN fee_invoices fi ON p.invoice_id = fi.id
                    LEFT JOIN students s ON fi.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN users u ON p.collected_by = u.id
                    WHERE p.payment_date BETWEEN :date_from AND :date_to
                    AND p.status = 'completed'
                    ORDER BY p.payment_date DESC";
            
            $params = [
                ':date_from' => $dateFrom . ' 00:00:00',
                ':date_to' => $dateTo . ' 23:59:59'
            ];
            
            if ($classId) {
                $sql = str_replace('WHERE p.payment_date', 'WHERE s.class_id = :class_id AND p.payment_date', $sql);
                $params[':class_id'] = $classId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totals = [
                'cash' => 0,
                'bank' => 0,
                'online' => 0,
                'total' => 0
            ];
            
            foreach ($reportData as $row) {
                $totals['total'] += $row['amount'];
                if (in_array($row['payment_method'], ['cash'])) {
                    $totals['cash'] += $row['amount'];
                } elseif (in_array($row['payment_method'], ['bank_transfer', 'cheque'])) {
                    $totals['bank'] += $row['amount'];
                } else {
                    $totals['online'] += $row['amount'];
                }
            }
            
        } elseif ($reportType === 'due') {
            // Due fee report
            $sql = "SELECT fi.*, 
                           s.first_name, s.last_name, s.admission_number, s.roll_number,
                           c.name AS class_name, sec.name AS section_name,
                           p.phone AS parent_phone,
                           (fi.total_amount - fi.paid_amount) AS due_amount
                    FROM fee_invoices fi
                    JOIN students s ON fi.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    LEFT JOIN parents p ON s.parent_id = p.id
                    WHERE fi.status IN ('pending', 'partial', 'overdue')
                    AND (fi.total_amount - fi.paid_amount) > 0
                    ORDER BY fi.due_date ASC";
            
            $params = [];
            
            if ($classId) {
                $sql = str_replace('WHERE fi.status', 'WHERE s.class_id = :class_id AND fi.status', $sql);
                $params[':class_id'] = $classId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total due
            $totals = [
                'total_due' => array_sum(array_column($reportData, 'due_amount')),
                'count' => count($reportData)
            ];
        }
        
        require_once __DIR__ . '/../views/reports/financial.php';
    }

    /**
     * Exam reports
     */
    public function exams() {
        requireRole(['super_admin', 'admin', 'teacher']);
        
        $examId = $_GET['exam_id'] ?? '';
        $classId = $_GET['class_id'] ?? '';
        
        // Get exams and classes for filter
        $exams = $this->db->query("SELECT * FROM exams ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        $reportData = [];
        
        if ($examId && $classId) {
            // Get exam results
            $sql = "SELECT s.id, s.first_name, s.last_name, s.admission_number, s.roll_number,
                           c.name AS class_name, sec.name AS section_name
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.class_id = :class_id AND s.status = 'active'
                    ORDER BY s.roll_number";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':class_id' => $classId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get subjects for this class
            $subjectsSql = "SELECT DISTINCT sub.id, sub.name, sub.full_marks, sub.pass_marks
                            FROM subjects sub
                            WHERE sub.class_id = :class_id
                            ORDER BY sub.name";
            $subjectsStmt = $this->db->prepare($subjectsSql);
            $subjectsStmt->execute([':class_id' => $classId]);
            $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get marks for each student
            foreach ($students as &$student) {
                $student['subjects'] = [];
                $student['total_marks'] = 0;
                $student['obtained_marks'] = 0;
                
                foreach ($subjects as $subject) {
                    $marksSql = "SELECT marks_obtained, grade, grade_point 
                                 FROM exam_results 
                                 WHERE exam_id = :exam_id 
                                 AND student_id = :student_id 
                                 AND subject_id = :subject_id";
                    $marksStmt = $this->db->prepare($marksSql);
                    $marksStmt->execute([
                        ':exam_id' => $examId,
                        ':student_id' => $student['id'],
                        ':subject_id' => $subject['id']
                    ]);
                    $marks = $marksStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $student['subjects'][$subject['id']] = $marks ?: ['marks_obtained' => '-', 'grade' => '-', 'grade_point' => 0];
                    
                    if ($marks) {
                        $student['total_marks'] += $subject['full_marks'];
                        $student['obtained_marks'] += $marks['marks_obtained'];
                    }
                }
                
                // Calculate GPA (placeholder - needs proper calculation)
                $student['gpa'] = $student['total_marks'] > 0 ? 
                    round(($student['obtained_marks'] / $student['total_marks']) * 5, 2) : 0;
            }
            
            $reportData = [
                'students' => $students,
                'subjects' => $subjects
            ];
        }
        
        require_once __DIR__ . '/../views/reports/exams.php';
    }
}
