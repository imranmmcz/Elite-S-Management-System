<?php $pageTitle = 'Dashboard'; ?>

<div class="mb-4">
    <h3><i class="fas fa-home text-primary me-2"></i> Admin Dashboard</h3>
    <p class="text-muted">Welcome back, <?= e($_SESSION['user_name']) ?>! Here's what's happening today.</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Students -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stat-label">Total Students</div>
                    <div class="stat-number"><?= number_format($stats['total_students'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Staff -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon me-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stat-label">Total Staff</div>
                    <div class="stat-number"><?= number_format($stats['total_staff'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Classes -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon me-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stat-label">Total Classes</div>
                    <div class="stat-number"><?= number_format($stats['total_classes'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Collection -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon me-3" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="stat-label">Month Collection</div>
                    <div class="stat-number" style="font-size: 24px;">
                        <?= formatCurrency($stats['fee_collection']['paid_amount'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance & Fee Charts -->
<div class="row g-4 mb-4">
    <!-- Today's Attendance -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check text-success me-2"></i> Today's Attendance</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['today_attendance'])): ?>
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-success"><?= $stats['today_attendance']['present'] ?? 0 ?></h3>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-danger"><?= $stats['today_attendance']['absent'] ?? 0 ?></h3>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-info"><?= $stats['today_attendance']['total'] ?? 0 ?></h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    
                    <canvas id="attendanceChart" height="200" class="mt-4"></canvas>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('attendanceChart');
                        if (ctx) {
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Present', 'Absent'],
                                    datasets: [{
                                        data: [
                                            <?= $stats['today_attendance']['present'] ?? 0 ?>,
                                            <?= $stats['today_attendance']['absent'] ?? 0 ?>
                                        ],
                                        backgroundColor: ['#28a745', '#dc3545'],
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                        }
                                    }
                                }
                            });
                        }
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No attendance marked today yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Fee Collection -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i> Fee Collection (This Month)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['fee_collection'])): ?>
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <h5 class="text-success"><?= formatCurrency($stats['fee_collection']['paid_amount'] ?? 0) ?></h5>
                            <small class="text-muted">Collected</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-danger"><?= formatCurrency($stats['fee_collection']['due_amount'] ?? 0) ?></h5>
                            <small class="text-muted">Due</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-info"><?= formatCurrency($stats['fee_collection']['total_amount'] ?? 0) ?></h5>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    
                    <canvas id="feeChart" height="200"></canvas>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('feeChart');
                        if (ctx) {
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Collected', 'Due'],
                                    datasets: [{
                                        data: [
                                            <?= $stats['fee_collection']['paid_amount'] ?? 0 ?>,
                                            <?= $stats['fee_collection']['due_amount'] ?? 0 ?>
                                        ],
                                        backgroundColor: ['#28a745', '#ffc107'],
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                        }
                                    }
                                }
                            });
                        }
                    });
                    </script>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No fee collection data for this month.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Activities -->
<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/students/create" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i> Add New Student
                    </a>
                    <a href="/attendance" class="btn btn-outline-success">
                        <i class="fas fa-calendar-check me-2"></i> Mark Attendance
                    </a>
                    <a href="/fees/invoices" class="btn btn-outline-info">
                        <i class="fas fa-file-invoice me-2"></i> Generate Invoice
                    </a>
                    <a href="/exams" class="btn btn-outline-warning">
                        <i class="fas fa-file-alt me-2"></i> Enter Marks
                    </a>
                    <a href="/reports" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar me-2"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Pending Approvals -->
        <?php if (!empty($stats['pending_certificates']) || !empty($stats['pending_leaves'])): ?>
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Pending Approvals</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['pending_certificates'])): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-certificate text-primary me-2"></i> Certificates</span>
                            <span class="badge bg-warning"><?= $stats['pending_certificates'] ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($stats['pending_leaves'])): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-times text-danger me-2"></i> Leave Requests</span>
                            <span class="badge bg-warning"><?= $stats['pending_leaves'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history text-info me-2"></i> Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['recent_activities'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_activities'] as $activity): ?>
                                    <tr>
                                        <td><?= e($activity['full_name'] ?? 'System') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $activity['action'] === 'create' ? 'success' : ($activity['action'] === 'update' ? 'primary' : 'danger') ?>">
                                                <?= ucfirst($activity['action']) ?>
                                            </span>
                                        </td>
                                        <td><?= ucfirst($activity['module']) ?></td>
                                        <td>
                                            <small class="text-muted"><?= timeAgo($activity['created_at']) ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No recent activities to show.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Events (Optional) -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Events</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="border-start border-4 border-primary ps-3 mb-3">
                    <h6 class="mb-1">Monthly Test</h6>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i> 15 March 2024
                    </small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="border-start border-4 border-success ps-3 mb-3">
                    <h6 class="mb-1">Annual Sports Day</h6>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i> 20 March 2024
                    </small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="border-start border-4 border-warning ps-3 mb-3">
                    <h6 class="mb-1">Parent-Teacher Meeting</h6>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i> 25 March 2024
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
