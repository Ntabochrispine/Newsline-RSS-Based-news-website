<?php
declare(strict_types=1);
require_once __DIR__.'/../config/database.php'; require_once __DIR__.'/../includes/functions.php'; require_once __DIR__.'/../includes/auth.php'; requireLogin();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Preview must be submitted from the article form.');}
verifyCsrfToken();
$title=trim((string)($_POST['title']??''));$excerpt=trim((string)($_POST['excerpt']??''));$content=trim((string)($_POST['content']??''));$region=(string)($_POST['region']??'world');$ids=array_values(array_unique(array_map('intval',(array)($_POST['category_ids']??[]))));
if($title===''||$content===''){http_response_code(422);exit('Enter a headline and article content before previewing.');}
$topics=[];if($ids){$ph=implode(',',array_fill(0,count($ids),'?'));$q=$pdo->prepare("SELECT name FROM categories WHERE id IN ($ph) AND category_type='topic'");$q->execute($ids);$topics=$q->fetchAll(PDO::FETCH_COLUMN);}
$pageTitle='Article Preview';require_once __DIR__.'/../includes/header.php';
?>
<div class="container py-5"><div class="alert alert-info"><strong>Preview only:</strong> This article has not been saved or published.</div><article class="mx-auto" style="max-width:850px"><div class="d-flex gap-2 flex-wrap mb-3"><?php foreach($topics as $t): ?><span class="badge bg-primary"><?= e($t) ?></span><?php endforeach; ?><span class="badge bg-secondary"><?= e(ucfirst($region)) ?></span></div><h1 class="display-5 fw-bold"><?= e($title) ?></h1><?php if($excerpt): ?><p class="lead text-muted"><?= e($excerpt) ?></p><?php endif; ?><hr><div class="fs-5" style="line-height:1.9"><?= nl2br(e($content)) ?></div></article><div class="text-center mt-4"><button class="btn btn-outline-secondary" onclick="window.close()">Close preview</button></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
