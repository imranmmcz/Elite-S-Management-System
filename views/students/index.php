<?php $pageTitle = 'Students'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0"><i class="fas fa-user-graduate text-primary me-2"></i> Students</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Students</li>
            </ol>
        </nav>
    </div>
    
    <?php if (hasPermission('student.create') || isAdmin()): ?>
        <a href="/students/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Student
        </a>
    <?php endif; ?>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/students" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, Admission No, Email..." value="<?= e($search) ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= $classId == $class['id'] ? 'selected' : '' ?>>
                            <?= e($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="transferred" <?= $status === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                    <option value="graduated" <?= $status === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            Total: <span class="badge bg-primary"><?= $total ?></span> students
        </h5>
        
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>
    
    <?php if (empty($students)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            No students found. <a href="/students/create">Add your first student</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Admission No</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Roll No</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <?php if ($student['photo']): ?>
                                    <img src="<?= APP_URL ?>/uploads/<?= e($student['photo']) ?>" 
                                         alt="<?= e($student['full_name']) ?>" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; font-weight: 600;">
                                        <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($student['admission_no']) ?></strong>
                            </td>
                            <td>
                                <a href="/students/<?= $student['id'] ?>" class="text-decoration-none">
                                    <?= e($student['full_name']) ?>
                                </a>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-<?= $student['gender'] === 'male' ? 'mars' : 'venus' ?> me-1"></i>
                                    <?= ucfirst($student['gender']) ?>
                                </small>
                            </td>
                            <td>
                                <?= e($student['class_name']) ?> 
                                <?php if ($student['section_name']): ?>
                                    <span class="badge bg-light text-dark"><?= e($student['section_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($student['roll_no'] ?? '-') ?></td>
                            <td>
                                <?php if ($student['phone']): ?>
                                    <i class="fas fa-phone text-muted me-1"></i>
                                    <?= e(formatPhone($student['phone'])) ?>
                                    <br>
                                <?php endif; ?>
                                <?php if ($student['email']): ?>
                                    <i class="fas fa-envelope text-muted me-1"></i>
                                    <small><?= e($student['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= statusBadge($student['status']) ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/students/<?= $student['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if (hasPermission('student.edit') || isAdmin()): ?>
                                        <a href="/students/<?= $student['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['prev'] ?>&search=<?= urlencode($search) ?>&class_id=<?= $classId ?>&status=<?= $status ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php 
                    $start = max(1, $pagination['current'] - 2);
                    $end = min($pagination['total_pages'], $pagination['current'] + 2);
                    
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&class_id=<?= $classId ?>&status=<?= $status ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['next'] ?>&search=<?= urlencode($search) ?>&class_id=<?= $classId ?>&status=<?= $status ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                
                <p class="text-center text-muted">
                    Showing <?= $pagination['from'] ?> to <?= $pagination['to'] ?> of <?= $total ?> students
                </p>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
