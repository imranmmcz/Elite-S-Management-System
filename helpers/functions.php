<?php
declare(strict_types=1);
/**
 * Elite School Management - Global Helper Functions
 * All utility functions for the application
 */

// ============ Security Functions ============

/**
 * Escape output for XSS protection
 */
function e(mixed $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF hidden input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    // Regenerate after use for extra security
    unset($_SESSION['csrf_token']);
    return true;
}

/**
 * Sanitize input string
 */
function sanitize(mixed $input): string {
    return trim(strip_tags((string)$input));
}

// ============ Routing & Navigation ============

/**
 * Redirect to URL
 */
function redirect(string $path, int $code = 302): never {
    $base = rtrim(APP_URL, '/');
    $url = str_starts_with($path, 'http') ? $path : $base . $path;
    header("Location: $url", true, $code);
    exit;
}

/**
 * Get current URL path
 */
function currentPath(): string {
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
}

/**
 * Check if current path matches
 */
function isActivePath(string $path): bool {
    return str_starts_with(currentPath(), $path);
}

// ============ Flash Messages ============

/**
 * Set flash message
 */
function flash(string $type, string $message): void {
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 */
function get_flashes(): array {
    $flashes = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $flashes;
}

// ============ Academic Calculations ============

/**
 * Calculate grade from marks (Bangladesh grading system)
 * @param float $marks Obtained marks
 * @param float $fullMarks Total marks
 * @return array ['grade', 'gpa', 'remark', 'color']
 */
function calculateGrade(float $marks, float $fullMarks = 100): array {
    if ($marks < 0 || $fullMarks <= 0) {
        return ['grade' => 'N/A', 'gpa' => 0.0, 'remark' => 'Invalid', 'color' => 'secondary'];
    }
    
    $pct = ($marks / $fullMarks) * 100;
    
    return match (true) {
        $pct >= 80 => ['grade' => 'A+', 'gpa' => 5.0, 'remark' => 'Outstanding',    'color' => 'success'],
        $pct >= 70 => ['grade' => 'A',  'gpa' => 4.0, 'remark' => 'Excellent',      'color' => 'success'],
        $pct >= 60 => ['grade' => 'A-', 'gpa' => 3.5, 'remark' => 'Very Good',      'color' => 'info'],
        $pct >= 50 => ['grade' => 'B',  'gpa' => 3.0, 'remark' => 'Good',           'color' => 'primary'],
        $pct >= 40 => ['grade' => 'C',  'gpa' => 2.0, 'remark' => 'Average',        'color' => 'warning'],
        $pct >= 33 => ['grade' => 'D',  'gpa' => 1.0, 'remark' => 'Below Average',  'color' => 'warning'],
        default    => ['grade' => 'F',  'gpa' => 0.0, 'remark' => 'Fail',           'color' => 'danger'],
    };
}

/**
 * Calculate overall GPA from multiple subjects
 * @param array $subjectResults Array of ['gpa' => float] for each subject
 * @return array ['gpa', 'grade', 'result', 'color']
 */
function calculateOverallGPA(array $subjectResults): array {
    if (empty($subjectResults)) {
        return ['gpa' => 0.0, 'grade' => 'N/A', 'result' => 'N/A', 'color' => 'secondary'];
    }
    
    $gpas = array_column($subjectResults, 'gpa');
    
    // If ANY subject is F (GPA 0.0) → overall FAIL
    if (in_array(0.0, $gpas, true)) {
        return ['gpa' => 0.0, 'grade' => 'F', 'result' => 'FAIL', 'color' => 'danger'];
    }
    
    // Calculate average GPA
    $avgGpa = round(array_sum($gpas) / count($gpas), 2);
    
    // Determine overall grade
    $gradeInfo = match (true) {
        $avgGpa >= 5.0 => ['grade' => 'A+', 'color' => 'success'],
        $avgGpa >= 4.0 => ['grade' => 'A',  'color' => 'success'],
        $avgGpa >= 3.5 => ['grade' => 'A-', 'color' => 'info'],
        $avgGpa >= 3.0 => ['grade' => 'B',  'color' => 'primary'],
        $avgGpa >= 2.0 => ['grade' => 'C',  'color' => 'warning'],
        $avgGpa >= 1.0 => ['grade' => 'D',  'color' => 'warning'],
        default        => ['grade' => 'F',  'color' => 'danger'],
    };
    
    return [
        'gpa'    => $avgGpa,
        'grade'  => $gradeInfo['grade'],
        'result' => 'PASS',
        'color'  => $gradeInfo['color']
    ];
}

// ============ Fee Calculations ============

/**
 * Calculate late fee based on due date
 * @param float $amount Original amount
 * @param string $dueDate Due date (Y-m-d)
 * @param float $ratePercent Late fee rate per month (default 2%)
 * @return float Late fee amount
 */
function calculateLateFee(float $amount, string $dueDate, float $ratePercent = 2.0): float {
    try {
        $due = new DateTime($dueDate);
        $today = new DateTime('today');
        
        if ($today <= $due) {
            return 0.0; // Not overdue
        }
        
        $daysLate = (int)$today->diff($due)->days;
        $monthsLate = (int)ceil($daysLate / 30);
        
        return round($amount * ($ratePercent / 100) * max(1, $monthsLate), 2);
    } catch (Exception $e) {
        return 0.0;
    }
}

/**
 * Get invoice status
 * @param float $totalAmount Total invoice amount
 * @param float $paidAmount Amount paid
 * @param string $dueDate Due date
 * @return string Status: paid, partial, partial_overdue, overdue, unpaid
 */
function getInvoiceStatus(float $totalAmount, float $paidAmount, string $dueDate): string {
    if ($paidAmount >= $totalAmount) {
        return 'paid';
    }
    
    try {
        $isOverdue = new DateTime('today') > new DateTime($dueDate);
    } catch (Exception $e) {
        $isOverdue = false;
    }
    
    if ($paidAmount > 0) {
        return $isOverdue ? 'partial_overdue' : 'partial';
    }
    
    return $isOverdue ? 'overdue' : 'unpaid';
}

/**
 * Get status badge HTML
 */
function statusBadge(string $status): string {
    $badges = [
        'paid'             => '<span class="badge bg-success">Paid</span>',
        'partial'          => '<span class="badge bg-info">Partial</span>',
        'partial_overdue'  => '<span class="badge bg-warning">Partial (Overdue)</span>',
        'overdue'          => '<span class="badge bg-danger">Overdue</span>',
        'unpaid'           => '<span class="badge bg-secondary">Unpaid</span>',
        'active'           => '<span class="badge bg-success">Active</span>',
        'inactive'         => '<span class="badge bg-secondary">Inactive</span>',
        'pending'          => '<span class="badge bg-warning">Pending</span>',
        'approved'         => '<span class="badge bg-success">Approved</span>',
        'rejected'         => '<span class="badge bg-danger">Rejected</span>',
        'success'          => '<span class="badge bg-success">Success</span>',
        'failed'           => '<span class="badge bg-danger">Failed</span>',
        'processing'       => '<span class="badge bg-info">Processing</span>',
    ];
    return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . e($status) . '</span>';
}

// ============ Number Generation ============

/**
 * Generate admission number
 * Format: ESM-YYYY-####
 */
function generateAdmissionNo(int $year, int $lastId): string {
    return sprintf('ESM-%d-%04d', $year, $lastId + 1);
}

/**
 * Generate invoice number
 * Format: INV-YYYYMM-#####
 */
function generateInvoiceNo(int $year, int $month, int $lastId): string {
    return sprintf('INV-%d%02d-%05d', $year, $month, $lastId + 1);
}

/**
 * Generate certificate number
 * Format: CERT-TYPE-YYYY-####
 */
function generateCertNo(string $typeCode, int $year, int $seq): string {
    return sprintf('CERT-%s-%d-%04d', strtoupper($typeCode), $year, $seq);
}

/**
 * Generate transaction ID
 */
function generateTransactionId(string $prefix = 'TXN'): string {
    return $prefix . '-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

// ============ Pagination ============

/**
 * Calculate pagination data
 * @param int $total Total records
 * @param int $currentPage Current page number
 * @param int $perPage Records per page
 * @return array Pagination data
 */
function paginate(int $total, int $currentPage, int $perPage = 20): array {
    $perPage = max(1, $perPage);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'total_pages' => $totalPages,
        'current'     => $currentPage,
        'offset'      => ($currentPage - 1) * $perPage,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
        'prev'        => $currentPage - 1,
        'next'        => $currentPage + 1,
        'from'        => (($currentPage - 1) * $perPage) + 1,
        'to'          => min($currentPage * $perPage, $total),
    ];
}

// ============ File Upload ============

/**
 * Validate and prepare uploaded file
 * @param array $file $_FILES array element
 * @param int $maxBytes Max file size in bytes (default 2MB)
 * @param array $allowed Allowed MIME types
 * @return array ['valid' => bool, 'error' => string, 'filename' => string, 'ext' => string, 'mime' => string]
 */
function validateUpload(array $file, int $maxBytes = 2097152, array $allowed = ['image/jpeg', 'image/png']): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension',
        ];
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Upload error code: ' . $file['error']];
    }
    
    if ($file['size'] > $maxBytes) {
        return ['valid' => false, 'error' => 'File too large (max ' . round($maxBytes / 1048576, 1) . 'MB)'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime, $allowed, true)) {
        return ['valid' => false, 'error' => 'Invalid file type: ' . $mime];
    }
    
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'application/pdf' => 'pdf',
        default => 'bin'
    };
    
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    
    return [
        'valid'    => true,
        'ext'      => $ext,
        'mime'     => $mime,
        'filename' => $filename,
        'size'     => $file['size'],
    ];
}

// ============ Formatting Functions ============

/**
 * Format currency
 */
function formatCurrency(float $amount, string $symbol = '৳'): string {
    return $symbol . ' ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate(string $date, string $format = 'd M Y'): string {
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format time ago
 */
function timeAgo(string $datetime): string {
    try {
        $diff = (new DateTime())->diff(new DateTime($datetime));
        
        if ($diff->y > 0) return $diff->y . ' বছর আগে';
        if ($diff->m > 0) return $diff->m . ' মাস আগে';
        if ($diff->d > 0) return $diff->d . ' দিন আগে';
        if ($diff->h > 0) return $diff->h . ' ঘণ্টা আগে';
        if ($diff->i > 0) return $diff->i . ' মিনিট আগে';
        return 'এইমাত্র';
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Format phone number (Bangladesh)
 */
function formatPhone(string $phone): string {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($clean) === 11 && str_starts_with($clean, '0')) {
        return substr($clean, 0, 4) . '-' . substr($clean, 4);
    }
    return $phone;
}

// ============ JSON Response ============

/**
 * Send JSON response and exit
 */
function jsonResponse(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

/**
 * Check if request is POST
 */
function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function isGet(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// ============ Validation Functions ============

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Bangladesh phone number
 */
function isValidBdPhone(string $phone): bool {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^01[3-9]\d{8}$/', $clean) === 1;
}

/**
 * Validate date
 */
function isValidDate(string $date, string $format = 'Y-m-d'): bool {
    try {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    } catch (Exception $e) {
        return false;
    }
}

// ============ Array Utilities ============

/**
 * Get value from array with default
 */
function arrayGet(array $array, string $key, mixed $default = null): mixed {
    return $array[$key] ?? $default;
}

/**
 * Pluck column from array of arrays
 */
function arrayPluck(array $array, string $column): array {
    return array_column($array, $column);
}

// ============ String Utilities ============

/**
 * Truncate string
 */
function str_limit(string $str, int $limit = 100, string $end = '...'): string {
    if (mb_strlen($str) <= $limit) {
        return $str;
    }
    return mb_substr($str, 0, $limit) . $end;
}

/**
 * Slug generation
 */
function str_slug(string $str): string {
    $str = mb_strtolower($str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}
