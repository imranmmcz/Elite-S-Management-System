<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - Elite School Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .login-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        
        .demo-credentials strong {
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h3>Elite School Management</h3>
            <p class="mb-0">স্কুল ম্যানেজমেন্ট সিস্টেম</p>
        </div>
        
        <!-- Body -->
        <div class="login-body">
            <!-- Flash Messages -->
            <?php foreach (get_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            
            <!-- Login Form -->
            <form method="POST" action="/login">
                <?= csrf_field() ?>
                
                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-envelope text-muted"></i> ইমেইল
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="your-email@example.com" 
                            required 
                            autofocus
                            value="admin@eliteschool.com"
                        >
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock text-muted"></i> পাসওয়ার্ড
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="পাসওয়ার্ড লিখুন" 
                            required
                            value="admin123"
                        >
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        আমাকে মনে রাখুন
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i> লগইন করুন
                </button>
            </form>
            
            <!-- Demo Credentials -->
            <div class="demo-credentials">
                <strong><i class="fas fa-info-circle"></i> ডেমো একাউন্ট:</strong><br>
                <small>
                    <strong>Admin:</strong> admin@eliteschool.com / admin123<br>
                    <strong>Teacher:</strong> teacher1@eliteschool.com / admin123<br>
                    <strong>Accountant:</strong> accountant@eliteschool.com / admin123
                </small>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="text-center text-white mt-4">
        <p class="mb-0">
            <small>
                <i class="fas fa-copyright"></i> 2024 Elite School Management System<br>
                Version 2.0.0 - All Rights Reserved
            </small>
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
