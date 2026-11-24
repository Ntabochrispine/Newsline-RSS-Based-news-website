<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$userId = currentUserId();

$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.title,
        a.slug,
        a.status,
        a.created_at,
        a.updated_at,
        a.published_at,
        c.name AS category_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
      AND a.article_type = 'user'
      AND a.deleted_at IS NULL
    ORDER BY a.created_at DESC
");

$stmt->execute([$userId]);
$articles = $stmt->fetchAll();

$statusCounts = [
    'draft' => 0,
    'pending' => 0,
    'published' => 0,
    'rejected' => 0
];

foreach ($articles as $article) {
    if (isset($statusCounts[$article['status']])) {
        $statusCounts[$article['status']]++;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2>
                Welcome, <?= e($_SESSION['user_name'] ?? 'User') ?>
            </h2>

            <p class="text-muted">
                Manage your news articles and submissions.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= e(url('user/create_article.php')) ?>"
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Create Article
            </a>

            <a href="<?= e(url('index.php')) ?>"
               class="btn btn-outline-secondary">
                View Website
            </a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">
            Your article has been saved successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            Your article has been updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            Your article has been deleted successfully.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-5">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Articles</h6>
                    <h3><?= count($articles) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Drafts</h6>
                    <h3><?= $statusCounts['draft'] ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Pending Review</h6>
                    <h3><?= $statusCounts['pending'] ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Published</h6>
                    <h3><?= $statusCounts['published'] ?></h3>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0">My Articles</h4>
        </div>

        <div class="card-body">

            <?php if (!$articles): ?>

                <div class="text-center py-5">
                    <i class="bi bi-newspaper display-4 text-muted"></i>

                    <h5 class="mt-3">You have not created any articles yet.</h5>

                    <p class="text-muted">
                        Start contributing by writing your first article.
                    </p>

                    <a href="<?= e(url('user/create_article.php')) ?>"
                       class="btn btn-primary">
                        Create Your First Article
                    </a>
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($articles as $article): ?>

                            <?php
                            $statusClass = match ($article['status']) {
                                'published' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            ?>

                            <tr>
                                <td>
                                    <strong><?= e($article['title']) ?></strong>
                                </td>

                                <td>
                                    <?= e($article['category_name'] ?? 'Uncategorized') ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= e(ucfirst($article['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(formatNewsDate($article['created_at'])) ?>
                                </td>

                                <td>
                                    <?= e(formatNewsDate($article['updated_at'])) ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-2">

                                        <a
                                            href="<?= e(url('user/edit_article.php?id=' . (int) $article['id'])) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= e(url('user/delete_article.php')) ?>"
                                            onsubmit="return confirm('Are you sure you want to delete this article?');"
                                        >
                                            <?= csrfField() ?>

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $article['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <div class="mt-4">
        <a href="<?= e(url('logout.php')) ?>"
           class="btn btn-outline-danger">
            Logout
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
