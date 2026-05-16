<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-certificate me-2"></i>Certificates</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Certificates</li>
                    </ol>
                </nav>
            </div>
            <?php if (hasPermission('certificate_create')): ?>
            <a href="/certificates/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Generate Certificate
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
                                <p class="text-muted mb-1">Total Issued</p>
                                <h3 class="mb-0"><?= $stats['total_issued'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-primary">
                                <i class="fas fa-certificate"></i>
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
                                <p class="text-muted mb-1">This Month</p>
                                <h3 class="mb-0"><?= $stats['this_month'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="fas fa-calendar-check"></i>
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
                                <p class="text-muted mb-1">Character</p>
                                <h3 class="mb-0"><?= $stats['by_type']['character'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-info">
                                <i class="fas fa-award"></i>
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
                                <p class="text-muted mb-1">Transfer</p>
                                <h3 class="mb-0"><?= $stats['by_type']['transfer'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-warning">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="/certificates" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Certificate Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="character" <?= ($_GET['type'] ?? '') == 'character' ? 'selected' : '' ?>>Character Certificate</option>
                            <option value="transfer" <?= ($_GET['type'] ?? '') == 'transfer' ? 'selected' : '' ?>>Transfer Certificate</option>
                            <option value="bonafide" <?= ($_GET['type'] ?? '') == 'bonafide' ? 'selected' : '' ?>>Bonafide Certificate</option>
                            <option value="conduct" <?= ($_GET['type'] ?? '') == 'conduct' ? 'selected' : '' ?>>Conduct Certificate</option>
                            <option value="attendance" <?= ($_GET['type'] ?? '') == 'attendance' ? 'selected' : '' ?>>Attendance Certificate</option>
                            <option value="completion" <?= ($_GET['type'] ?? '') == 'completion' ? 'selected' : '' ?>>Completion Certificate</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="issued" <?= ($_GET['status'] ?? '') == 'issued' ? 'selected' : '' ?>>Issued</option>
                            <option value="cancelled" <?= ($_GET['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Certificates List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Issued Certificates</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Certificate #</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Issue Date</th>
                                <th>Issued By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certificates)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No certificates issued yet. Click "Generate Certificate" to create one.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cert['certificate_number']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($cert['admission_number']) ?></small>
                                </td>
                                <td><span class="badge bg-info"><?= ucfirst($cert['certificate_type']) ?></span></td>
                                <td><?= date('d M Y', strtotime($cert['issue_date'])) ?></td>
                                <td><?= htmlspecialchars($cert['issued_by_name'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $cert['status'] == 'issued' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($cert['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/certificates/<?= $cert['id'] ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/certificates/<?= $cert['id'] ?>/download" class="btn btn-sm btn-success" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="/certificates/<?= $cert['id'] ?>/print" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
