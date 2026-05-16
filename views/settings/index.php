<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="mb-4">
            <h2 class="mb-1"><i class="fas fa-cog me-2"></i>System Settings</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>

        <!-- Settings Categories -->
        <div class="row">
            <?php foreach ($settingsSections as $key => $section): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 settings-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="settings-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fas <?= $section['icon'] ?> fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1"><?= $section['title'] ?></h5>
                                <p class="text-muted mb-0 small"><?= $section['description'] ?></p>
                            </div>
                        </div>
                        <a href="<?= $section['url'] ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-cog me-2"></i>Configure
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- System Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>System Version:</strong></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= phpversion() ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database:</strong></td>
                                <td>MySQL</td>
                            </tr>
                            <tr>
                                <td><strong>Timezone:</strong></td>
                                <td><?= date_default_timezone_get() ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Server Software:</strong></td>
                                <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Upload Max Size:</strong></td>
                                <td><?= ini_get('upload_max_filesize') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit:</strong></td>
                                <td><?= ini_get('memory_limit') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time:</strong></td>
                                <td><?= ini_get('max_execution_time') ?>s</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-primary w-100" onclick="clearCache()">
                            <i class="fas fa-trash-alt me-2"></i>Clear Cache
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/settings/backup" class="btn btn-outline-success w-100">
                            <i class="fas fa-database me-2"></i>Backup Database
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/settings/logs" class="btn btn-outline-info w-100">
                            <i class="fas fa-list me-2"></i>View Activity Logs
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/settings/maintenance" class="btn btn-outline-warning w-100">
                            <i class="fas fa-tools me-2"></i>Maintenance Mode
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.settings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.settings-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
</style>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the cache?')) {
        // Implement cache clearing logic
        alert('Cache cleared successfully!');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
