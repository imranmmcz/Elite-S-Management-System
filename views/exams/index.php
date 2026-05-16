<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-file-alt me-2"></i>Exam Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Exams</li>
                    </ol>
                </nav>
            </div>
            <?php if (hasPermission('exam_create')): ?>
            <a href="/exams/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Exam
            </a>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Exams</p>
                                <h3 class="mb-0">0</h3>
                            </div>
                            <div class="stats-icon bg-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Upcoming</p>
                                <h3 class="mb-0">0</h3>
                            </div>
                            <div class="stats-icon bg-info">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Ongoing</p>
                                <h3 class="mb-0">0</h3>
                            </div>
                            <div class="stats-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Completed</p>
                                <h3 class="mb-0">0</h3>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Exams</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Exam Module</strong> - This module will allow you to create and manage exams, schedules, mark entry, and result generation.
                    <br><br>
                    <strong>Features to be implemented:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Create exam schedules with subjects and dates</li>
                        <li>Mark entry and grade calculation</li>
                        <li>Result card generation</li>
                        <li>Merit list generation</li>
                        <li>Exam-wise performance analysis</li>
                    </ul>
                </div>

                <!-- Sample table structure -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th>Class</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No exams created yet. Click "Create Exam" to add your first exam.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
