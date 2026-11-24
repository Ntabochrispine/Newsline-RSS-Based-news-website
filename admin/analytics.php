<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);

function analyticsCount(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

$totalViews = analyticsCount($pdo, 'SELECT COUNT(*) FROM article_views');
$publishedUser = analyticsCount($pdo, "SELECT COUNT(*) FROM articles WHERE article_type='user' AND status='published' AND deleted_at IS NULL");
$publishedRss = analyticsCount($pdo, "SELECT COUNT(*) FROM articles WHERE article_type='rss' AND status='published' AND deleted_at IS NULL");
$pending = analyticsCount($pdo, "SELECT COUNT(*) FROM articles WHERE status='pending' AND deleted_at IS NULL");
$drafts = analyticsCount($pdo, "SELECT COUNT(*) FROM articles WHERE status='draft' AND deleted_at IS NULL");
$rejected = analyticsCount($pdo, "SELECT COUNT(*) FROM articles WHERE status='rejected' AND deleted_at IS NULL");

$categoryStmt = $pdo->query("\n    SELECT c.name, COUNT(a.id) AS article_count\n    FROM categories c\n    LEFT JOIN articles a ON a.category_id=c.id AND a.status='published' AND a.deleted_at IS NULL\n    WHERE c.status='active'\n    GROUP BY c.id, c.name\n    ORDER BY article_count DESC, c.name ASC\n    LIMIT 12\n");
$categories = $categoryStmt->fetchAll();

$popularStmt = $pdo->query("\n    SELECT a.id, a.title, a.slug, COUNT(v.id) AS views\n    FROM articles a\n    LEFT JOIN article_views v ON v.article_id=a.id\n    WHERE a.status='published' AND a.deleted_at IS NULL\n    GROUP BY a.id, a.title, a.slug\n    ORDER BY views DESC, a.published_at DESC\n    LIMIT 10\n");
$popularArticles = $popularStmt->fetchAll();

$dailyStmt = $pdo->query("\n    SELECT DATE(created_at) AS day, COUNT(*) AS article_count\n    FROM articles\n    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)\n      AND deleted_at IS NULL\n    GROUP BY DATE(created_at)\n    ORDER BY day ASC\n");
$dailyRows = $dailyStmt->fetchAll();
$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[$row['day']] = (int)$row['article_count'];
}

$daily = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $daily[] = ['day' => $day, 'count' => $dailyMap[$day] ?? 0];
}

$pageTitle = 'Analytics';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">News Analytics</h1>
            <p class="text-muted mb-0">Content, readership and publishing activity overview.</p>
        </div>
        <a href="<?= e(url('admin/index.php')) ?>" class="btn btn-outline-secondary">Admin Dashboard</a>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Published User', $publishedUser, 'primary'],
            ['Published RSS', $publishedRss, 'info'],
            ['Pending Review', $pending, 'warning'],
            ['Drafts', $drafts, 'secondary'],
            ['Rejected', $rejected, 'danger'],
            ['Total Views', number_format($totalViews), 'success'],
        ];
        foreach ($cards as [$label, $value, $class]):
        ?>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted"><?= e($label) ?></div>
                    <div class="fs-3 fw-bold text-<?= e($class) ?>"><?= e((string)$value) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>Articles Created — Last 7 Days</strong></div>
                <div class="card-body">
                    <?php $maxDaily = max(1, max(array_column($daily, 'count'))); ?>
                    <?php foreach ($daily as $row): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?= e(date('D, M j', strtotime($row['day']))) ?></span>
                                <strong><?= (int)$row['count'] ?></strong>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= (int)round(($row['count'] / $maxDaily) * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>Published Articles by Category</strong></div>
                <div class="card-body">
                    <?php if (!$categories): ?>
                        <p class="text-muted mb-0">No published category data yet.</p>
                    <?php else: ?>
                        <?php $maxCategory = max(1, max(array_map(fn($r) => (int)$r['article_count'], $categories))); ?>
                        <?php foreach ($categories as $row): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= e($row['name']) ?></span>
                                    <strong><?= (int)$row['article_count'] ?></strong>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-info" style="width: <?= (int)round(((int)$row['article_count'] / $maxCategory) * 100) ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>Most Viewed Published Articles</strong></div>
                <div class="card-body p-0">
                    <?php if (!$popularArticles): ?>
                        <p class="p-3 text-muted mb-0">No published articles have recorded views yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>Article</th><th>Views</th><th>Open</th></tr></thead>
                                <tbody>
                                <?php foreach ($popularArticles as $row): ?>
                                    <tr>
                                        <td><?= e($row['title']) ?></td>
                                        <td><span class="badge bg-primary"><?= number_format((int)$row['views']) ?></span></td>
                                        <td><a href="<?= e(articleUrl((int)$row['id'], $row['slug'])) ?>" target="_blank" rel="noopener">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
