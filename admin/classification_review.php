<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['author']);

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;
$count = (int)$pdo->query("SELECT COUNT(*) FROM articles WHERE article_type='rss' AND classification_needs_review=1 AND deleted_at IS NULL")->fetchColumn();
$stmt = $pdo->prepare("SELECT a.id,a.title,a.region,a.country_code,a.classification_confidence,a.classification_notes,a.published_at,c.name AS category_name,a.source_name FROM articles a LEFT JOIN categories c ON c.id=a.category_id WHERE a.article_type='rss' AND a.classification_needs_review=1 AND a.deleted_at IS NULL ORDER BY a.classification_confidence ASC,a.published_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1,$perPage,PDO::PARAM_INT);
$stmt->bindValue(2,$offset,PDO::PARAM_INT);
$stmt->execute();
$articles=$stmt->fetchAll();
$totalPages=max(1,(int)ceil($count/$perPage));
$pageTitle='Classification Review';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h1 class="h2 mb-1">Classification Review</h1><p class="text-muted mb-0">Review RSS articles whose automatic topic or geographical classification is uncertain.</p></div>
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/articles.php')) ?>">Back to newsroom</a>
  </div>
  <div class="alert alert-info">Automatic RSS publication remains enabled. This queue does not unpublish articles; it helps Authors correct questionable classifications.</div>
  <div class="card shadow-sm"><div class="card-header bg-white"><strong><?= number_format($count) ?> articles need classification review</strong></div><div class="card-body">
  <?php if (!$articles): ?><p class="text-muted mb-0">No RSS classifications currently need review.</p><?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Article</th><th>Current topic</th><th>Coverage</th><th>Confidence</th><th>Reason</th><th></th></tr></thead><tbody>
  <?php foreach($articles as $a): ?><tr>
    <td><strong><?= e($a['title']) ?></strong><div class="small text-muted"><?= e($a['source_name'] ?? 'RSS source') ?></div></td>
    <td><?= e($a['category_name'] ?? 'Uncategorized') ?></td>
    <td><?= e(ucfirst($a['region'])) ?><?= $a['country_code'] ? ' · '.e($a['country_code']) : '' ?></td>
    <td><?= $a['classification_confidence'] !== null ? number_format((float)$a['classification_confidence'],0).'%' : '—' ?></td>
    <td class="small text-muted"><?= e($a['classification_notes'] ?? 'Please verify classification') ?></td>
    <td><a class="btn btn-sm btn-primary" href="<?= e(url('admin/edit_article.php?id='.(int)$a['id'])) ?>">Review / Correct</a></td>
  </tr><?php endforeach; ?></tbody></table></div>
  <?php if($totalPages>1): ?><nav><ul class="pagination mb-0"><?php for($i=1;$i<=$totalPages;$i++): ?><li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
  <?php endif; ?></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
