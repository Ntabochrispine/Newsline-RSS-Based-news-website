<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');
function sitemapXml(string $value): string { return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8'); }
$articles = $pdo->query("SELECT slug, updated_at, published_at, created_at FROM articles WHERE status = 'published' AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT 5000")->fetchAll();
$categories = $pdo->query("SELECT slug, updated_at FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?= sitemapXml(absoluteUrl('')) ?></loc><changefreq>hourly</changefreq><priority>1.0</priority></url>
    <url><loc><?= sitemapXml(absoluteUrl('latest.php')) ?></loc><changefreq>hourly</changefreq><priority>0.8</priority></url>
    <?php foreach ($categories as $category): ?>
        <url><loc><?= sitemapXml(absoluteUrl('category.php?slug=' . rawurlencode($category['slug']))) ?></loc><changefreq>daily</changefreq><priority>0.7</priority></url>
    <?php endforeach; ?>
    <?php foreach ($articles as $article): ?>
        <?php $date = $article['updated_at'] ?: ($article['published_at'] ?: $article['created_at']); ?>
        <url><loc><?= sitemapXml(absoluteUrl('article.php?slug=' . rawurlencode($article['slug']))) ?></loc><lastmod><?= sitemapXml(date('Y-m-d', strtotime($date))) ?></lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>
    <?php endforeach; ?>
</urlset>
