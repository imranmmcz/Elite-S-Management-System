<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্বাগতম - Elite School Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .welcome-container {
            padding: 50px 20px;
        }
        
        .welcome-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
        }
        
        .welcome-header i {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .welcome-header h1 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-header p {
            font-size: 20px;
            opacity: 0.95;
        }
        
        .welcome-body {
            padding: 50px 40px;
        }
        
        .feature-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .btn-start {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 18px 50px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s;
        }
        
        .btn-start:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="welcome-container">
    <div class="welcome-card">
        <!-- Header -->
        <div class="welcome-header">
            <i class="fas fa-graduation-cap"></i>
            <h1>Elite School Management</h1>
            <p>সম্পূর্ণ স্কুল ম্যানেজমেন্ট ERP সিস্টেম</p>
        </div>
        
        <!-- Body -->
        <div class="welcome-body">
            <div class="text-center mb-5">
                <h2 class="mb-3">সিস্টেমে স্বাগতম! 🎉</h2>
                <p class="lead text-muted">বাংলাদেশের শিক্ষা প্রতিষ্ঠানের জন্য তৈরি সম্পূর্ণ ম্যানেজমেন্ট সলিউশন</p>
            </div>
            
            <!-- Features -->
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5>Student Management</h5>
                        <p class="mb-0 text-muted">শিক্ষার্থী ভর্তি, প্রোফাইল, ডকুমেন্ট ম্যানেজমেন্ট</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5>Attendance System</h5>
                        <p class="mb-0 text-muted">দৈনিক উপস্থিতি, অনুপস্থিতি SMS নোটিফিকেশন</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5>Exam & Grading</h5>
                        <p class="mb-0 text-muted">পরীক্ষা, মার্কস এন্ট্রি, GPA গণনা, রিপোর্ট কার্ড</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                        <h5>Fee Management</h5>
                        <p class="mb-0 text-muted">ফি ইনভয়েস, কালেকশন, বকেয়া ট্র্যাকিং</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h5>Online Payment</h5>
                        <p class="mb-0 text-muted">bKash, SSLCommerz, Nagad ইন্টিগ্রেশন</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Notification</h5>
                        <p class="mb-0 text-muted">SMS/Email নোটিফিকেশন সিস্টেম</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h5>PDF Reports</h5>
                        <p class="mb-0 text-muted">সব রিপোর্ট PDF ফরম্যাটে জেনারেট</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h5>Certificate</h5>
                        <p class="mb-0 text-muted">QR কোড সহ যাচাইযোগ্য সার্টিফিকেট</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h5>Library</h5>
                        <p class="mb-0 text-muted">বই ইস্যু/রিটার্ন, ইনভেন্টরি ম্যানেজমেন্ট</p>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats">
                <div class="row">
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">35+</div>
                        <div class="stat-label">Database Tables</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Core Modules</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">7</div>
                        <div class="stat-label">User Roles</div>
                    </div>
                    <div class="col-md-3 stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Production Ready</div>
                    </div>
                </div>
            </div>
            
            <!-- CTA -->
            <div class="text-center mt-5">
                <a href="/login" class="btn-start">
                    <i class="fas fa-sign-in-alt"></i> এখনই শুরু করুন
                </a>
                
                <div class="mt-4">
                    <p class="text-muted mb-2">
                        <i class="fas fa-book"></i> ইনস্টলেশন গাইড: 
                        <a href="INSTALL.md" target="_blank">INSTALL.md</a>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-file-alt"></i> ডকুমেন্টেশন: 
                        <a href="README.md" target="_blank">README.md</a>
                    </p>
                </div>
            </div>
            
            <!-- Version Info -->
            <div class="text-center mt-5 pt-4 border-top">
                <p class="text-muted mb-0">
                    <strong>Elite School Management System</strong> v2.0.0<br>
                    <small>© 2024 All Rights Reserved</small>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
