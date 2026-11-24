<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../fetch_rss.php';
requireRole(['author']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required.'); }
verifyCsrfToken();
$rows=$pdo->query("SELECT id,title,excerpt,content FROM articles WHERE article_type='rss' AND deleted_at IS NULL AND classification_source <> 'author_review' ORDER BY id")->fetchAll();
$update=$pdo->prepare("UPDATE articles SET category_id=?, region=?, country_code=?, classification_confidence=?, classification_source='rss_rules', classification_needs_review=?, classification_notes=? WHERE id=?");
$count=0;
foreach($rows as $row){$c=classifyRssArticle($pdo,$row);$update->execute([$c['category_id'],$c['region'],$c['country_code'],$c['confidence'],$c['needs_review'],$c['notes'],$row['id']]);$count++;}
$_SESSION['rss_reclassified_count']=$count;
header('Location: '.url('admin/articles.php?reclassified=1')); exit;
