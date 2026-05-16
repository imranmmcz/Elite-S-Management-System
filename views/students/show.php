<?php $pageTitle = $student['full_name']; ?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="fas fa-user text-primary me-2"></i> Student Profile</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/students">Students</a></li>
                    <li class="breadcrumb-item active"><?= e($student['full_name']) ?></li>
                </ol>
            </nav>
        </div>
        
        <?php if (isAdmin()): ?>
            <div>
                <a href="/students/<?= $student['id'] ?>/edit" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i> Edit
                </a>
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Print
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <?php if ($student['photo']): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= e($student['photo']) ?>" 
                         alt="<?= e($student['full_name']) ?>" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 150px; height: 150px; font-size: 60px; font-weight: 600;">
                        <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <h4 class="mb-1"><?= e($student['full_name']) ?></h4>
                <p class="text-muted mb-3"><?= e($student['admission_no']) ?></p>
                
                <?= statusBadge($student['status']) ?>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="/fees?student_id=<?= $student['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-money-bill me-2"></i> View Fees
                    </a>
                    <a href="/attendance?student_id=<?= $student['id'] ?>" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-calendar-check me-2"></i> View Attendance
                    </a>
                    <a href="/exams/student/<?= $student['id'] ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-alt me-2"></i> View Results
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i> Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Attendance</span>
                        <strong><?= $attendanceStats['attendance_percentage'] ?? 0 ?>%</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?= $attendanceStats['attendance_percentage'] ?? 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fas fa-calendar-day text-success me-1"></i> Present Days:</span>
                    <strong><?= $attendanceStats['present_days'] ?? 0 ?></strong>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="fas fa-calendar-times text-danger me-1"></i> Absent Days:</span>
                    <strong><?= $attendanceStats['absent_days'] ?? 0 ?></strong>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <span><i class="fas fa-exclamation-triangle text-warning me-1"></i> Pending Fees:</span>
                    <strong><?= count($feeStatus) ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-md-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Full Name:</strong><br>
                        <?= e($student['full_name']) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Admission No:</strong><br>
                        <?= e($student['admission_no']) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Date of Birth:</strong><br>
                        <?= formatDate($student['date_of_birth']) ?> (Age: <?= $student['age'] ?> years)
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Gender:</strong><br>
                        <i class="fas fa-<?= $student['gender'] === 'male' ? 'mars' : 'venus' ?> me-1"></i>
                        <?= ucfirst($student['gender']) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Blood Group:</strong><br>
                        <?= e($student['blood_group'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Religion:</strong><br>
                        <?= e($student['religion'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Class:</strong><br>
                        <?= e($student['class_name']) ?> - Section <?= e($student['section_name']) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Roll No:</strong><br>
                        <?= e($student['roll_no'] ?: 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-phone me-2"></i> Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong><br>
                        <?php if ($student['email']): ?>
                            <a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Phone:</strong><br>
                        <?= $student['phone'] ? e(formatPhone($student['phone'])) : 'N/A' ?>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <strong>Present Address:</strong><br>
                        <?= e($student['present_address'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <strong>Permanent Address:</strong><br>
                        <?= e($student['permanent_address'] ?: 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Parent Information -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Parent/Guardian Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <strong>Father's Name:</strong><br>
                        <?= e($student['father_name'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong>Father's Phone:</strong><br>
                        <?= $student['father_phone'] ? e(formatPhone($student['father_phone'])) : 'N/A' ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong>Father's Occupation:</strong><br>
                        <?= e($student['father_occupation'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong>Mother's Name:</strong><br>
                        <?= e($student['mother_name'] ?: 'N/A') ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong>Mother's Phone:</strong><br>
                        <?= $student['mother_phone'] ? e(formatPhone($student['mother_phone'])) : 'N/A' ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <strong>Mother's Occupation:</strong><br>
                        <?= e($student['mother_occupation'] ?: 'N/A') ?>
                    </div>
                    
                    <?php if ($student['guardian_name']): ?>
                        <div class="col-md-4 mb-3">
                            <strong>Guardian's Name:</strong><br>
                            <?= e($student['guardian_name']) ?>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>Guardian's Phone:</strong><br>
                            <?= e(formatPhone($student['guardian_phone'])) ?>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <strong>Guardian's Relation:</strong><br>
                            <?= e($student['guardian_relation']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Pending Fees -->
        <?php if (!empty($feeStatus)): ?>
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Pending Fees</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feeStatus as $fee): ?>
                                    <tr>
                                        <td><?= e($fee['fee_type']) ?></td>
                                        <td><?= formatCurrency($fee['due_amount']) ?></td>
                                        <td><?= formatDate($fee['due_date']) ?></td>
                                        <td><?= statusBadge($fee['status']) ?></td>
                                        <td>
                                            <a href="/fees/<?= $fee['id'] ?>/view" class="btn btn-sm btn-primary">Pay Now</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Recent Exam Results -->
        <?php if (!empty($recentExams)): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Recent Exam Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>GPA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExams as $exam): ?>
                                    <tr>
                                        <td><?= e($exam['exam_name']) ?></td>
                                        <td><?= $exam['marks_obtained'] ?> / <?= $exam['full_marks'] ?></td>
                                        <td><span class="badge bg-primary"><?= e($exam['grade']) ?></span></td>
                                        <td><strong><?= $exam['gpa'] ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
