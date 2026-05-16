<?php
declare(strict_types=1);
/**
 * Elite School Management - Payment Gateway Configuration
 * bKash, SSLCommerz, Nagad integration settings
 */

return [
    'default_gateway' => 'sslcommerz', // bkash, sslcommerz, nagad
    
    // Global settings
    'currency' => 'BDT',
    'sandbox_mode' => true, // Set false in production
    
    // ============ bKash Configuration ============
    'bkash' => [
        'enabled'      => true,
        'version'      => '1.2.0-beta',
        
        // Sandbox credentials
        'sandbox' => [
            'app_key'     => 'YOUR_BKASH_SANDBOX_APP_KEY',
            'app_secret'  => 'YOUR_BKASH_SANDBOX_APP_SECRET',
            'username'    => 'YOUR_BKASH_SANDBOX_USERNAME',
            'password'    => 'YOUR_BKASH_SANDBOX_PASSWORD',
            'base_url'    => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
        ],
        
        // Production credentials
        'production' => [
            'app_key'     => 'YOUR_BKASH_PRODUCTION_APP_KEY',
            'app_secret'  => 'YOUR_BKASH_PRODUCTION_APP_SECRET',
            'username'    => 'YOUR_BKASH_PRODUCTION_USERNAME',
            'password'    => 'YOUR_BKASH_PRODUCTION_PASSWORD',
            'base_url'    => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
        ],
        
        'callback_url'    => APP_URL . '/payments/bkash/callback',
        'script_url'      => 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js',
    ],
    
    // ============ SSLCommerz Configuration ============
    'sslcommerz' => [
        'enabled'  => true,
        'version'  => 'v3',
        
        // Sandbox credentials
        'sandbox' => [
            'store_id'       => 'YOUR_SSLCOMMERZ_SANDBOX_STORE_ID',
            'store_password' => 'YOUR_SSLCOMMERZ_SANDBOX_PASSWORD',
            'api_url'        => 'https://sandbox.sslcommerz.com/gwprocess/v3/api.php',
            'validation_url' => 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
        
        // Production credentials
        'production' => [
            'store_id'       => 'YOUR_SSLCOMMERZ_PRODUCTION_STORE_ID',
            'store_password' => 'YOUR_SSLCOMMERZ_PRODUCTION_PASSWORD',
            'api_url'        => 'https://securepay.sslcommerz.com/gwprocess/v3/api.php',
            'validation_url' => 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',
        ],
        
        'success_url'    => APP_URL . '/payments/sslcommerz/success',
        'fail_url'       => APP_URL . '/payments/sslcommerz/fail',
        'cancel_url'     => APP_URL . '/payments/sslcommerz/cancel',
        'ipn_url'        => APP_URL . '/payments/sslcommerz/ipn',
    ],
    
    // ============ Nagad Configuration ============
    'nagad' => [
        'enabled'  => true,
        
        // Sandbox credentials
        'sandbox' => [
            'merchant_id'     => 'YOUR_NAGAD_SANDBOX_MERCHANT_ID',
            'merchant_number' => 'YOUR_NAGAD_SANDBOX_MERCHANT_NUMBER',
            'public_key'      => 'YOUR_NAGAD_SANDBOX_PUBLIC_KEY',
            'private_key'     => 'YOUR_NAGAD_SANDBOX_PRIVATE_KEY',
            'base_url'        => 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs',
        ],
        
        // Production credentials
        'production' => [
            'merchant_id'     => 'YOUR_NAGAD_PRODUCTION_MERCHANT_ID',
            'merchant_number' => 'YOUR_NAGAD_PRODUCTION_MERCHANT_NUMBER',
            'public_key'      => 'YOUR_NAGAD_PRODUCTION_PUBLIC_KEY',
            'private_key'     => 'YOUR_NAGAD_PRODUCTION_PRIVATE_KEY',
            'base_url'        => 'https://api.mynagad.com/api/dfs',
        ],
        
        'callback_url' => APP_URL . '/payments/nagad/callback',
    ],
    
    // ============ Transaction Settings ============
    'transaction' => [
        'timeout'          => 900, // 15 minutes
        'auto_refund_days' => 7,   // Auto-refund failed transactions after 7 days
        'min_amount'       => 10.00,
        'max_amount'       => 100000.00,
    ],
    
    // ============ Logging ============
    'logging' => [
        'enabled'      => true,
        'log_requests' => true,
        'log_responses' => true,
        'log_file'     => ROOT . '/logs/payment.log',
    ],
];
