<?php
/**
 * Payment Controller
 * Handles payment collection, gateway integration, and receipts
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class PaymentController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
    }

    /**
     * Display payment history
     */
    public function index() {
        requireRole(['super_admin', 'admin', 'accountant', 'student', 'parent']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters
        $method = $_GET['method'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        // If student/parent, show only their payments
        if ($_SESSION['role'] === 'student') {
            $where[] = 's.id = :student_id';
            $params[':student_id'] = $_SESSION['student_id'];
        }
        
        if ($method) {
            $where[] = 'p.payment_method = :method';
            $params[':method'] = $method;
        }
        
        if ($status) {
            $where[] = 'p.status = :status';
            $params[':status'] = $status;
        }
        
        if ($dateFrom) {
            $where[] = 'p.payment_date >= :date_from';
            $params[':date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $where[] = 'p.payment_date <= :date_to';
            $params[':date_to'] = $dateTo;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get payments with invoice and student info
        $sql = "SELECT p.*, 
                       fi.invoice_number, fi.total_amount AS invoice_total,
                       s.first_name, s.last_name, s.admission_number,
                       c.name AS class_name,
                       u.username AS collected_by_name
                FROM payments p
                LEFT JOIN fee_invoices fi ON p.invoice_id = fi.id
                LEFT JOIN students s ON fi.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON p.collected_by = u.id
                WHERE $whereClause
                ORDER BY p.payment_date DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM payments p
                     LEFT JOIN fee_invoices fi ON p.invoice_id = fi.id
                     LEFT JOIN students s ON fi.student_id = s.id
                     WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Get payment statistics
        $stats = $this->getPaymentStatistics();
        
        // Load view
        require_once __DIR__ . '/../views/payments/index.php';
    }

    /**
     * Show payment form
     */
    public function create() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        $invoiceId = $_GET['invoice_id'] ?? null;
        
        if ($invoiceId) {
            // Get invoice details
            $sql = "SELECT fi.*, 
                           s.first_name, s.last_name, s.admission_number,
                           c.name AS class_name, sec.name AS section_name
                    FROM fee_invoices fi
                    JOIN students s ON fi.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE fi.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $invoiceId]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                redirect('/fees', 'error', 'Invoice not found');
                return;
            }
        } else {
            $invoice = null;
        }
        
        require_once __DIR__ . '/../views/payments/create.php';
    }

    /**
     * Process payment
     */
    public function store() {
        requireRole(['super_admin', 'admin', 'accountant']);
        
        if (!verify_csrf()) {
            redirect('/payments', 'error', 'Invalid security token');
            return;
        }
        
        $invoiceId = $_POST['invoice_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $transactionId = $_POST['transaction_id'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$invoiceId || $amount <= 0) {
            redirect('/payments/create', 'error', 'Invalid payment data');
            return;
        }
        
        try {
            // Get invoice
            $invoice = $this->db->query("SELECT * FROM fee_invoices WHERE id = $invoiceId")->fetch(PDO::FETCH_ASSOC);
            
            if (!$invoice) {
                redirect('/payments/create', 'error', 'Invoice not found');
                return;
            }
            
            // Check if amount exceeds pending amount
            $pendingAmount = $invoice['total_amount'] - $invoice['paid_amount'];
            if ($amount > $pendingAmount) {
                redirect('/payments/create?invoice_id=' . $invoiceId, 'error', 'Payment amount exceeds pending amount');
                return;
            }
            
            $this->db->beginTransaction();
            
            // Generate receipt number
            $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert payment
            $insertSql = "INSERT INTO payments 
                          (receipt_number, invoice_id, amount, payment_method, transaction_id, 
                           payment_date, collected_by, status, notes)
                          VALUES 
                          (:receipt_number, :invoice_id, :amount, :payment_method, :transaction_id,
                           :payment_date, :collected_by, 'completed', :notes)";
            
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute([
                ':receipt_number' => $receiptNumber,
                ':invoice_id' => $invoiceId,
                ':amount' => $amount,
                ':payment_method' => $paymentMethod,
                ':transaction_id' => $transactionId,
                ':payment_date' => date('Y-m-d H:i:s'),
                ':collected_by' => $_SESSION['user_id'],
                ':notes' => $notes
            ]);
            
            $paymentId = $this->db->lastInsertId();
            
            // Update invoice paid amount and status
            $newPaidAmount = $invoice['paid_amount'] + $amount;
            $newStatus = ($newPaidAmount >= $invoice['total_amount']) ? 'paid' : 'partial';
            
            $updateSql = "UPDATE fee_invoices 
                          SET paid_amount = :paid_amount, 
                              status = :status 
                          WHERE id = :id";
            
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                ':paid_amount' => $newPaidAmount,
                ':status' => $newStatus,
                ':id' => $invoiceId
            ]);
            
            // Log activity
            logActivity($_SESSION['user_id'], 'payment_created', "Payment {$receiptNumber} of ৳{$amount} created for invoice {$invoice['invoice_number']}");
            
            $this->db->commit();
            
            redirect('/payments/' . $paymentId, 'success', 'Payment recorded successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            redirect('/payments/create?invoice_id=' . $invoiceId, 'error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Show payment receipt
     */
    public function show() {
        requireRole(['super_admin', 'admin', 'accountant', 'student', 'parent']);
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            redirect('/payments', 'error', 'Payment not found');
            return;
        }
        
        // Get payment with details
        $sql = "SELECT p.*, 
                       fi.invoice_number, fi.total_amount AS invoice_total, fi.amount AS invoice_amount,
                       s.first_name, s.last_name, s.admission_number, s.roll_number,
                       c.name AS class_name, sec.name AS section_name,
                       u.username AS collected_by_name,
                       par.first_name AS parent_first_name, par.last_name AS parent_last_name
                FROM payments p
                LEFT JOIN fee_invoices fi ON p.invoice_id = fi.id
                LEFT JOIN students s ON fi.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN users u ON p.collected_by = u.id
                LEFT JOIN parents par ON s.parent_id = par.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            redirect('/payments', 'error', 'Payment not found');
            return;
        }
        
        require_once __DIR__ . '/../views/payments/show.php';
    }

    /**
     * Get payment statistics
     */
    private function getPaymentStatistics() {
        $stats = [
            'today_total' => 0,
            'today_count' => 0,
            'week_total' => 0,
            'month_total' => 0,
            'total_collected' => 0
        ];
        
        // Today's payments
        $todaySql = "SELECT COUNT(*) AS count, COALESCE(SUM(amount), 0) AS total 
                     FROM payments 
                     WHERE DATE(payment_date) = CURDATE() AND status = 'completed'";
        $today = $this->db->query($todaySql)->fetch(PDO::FETCH_ASSOC);
        $stats['today_total'] = $today['total'];
        $stats['today_count'] = $today['count'];
        
        // This week's payments
        $weekSql = "SELECT COALESCE(SUM(amount), 0) AS total 
                    FROM payments 
                    WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'completed'";
        $stats['week_total'] = $this->db->query($weekSql)->fetchColumn();
        
        // This month's payments
        $monthSql = "SELECT COALESCE(SUM(amount), 0) AS total 
                     FROM payments 
                     WHERE YEAR(payment_date) = YEAR(CURDATE()) 
                     AND MONTH(payment_date) = MONTH(CURDATE()) 
                     AND status = 'completed'";
        $stats['month_total'] = $this->db->query($monthSql)->fetchColumn();
        
        // Total collected
        $totalSql = "SELECT COALESCE(SUM(amount), 0) AS total 
                     FROM payments 
                     WHERE status = 'completed'";
        $stats['total_collected'] = $this->db->query($totalSql)->fetchColumn();
        
        return $stats;
    }
}
