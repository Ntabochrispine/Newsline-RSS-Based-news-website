<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);
$page=max(1,(int)($_GET['page']??1)); $perPage=50;
$total=(int)$pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
$totalPages=max(1,(int)ceil($total/$perPage)); $page=min($page,$totalPages); $offset=($page-1)*$perPage;
$stmt=$pdo->prepare("SELECT l.*,u.username FROM audit_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC,l.id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1,$perPage,PDO::PARAM_INT); $stmt->bindValue(2,$offset,PDO::PARAM_INT); $stmt->execute(); $logs=$stmt->fetchAll();
$pageTitle='Admin Activity Log'; require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><div><h1 class="h2">Admin Activity Log</h1><p class="text-muted mb-0">Important administrative and editorial actions.</p></div><a href="<?= e(url('admin/index.php')) ?>" class="btn btn-outline-secondary">Admin Dashboard</a></div>
<div class="card shadow-sm"><div class="card-body">
<?php if (!$logs): ?><p class="text-muted mb-0">No activity has been recorded yet.</p>
<?php else: ?>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>
<?php foreach($logs as $log): ?><tr><td><?= e(formatNewsDate($log['created_at'])) ?></td><td><?= e($log['username']??'System') ?></td><td><span class="badge bg-secondary"><?= e(str_replace('_',' ',ucfirst($log['action']))) ?></span></td><td><?= e($log['description']) ?></td><td><?= e($log['ip_address']??'') ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if($totalPages>1): ?><nav><ul class="pagination mb-0"><?php for($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="<?= e(url('admin/activity.php?page='.$i)) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php endif; ?></div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
