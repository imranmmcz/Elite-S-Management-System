</div><!-- End content-wrapper -->

<!-- Footer -->
<footer class="text-center py-3 mt-5" style="margin-left: var(--sidebar-width); color: #6c757d;">
    <small>
        © 2024 Elite School Management System v2.0.0 | 
        <a href="<?= APP_URL ?>/README.md" target="_blank">Documentation</a> | 
        <a href="<?= APP_URL ?>/INSTALL.md" target="_blank">Help</a>
    </small>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
// Sidebar toggle for mobile
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('show');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(e.target) && 
        !toggle?.contains(e.target) &&
        sidebar.classList.contains('show')) {
        sidebar.classList.remove('show');
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Form validation
document.querySelectorAll('form[data-validate]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Are you sure?')) {
            e.preventDefault();
        }
    });
});

// Dynamic section loading based on class
document.querySelectorAll('[data-load-sections]').forEach(function(classSelect) {
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        const sectionSelect = document.querySelector('[data-section-target]');
        
        if (classId && sectionSelect) {
            fetch(`/api/sections?class_id=${classId}`)
                .then(r => r.json())
                .then(sections => {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                    sections.forEach(s => {
                        sectionSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                    });
                })
                .catch(e => console.error('Failed to load sections:', e));
        }
    });
});

// Print functionality
function printDiv(divId) {
    const content = document.getElementById(divId);
    if (content) {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
}
</script>

</body>
</html>
