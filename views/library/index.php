<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-book me-2"></i>Library Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Library</li>
                    </ol>
                </nav>
            </div>
            <div>
                <?php if (hasPermission('book_issue')): ?>
                <a href="/library/issue" class="btn btn-success me-2">
                    <i class="fas fa-hand-holding me-2"></i>Issue Book
                </a>
                <a href="/library/return" class="btn btn-warning me-2">
                    <i class="fas fa-undo me-2"></i>Return Book
                </a>
                <?php endif; ?>
                <a href="/library/books" class="btn btn-primary">
                    <i class="fas fa-book me-2"></i>All Books
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
                                <p class="text-muted mb-1">Total Books</p>
                                <h3 class="mb-0"><?= $stats['total_books'] ?? 0 ?></h3>
                                <small class="text-muted"><?= $stats['total_copies'] ?? 0 ?> copies</small>
                            </div>
                            <div class="stats-icon bg-primary">
                                <i class="fas fa-book"></i>
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
                                <p class="text-muted mb-1">Available</p>
                                <h3 class="mb-0"><?= $stats['available_books'] ?? 0 ?></h3>
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
                                <p class="text-muted mb-1">Issued</p>
                                <h3 class="mb-0"><?= $stats['issued_books'] ?? 0 ?></h3>
                            </div>
                            <div class="stats-icon bg-info">
                                <i class="fas fa-hand-holding"></i>
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
                                <h3 class="mb-0"><?= $stats['overdue_books'] ?? 0 ?></h3>
                                <small class="text-danger">৳<?= number_format($stats['total_fines'] ?? 0, 2) ?> fines</small>
                            </div>
                            <div class="stats-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Issues -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        My Issued Books
                    <?php else: ?>
                        Recent Book Issues
                    <?php endif; ?>
                </h5>
                <a href="/library/books" class="btn btn-sm btn-outline-primary">Browse All Books</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentIssues)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        You have no issued books currently.
                    <?php else: ?>
                        No books issued recently.
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <?php if (in_array($_SESSION['role'], ['super_admin', 'admin', 'librarian'])): ?>
                                <th>Student</th>
                                <?php endif; ?>
                                <th>Book Title</th>
                                <th>ISBN</th>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <th>Days Left</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentIssues as $issue): ?>
                            <?php
                            $daysLeft = (strtotime($issue['expected_return_date']) - time()) / (60 * 60 * 24);
                            $statusClass = $daysLeft < 0 ? 'danger' : ($daysLeft < 3 ? 'warning' : 'success');
                            ?>
                            <tr>
                                <?php if (in_array($_SESSION['role'], ['super_admin', 'admin', 'librarian'])): ?>
                                <td>
                                    <?= htmlspecialchars($issue['first_name'] . ' ' . $issue['last_name']) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($issue['class_name'] ?? '') ?></small>
                                </td>
                                <?php endif; ?>
                                <td><strong><?= htmlspecialchars($issue['book_title']) ?></strong></td>
                                <td><?= htmlspecialchars($issue['isbn'] ?? 'N/A') ?></td>
                                <td><?= date('d M Y', strtotime($issue['issue_date'])) ?></td>
                                <td><?= date('d M Y', strtotime($issue['expected_return_date'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= $daysLeft < 0 ? abs(ceil($daysLeft)) . ' days overdue' : ceil($daysLeft) . ' days' ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-info"><?= ucfirst($issue['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
