<?php $pageTitle = 'Add Student'; ?>

<div class="mb-4">
    <h3><i class="fas fa-user-plus text-primary me-2"></i> Add New Student</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/students">Students</a></li>
            <li class="breadcrumb-item active">Add Student</li>
        </ol>
    </nav>
</div>

<form method="POST" action="/students/store" enctype="multipart/form-data" data-validate>
    <?= csrf_field() ?>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i> Basic Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="religion" class="form-control">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                    <small class="text-muted">Max 2MB, JPG/PNG only</small>
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
                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" class="form-select" required>
                        <option value="">Select Academic Year</option>
                        <?php foreach ($academicYears as $year): ?>
                            <option value="<?= $year['id'] ?>" <?= $year['is_active'] ? 'selected' : '' ?>>
                                <?= e($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select" required data-load-sections>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= e($class['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select name="section_id" class="form-select" required data-section-target>
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Roll No</label>
                    <input type="text" name="roll_no" class="form-control">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" class="form-control" value="<?= date('Y-m-d') ?>">
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
                    <input type="email" name="email" class="form-control">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="01XXXXXXXXX">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Present Address</label>
                    <textarea name="present_address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Permanent Address</label>
                    <textarea name="permanent_address" class="form-control" rows="2"></textarea>
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
                    <input type="text" name="father_name" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Father's Phone</label>
                    <input type="tel" name="father_phone" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Father's Occupation</label>
                    <input type="text" name="father_occupation" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Phone</label>
                    <input type="tel" name="mother_phone" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Mother's Occupation</label>
                    <input type="text" name="mother_occupation" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Name</label>
                    <input type="text" name="guardian_name" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Phone</label>
                    <input type="tel" name="guardian_phone" class="form-control">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Guardian's Relation</label>
                    <input type="text" name="guardian_relation" class="form-control" placeholder="e.g., Uncle, Aunt">
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="/students" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i> Save Student
        </button>
    </div>
</form>

<script>
// Load sections when class is selected
document.querySelector('[data-load-sections]')?.addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.querySelector('[data-section-target]');
    
    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        return;
    }
    
    // Fetch sections for the selected class
    fetch(`<?= APP_URL ?>/api/sections.php?class_id=${classId}`)
        .then(r => r.json())
        .then(sections => {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            sections.forEach(s => {
                sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        })
        .catch(e => {
            console.error('Failed to load sections:', e);
            alert('Failed to load sections. Please refresh the page.');
        });
});
</script>
