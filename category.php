<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$categoryStmt = $pdo->prepare("
    SELECT id, name, slug, description
    FROM categories
    WHERE slug = ? AND status = 'active' AND category_type = 'topic'
    LIMIT 1
");
$categoryStmt->execute([$slug]);
$category = $categoryStmt->fetch();

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Category Not Found';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-5 text-center">
        <h1 class="h3">Category not found</h1>
        <p class="text-muted">The requested category does not exist or is inactive.</p>
        <a href="<?= e(url('index.php')) ?>" class="btn btn-primary">Back to homepage</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM articles a
    WHERE (a.category_id = ? OR EXISTS (SELECT 1 FROM article_categories ac WHERE ac.article_id=a.id AND ac.category_id=?)) AND a.status = 'published' AND a.deleted_at IS NULL
");
$countStmt->execute([(int)$category['id'], (int)$category['id']]);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalArticles / $perPage));

$articlesStmt = $pdo->prepare("
    SELECT id, title, slug, excerpt, content, featured_image,
           source_name, published_at, created_at
    FROM articles a
    WHERE (a.category_id = :category_id OR EXISTS (SELECT 1 FROM article_categories ac WHERE ac.article_id=a.id AND ac.category_id=:category_id2)) AND a.status = 'published' AND a.deleted_at IS NULL
    ORDER BY COALESCE(published_at, created_at) DESC
    LIMIT :limit OFFSET :offset
");
$articlesStmt->bindValue(':category_id', (int)$category['id'], PDO::PARAM_INT);
$articlesStmt->bindValue(':category_id2', (int)$category['id'], PDO::PARAM_INT);
$articlesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$articlesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$articlesStmt->execute();
$articles = $articlesStmt->fetchAll();

$pageTitle = $category['name'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="section-title">
        <div>
            <h1 class="h2 mb-1"><?= e($category['name']) ?> News</h1>
            <?php if (!empty($category['description'])): ?>
                <p class="text-muted mb-0"><?= e($category['description']) ?></p>
            <?php endif; ?>
        </div>
        <span class="text-muted"><?= number_format($totalArticles) ?> articles</span>
    </div>

    <?php if (!$articles): ?>
        <div class="alert alert-info">There are no published articles in this category yet.</div>
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
                            <h2 class="news-title">
                                <a href="<?= e(articleUrl((int)$article['id'], $article['slug'])) ?>">
                                    <?= e($article['title']) ?>
                                </a>
                            </h2>
                            <p class="news-excerpt">
                                <?= e(createExcerpt($article['excerpt'] ?: $article['content'], 160)) ?>
                            </p>
                            <div class="news-meta">
                                <?= e(formatNewsDate($article['published_at'] ?: $article['created_at'])) ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Category pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="<?= e(categoryUrl($category['slug']) . '&page=' . $i) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
