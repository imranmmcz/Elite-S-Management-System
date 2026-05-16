<?php
/**
 * Certificate Controller
 * Handles certificate generation - character certificates, transfer certificates, etc.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class CertificateController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
    }

    /**
     * Display certificates dashboard
     */
    public function index() {
        requireRole(['super_admin', 'admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($type) {
            $where[] = 'c.certificate_type = :type';
            $params[':type'] = $type;
        }
        
        if ($status) {
            $where[] = 'c.status = :status';
            $params[':status'] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get certificates with student info
        $sql = "SELECT c.*, 
                       s.first_name, s.last_name, s.admission_number, s.roll_number,
                       cl.name AS class_name,
                       u.username AS issued_by_name
                FROM certificates c
                JOIN students s ON c.student_id = s.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                LEFT JOIN users u ON c.issued_by = u.id
                WHERE $whereClause
                ORDER BY c.issue_date DESC, c.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM certificates c
                     JOIN students s ON c.student_id = s.id
                     WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Get statistics
        $stats = $this->getCertificateStatistics();
        
        require_once __DIR__ . '/../views/certificates/index.php';
    }

    /**
     * Show certificate generation form
     */
    public function create() {
        requireRole(['super_admin', 'admin']);
        
        $studentId = $_GET['student_id'] ?? null;
        $student = null;
        
        if ($studentId) {
            // Get student details
            $sql = "SELECT s.*, 
                           c.name AS class_name, 
                           sec.name AS section_name,
                           p.first_name AS parent_first_name, 
                           p.last_name AS parent_last_name
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    LEFT JOIN parents p ON s.parent_id = p.id
                    WHERE s.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Get all active students for dropdown
        $students = $this->db->query("SELECT id, first_name, last_name, admission_number, roll_number 
                                      FROM students 
                                      WHERE status = 'active' 
                                      ORDER BY first_name, last_name")->fetchAll(PDO::FETCH_ASSOC);
        
        $certificateTypes = [
            'character' => 'Character Certificate',
            'transfer' => 'Transfer Certificate',
            'bonafide' => 'Bonafide Certificate',
            'conduct' => 'Conduct Certificate',
            'attendance' => 'Attendance Certificate',
            'completion' => 'Course Completion Certificate'
        ];
        
        require_once __DIR__ . '/../views/certificates/create.php';
    }

    /**
     * Generate certificate
     */
    public function store() {
        requireRole(['super_admin', 'admin']);
        
        if (!verify_csrf()) {
            redirect('/certificates', 'error', 'Invalid security token');
            return;
        }
        
        $studentId = $_POST['student_id'] ?? null;
        $certificateType = $_POST['certificate_type'] ?? null;
        $issueDate = $_POST['issue_date'] ?? date('Y-m-d');
        $purpose = $_POST['purpose'] ?? null;
        $content = $_POST['content'] ?? null;
        
        if (!$studentId || !$certificateType) {
            redirect('/certificates/create', 'error', 'Missing required fields');
            return;
        }
        
        try {
            // Get student details
            $student = $this->db->query("SELECT * FROM students WHERE id = $studentId")->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                redirect('/certificates/create', 'error', 'Student not found');
                return;
            }
            
            $this->db->beginTransaction();
            
            // Generate certificate number
            $certificateNumber = 'CERT-' . strtoupper(substr($certificateType, 0, 3)) . '-' . 
                                 date('Y') . '-' . str_pad($studentId, 4, '0', STR_PAD_LEFT) . '-' . 
                                 str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            // Insert certificate
            $insertSql = "INSERT INTO certificates 
                          (certificate_number, student_id, certificate_type, issue_date, 
                           purpose, content, issued_by, status)
                          VALUES 
                          (:certificate_number, :student_id, :certificate_type, :issue_date,
                           :purpose, :content, :issued_by, 'issued')";
            
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute([
                ':certificate_number' => $certificateNumber,
                ':student_id' => $studentId,
                ':certificate_type' => $certificateType,
                ':issue_date' => $issueDate,
                ':purpose' => $purpose,
                ':content' => $content,
                ':issued_by' => $_SESSION['user_id']
            ]);
            
            $certificateId = $this->db->lastInsertId();
            
            // Log activity
            logActivity($_SESSION['user_id'], 'certificate_issued', 
                       "Certificate {$certificateNumber} issued to {$student['first_name']} {$student['last_name']}");
            
            $this->db->commit();
            
            redirect('/certificates/' . $certificateId, 'success', 'Certificate generated successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            redirect('/certificates/create', 'error', 'Failed to generate certificate: ' . $e->getMessage());
        }
    }

    /**
     * Show certificate details and preview
     */
    public function show() {
        requireRole(['super_admin', 'admin', 'student', 'parent']);
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('/certificates', 'error', 'Certificate not found');
            return;
        }
        
        // Get certificate with details
        $sql = "SELECT c.*, 
                       s.first_name, s.last_name, s.admission_number, s.roll_number, 
                       s.date_of_birth, s.photo,
                       cl.name AS class_name, sec.name AS section_name,
                       p.first_name AS parent_first_name, p.last_name AS parent_last_name,
                       u.username AS issued_by_name
                FROM certificates c
                JOIN students s ON c.student_id = s.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN parents p ON s.parent_id = p.id
                LEFT JOIN users u ON c.issued_by = u.id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            redirect('/certificates', 'error', 'Certificate not found');
            return;
        }
        
        // Check permission - students/parents can only view their own certificates
        if (in_array($_SESSION['role'], ['student', 'parent'])) {
            if ($_SESSION['role'] === 'student' && $certificate['student_id'] != $_SESSION['student_id']) {
                redirect('/certificates', 'error', 'Access denied');
                return;
            }
        }
        
        require_once __DIR__ . '/../views/certificates/show.php';
    }

    /**
     * Download certificate as PDF
     */
    public function download() {
        requireRole(['super_admin', 'admin', 'student', 'parent']);
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('/certificates', 'error', 'Certificate not found');
            return;
        }
        
        // Get certificate details (same query as show method)
        $sql = "SELECT c.*, 
                       s.first_name, s.last_name, s.admission_number, s.roll_number, 
                       s.date_of_birth,
                       cl.name AS class_name, sec.name AS section_name,
                       p.first_name AS parent_first_name, p.last_name AS parent_last_name
                FROM certificates c
                JOIN students s ON c.student_id = s.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN parents p ON s.parent_id = p.id
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            redirect('/certificates', 'error', 'Certificate not found');
            return;
        }
        
        // TODO: Implement PDF generation using TCPDF
        // For now, show a placeholder message
        echo "PDF Download functionality will be implemented using TCPDF library.<br>";
        echo "Certificate Number: " . htmlspecialchars($certificate['certificate_number']);
        exit;
    }

    /**
     * Get certificate statistics
     */
    private function getCertificateStatistics() {
        $stats = [
            'total_issued' => 0,
            'this_month' => 0,
            'by_type' => []
        ];
        
        // Total issued
        $stats['total_issued'] = $this->db->query("SELECT COUNT(*) FROM certificates WHERE status = 'issued'")->fetchColumn();
        
        // This month
        $monthSql = "SELECT COUNT(*) FROM certificates 
                     WHERE YEAR(issue_date) = YEAR(CURDATE()) 
                     AND MONTH(issue_date) = MONTH(CURDATE())";
        $stats['this_month'] = $this->db->query($monthSql)->fetchColumn();
        
        // By type
        $typeSql = "SELECT certificate_type, COUNT(*) AS count 
                    FROM certificates 
                    GROUP BY certificate_type";
        $typeResult = $this->db->query($typeSql)->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($typeResult as $row) {
            $stats['by_type'][$row['certificate_type']] = $row['count'];
        }
        
        return $stats;
    }
}
