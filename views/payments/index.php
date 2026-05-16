<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-receipt me-2"></i>Payment History</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </nav>
            </div>
            <?php if (hasPermission('payment_create')): ?>
            <a href="/payments/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Payment
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
                                <p class="text-muted mb-1">Today's Collection</p>
                                <h3 class="mb-0">৳<?= number_format($stats['today_total'] ?? 0, 2) ?></h3>
                                <small class="text-muted"><?= $stats['today_count'] ?? 0 ?> payments</small>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="fas fa-money-bill-wave"></i>
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
                                <p class="text-muted mb-1">This Week</p>
                                <h3 class="mb-0">৳<?= number_format($stats['week_total'] ?? 0, 2) ?></h3>
                            </div>
                            <div class="stats-icon bg-info">
                                <i class="fas fa-calendar-week"></i>
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
                                <h3 class="mb-0">৳<?= number_format($stats['month_total'] ?? 0, 2) ?></h3>
                            </div>
                            <div class="stats-icon bg-primary">
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
                                <p class="text-muted mb-1">Total Collected</p>
                                <h3 class="mb-0">৳<?= number_format($stats['total_collected'] ?? 0, 2) ?></h3>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="/payments" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <select name="method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" <?= ($_GET['method'] ?? '') == 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="bank_transfer" <?= ($_GET['method'] ?? '') == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="bkash" <?= ($_GET['method'] ?? '') == 'bkash' ? 'selected' : '' ?>>bKash</option>
                            <option value="nagad" <?= ($_GET['method'] ?? '') == 'nagad' ? 'selected' : '' ?>>Nagad</option>
                            <option value="card" <?= ($_GET['method'] ?? '') == 'card' ? 'selected' : '' ?>>Card</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $_GET['date_from'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $_GET['date_to'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" <?= ($_GET['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= ($_GET['status'] ?? '') == 'failed' ? 'selected' : '' ?>>Failed</option>
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

        <!-- Payments List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Invoice #</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Collected By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No payment records found.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($payment['receipt_number']) ?></strong></td>
                                <td><?= date('d M Y H:i', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($payment['admission_number']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($payment['invoice_number']) ?></td>
                                <td class="text-success"><strong>৳<?= number_format($payment['amount'], 2) ?></strong></td>
                                <td>
                                    <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
                                </td>
                                <td><?= htmlspecialchars($payment['collected_by_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger'
                                    ];
                                    $color = $statusColors[$payment['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($payment['status']) ?></span>
                                </td>
                                <td>
                                    <a href="/payments/<?= $payment['id'] ?>" class="btn btn-sm btn-info" title="View Receipt">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/payments/<?= $payment['id'] ?>/print" class="btn btn-sm btn-secondary" title="Print Receipt" target="_blank">
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
