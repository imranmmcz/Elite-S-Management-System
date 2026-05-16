-- ===================================================================
-- Elite School Management - Demo Data
-- Insert sample data for testing
-- ===================================================================

USE `elite_school_db`;

-- Insert roles
INSERT INTO `roles` (`id`, `name`, `display_name`, `permissions`, `is_system`) VALUES
(1, 'super_admin', 'Super Administrator', '["*"]', 1),
(2, 'admin', 'Administrator', '["student.*", "attendance.*", "exam.*", "fee.*", "hr.*", "library.*", "report.*"]', 1),
(3, 'teacher', 'Teacher', '["student.view", "attendance.*", "exam.*"]', 1),
(4, 'accountant', 'Accountant', '["fee.*", "payment.*", "report.view"]', 1),
(5, 'librarian', 'Librarian', '["library.*", "student.view"]', 1),
(6, 'student', 'Student', '["profile.view", "exam.view", "fee.view"]', 1),
(7, 'parent', 'Parent', '["student.view", "exam.view", "fee.view", "attendance.view"]', 1);

-- Insert users (password: admin123 for all - hashed with bcrypt cost 12)
INSERT INTO `users` (`id`, `role_id`, `email`, `password`, `full_name`, `phone`, `is_active`) VALUES
(1, 1, 'admin@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Super Admin', '01700000001', 1),
(2, 2, 'principal@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Principal Rahman', '01700000002', 1),
(3, 3, 'teacher1@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Fatima Akter', '01700000003', 1),
(4, 3, 'teacher2@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Kamal Ahmed', '01700000004', 1),
(5, 4, 'accountant@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Jamal Uddin', '01700000005', 1),
(6, 5, 'librarian@eliteschool.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5OfmcSmyYi28i', 'Nasrin Sultana', '01700000006', 1);

-- Insert academic years
INSERT INTO `academic_years` (`id`, `name`, `start_date`, `end_date`, `is_active`) VALUES
(1, '2024-2025', '2024-01-01', '2024-12-31', 1),
(2, '2025-2026', '2025-01-01', '2025-12-31', 0);

-- Insert classes
INSERT INTO `classes` (`id`, `name`, `numeric_name`) VALUES
(1, 'Class 1', 1),
(2, 'Class 2', 2),
(3, 'Class 3', 3),
(4, 'Class 4', 4),
(5, 'Class 5', 5),
(6, 'Class 6', 6),
(7, 'Class 7', 7),
(8, 'Class 8', 8),
(9, 'Class 9', 9),
(10, 'Class 10', 10);

-- Insert sections
INSERT INTO `sections` (`class_id`, `name`, `capacity`) VALUES
(1, 'A', 40), (1, 'B', 40),
(2, 'A', 40), (2, 'B', 40),
(3, 'A', 40), (3, 'B', 40),
(4, 'A', 40), (4, 'B', 40),
(5, 'A', 40), (5, 'B', 40),
(6, 'A', 35), (6, 'B', 35),
(7, 'A', 35), (7, 'B', 35),
(8, 'A', 35), (8, 'B', 35),
(9, 'A', 30), (9, 'B', 30),
(10, 'A', 30), (10, 'B', 30);

-- Insert subjects
INSERT INTO `subjects` (`id`, `name`, `code`, `type`) VALUES
(1, 'Bangla', 'BNG', 'theory'),
(2, 'English', 'ENG', 'theory'),
(3, 'Mathematics', 'MATH', 'theory'),
(4, 'Science', 'SCI', 'both'),
(5, 'Social Studies', 'SS', 'theory'),
(6, 'ICT', 'ICT', 'both'),
(7, 'Religion', 'REL', 'theory'),
(8, 'Physics', 'PHY', 'both'),
(9, 'Chemistry', 'CHEM', 'both'),
(10, 'Biology', 'BIO', 'both');

-- Insert some class subjects (Class 6 example)
INSERT INTO `class_subjects` (`class_id`, `subject_id`, `teacher_id`, `full_marks`, `pass_marks`) VALUES
(6, 1, 3, 100, 33),
(6, 2, 4, 100, 33),
(6, 3, 3, 100, 33),
(6, 4, 4, 100, 33),
(6, 5, 3, 100, 33),
(6, 6, 4, 100, 33),
(6, 7, 3, 100, 33);

-- Insert sample students
INSERT INTO `students` (`id`, `academic_year_id`, `admission_no`, `roll_no`, `first_name`, `last_name`, `date_of_birth`, `gender`, `blood_group`, `email`, `phone`, `father_name`, `father_phone`, `mother_name`, `mother_phone`, `class_id`, `section_id`, `status`, `admission_date`) VALUES
(1, 1, 'ESM-2024-0001', '1', 'Rahim', 'Ahmed', '2012-05-15', 'male', 'A+', NULL, '01800000001', 'Mr. Ahmed', '01700000011', 'Mrs. Hasina', '01700000012', 6, 1, 'active', '2024-01-05'),
(2, 1, 'ESM-2024-0002', '2', 'Fatima', 'Begum', '2012-08-22', 'female', 'B+', NULL, '01800000002', 'Mr. Karim', '01700000013', 'Mrs. Amina', '01700000014', 6, 1, 'active', '2024-01-05'),
(3, 1, 'ESM-2024-0003', '3', 'Kamal', 'Hossain', '2012-03-10', 'male', 'O+', NULL, '01800000003', 'Mr. Hossain', '01700000015', 'Mrs. Rokeya', '01700000016', 6, 1, 'active', '2024-01-05'),
(4, 1, 'ESM-2024-0004', '4', 'Ayesha', 'Sultana', '2012-11-05', 'female', 'AB+', NULL, '01800000004', 'Mr. Rahman', '01700000017', 'Mrs. Nasrin', '01700000018', 6, 1, 'active', '2024-01-05'),
(5, 1, 'ESM-2024-0005', '5', 'Sohel', 'Rana', '2012-07-18', 'male', 'A-', NULL, '01800000005', 'Mr. Rana', '01700000019', 'Mrs. Sultana', '01700000020', 6, 2, 'active', '2024-01-05');

-- Insert fee types
INSERT INTO `fee_types` (`id`, `name`, `description`, `is_recurring`) VALUES
(1, 'Tuition Fee', 'Monthly tuition fee', 1),
(2, 'Admission Fee', 'One-time admission fee', 0),
(3, 'Exam Fee', 'Semester/Annual exam fee', 0),
(4, 'Library Fee', 'Annual library fee', 0),
(5, 'Sports Fee', 'Annual sports fee', 0),
(6, 'Development Fee', 'Annual school development fee', 0);

-- Insert fee allocations (Class 6)
INSERT INTO `fee_allocations` (`academic_year_id`, `class_id`, `fee_type_id`, `amount`, `due_day`) VALUES
(1, 6, 1, 1500.00, 10),
(1, 6, 2, 5000.00, 5),
(1, 6, 3, 500.00, 15),
(1, 6, 4, 300.00, 10),
(1, 6, 5, 200.00, 10),
(1, 6, 6, 1000.00, 10);

-- Insert sample invoices
INSERT INTO `invoices` (`invoice_no`, `student_id`, `academic_year_id`, `fee_type_id`, `amount`, `discount`, `late_fee`, `total_amount`, `paid_amount`, `due_amount`, `due_date`, `status`, `generated_by`) VALUES
('INV-202401-00001', 1, 1, 1, 1500.00, 0.00, 0.00, 1500.00, 1500.00, 0.00, '2024-01-10', 'paid', 2),
('INV-202402-00002', 1, 1, 1, 1500.00, 0.00, 0.00, 1500.00, 1500.00, 0.00, '2024-02-10', 'paid', 2),
('INV-202403-00003', 1, 1, 1, 1500.00, 0.00, 0.00, 1500.00, 500.00, 1000.00, '2024-03-10', 'partial', 2),
('INV-202401-00004', 2, 1, 1, 1500.00, 100.00, 0.00, 1400.00, 0.00, 1400.00, '2024-01-10', 'unpaid', 2);

-- Insert sample payments
INSERT INTO `payments` (`invoice_id`, `transaction_id`, `payment_method`, `gateway`, `amount`, `payment_date`, `reference_no`, `received_by`, `status`) VALUES
(1, 'TXN-20240105-001', 'cash', NULL, 1500.00, '2024-01-05', 'CASH-001', 5, 'success'),
(2, 'TXN-20240205-002', 'online', 'bkash', 1500.00, '2024-02-05', 'BKA123456', 5, 'success'),
(3, 'TXN-20240305-003', 'cash', NULL, 500.00, '2024-03-05', 'CASH-002', 5, 'success');

-- Insert payment gateways
INSERT INTO `payment_gateways` (`name`, `display_name`, `is_enabled`, `is_sandbox`, `config`) VALUES
('bkash', 'bKash', 1, 1, '{"app_key": "", "app_secret": ""}'),
('sslcommerz', 'SSLCommerz', 1, 1, '{"store_id": "", "store_password": ""}'),
('nagad', 'Nagad', 0, 1, '{"merchant_id": ""}');

-- Insert notification templates
INSERT INTO `notification_templates` (`code`, `name`, `type`, `subject`, `body`, `variables`, `is_active`) VALUES
('absence_sms', 'Absence Notification', 'sms', NULL, 'প্রিয় অভিভাবক, আপনার সন্তান {student_name} আজ {date} তারিখে স্কুলে অনুপস্থিত ছিল। - {school_name}', '["student_name", "date", "school_name"]', 1),
('fee_due_sms', 'Fee Due Reminder', 'sms', NULL, 'প্রিয় অভিভাবক, {student_name} এর {fee_type} বকেয়া আছে ৳{amount}। শেষ তারিখ: {due_date}। - {school_name}', '["student_name", "fee_type", "amount", "due_date", "school_name"]', 1),
('exam_result_sms', 'Exam Result Notification', 'sms', NULL, 'প্রিয় অভিভাবক, {student_name} এর {exam_name} পরীক্ষার ফলাফল প্রকাশ হয়েছে। GPA: {gpa}, Grade: {grade}। - {school_name}', '["student_name", "exam_name", "gpa", "grade", "school_name"]', 1),
('payment_receipt', 'Payment Receipt', 'email', 'Payment Receipt - {invoice_no}', '<h2>Payment Confirmation</h2><p>Dear Parent,</p><p>We have received your payment of ৳{amount} for {student_name}.</p><p>Invoice: {invoice_no}<br>Date: {date}</p><p>Thank you!</p>', '["invoice_no", "amount", "student_name", "date"]', 1);

-- Insert certificate types
INSERT INTO `certificate_types` (`code`, `name`, `template`, `variables`, `requires_approval`, `is_active`) VALUES
('BON', 'Bonafide Certificate', '<html>Bonafide Certificate Template</html>', '["student_name", "class", "roll", "admission_no", "date"]', 1, 1),
('CHAR', 'Character Certificate', '<html>Character Certificate Template</html>', '["student_name", "class", "roll", "conduct", "date"]', 1, 1),
('ACH', 'Achievement Certificate', '<html>Achievement Certificate Template</html>', '["student_name", "achievement", "date"]', 0, 1),
('ADM', 'Admission Certificate', '<html>Admission Certificate Template</html>', '["student_name", "class", "admission_no", "date"]', 1, 1),
('COMP', 'Completion Certificate', '<html>Completion Certificate Template</html>', '["student_name", "class", "year", "result", "date"]', 1, 1);

-- Insert library books
INSERT INTO `library_books` (`isbn`, `title`, `author`, `publisher`, `publication_year`, `category`, `total_copies`, `available_copies`, `price`, `status`) VALUES
('978-0-13-468599-1', 'Computer Science Fundamentals', 'Dr. Smith', 'Pearson', 2020, 'Computer Science', 5, 5, 850.00, 'active'),
('978-0-07-338097-8', 'Mathematics Grade 6', 'Dr. Rahman', 'McGraw Hill', 2019, 'Mathematics', 10, 8, 450.00, 'active'),
('978-0-19-432581-8', 'English Grammar', 'John Doe', 'Oxford', 2021, 'Language', 8, 7, 520.00, 'active'),
('978-0-321-85656-8', 'Science Experiments', 'Dr. Lee', 'Addison Wesley', 2020, 'Science', 6, 4, 680.00, 'active');

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `data_type`, `category`, `description`) VALUES
('school_name', 'Elite School', 'string', 'general', 'School name'),
('school_address', 'Dhaka, Bangladesh', 'string', 'general', 'School address'),
('school_phone', '01700000000', 'string', 'general', 'School contact phone'),
('school_email', 'info@eliteschool.com', 'string', 'general', 'School contact email'),
('currency_symbol', '৳', 'string', 'finance', 'Currency symbol'),
('academic_year_start_month', '1', 'number', 'academic', 'Academic year start month (1-12)'),
('default_late_fee_rate', '2.0', 'number', 'finance', 'Default late fee percentage per month'),
('attendance_absence_notify', 'true', 'boolean', 'notification', 'Send SMS on absence'),
('library_issue_days', '14', 'number', 'library', 'Default book issue days'),
('library_fine_per_day', '5.00', 'number', 'library', 'Fine amount per day for overdue books');

-- ===================================================================
-- End of Demo Data
-- ===================================================================
