<?php
/**
 * Library Controller
 * Handles library management - books, issue/return, fines
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class LibraryController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
    }

    /**
     * Display library dashboard
     */
    public function index() {
        requireRole(['super_admin', 'admin', 'librarian', 'teacher', 'student']);
        
        // Get library statistics
        $stats = $this->getLibraryStatistics();
        
        // Get recent issued books
        $recentIssues = [];
        if (in_array($_SESSION['role'], ['super_admin', 'admin', 'librarian'])) {
            $sql = "SELECT bi.*, 
                           b.title AS book_title, b.isbn,
                           s.first_name, s.last_name, s.admission_number,
                           c.name AS class_name
                    FROM book_issues bi
                    JOIN books b ON bi.book_id = b.id
                    LEFT JOIN students s ON bi.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE bi.status = 'issued'
                    ORDER BY bi.issue_date DESC
                    LIMIT 10";
            $recentIssues = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($_SESSION['role'] === 'student') {
            // Show student's own issued books
            $sql = "SELECT bi.*, 
                           b.title AS book_title, b.isbn, b.author
                    FROM book_issues bi
                    JOIN books b ON bi.book_id = b.id
                    WHERE bi.student_id = :student_id
                    ORDER BY bi.issue_date DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':student_id' => $_SESSION['student_id']]);
            $recentIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/../views/library/index.php';
    }

    /**
     * Display books list
     */
    public function books() {
        requireRole(['super_admin', 'admin', 'librarian', 'teacher', 'student']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $availability = $_GET['availability'] ?? '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($search) {
            $where[] = '(b.title LIKE :search OR b.author LIKE :search OR b.isbn LIKE :search)';
            $params[':search'] = "%$search%";
        }
        
        if ($category) {
            $where[] = 'b.category = :category';
            $params[':category'] = $category;
        }
        
        if ($availability === 'available') {
            $where[] = 'b.quantity > b.issued_quantity';
        } elseif ($availability === 'unavailable') {
            $where[] = 'b.quantity <= b.issued_quantity';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get books
        $sql = "SELECT b.*, 
                       (b.quantity - b.issued_quantity) AS available_quantity
                FROM books b
                WHERE $whereClause
                ORDER BY b.title
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) FROM books b WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Get categories for filter
        $categories = $this->db->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
        
        require_once __DIR__ . '/../views/library/books.php';
    }

    /**
     * Issue a book
     */
    public function issue() {
        requireRole(['super_admin', 'admin', 'librarian']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/library', 'error', 'Invalid security token');
                return;
            }
            
            $bookId = $_POST['book_id'] ?? null;
            $studentId = $_POST['student_id'] ?? null;
            $issueDate = $_POST['issue_date'] ?? date('Y-m-d');
            $returnDate = $_POST['return_date'] ?? date('Y-m-d', strtotime('+14 days'));
            
            if (!$bookId || !$studentId) {
                redirect('/library', 'error', 'Missing required fields');
                return;
            }
            
            try {
                // Check book availability
                $book = $this->db->query("SELECT * FROM books WHERE id = $bookId")->fetch(PDO::FETCH_ASSOC);
                
                if (!$book) {
                    redirect('/library', 'error', 'Book not found');
                    return;
                }
                
                if ($book['quantity'] <= $book['issued_quantity']) {
                    redirect('/library', 'error', 'Book is not available');
                    return;
                }
                
                // Check if student already has this book
                $checkSql = "SELECT id FROM book_issues 
                             WHERE book_id = :book_id 
                             AND student_id = :student_id 
                             AND status = 'issued'";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute([':book_id' => $bookId, ':student_id' => $studentId]);
                
                if ($checkStmt->fetch()) {
                    redirect('/library', 'error', 'Student already has this book');
                    return;
                }
                
                $this->db->beginTransaction();
                
                // Insert book issue
                $insertSql = "INSERT INTO book_issues 
                              (book_id, student_id, issue_date, expected_return_date, issued_by, status)
                              VALUES 
                              (:book_id, :student_id, :issue_date, :return_date, :issued_by, 'issued')";
                
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute([
                    ':book_id' => $bookId,
                    ':student_id' => $studentId,
                    ':issue_date' => $issueDate,
                    ':return_date' => $returnDate,
                    ':issued_by' => $_SESSION['user_id']
                ]);
                
                // Update book issued quantity
                $this->db->exec("UPDATE books SET issued_quantity = issued_quantity + 1 WHERE id = $bookId");
                
                // Log activity
                logActivity($_SESSION['user_id'], 'book_issued', "Book '{$book['title']}' issued to student ID: $studentId");
                
                $this->db->commit();
                
                redirect('/library', 'success', 'Book issued successfully');
                
            } catch (Exception $e) {
                $this->db->rollBack();
                redirect('/library', 'error', 'Failed to issue book: ' . $e->getMessage());
            }
            
            return;
        }
        
        // GET request - show form
        $books = $this->db->query("SELECT * FROM books WHERE quantity > issued_quantity ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
        $students = $this->db->query("SELECT id, first_name, last_name, admission_number FROM students WHERE status = 'active' ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/library/issue.php';
    }

    /**
     * Return a book
     */
    public function return() {
        requireRole(['super_admin', 'admin', 'librarian']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/library', 'error', 'Invalid security token');
                return;
            }
            
            $issueId = $_POST['issue_id'] ?? null;
            $returnDate = $_POST['return_date'] ?? date('Y-m-d');
            $condition = $_POST['condition'] ?? 'good';
            
            if (!$issueId) {
                redirect('/library', 'error', 'Issue ID not provided');
                return;
            }
            
            try {
                // Get issue details
                $issue = $this->db->query("SELECT * FROM book_issues WHERE id = $issueId AND status = 'issued'")->fetch(PDO::FETCH_ASSOC);
                
                if (!$issue) {
                    redirect('/library', 'error', 'Issue record not found or already returned');
                    return;
                }
                
                $this->db->beginTransaction();
                
                // Calculate fine if overdue
                $fine = 0;
                if ($returnDate > $issue['expected_return_date']) {
                    $daysOverdue = (strtotime($returnDate) - strtotime($issue['expected_return_date'])) / (60 * 60 * 24);
                    $fine = $daysOverdue * 5; // 5 BDT per day
                }
                
                // Update issue record
                $updateSql = "UPDATE book_issues 
                              SET status = 'returned', 
                                  actual_return_date = :return_date, 
                                  fine = :fine,
                                  book_condition = :condition
                              WHERE id = :id";
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([
                    ':return_date' => $returnDate,
                    ':fine' => $fine,
                    ':condition' => $condition,
                    ':id' => $issueId
                ]);
                
                // Update book issued quantity
                $this->db->exec("UPDATE books SET issued_quantity = issued_quantity - 1 WHERE id = {$issue['book_id']}");
                
                // Log activity
                logActivity($_SESSION['user_id'], 'book_returned', "Book returned from issue ID: $issueId" . ($fine > 0 ? " with fine: ৳$fine" : ""));
                
                $this->db->commit();
                
                $message = 'Book returned successfully';
                if ($fine > 0) {
                    $message .= ". Fine: ৳" . number_format($fine, 2);
                }
                
                redirect('/library', 'success', $message);
                
            } catch (Exception $e) {
                $this->db->rollBack();
                redirect('/library', 'error', 'Failed to return book: ' . $e->getMessage());
            }
            
            return;
        }
        
        // GET request - show issued books
        $sql = "SELECT bi.*, 
                       b.title AS book_title, b.isbn,
                       s.first_name, s.last_name, s.admission_number,
                       DATEDIFF(CURDATE(), bi.expected_return_date) AS days_overdue
                FROM book_issues bi
                JOIN books b ON bi.book_id = b.id
                JOIN students s ON bi.student_id = s.id
                WHERE bi.status = 'issued'
                ORDER BY bi.expected_return_date ASC";
        
        $issuedBooks = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/library/return.php';
    }

    /**
     * Get library statistics
     */
    private function getLibraryStatistics() {
        $stats = [
            'total_books' => 0,
            'total_copies' => 0,
            'issued_books' => 0,
            'available_books' => 0,
            'overdue_books' => 0,
            'total_fines' => 0
        ];
        
        // Total books and copies
        $bookStats = $this->db->query("SELECT COUNT(*) AS total_books, 
                                               SUM(quantity) AS total_copies,
                                               SUM(issued_quantity) AS issued_books
                                        FROM books")->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_books'] = $bookStats['total_books'];
        $stats['total_copies'] = $bookStats['total_copies'];
        $stats['issued_books'] = $bookStats['issued_books'];
        $stats['available_books'] = $bookStats['total_copies'] - $bookStats['issued_books'];
        
        // Overdue books
        $stats['overdue_books'] = $this->db->query("SELECT COUNT(*) FROM book_issues 
                                                     WHERE status = 'issued' 
                                                     AND expected_return_date < CURDATE()")->fetchColumn();
        
        // Total fines
        $stats['total_fines'] = $this->db->query("SELECT COALESCE(SUM(fine), 0) FROM book_issues 
                                                   WHERE fine > 0")->fetchColumn();
        
        return $stats;
    }
}
