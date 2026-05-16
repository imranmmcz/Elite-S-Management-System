<?php
declare(strict_types=1);
/**
 * Elite School Management - Notification Configuration
 * SMS & Email service settings
 */

return [
    // ============ SMS Configuration ============
    'sms' => [
        'default_provider' => 'ssl_wireless', // twilio, ssl_wireless, disabled
        
        // Twilio (International SMS)
        'twilio' => [
            'enabled'     => false,
            'account_sid' => 'YOUR_TWILIO_ACCOUNT_SID',
            'auth_token'  => 'YOUR_TWILIO_AUTH_TOKEN',
            'from_number' => '+1234567890',
        ],
        
        // SSL Wireless (Bangladesh)
        'ssl_wireless' => [
            'enabled'  => true,
            'api_url'  => 'https://smsplus.sslwireless.com/api/v3/send-sms',
            'api_token' => 'YOUR_SSL_WIRELESS_API_TOKEN',
            'sid'      => 'YOUR_SSL_WIRELESS_SID',
            'sender_id' => 'ELITESCHOOL', // Must be approved by SSL Wireless
        ],
    ],
    
    // ============ Email Configuration ============
    'email' => [
        'enabled' => true,
        'provider' => 'smtp', // smtp, sendgrid, disabled
        
        // SMTP Configuration (Gmail / Custom SMTP)
        'smtp' => [
            'host'       => 'smtp.gmail.com',
            'port'       => 587,
            'username'   => 'your-email@gmail.com',
            'password'   => 'your-app-password', // Gmail: Use App Password
            'encryption' => 'tls', // tls or ssl
            'from_email' => 'noreply@eliteschool.com',
            'from_name'  => 'Elite School Management',
        ],
    ],
    
    // ============ Queue & Retry Configuration ============
    'queue' => [
        'enabled'       => true,
        'batch_size'    => 50, // Process 50 notifications per cron run
        'retry_attempts' => 3,
        'retry_delay'   => 300, // seconds (5 minutes)
    ],
    
    // ============ Test Mode ============
    'test_mode' => true, // If true, logs only - no actual SMS/Email sent
    'test_recipients' => [
        'sms'   => '+8801700000000',
        'email' => 'test@example.com',
    ],
    
    // ============ Rate Limiting ============
    'rate_limit' => [
        'enabled'     => true,
        'sms_per_hour'   => 100,
        'email_per_hour' => 500,
    ],
    
    // ============ Notification Templates ============
    'templates' => [
        'absence_sms' => 'প্রিয় অভিভাবক, আপনার সন্তান {student_name} আজ {date} তারিখে স্কুলে অনুপস্থিত ছিল। - {school_name}',
        
        'fee_due_sms' => 'প্রিয় অভিভাবক, {student_name} এর {fee_type} বকেয়া আছে ৳{amount}। শেষ তারিখ: {due_date}। - {school_name}',
        
        'exam_result_sms' => 'প্রিয় অভিভাবক, {student_name} এর {exam_name} পরীক্ষার ফলাফল প্রকাশ হয়েছে। GPA: {gpa}, Grade: {grade}। - {school_name}',
        
        'fee_payment_email' => [
            'subject' => 'Payment Receipt - {invoice_no}',
            'body'    => '<h2>Payment Confirmation</h2><p>Dear Parent,</p><p>We have received your payment of ৳{amount} for {student_name}.</p><p>Invoice: {invoice_no}<br>Date: {date}</p><p>Thank you!</p>',
        ],
    ],
];
