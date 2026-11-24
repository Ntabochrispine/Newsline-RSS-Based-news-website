<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);

$checks = [];
$checks[] = ['name' => 'PHP version 8.0 or newer', 'ok' => version_compare(PHP_VERSION, '8.0.0', '>='), 'detail' => 'Running PHP ' . PHP_VERSION];
foreach (['pdo_mysql', 'curl', 'simplexml', 'fileinfo', 'mbstring'] as $extension) {
    $checks[] = ['name' => 'PHP extension: ' . $extension, 'ok' => extension_loaded($extension), 'detail' => extension_loaded($extension) ? 'Enabled' : 'Not enabled. Enable it in XAMPP php.ini and restart Apache.'];
}

$requiredTables = ['users', 'categories', 'rss_feeds', 'articles', 'article_views', 'article_categories', 'article_revisions', 'audit_logs', 'tags', 'article_tags'];
foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query('SELECT 1 FROM `' . $table . '` LIMIT 1');
        $stmt->fetchColumn();
        $checks[] = ['name' => 'Database table: ' . $table, 'ok' => true, 'detail' => 'Available'];
    } catch (PDOException $exception) {
        $checks[] = ['name' => 'Database table: ' . $table, 'ok' => false, 'detail' => 'Missing or inaccessible'];
    }
}

$uploadDir = __DIR__ . '/../assets/images/uploads';
$uploadReady = is_dir($uploadDir) && is_writable($uploadDir);
$checks[] = ['name' => 'Article image upload directory', 'ok' => $uploadReady, 'detail' => $uploadReady ? 'Directory exists and is writable' : 'Create the directory and grant PHP write permission'];
$checks[] = ['name' => 'Application URL configuration', 'ok' => BASE_URL !== '' && SITE_URL !== '', 'detail' => 'BASE_URL=' . BASE_URL . ' | SITE_URL=' . SITE_URL . ' (update SITE_URL before production)'];

$passed = count(array_filter($checks, fn(array $check): bool => $check['ok']));
$total = count($checks);
$pageTitle = 'System Health Check';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h1 class="h2 mb-1">System Health Check</h1><p class="text-muted mb-0">Check whether this installation is ready for normal operation.</p></div>
        <a class="btn btn-outline-secondary" href="<?= e(url('admin/index.php')) ?>">Back to dashboard</a>
    </div>
    <div class="alert <?= $passed === $total ? 'alert-success' : 'alert-warning' ?>">
        <strong><?= $passed ?> of <?= $total ?> checks passed.</strong>
        <?= $passed === $total ? 'The basic environment checks are passing.' : 'Resolve the failed checks before relying on the affected features.' ?>
    </div>
    <div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead><tbody>
        <?php foreach ($checks as $check): ?>
            <tr><td><?= e($check['name']) ?></td><td><span class="badge <?= $check['ok'] ? 'bg-success' : 'bg-danger' ?>"><?= $check['ok'] ? 'PASS' : 'FAIL' ?></span></td><td><?= e($check['detail']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
    <p class="small text-muted mt-3">This page checks application prerequisites and table availability. It does not verify live RSS publisher availability or the contents of every database record.</p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
