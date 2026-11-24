<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);

function adminCount(PDO $pdo, string $sql): int
{
    return (int) $pdo->query($sql)->fetchColumn();
}

$totalUsers = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM users"
);

$activeUsers = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM users WHERE status = 'active'"
);

$totalArticles = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM articles WHERE deleted_at IS NULL"
);

$pendingArticles = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM articles
     WHERE status = 'pending'
       AND article_type = 'user'
       AND deleted_at IS NULL"
);

$publishedArticles = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM articles
     WHERE status = 'published'
       AND deleted_at IS NULL"
);

$totalFeeds = adminCount(
    $pdo,
    "SELECT COUNT(*) FROM rss_feeds"
);

$recentStmt = $pdo->query("
    SELECT
        a.id,
        a.title,
        a.status,
        a.created_at,
        a.article_type,
        u.first_name,
        u.last_name,
        c.name AS category_name
    FROM articles a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.deleted_at IS NULL
    ORDER BY a.created_at DESC
    LIMIT 8
");

$recentArticles = $recentStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="mb-3"><a class="btn btn-outline-dark btn-sm" href="<?= e(url('admin/activity.php')) ?>"><i class="bi bi-clock-history"></i> Activity Log</a></div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2>Administration Dashboard</h2>
            <p class="text-muted mb-0">
                Manage articles, users and your news platform.
            </p>
        </div>

        <span class="badge bg-primary fs-6">
            <?= e(ucfirst(currentUserRole())) ?>
        </span>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Registered Users</div>
                    <h2><?= $totalUsers ?></h2>
                    <small class="text-success">
                        <?= $activeUsers ?> active accounts
                    </small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">All Articles</div>
                    <h2><?= $totalArticles ?></h2>
                    <small class="text-muted">Including RSS articles</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Pending Review</div>
                    <h2><?= $pendingArticles ?></h2>
                    <a href="<?= e(url('admin/articles.php?status=pending')) ?>">
                        Review submissions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">Published Articles</div>
                    <h2><?= $publishedArticles ?></h2>
                    <small class="text-success">Visible on the website</small>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-3 mb-5">

        <div class="col-md-3">
            <a href="<?= e(url('admin/articles.php')) ?>"
               class="btn btn-outline-primary w-100 py-3">
                <i class="bi bi-newspaper"></i>
                Manage Articles
            </a>
        </div>

        <?php if (currentUserRole() === 'admin'): ?>

            <div class="col-md-3">
                <a href="<?= e(url('admin/users.php')) ?>"
                   class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-people"></i>
                    Manage Users
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= e(url('admin/feeds.php')) ?>"
                   class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-rss"></i>
                    Manage RSS Feeds
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= e(url('admin/categories.php')) ?>"
                   class="btn btn-outline-primary w-100 py-3">
                    <i class="bi bi-tags"></i>
                    Manage Categories
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= e(url('admin/analytics.php')) ?>"
                   class="btn btn-outline-success w-100 py-3">
                    <i class="bi bi-bar-chart"></i>
                    Analytics
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= e(url('admin/system_check.php')) ?>"
                   class="btn btn-outline-secondary w-100 py-3">
                    <i class="bi bi-clipboard2-check"></i>
                    System Health Check
                </a>
            </div>

        <?php endif; ?>

    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <h5 class="mb-0">Recently Created Articles</h5>

            <a href="<?= e(url('admin/articles.php')) ?>">
                View all
            </a>
        </div>

        <div class="card-body">

            <?php if (!$recentArticles): ?>

                <p class="text-muted mb-0">No articles have been created yet.</p>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($recentArticles as $article): ?>

                            <?php
                            $statusClass = match ($article['status']) {
                                'published' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };

                            $author = trim(
                                ($article['first_name'] ?? '') . ' ' .
                                ($article['last_name'] ?? '')
                            );
                            ?>

                            <tr>
                                <td><?= e($article['title']) ?></td>
                                <td>
                                    <?= e($author !== '' ? $author : 'RSS Source') ?>
                                </td>
                                <td><?= e($article['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= e(strtoupper($article['article_type'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= e(ucfirst($article['status'])) ?>
                                    </span>
                                </td>
                                <td><?= e(formatNewsDate($article['created_at'])) ?></td>
                            </tr>

                        <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
