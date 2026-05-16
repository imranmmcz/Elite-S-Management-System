<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i>Fee Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Fees</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="/fees/generate" class="btn btn-success me-2">
                    <i class="fas fa-file-invoice me-2"></i>Generate Invoices
                </a>
                <a href="/fees/structures" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Fee Structures
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Pending</p>
                                <h3 class="mb-0"><?= $stats['total_pending'] ?? 0 ?></h3>
                                <small class="text-muted">৳<?= number_format($stats['pending_amount'] ?? 0, 2) ?></small>
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
                                <p class="text-muted mb-1">Paid</p>
                                <h3 class="mb-0"><?= $stats['total_paid'] ?? 0 ?></h3>
                                <small class="text-success">৳<?= number_format($stats['paid_amount'] ?? 0, 2) ?></small>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="fas fa-check-circle"></i>
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
                                <p class="text-muted mb-1">Overdue</p>
                                <h3 class="mb-0"><?= $stats['total_overdue'] ?? 0 ?></h3>
                                <small class="text-danger">৳<?= number_format($stats['overdue_amount'] ?? 0, 2) ?></small>
                            </div>
                            <div class="stats-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
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
                                <p class="text-muted mb-1">Partial</p>
                                <h3 class="mb-0"><?= $stats['total_partial'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-info">
                                <i class="fas fa-percent"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="/fees" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= ($_GET['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="partial" <?= ($_GET['status'] ?? '') == 'partial' ? 'selected' : '' ?>>Partial</option>
                            <option value="paid" <?= ($_GET['status'] ?? '') == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="overdue" <?= ($_GET['status'] ?? '') == 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" class="form-control" value="<?= $_GET['month'] ?? '' ?>">
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

        <!-- Invoices List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Fee Invoices</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No invoices found. Generate invoices from Fee Structures.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($invoice['admission_number']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($invoice['class_name'] ?? 'N/A') ?></td>
                                <td>৳<?= number_format($invoice['total_amount'], 2) ?></td>
                                <td class="text-success">৳<?= number_format($invoice['paid_amount'], 2) ?></td>
                                <td class="text-danger">৳<?= number_format($invoice['total_amount'] - $invoice['paid_amount'], 2) ?></td>
                                <td><?= date('d M Y', strtotime($invoice['due_date'])) ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'overdue' => 'danger'
                                    ];
                                    $color = $statusColors[$invoice['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($invoice['status']) ?></span>
                                </td>
                                <td>
                                    <a href="/fees/<?= $invoice['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($invoice['status'] !== 'paid' && hasPermission('payment_create')): ?>
                                    <a href="/payments/create?invoice_id=<?= $invoice['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                    <?php endif; ?>
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
                            <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['class_id']) ? '&class_id='.$_GET['class_id'] : '' ?><?= isset($_GET['status']) ? '&status='.$_GET['status'] : '' ?>"><?= $i ?></a>
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
