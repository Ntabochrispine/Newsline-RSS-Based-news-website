<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;
$articles = [];
$totalArticles = 0;
$totalPages = 1;

if ($query !== '') {
    $term = '%' . $query . '%';
    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM articles a
        WHERE a.status = 'published' AND a.deleted_at IS NULL
          AND (a.title LIKE :term1 OR a.excerpt LIKE :term2 OR a.content LIKE :term3 OR a.source_name LIKE :term4)
    ");
    $countStmt->execute([
        ':term1' => $term, ':term2' => $term,
        ':term3' => $term, ':term4' => $term
    ]);
    $totalArticles = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalArticles / $perPage));

    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image,
               a.source_name, a.published_at, a.created_at, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        WHERE a.status = 'published' AND a.deleted_at IS NULL
          AND (a.title LIKE :term1 OR a.excerpt LIKE :term2 OR a.content LIKE :term3 OR a.source_name LIKE :term4)
        ORDER BY COALESCE(a.published_at, a.created_at) DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':term1', $term, PDO::PARAM_STR);
    $stmt->bindValue(':term2', $term, PDO::PARAM_STR);
    $stmt->bindValue(':term3', $term, PDO::PARAM_STR);
    $stmt->bindValue(':term4', $term, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll();
}

$pageTitle = $query === '' ? 'Search News' : 'Search: ' . $query;
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="section-title">
        <div>
            <h1 class="h2 mb-1">Search News</h1>
            <?php if ($query !== ''): ?>
                <p class="text-muted mb-0">
                    <?= number_format($totalArticles) ?> result(s) for
                    <strong><?= e($query) ?></strong>
                </p>
            <?php else: ?>
                <p class="text-muted mb-0">Find articles by title, content or publisher.</p>
            <?php endif; ?>
        </div>
    </div>

    <form action="<?= e(url('search.php')) ?>" method="GET" class="mb-4">
        <div class="input-group input-group-lg">
            <input type="search" class="form-control" name="q"
                   value="<?= e($query) ?>" placeholder="What are you looking for?" required>
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    <?php if ($query === ''): ?>
        <div class="alert alert-info">Enter a keyword to search published news articles.</div>
    <?php elseif (!$articles): ?>
        <div class="alert alert-warning">No published articles matched your search. Try different keywords.</div>
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
                            <?php if (!empty($article['category_name'])): ?>
                                <span class="news-category"><?= e($article['category_name']) ?></span>
                            <?php endif; ?>
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
            <nav class="mt-5" aria-label="Search pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="<?= e(searchUrl($query) . '&page=' . $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
