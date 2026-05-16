<?php $pageTitle = 'Attendance Management'; ?>

<div class="mb-4">
    <h3><i class="fas fa-calendar-check text-success me-2"></i> Attendance Management</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Attendance</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Select Class & Date</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/attendance" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="<?= e($date) ?>" required>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Class <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select" required data-load-sections>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= $classId == $class['id'] ? 'selected' : '' ?>>
                            <?= e($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Section <span class="text-danger">*</span></label>
                <select name="section_id" class="form-select" required data-section-target>
                    <option value="">Select Section</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['id'] ?>" <?= $sectionId == $section['id'] ? 'selected' : '' ?>>
                            <?= e($section['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Load Students
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Marking Form -->
<?php if (!empty($students)): ?>
    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i> Mark Attendance</h5>
            <span class="badge bg-light text-dark">Total Students: <?= count($students) ?></span>
        </div>
        <div class="card-body">
            
            <?php if (!empty($attendanceRecords)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Attendance already marked for this date. You can update it below.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/attendance/mark" data-validate>
                <?= csrf_field() ?>
                
                <input type="hidden" name="date" value="<?= e($date) ?>">
                <input type="hidden" name="class_id" value="<?= e($classId) ?>">
                <input type="hidden" name="section_id" value="<?= e($sectionId) ?>">
                
                <!-- Quick Actions -->
                <div class="mb-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                        <i class="fas fa-check-double me-1"></i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                        <i class="fas fa-times-circle me-1"></i> Mark All Absent
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Admission No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): 
                                $currentStatus = $attendanceMap[$student['id']]['status'] ?? 'present';
                                $currentRemarks = $attendanceMap[$student['id']]['remarks'] ?? '';
                            ?>
                                <tr>
                                    <td><strong><?= e($student['roll_no'] ?? '-') ?></strong></td>
                                    <td><?= e($student['admission_no']) ?></td>
                                    <td><?= e($student['full_name']) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" 
                                                   name="attendance[<?= $student['id'] ?>]" 
                                                   id="present_<?= $student['id'] ?>" 
                                                   value="present" 
                                                   <?= $currentStatus === 'present' ? 'checked' : '' ?>
                                                   required>
                                            <label class="btn btn-outline-success btn-sm" for="present_<?= $student['id'] ?>">
                                                <i class="fas fa-check"></i> Present
                                            </label>
                                            
                                            <input type="radio" class="btn-check" 
                                                   name="attendance[<?= $student['id'] ?>]" 
                                                   id="absent_<?= $student['id'] ?>" 
                                                   value="absent"
                                                   <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger btn-sm" for="absent_<?= $student['id'] ?>">
                                                <i class="fas fa-times"></i> Absent
                                            </label>
                                            
                                            <input type="radio" class="btn-check" 
                                                   name="attendance[<?= $student['id'] ?>]" 
                                                   id="late_<?= $student['id'] ?>" 
                                                   value="late"
                                                   <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning btn-sm" for="late_<?= $student['id'] ?>">
                                                <i class="fas fa-clock"></i> Late
                                            </label>
                                            
                                            <input type="radio" class="btn-check" 
                                                   name="attendance[<?= $student['id'] ?>]" 
                                                   id="leave_<?= $student['id'] ?>" 
                                                   value="leave"
                                                   <?= $currentStatus === 'leave' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-info btn-sm" for="leave_<?= $student['id'] ?>">
                                                <i class="fas fa-calendar-day"></i> Leave
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="remarks[<?= $student['id'] ?>]" 
                                               class="form-control form-control-sm" 
                                               placeholder="Optional remarks"
                                               value="<?= e($currentRemarks) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="/attendance" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i>
        Please select date, class and section to mark attendance.
    </div>
<?php endif; ?>

<script>
// Mark all students with same status
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(function(radio) {
        radio.checked = true;
    });
}

// Load sections when class is selected
document.querySelector('[data-load-sections]')?.addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.querySelector('[data-section-target]');
    
    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        return;
    }
    
    fetch(`<?= APP_URL ?>/api/sections.php?class_id=${classId}`)
        .then(r => r.json())
        .then(sections => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            sections.forEach(s => {
                sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        })
        .catch(e => console.error('Failed to load sections:', e));
});
</script>
