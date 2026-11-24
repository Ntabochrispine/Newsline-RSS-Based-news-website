<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$slug = trim((string)($_GET['slug'] ?? ''));

if (!$articleId && $slug === '') {
    http_response_code(404);
    $article = null;
} else {
    if ($articleId) {
        $stmt = $pdo->prepare("
            SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                   u.first_name, u.last_name, u.username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            LEFT JOIN users u ON u.id = a.user_id
            WHERE a.id = ? AND a.status = 'published'
              AND a.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$articleId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                   u.first_name, u.last_name, u.username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            LEFT JOIN users u ON u.id = a.user_id
            WHERE a.slug = ? AND a.status = 'published'
              AND a.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$slug]);
    }
    $article = $stmt->fetch();
}

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article Not Found';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-5 text-center">
        <h1 class="h3">Article not found</h1>
        <p class="text-muted">This article may have been removed or is not publicly available.</p>
        <a class="btn btn-primary" href="<?= e(url('index.php')) ?>">Return to homepage</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Record one row for each page view. The dashboard uses this table for trending articles.
try {
    $view = $pdo->prepare("
        INSERT INTO article_views (article_id, ip_address, user_agent, viewed_at)
        VALUES (?, ?, ?, NOW())
    ");
    $view->execute([
        (int)$article['id'],
        substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500)
    ]);
} catch (PDOException $exception) {
    error_log('Article view tracking failed: ' . $exception->getMessage());
}

$count = $pdo->prepare("SELECT COUNT(*) FROM article_views WHERE article_id = ?");
$count->execute([(int)$article['id']]);
$views = (int)$count->fetchColumn();

$related = [];
if (!empty($article['category_id'])) {
    $relatedStmt = $pdo->prepare("
        SELECT id, title, slug, excerpt, featured_image, published_at, created_at
        FROM articles
        WHERE category_id = ? AND id <> ?
          AND status = 'published' AND deleted_at IS NULL
        ORDER BY COALESCE(published_at, created_at) DESC
        LIMIT 4
    ");
    $relatedStmt->execute([(int)$article['category_id'], (int)$article['id']]);
    $related = $relatedStmt->fetchAll();
}

$articleTopics = getArticleTopics($pdo, (int)$article['id']);
if (!$articleTopics && !empty($article['category_name'])) { $articleTopics = [['name'=>$article['category_name'], 'slug'=>$article['category_slug']]]; }

$author = trim((string)($article['first_name'] ?? '') . ' ' . (string)($article['last_name'] ?? ''));
if ($author === '') {
    $author = (string)($article['source_name'] ?: 'RSS News Editorial Team');
}
$published = $article['published_at'] ?: $article['created_at'];
$pageTitle = $article['title'];
$metaDescription = createExcerpt($article['excerpt'] ?: $article['content'], 160);
$canonicalUrl = absoluteUrl('article.php?slug=' . rawurlencode($article['slug']));
$openGraphType = 'article';
$metaImage = newsImageUrl($article['featured_image'] ?? null);
if ($metaImage && !preg_match('~^https?://~i', $metaImage)) { $metaImage = absoluteUrl($metaImage); }
require_once __DIR__ . '/includes/header.php';

$sourceUrl = (string)($article['source_url'] ?? '');
$sourceScheme = strtolower((string)parse_url($sourceUrl, PHP_URL_SCHEME));
$validSourceUrl = filter_var($sourceUrl, FILTER_VALIDATE_URL)
    && in_array($sourceScheme, ['http', 'https'], true);
?>
<div class="container py-4">
    <div class="mb-3 small">
        <a href="<?= e(url('index.php')) ?>">Home</a>
        <?php if (!empty($article['category_name'])): ?>
            <span class="text-muted mx-2">/</span>
            <a href="<?= e(categoryUrl($article['category_slug'])) ?>"><?= e($article['category_name']) ?></a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <main class="col-lg-8">
            <article class="article-content">
                <?php if ($articleTopics): ?>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($articleTopics as $topic): ?>
                            <a class="badge bg-primary text-decoration-none" href="<?= e(categoryUrl($topic['slug'])) ?>"><?= e($topic['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($article['is_breaking'])): ?><div class="mb-3"><span class="badge bg-danger"><i class="bi bi-lightning-fill"></i> BREAKING NEWS</span></div><?php endif; ?>
                <h1 class="fw-bold mb-3"><?= e($article['title']) ?></h1>

                <?php if (!empty($article['excerpt'])): ?>
                    <p class="lead text-muted mb-4"><?= e($article['excerpt']) ?></p>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-3 text-muted small mb-4">
                    <span><i class="bi bi-person"></i> <?= e($author) ?></span>
                    <span><i class="bi bi-calendar3"></i> <?= e(formatNewsDate($published)) ?></span>
                    <span><i class="bi bi-eye"></i> <?= number_format($views) ?> views</span>
                </div>

                <?php $image = newsImageUrl($article['featured_image'] ?? null); ?>
                <?php if ($image): ?>
                    <img src="<?= e($image) ?>" alt="<?= e($article['title']) ?>"
                         class="img-fluid w-100 rounded mb-4"
                         style="max-height:480px;object-fit:cover"
                         onerror="this.style.display='none'">
                <?php endif; ?>

                <div class="article-body fs-5" style="line-height:1.9">
                    <?php
                    // RSS HTML is treated as untrusted. Display readable text, not executable markup.
                    $body = html_entity_decode(
                        strip_tags((string)($article['content'] ?? '')),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );
                    echo nl2br(e($body));
                    ?>
                </div>

                <?php if (($article['article_type'] ?? '') === 'rss' && $validSourceUrl): ?>
                    <div class="alert alert-light border mt-4">
                        <strong>Original publisher:</strong> <?= e($article['source_name'] ?: 'External source') ?>
                        <div class="mt-2">
                            <a class="btn btn-outline-primary btn-sm" href="<?= e($sourceUrl) ?>"
                               target="_blank" rel="noopener noreferrer">
                                Read at original source <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="border-top mt-4 pt-3">
                    <strong class="d-block mb-2">Share this article</strong>
                    <?php $shareUrl = absoluteUrl('article.php?slug=' . rawurlencode($article['slug'])); ?>
                    <?php $shareText = rawurlencode($article['title'] . ' ' . $shareUrl); ?>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=<?= e(rawurlencode($shareUrl)) ?>"><i class="bi bi-facebook"></i> Facebook</a>
                        <a class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?text=<?= e($shareText) ?>"><i class="bi bi-twitter-x"></i> X</a>
                        <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=<?= e($shareText) ?>"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
            </article>
        </main>

        <aside class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Article details</h2>
                    <p class="mb-2"><strong>Author/Source:</strong> <?= e($author) ?></p>
                    <p class="mb-2"><strong>Published:</strong> <?= e(formatNewsDate($published)) ?></p>
                    <p class="mb-0"><strong>Views:</strong> <?= number_format($views) ?></p>
                </div>
            </div>

            <?php if ($related): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3">Related news</h2>
                        <?php foreach ($related as $item): ?>
                            <div class="d-flex gap-3 mb-3">
                                <?php $relatedImage = newsImageUrl($item['featured_image'] ?? null); ?>
                                <?php if ($relatedImage): ?>
                                    <img src="<?= e($relatedImage) ?>" alt=""
                                         class="rounded" style="width:88px;height:70px;object-fit:cover"
                                         onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div>
                                    <a class="fw-semibold text-decoration-none"
                                       href="<?= e(articleUrl((int)$item['id'], $item['slug'])) ?>">
                                        <?= e($item['title']) ?>
                                    </a>
                                    <div class="small text-muted mt-1">
                                        <?= e(formatNewsDate($item['published_at'] ?: $item['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
