<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$stmt = $pdo->query("SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.source_name, a.published_at, a.created_at, c.name AS category_name FROM articles a LEFT JOIN categories c ON c.id = a.category_id WHERE a.status = 'published' AND a.deleted_at IS NULL ORDER BY COALESCE(a.published_at, a.created_at) DESC LIMIT 30");
$articles = $stmt->fetchAll();

function rssXml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
<channel>
    <title>RSS News Portal</title>
    <link><?= rssXml(absoluteUrl('')) ?></link>
    <description>Latest news from Kenya, Africa and around the world.</description>
    <language>en-ke</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <?php foreach ($articles as $article): ?>
        <?php $link = absoluteUrl('article.php?slug=' . rawurlencode($article['slug'])); ?>
        <item>
            <title><?= rssXml($article['title']) ?></title>
            <link><?= rssXml($link) ?></link>
            <guid isPermaLink="true"><?= rssXml($link) ?></guid>
            <description><?= rssXml(createExcerpt($article['excerpt'] ?: $article['content'], 300)) ?></description>
            <?php if (!empty($article['category_name'])): ?><category><?= rssXml($article['category_name']) ?></category><?php endif; ?>
            <?php $date = $article['published_at'] ?: $article['created_at']; ?>
            <pubDate><?= date(DATE_RSS, strtotime($date)) ?></pubDate>
        </item>
    <?php endforeach; ?>
</channel>
</rss>
