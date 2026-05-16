<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Elite School Management</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h4 {
            margin: 10px 0 0 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-brand i {
            font-size: 40px;
            color: var(--primary-color);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-menu a i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }
        
        /* Header */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 999;
        }
        
        .header-title h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Content */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }
        
        /* Cards */
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card .card-body {
            padding: 25px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        /* Badges */
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 8px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-header,
            .content-wrapper {
                margin-left: 0;
            }
        }
        
        /* Print styles */
        @media print {
            .sidebar,
            .main-header,
            .no-print {
                display: none !important;
            }
            
            .content-wrapper {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-graduation-cap"></i>
        <h4>Elite School</h4>
        <small>Management System</small>
    </div>
    
    <div class="sidebar-menu">
        <?php 
        $currentPath = currentPath();
        foreach (getMenuItems() as $item): 
            $isActive = isActivePath($item['url']) ? 'active' : '';
        ?>
            <a href="<?= e($item['url']) ?>" class="<?= $isActive ?>">
                <i class="fas <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
        
        <a href="/logout" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <i class="fas fa-sign-out-alt"></i>
            <span>লগআউট</span>
        </a>
    </div>
</div>

<!-- Header -->
<div class="main-header">
    <div class="header-title">
        <button class="btn btn-link d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h5><?= $pageTitle ?? 'Dashboard' ?></h5>
    </div>
    
    <div class="header-actions">
        <div class="dropdown">
            <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                <i class="fas fa-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><a class="dropdown-item" href="#"><small>New student admission pending</small></a></li>
                <li><a class="dropdown-item" href="#"><small>Fee payment received</small></a></li>
                <li><a class="dropdown-item" href="#"><small>Exam schedule updated</small></a></li>
            </ul>
        </div>
        
        <div class="dropdown">
            <div class="user-profile" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="d-none d-md-block">
                    <div style="font-size: 14px; font-weight: 600;">
                        <?= e($_SESSION['user_name'] ?? 'User') ?>
                    </div>
                    <div style="font-size: 12px; color: #6c757d;">
                        <?= e(getRoleDisplayName($_SESSION['role'] ?? '')) ?>
                    </div>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i> প্রোফাইল</a></li>
                <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i> সেটিংস</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> লগআউট</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content-wrapper">
    
    <!-- Flash Messages -->
    <?php foreach (get_flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
