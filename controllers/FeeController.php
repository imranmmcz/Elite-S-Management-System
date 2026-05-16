<?php
/**
 * Fee Controller
 * Handles fee structure management, invoice generation, and fee collection
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class FeeController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
    }

    /**
     * Display fee dashboard with invoices list
     */
    public function index() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters
        $classId = $_GET['class_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $month = $_GET['month'] ?? '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($classId) {
            $where[] = 's.class_id = :class_id';
            $params[':class_id'] = $classId;
        }
        
        if ($status) {
            $where[] = 'fi.status = :status';
            $params[':status'] = $status;
        }
        
        if ($month) {
            $where[] = 'DATE_FORMAT(fi.invoice_date, "%Y-%m") = :month';
            $params[':month'] = $month;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get invoices with student info
        $sql = "SELECT fi.*, 
                       s.first_name, s.last_name, s.admission_number, s.roll_number,
                       c.name AS class_name, sec.name AS section_name,
                       fs.name AS fee_structure_name
                FROM fee_invoices fi
                JOIN students s ON fi.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN fee_structures fs ON fi.fee_structure_id = fs.id
                WHERE $whereClause
                ORDER BY fi.invoice_date DESC, fi.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM fee_invoices fi
                     JOIN students s ON fi.student_id = s.id
                     WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Get statistics
        $stats = $this->getFeeStatistics();
        
        // Get all classes for filter
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        // Load view
        require_once __DIR__ . '/../views/fees/index.php';
    }

    /**
     * Display fee structures management
     */
    public function structures() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        // Get all fee structures
        $sql = "SELECT fs.*, 
                       c.name AS class_name,
                       COUNT(DISTINCT fi.id) AS total_invoices,
                       SUM(CASE WHEN fi.status = 'paid' THEN fi.total_amount ELSE 0 END) AS total_collected
                FROM fee_structures fs
                LEFT JOIN classes c ON fs.class_id = c.id
                LEFT JOIN fee_invoices fi ON fs.id = fi.fee_structure_id
                GROUP BY fs.id
                ORDER BY fs.created_at DESC";
        
        $structures = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all classes
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/fees/structures.php';
    }

    /**
     * Show invoice details
     */
    public function show() {
        requireRole(['super_admin', 'admin', 'accountant', 'student', 'parent']);
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('/fees', 'error', 'Invoice not found');
            return;
        }
        
        // Get invoice with details
        $sql = "SELECT fi.*, 
                       s.first_name, s.last_name, s.admission_number, s.roll_number, s.photo,
                       c.name AS class_name, sec.name AS section_name,
                       fs.name AS fee_structure_name,
                       p.first_name AS parent_first_name, p.last_name AS parent_last_name, p.phone AS parent_phone
                FROM fee_invoices fi
                JOIN students s ON fi.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN fee_structures fs ON fi.fee_structure_id = fs.id
                LEFT JOIN parents p ON s.parent_id = p.id
                WHERE fi.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$invoice) {
            redirect('/fees', 'error', 'Invoice not found');
            return;
        }
        
        // Check permission - students/parents can only view their own invoices
        if (in_array($_SESSION['role'], ['student', 'parent'])) {
            if ($_SESSION['role'] === 'student' && $invoice['student_id'] != $_SESSION['student_id']) {
                redirect('/fees', 'error', 'Access denied');
                return;
            }
            // Add parent permission check here if needed
        }
        
        // Get payment history
        $paymentsSql = "SELECT * FROM payments 
                        WHERE invoice_id = :invoice_id 
                        ORDER BY payment_date DESC";
        $paymentsStmt = $this->db->prepare($paymentsSql);
        $paymentsStmt->execute([':invoice_id' => $id]);
        $payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/fees/show.php';
    }

    /**
     * Generate invoices for a class
     */
    public function generate() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/fees/structures', 'error', 'Invalid security token');
                return;
            }
            
            $classId = $_POST['class_id'] ?? null;
            $feeStructureId = $_POST['fee_structure_id'] ?? null;
            $month = $_POST['month'] ?? date('Y-m');
            
            if (!$classId || !$feeStructureId) {
                redirect('/fees/structures', 'error', 'Missing required fields');
                return;
            }
            
            try {
                // Get fee structure
                $feeStructure = $this->db->query("SELECT * FROM fee_structures WHERE id = $feeStructureId")->fetch(PDO::FETCH_ASSOC);
                
                if (!$feeStructure) {
                    redirect('/fees/structures', 'error', 'Fee structure not found');
                    return;
                }
                
                // Get all active students in the class
                $students = $this->db->query("SELECT * FROM students WHERE class_id = $classId AND status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($students)) {
                    redirect('/fees/structures', 'error', 'No active students found in this class');
                    return;
                }
                
                $this->db->beginTransaction();
                
                $generatedCount = 0;
                $dueDate = date('Y-m-d', strtotime("+{$feeStructure['due_days']} days"));
                
                foreach ($students as $student) {
                    // Check if invoice already exists for this month
                    $checkSql = "SELECT id FROM fee_invoices 
                                 WHERE student_id = :student_id 
                                 AND fee_structure_id = :fee_structure_id 
                                 AND DATE_FORMAT(invoice_date, '%Y-%m') = :month";
                    $checkStmt = $this->db->prepare($checkSql);
                    $checkStmt->execute([
                        ':student_id' => $student['id'],
                        ':fee_structure_id' => $feeStructureId,
                        ':month' => $month
                    ]);
                    
                    if ($checkStmt->fetch()) {
                        continue; // Skip if already exists
                    }
                    
                    // Create invoice
                    $invoiceNumber = 'INV-' . date('Ym') . '-' . str_pad($student['id'], 4, '0', STR_PAD_LEFT);
                    
                    $insertSql = "INSERT INTO fee_invoices 
                                  (invoice_number, student_id, fee_structure_id, invoice_date, due_date, 
                                   amount, discount, late_fee, total_amount, paid_amount, status)
                                  VALUES 
                                  (:invoice_number, :student_id, :fee_structure_id, :invoice_date, :due_date,
                                   :amount, 0, 0, :amount, 0, 'pending')";
                    
                    $insertStmt = $this->db->prepare($insertSql);
                    $insertStmt->execute([
                        ':invoice_number' => $invoiceNumber,
                        ':student_id' => $student['id'],
                        ':fee_structure_id' => $feeStructureId,
                        ':invoice_date' => date('Y-m-01', strtotime($month)),
                        ':due_date' => $dueDate,
                        ':amount' => $feeStructure['amount']
                    ]);
                    
                    $generatedCount++;
                }
                
                $this->db->commit();
                
                redirect('/fees', 'success', "Successfully generated $generatedCount invoices");
                
            } catch (Exception $e) {
                $this->db->rollBack();
                redirect('/fees/structures', 'error', 'Failed to generate invoices: ' . $e->getMessage());
            }
            
            return;
        }
        
        // GET request - show form
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $feeStructures = $this->db->query("SELECT * FROM fee_structures ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/fees/generate.php';
    }

    /**
     * Get fee statistics for dashboard
     */
    private function getFeeStatistics() {
        $stats = [
            'total_pending' => 0,
            'total_paid' => 0,
            'total_overdue' => 0,
            'total_partial' => 0,
            'pending_amount' => 0,
            'paid_amount' => 0,
            'overdue_amount' => 0
        ];
        
        // Get counts and amounts by status
        $sql = "SELECT 
                    status,
                    COUNT(*) AS count,
                    SUM(total_amount - paid_amount) AS pending_amount,
                    SUM(paid_amount) AS paid_amount
                FROM fee_invoices
                GROUP BY status";
        
        $result = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $row) {
            switch ($row['status']) {
                case 'pending':
                    $stats['total_pending'] = $row['count'];
                    $stats['pending_amount'] += $row['pending_amount'];
                    break;
                case 'paid':
                    $stats['total_paid'] = $row['count'];
                    $stats['paid_amount'] += $row['paid_amount'];
                    break;
                case 'partial':
                    $stats['total_partial'] = $row['count'];
                    $stats['pending_amount'] += $row['pending_amount'];
                    break;
                case 'overdue':
                    $stats['total_overdue'] = $row['count'];
                    $stats['overdue_amount'] += $row['pending_amount'];
                    break;
            }
        }
        
        return $stats;
    }
}
