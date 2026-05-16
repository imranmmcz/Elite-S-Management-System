<?php
/**
 * Settings Controller
 * Handles system settings - school info, academic year, classes, subjects, etc.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/functions.php';

class SettingsController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        requireLogin();
        requireRole(['super_admin', 'admin']);
    }

    /**
     * Display settings dashboard
     */
    public function index() {
        $settingsSections = [
            'general' => [
                'title' => 'General Settings',
                'description' => 'School information, logo, contact details',
                'icon' => 'fa-cog',
                'url' => '/settings/general'
            ],
            'academic' => [
                'title' => 'Academic Settings',
                'description' => 'Academic year, terms, grading system',
                'icon' => 'fa-graduation-cap',
                'url' => '/settings/academic'
            ],
            'classes' => [
                'title' => 'Classes & Sections',
                'description' => 'Manage classes, sections, and subjects',
                'icon' => 'fa-school',
                'url' => '/settings/classes'
            ],
            'fee' => [
                'title' => 'Fee Settings',
                'description' => 'Fee structures, late fee configuration',
                'icon' => 'fa-dollar-sign',
                'url' => '/settings/fees'
            ],
            'notification' => [
                'title' => 'Notification Settings',
                'description' => 'SMS, Email, and notification preferences',
                'icon' => 'fa-bell',
                'url' => '/settings/notifications'
            ],
            'users' => [
                'title' => 'User Management',
                'description' => 'Manage users, roles, and permissions',
                'icon' => 'fa-users',
                'url' => '/settings/users'
            ]
        ];
        
        require_once __DIR__ . '/../views/settings/index.php';
    }

    /**
     * General settings
     */
    public function general() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/settings/general', 'error', 'Invalid security token');
                return;
            }
            
            // Update general settings
            $settings = [
                'school_name' => $_POST['school_name'] ?? '',
                'school_code' => $_POST['school_code'] ?? '',
                'address' => $_POST['address'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'website' => $_POST['website'] ?? '',
                'principal_name' => $_POST['principal_name'] ?? '',
                'principal_signature' => $_POST['principal_signature'] ?? ''
            ];
            
            try {
                foreach ($settings as $key => $value) {
                    $checkSql = "SELECT id FROM settings WHERE setting_key = :key";
                    $checkStmt = $this->db->prepare($checkSql);
                    $checkStmt->execute([':key' => $key]);
                    
                    if ($checkStmt->fetch()) {
                        // Update existing
                        $updateSql = "UPDATE settings SET setting_value = :value WHERE setting_key = :key";
                        $updateStmt = $this->db->prepare($updateSql);
                        $updateStmt->execute([':value' => $value, ':key' => $key]);
                    } else {
                        // Insert new
                        $insertSql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)";
                        $insertStmt = $this->db->prepare($insertSql);
                        $insertStmt->execute([':key' => $key, ':value' => $value]);
                    }
                }
                
                logActivity($_SESSION['user_id'], 'settings_updated', 'General settings updated');
                redirect('/settings/general', 'success', 'Settings updated successfully');
                
            } catch (Exception $e) {
                redirect('/settings/general', 'error', 'Failed to update settings: ' . $e->getMessage());
            }
            
            return;
        }
        
        // Get current settings
        $settingsData = [];
        $result = $this->db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $settingsData[$row['setting_key']] = $row['setting_value'];
        }
        
        require_once __DIR__ . '/../views/settings/general.php';
    }

    /**
     * Academic settings
     */
    public function academic() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/settings/academic', 'error', 'Invalid security token');
                return;
            }
            
            $settings = [
                'current_academic_year' => $_POST['current_academic_year'] ?? '',
                'academic_year_start' => $_POST['academic_year_start'] ?? '',
                'academic_year_end' => $_POST['academic_year_end'] ?? '',
                'grading_system' => $_POST['grading_system'] ?? 'bangladesh'
            ];
            
            try {
                foreach ($settings as $key => $value) {
                    $checkSql = "SELECT id FROM settings WHERE setting_key = :key";
                    $checkStmt = $this->db->prepare($checkSql);
                    $checkStmt->execute([':key' => $key]);
                    
                    if ($checkStmt->fetch()) {
                        $updateSql = "UPDATE settings SET setting_value = :value WHERE setting_key = :key";
                        $updateStmt = $this->db->prepare($updateSql);
                        $updateStmt->execute([':value' => $value, ':key' => $key]);
                    } else {
                        $insertSql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)";
                        $insertStmt = $this->db->prepare($insertSql);
                        $insertStmt->execute([':key' => $key, ':value' => $value]);
                    }
                }
                
                logActivity($_SESSION['user_id'], 'settings_updated', 'Academic settings updated');
                redirect('/settings/academic', 'success', 'Academic settings updated successfully');
                
            } catch (Exception $e) {
                redirect('/settings/academic', 'error', 'Failed to update settings: ' . $e->getMessage());
            }
            
            return;
        }
        
        // Get current settings
        $settingsData = [];
        $result = $this->db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $settingsData[$row['setting_key']] = $row['setting_value'];
        }
        
        require_once __DIR__ . '/../views/settings/academic.php';
    }

    /**
     * Classes and sections management
     */
    public function classes() {
        $page = $_GET['page'] ?? 'classes';
        
        if ($page === 'sections') {
            $this->manageSections();
        } elseif ($page === 'subjects') {
            $this->manageSubjects();
        } else {
            $this->manageClasses();
        }
    }

    /**
     * Manage classes
     */
    private function manageClasses() {
        // Get all classes
        $classes = $this->db->query("SELECT c.*, 
                                            COUNT(DISTINCT s.id) AS student_count,
                                            COUNT(DISTINCT sec.id) AS section_count
                                     FROM classes c
                                     LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                                     LEFT JOIN sections sec ON c.id = sec.class_id
                                     GROUP BY c.id
                                     ORDER BY c.name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/settings/classes.php';
    }

    /**
     * Manage sections
     */
    private function manageSections() {
        // Get all sections with class info
        $sections = $this->db->query("SELECT s.*, 
                                             c.name AS class_name,
                                             COUNT(DISTINCT st.id) AS student_count
                                      FROM sections s
                                      LEFT JOIN classes c ON s.class_id = c.id
                                      LEFT JOIN students st ON s.id = st.section_id AND st.status = 'active'
                                      GROUP BY s.id
                                      ORDER BY c.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
        
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/settings/sections.php';
    }

    /**
     * Manage subjects
     */
    private function manageSubjects() {
        // Get all subjects with class info
        $subjects = $this->db->query("SELECT s.*, 
                                             c.name AS class_name
                                      FROM subjects s
                                      LEFT JOIN classes c ON s.class_id = c.id
                                      ORDER BY c.name, s.name")->fetchAll(PDO::FETCH_ASSOC);
        
        $classes = $this->db->query("SELECT * FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/settings/subjects.php';
    }

    /**
     * User management
     */
    public function users() {
        $users = $this->db->query("SELECT u.*, 
                                          COUNT(DISTINCT al.id) AS login_count,
                                          MAX(al.login_time) AS last_login
                                   FROM users u
                                   LEFT JOIN activity_logs al ON u.id = al.user_id AND al.action = 'login'
                                   GROUP BY u.id
                                   ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../views/settings/users.php';
    }

    /**
     * Notification settings
     */
    public function notifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                redirect('/settings/notifications', 'error', 'Invalid security token');
                return;
            }
            
            $settings = [
                'sms_enabled' => isset($_POST['sms_enabled']) ? '1' : '0',
                'email_enabled' => isset($_POST['email_enabled']) ? '1' : '0',
                'sms_provider' => $_POST['sms_provider'] ?? '',
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => $_POST['smtp_port'] ?? '',
                'smtp_username' => $_POST['smtp_username'] ?? '',
                'smtp_from_email' => $_POST['smtp_from_email'] ?? '',
                'smtp_from_name' => $_POST['smtp_from_name'] ?? ''
            ];
            
            // Only update password if provided
            if (!empty($_POST['smtp_password'])) {
                $settings['smtp_password'] = $_POST['smtp_password'];
            }
            
            try {
                foreach ($settings as $key => $value) {
                    $checkSql = "SELECT id FROM settings WHERE setting_key = :key";
                    $checkStmt = $this->db->prepare($checkSql);
                    $checkStmt->execute([':key' => $key]);
                    
                    if ($checkStmt->fetch()) {
                        $updateSql = "UPDATE settings SET setting_value = :value WHERE setting_key = :key";
                        $updateStmt = $this->db->prepare($updateSql);
                        $updateStmt->execute([':value' => $value, ':key' => $key]);
                    } else {
                        $insertSql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)";
                        $insertStmt = $this->db->prepare($insertSql);
                        $insertStmt->execute([':key' => $key, ':value' => $value]);
                    }
                }
                
                logActivity($_SESSION['user_id'], 'settings_updated', 'Notification settings updated');
                redirect('/settings/notifications', 'success', 'Notification settings updated successfully');
                
            } catch (Exception $e) {
                redirect('/settings/notifications', 'error', 'Failed to update settings: ' . $e->getMessage());
            }
            
            return;
        }
        
        // Get current settings
        $settingsData = [];
        $result = $this->db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $settingsData[$row['setting_key']] = $row['setting_value'];
        }
        
        require_once __DIR__ . '/../views/settings/notifications.php';
    }
}
