<?php $pageTitle = 'Edit Student'; ?>

<div class="mb-4">
    <h3><i class="fas fa-user-edit text-warning me-2"></i> Edit Student</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/students">Students</a></li>
            <li class="breadcrumb-item"><a href="/students/<?= $student['id'] ?>"><?= e($student['first_name']) ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form method="POST" action="/students/<?= $student['id'] ?>/update" enctype="multipart/form-data" data-validate>
    <?= csrf_field() ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i> Basic Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= e($student['first_name']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= e($student['last_name']) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?= e($student['date_of_birth']) ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="male" <?= $student['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $student['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= $student['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= $student['blood_group'] === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="religion" class="form-control" value="<?= e($student['religion']) ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                    <?php if ($student['photo']): ?>
                        <small class="text-muted">Current: <a href="<?= APP_URL ?>/uploads/<?= e($student['photo']) ?>" target="_blank">View</a></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> Academic Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select" required data-load-sections>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= $student['class_id'] == $class['id'] ? 'selected' : '' ?>>
                                <?= e($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select name="section_id" class="form-select" required data-section-target>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['id'] ?>" <?= $student['section_id'] == $section['id'] ? 'selected' : '' ?>>
                                <?= e($section['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Roll No</label>
                    <input type="text" name="roll_no" class="form-control" value="<?= e($student['roll_no']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?= $student['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $student['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="transferred" <?= $student['status'] === 'transferred' ? 'selected' : '' ?>>Transferred</option>
                        <option value="graduated" <?= $student['status'] === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="expelled" <?= $student['status'] === 'expelled' ? 'selected' : '' ?>>Expelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-phone me-2"></i> Contact Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($student['email']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($student['phone']) ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Present Address</label>
                    <textarea name="present_address" class="form-control" rows="2"><?= e($student['present_address']) ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Permanent Address</label>
                    <textarea name="permanent_address" class="form-control" rows="2"><?= e($student['permanent_address']) ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Parent/Guardian Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" value="<?= e($student['father_name']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Father's Phone</label>
                    <input type="tel" name="father_phone" class="form-control" value="<?= e($student['father_phone']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Father's Occupation</label>
                    <input type="text" name="father_occupation" class="form-control" value="<?= e($student['father_occupation']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control" value="<?= e($student['mother_name']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Phone</label>
                    <input type="tel" name="mother_phone" class="form-control" value="<?= e($student['mother_phone']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Occupation</label>
                    <input type="text" name="mother_occupation" class="form-control" value="<?= e($student['mother_occupation']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Name</label>
                    <input type="text" name="guardian_name" class="form-control" value="<?= e($student['guardian_name']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Phone</label>
                    <input type="tel" name="guardian_phone" class="form-control" value="<?= e($student['guardian_phone']) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Relation</label>
                    <input type="text" name="guardian_relation" class="form-control" value="<?= e($student['guardian_relation']) ?>">
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="/students/<?= $student['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i> Update Student
        </button>
    </div>
</form>

<script>
// Load sections when class is selected
document.querySelector('[data-load-sections]')?.addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.querySelector('[data-section-target]');
    
    if (!classId) return;
    
    fetch(`<?= APP_URL ?>/api/sections.php?class_id=${classId}`)
        .then(r => r.json())
        .then(sections => {
            const currentSectionId = <?= $student['section_id'] ?>;
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            sections.forEach(s => {
                const selected = s.id == currentSectionId ? 'selected' : '';
                sectionSelect.innerHTML += `<option value="${s.id}" ${selected}>${s.name}</option>`;
            });
        })
        .catch(e => console.error('Failed to load sections:', e));
});
</script>
