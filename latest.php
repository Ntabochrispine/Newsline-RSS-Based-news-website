<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;
$region = (string)($_GET['region'] ?? '');
$validRegions = ['kenya', 'africa', 'world'];
if (!in_array($region, $validRegions, true)) $region = '';
$whereRegion = $region === 'africa' ? " AND region IN ('africa','kenya')" : ($region !== '' ? ' AND region = ' . $pdo->quote($region) : '');

$totalArticles = (int)$pdo->query("
    SELECT COUNT(*) FROM articles
    WHERE status = 'published' AND deleted_at IS NULL" . $whereRegion)->fetchColumn();

$totalPages = max(1, (int)ceil($totalArticles / $perPage));

$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image,
           a.published_at, a.created_at, c.name AS category_name
    FROM articles a
    LEFT JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published' AND a.deleted_at IS NULL" . $whereRegion . "
    ORDER BY COALESCE(a.published_at, a.created_at) DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

$pageTitle = $region === '' ? 'Latest News' : ucfirst($region) . ' News';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="section-title">
        <h1 class="h2 mb-0"><?= e($pageTitle) ?></h1>
        <span class="text-muted"><?= number_format($totalArticles) ?> published articles</span>
    </div>

    <?php if (!$articles): ?>
        <div class="alert alert-info">There are no published articles yet.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $article): ?>
                <div class="col-lg-4 col-md-6">
                    <article class="news-card h-100">
                        <?php $image = newsImageUrl($article['featured_image'] ?? null); ?>
                        <?php if ($image): ?>
                            <img src="<?= e($image) ?>" alt="<?= e($article['title']) ?>"
                                 class="news-card-image" onerror="this.style.display='none'">
                        <?php else: ?>
                            <div class="news-card-image d-flex align-items-center justify-content-center">
                                <i class="bi bi-newspaper" style="font-size:48px"></i>
                            </div>
                        <?php endif; ?>
                        <div class="news-card-body">
                            <?php if ($article['category_name']): ?>
                                <span class="news-category"><?= e($article['category_name']) ?></span>
                            <?php endif; ?>
                            <h2 class="news-title">
                                <a href="<?= e(articleUrl((int)$article['id'], $article['slug'])) ?>"><?= e($article['title']) ?></a>
                            </h2>
                            <p class="news-excerpt"><?= e(createExcerpt($article['excerpt'] ?: $article['content'], 160)) ?></p>
                            <div class="news-meta"><?= e(formatNewsDate($article['published_at'] ?: $article['created_at'])) ?></div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Latest news pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e(url('latest.php?' . http_build_query(array_filter(['page' => $i, 'region' => $region])))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
