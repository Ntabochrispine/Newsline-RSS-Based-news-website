<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole(['author']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyCsrfToken();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid article.'); }
$stmt=$pdo->prepare("SELECT * FROM articles WHERE id=? AND deleted_at IS NULL LIMIT 1"); $stmt->execute([$id]); $article=$stmt->fetch();
if (!$article) { http_response_code(404); exit('Article not found.'); }
$categories=$pdo->query("SELECT id,name FROM categories WHERE status='active' AND category_type='topic' ORDER BY name")->fetchAll();
$selectedCategoryIds=array_map('intval',array_column(getArticleTopics($pdo,(int)$id),'id'));
if (!$selectedCategoryIds && !empty($article['category_id'])) $selectedCategoryIds=[(int)$article['category_id']];
$errors=[];
$title=$article['title']; $excerpt=$article['excerpt']??''; $content=$article['content']??''; $categoryId=(string)($article['category_id']??''); $region=$article['region']??'world'; $countryCode=$article['country_code']??'';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $title=trim((string)($_POST['title']??'')); $excerpt=trim((string)($_POST['excerpt']??'')); $content=trim((string)($_POST['content']??''));
  $selectedCategoryIds=array_values(array_unique(array_map('intval',(array)($_POST['category_ids']??[])))); $categoryId=(string)($selectedCategoryIds[0]??''); $region=(string)($_POST['region']??'world'); $countryCode=strtoupper(trim((string)($_POST['country_code']??'')));
  if ($title==='' || textLength($title)<5 || textLength($title)>255) $errors[]='Title must be between 5 and 255 characters.';
  if ($content==='') $errors[]='Article content is required.';
  if (!in_array($region,['kenya','africa','world'],true)) $errors[]='Invalid geographical coverage.';
  if ($countryCode !== '' && !preg_match('/^[A-Z]{2}$/', $countryCode)) $errors[]='Country code must be two letters, for example KE or UG.';
  if (!$selectedCategoryIds) { $errors[]='Select at least one topic.'; } else {
    $ph=implode(',',array_fill(0,count($selectedCategoryIds),'?'));
    $cat=$pdo->prepare("SELECT COUNT(*) FROM categories WHERE id IN ($ph) AND status='active' AND category_type='topic'"); $cat->execute($selectedCategoryIds);
    if ((int)$cat->fetchColumn()!==count($selectedCategoryIds)) $errors[]='Select valid topic categories.';
  }
  if (!$errors) {
    // Preserve the previous version before applying editorial changes.
    $revision = $pdo->prepare("INSERT INTO article_revisions (article_id, user_id, title, excerpt, content, status, change_summary) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $revision->execute([$id, currentUserId(), $article['title'], $article['excerpt'], $article['content'], $article['status'], 'Before editorial update']);
    $newStatus=$article['status'];
    if ($article['article_type']==='user' && $article['status']==='published') $newStatus='published';
    $upd=$pdo->prepare("UPDATE articles SET title=?, slug=?, excerpt=?, content=?, category_id=?, region=?, country_code=?, classification_source='author_review', classification_confidence=100, classification_needs_review=0, classification_notes='Reviewed and corrected by author', status=?, updated_at=NOW() WHERE id=?");
    $upd->execute([$title,uniqueArticleSlug($pdo,$title,$id),$excerpt,$content,(int)$categoryId,$region,($countryCode !== '' ? $countryCode : null),$newStatus,$id]);
    saveArticleTopics($pdo,(int)$id,$selectedCategoryIds,'author_review');
    if (function_exists('logAdminActivity')) logAdminActivity($pdo,'article_edit','Editorially updated article: '.$id,$id);
    header('Location: '.url('admin/articles.php?updated=1')); exit;
  }
}
$pageTitle='Edit Article'; require_once __DIR__.'/../includes/header.php';
?>
<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-9">
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h2">Edit Article</h1><p class="text-muted mb-0">Editorial changes apply to the article shown on the public website.</p></div><a class="btn btn-outline-secondary" href="<?= e(url('admin/articles.php')) ?>">Back to newsroom</a></div>
<?php foreach($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="card card-body shadow-sm"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="mb-3"><label class="form-label">Headline</label><input class="form-control" name="title" maxlength="255" value="<?= e($title) ?>" required></div>
<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Topics</label><select class="form-select" name="category_ids[]" multiple size="6" required><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= in_array((int)$cat['id'],$selectedCategoryIds,true)?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select><div class="form-text">Select all applicable topics. Use Ctrl/Command for multiple selections.</div></div><div class="col-md-6"><label class="form-label">Geographical coverage</label><select class="form-select" name="region"><option value="kenya" <?= $region==='kenya'?'selected':'' ?>>Kenya</option><option value="africa" <?= $region==='africa'?'selected':'' ?>>Africa</option><option value="world" <?= $region==='world'?'selected':'' ?>>World</option></select></div></div>
<div class="mb-3"><label class="form-label">Country code (optional)</label><input class="form-control" name="country_code" maxlength="2" pattern="[A-Za-z]{2}" value="<?= e($countryCode) ?>" placeholder="KE, UG, NG, ZA"><div class="form-text">Use the two-letter ISO country code. Leave blank when the article is not about one specific country.</div></div>
<div class="mb-3"><label class="form-label">Excerpt</label><textarea class="form-control" rows="3" name="excerpt"><?= e($excerpt) ?></textarea></div>
<div class="mb-3"><label class="form-label">Article content</label><textarea class="form-control" rows="14" name="content" required><?= e($content) ?></textarea></div>
<div class="alert alert-info">Article type: <?= e(strtoupper($article['article_type'])) ?> · Current status: <?= e(ucfirst($article['status'])) ?>. RSS source attribution is retained.</div>
<div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-dark" type="submit" formaction="<?= e(url('user/preview_article.php')) ?>" formtarget="_blank">Preview Changes</button><button class="btn btn-primary" type="submit">Save Editorial Changes</button><a class="btn btn-outline-info" href="<?= e(url('admin/article_history.php?id='.$id)) ?>">Editorial History</a></div></form>
</div></div></div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>
