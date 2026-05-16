<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="mb-4">
            <h2 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>

        <!-- Report Categories -->
        <div class="row">
            <?php foreach ($reportTypes as $key => $report): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 report-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="report-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas <?= $report['icon'] ?> fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1"><?= $report['title'] ?></h5>
                                <p class="text-muted mb-0 small"><?= $report['description'] ?></p>
                            </div>
                        </div>
                        <a href="<?= $report['url'] ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-alt me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Quick Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                            <h4 class="mb-0">0</h4>
                            <small class="text-muted">Total Students</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                            <h4 class="mb-0">0</h4>
                            <small class="text-muted">Total Teachers</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-money-bill-wave fa-2x text-info mb-2"></i>
                            <h4 class="mb-0">৳0</h4>
                            <small class="text-muted">Total Collection</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-percent fa-2x text-warning mb-2"></i>
                            <h4 class="mb-0">0%</h4>
                            <small class="text-muted">Avg Attendance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.report-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
