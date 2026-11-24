<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['author']);
$notice=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verifyCsrfToken();
  $id=(int)($_POST['article_id']??0); $action=(string)($_POST['action']??'');
  try {
    if ($id<1) throw new RuntimeException('Invalid article.');
    if ($action==='schedule') {
      $when=str_replace('T',' ',(string)($_POST['scheduled_at']??''));
      $dt=DateTime::createFromFormat('Y-m-d H:i',$when);
      if (!$dt || $dt <= new DateTime()) throw new RuntimeException('Choose a valid future date and time.');
      $q=$pdo->prepare("UPDATE articles SET status='draft', scheduled_at=?, published_at=NULL, updated_at=NOW() WHERE id=? AND deleted_at IS NULL"); $q->execute([$dt->format('Y-m-d H:i:s'),$id]);
      $notice='Article scheduled successfully.';
    } elseif ($action==='breaking') {
      $q=$pdo->prepare("UPDATE articles SET is_breaking=IF(is_breaking=1,0,1), updated_at=NOW() WHERE id=? AND status='published' AND deleted_at IS NULL"); $q->execute([$id]); $notice='Breaking-news setting updated.';
    } elseif ($action==='restore') {
      $q=$pdo->prepare("UPDATE articles SET deleted_at=NULL, updated_at=NOW() WHERE id=? AND deleted_at IS NOT NULL"); $q->execute([$id]); $notice='Article restored. Review its status before republishing.';
    } else throw new RuntimeException('Unknown action.');
    if (function_exists('logAdminActivity')) logAdminActivity($pdo,'editorial_'.$action,'Editorial action '.$action.' for article '.$id,$id);
  } catch (Throwable $e) { $error=$e instanceof RuntimeException ? $e->getMessage() : 'Could not update the article.'; if (!$e instanceof RuntimeException) error_log($e->getMessage()); }
}
$scheduled=$pdo->query("SELECT id,title,scheduled_at,status FROM articles WHERE scheduled_at IS NOT NULL AND deleted_at IS NULL ORDER BY scheduled_at ASC LIMIT 100")->fetchAll();
$unscheduled=$pdo->query("SELECT id,title,status FROM articles WHERE scheduled_at IS NULL AND deleted_at IS NULL AND status IN ('draft','pending','published') ORDER BY updated_at DESC LIMIT 100")->fetchAll();
$published=$pdo->query("SELECT id,title,slug,is_breaking FROM articles WHERE status='published' AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 100")->fetchAll();
$deleted=$pdo->query("SELECT id,title,deleted_at FROM articles WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT 100")->fetchAll();
$pageTitle='Editorial Tools'; require_once __DIR__.'/../includes/header.php';
?>
<div class="container py-5"><div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h2">Editorial Tools</h1><p class="text-muted mb-0">Schedule publication, manage breaking news and recover deleted articles.</p></div><a class="btn btn-outline-secondary" href="<?= e(url('admin/articles.php')) ?>">Back to newsroom</a></div>
<?php if($notice): ?><div class="alert alert-success"><?= e($notice) ?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="card mb-4"><div class="card-header fw-bold">Schedule an article</div><div class="card-body"><p class="text-muted">Choose a draft, pending or published article and set a future publication time.</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Article</th><th>Status</th><th>Schedule</th></tr></thead><tbody><?php foreach($unscheduled as $a): ?><tr><td><?= e($a['title']) ?></td><td><?= e(ucfirst($a['status'])) ?></td><td><form method="post" class="d-flex gap-2"><?= csrfField() ?><input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="schedule"><input class="form-control form-control-sm" type="datetime-local" name="scheduled_at" required><button class="btn btn-sm btn-primary">Schedule</button></form></td></tr><?php endforeach; ?><?php if(!$unscheduled): ?><tr><td colspan="3" class="text-muted">No eligible articles.</td></tr><?php endif; ?></tbody></table></div></div></div>
<div class="card mb-4"><div class="card-header fw-bold">Scheduled articles</div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Article</th><th>Scheduled time</th><th>Set schedule</th></tr></thead><tbody><?php foreach($scheduled as $a): ?><tr><td><?= e($a['title']) ?></td><td><?= e($a['scheduled_at']) ?></td><td><form method="post" class="d-flex gap-2"><?= csrfField() ?><input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="schedule"><input class="form-control form-control-sm" type="datetime-local" name="scheduled_at" required><button class="btn btn-sm btn-primary">Reschedule</button></form></td></tr><?php endforeach; ?><?php if(!$scheduled): ?><tr><td colspan="3" class="text-muted">No scheduled articles.</td></tr><?php endif; ?></tbody></table></div></div></div>
<div class="card mb-4"><div class="card-header fw-bold">Breaking news controls</div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Published article</th><th>Breaking status</th><th>Action</th></tr></thead><tbody><?php foreach($published as $a): ?><tr><td><?= e($a['title']) ?></td><td><?= $a['is_breaking']?'Breaking':'Regular' ?></td><td><form method="post"><?= csrfField() ?><input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="breaking"><button class="btn btn-sm btn-outline-danger">Toggle breaking</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<div class="card"><div class="card-header fw-bold">Recently deleted articles</div><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Article</th><th>Deleted</th><th>Action</th></tr></thead><tbody><?php foreach($deleted as $a): ?><tr><td><?= e($a['title']) ?></td><td><?= e($a['deleted_at']) ?></td><td><form method="post" onsubmit="return confirm('Restore this article?')"><?= csrfField() ?><input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="restore"><button class="btn btn-sm btn-success">Restore</button></form></td></tr><?php endforeach; ?><?php if(!$deleted): ?><tr><td colspan="3" class="text-muted">Trash is empty.</td></tr><?php endif; ?></tbody></table></div></div></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
