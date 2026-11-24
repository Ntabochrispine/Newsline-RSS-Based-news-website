<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

$errors = [];
$success = '';

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

$editingFeed = null;

if ($editId) {
    $editStmt = $pdo->prepare("
        SELECT *
        FROM rss_feeds
        WHERE id = ?
    ");

    $editStmt->execute([$editId]);
    $editingFeed = $editStmt->fetch();

    if (!$editingFeed) {
        $editId = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $feedId = filter_input(INPUT_POST, 'feed_id', FILTER_VALIDATE_INT);

            $name = trim($_POST['name'] ?? '');
            $feedUrl = trim($_POST['feed_url'] ?? '');
            $sourceName = trim($_POST['source_name'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if ($name === '' || textLength($name) > 150) {
                throw new RuntimeException('Enter a feed name of up to 150 characters.');
            }

            if ($sourceName === '' || textLength($sourceName) > 150) {
                throw new RuntimeException('Enter a publisher name of up to 150 characters.');
            }

            if (
                !filter_var($feedUrl, FILTER_VALIDATE_URL) ||
                !in_array(strtolower((string) parse_url($feedUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
            ) {
                throw new RuntimeException('Enter a valid HTTP or HTTPS RSS feed URL.');
            }

            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new RuntimeException('Invalid feed status.');
            }

            // Feed categories are assigned per article by the RSS classifier.

            if ($feedId) {
                $update = $pdo->prepare("
                    UPDATE rss_feeds
                    SET
                        name = ?,
                        feed_url = ?,
                        source_name = ?,
                        category_id = NULL,
                        status = ?
                    WHERE id = ?
                ");

                $update->execute([
                    $name,
                    $feedUrl,
                    $sourceName,
                    $status,
                    $feedId
                ]);

                $success = 'RSS feed updated successfully.';
            } else {
                $insert = $pdo->prepare("
                    INSERT INTO rss_feeds (
                        name,
                        feed_url,
                        source_name,
                        category_id,
                        status
                    ) VALUES (?, ?, ?, NULL, ?)
                ");

                $insert->execute([
                    $name,
                    $feedUrl,
                    $sourceName,
                    $status
                ]);

                $success = 'RSS feed added successfully.';
            }

            $editId = null;
            $editingFeed = null;

        } elseif ($action === 'toggle') {
            $feedId = filter_input(INPUT_POST, 'feed_id', FILTER_VALIDATE_INT);

            if (!$feedId) {
                throw new RuntimeException('Invalid feed selected.');
            }

            $update = $pdo->prepare("
                UPDATE rss_feeds
                SET status = IF(status = 'active', 'inactive', 'active')
                WHERE id = ?
            ");

            $update->execute([$feedId]);

            header('Location: ' . url('admin/feeds.php?updated=1'));
            exit;

        } elseif ($action === 'delete') {
            $feedId = filter_input(INPUT_POST, 'feed_id', FILTER_VALIDATE_INT);

            if (!$feedId) {
                throw new RuntimeException('Invalid feed selected.');
            }

            $delete = $pdo->prepare("
                DELETE FROM rss_feeds
                WHERE id = ?
            ");

            $delete->execute([$feedId]);

            header('Location: ' . url('admin/feeds.php?deleted=1'));
            exit;

        } else {
            throw new RuntimeException('Invalid request.');
        }

    } catch (RuntimeException $exception) {
        $errors[] = $exception->getMessage();
    } catch (PDOException $exception) {
        $errors[] = 'Database operation failed. Check the feed details and try again.';
    }
}

$feeds = $pdo->query("
    SELECT
        f.*
    FROM rss_feeds f
    ORDER BY f.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2>RSS Feed Management</h2>
            <p class="text-muted mb-0">
                Manage external news sources and importing settings.
            </p>
        </div>

        <a href="<?= e(url('admin/index.php')) ?>"
           class="btn btn-outline-secondary">
            Admin Dashboard
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Feed status updated.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Feed deleted.</div>
    <?php endif; ?>

    <?php if (isset($_GET['fetch_error'])): ?>
    <div class="alert alert-danger">
        <?= e($_SESSION['rss_import_error'] ?? 'RSS import failed.') ?>
    </div>
    <?php unset($_SESSION['rss_import_error']); ?>
<?php endif; ?>

<?php if (isset($_GET['fetched']) && isset($_SESSION['rss_import_summary'])): ?>

    <?php $summary = $_SESSION['rss_import_summary']; ?>

    <div class="alert alert-success">
        <h5>RSS Import Completed</h5>

        <p class="mb-1">
            Feeds checked: <?= (int) $summary['feeds_checked'] ?>
        </p>

        <p class="mb-1">
            New articles: <?= (int) $summary['total_new'] ?>
        </p>

        <p class="mb-1">
            Duplicates skipped: <?= (int) $summary['total_duplicates'] ?>
        </p>

        <p class="mb-0">
            Feed errors: <?= count($summary['feed_errors']) ?>
        </p>
        <?php
        $itemErrorCount = 0;
        foreach (($summary['feed_results'] ?? []) as $feedResult) {
            $itemErrorCount += count($feedResult['errors'] ?? []);
        }
        ?>
        <p class="mb-0">Article errors: <?= (int)$itemErrorCount ?></p>
    </div>

    <?php foreach (($summary['feed_results'] ?? []) as $feedResult): ?>
        <?php if (!empty($feedResult['errors'])): ?>
            <div class="alert alert-warning">
                <strong><?= e($feedResult['feed']) ?>:</strong>
                <?php foreach ($feedResult['errors'] as $itemError): ?>
                    <div><?= e($itemError) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php foreach ($summary['feed_errors'] as $feedError): ?>
        <div class="alert alert-warning">
            <?= e($feedError['feed']) ?>:
            <?= e($feedError['error']) ?>
        </div>
    <?php endforeach; ?>

    <?php unset($_SESSION['rss_import_summary']); ?>

<?php endif; ?>


    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <?= $editingFeed ? 'Edit RSS Feed' : 'Add New RSS Feed' ?>
            </h5>
        </div>

        <div class="card-body">

            <form method="POST">
                <?= csrfField() ?>

                <input type="hidden" name="action" value="save">

                <input type="hidden"
                       name="feed_id"
                       value="<?= (int) ($editingFeed['id'] ?? 0) ?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Feed Name</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            maxlength="150"
                            required
                            value="<?= e($editingFeed['name'] ?? '') ?>"
                            placeholder="BBC World RSS"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Publisher Name</label>
                        <input
                            type="text"
                            name="source_name"
                            class="form-control"
                            maxlength="150"
                            required
                            value="<?= e($editingFeed['source_name'] ?? '') ?>"
                            placeholder="BBC News"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">RSS Feed URL</label>
                        <input
                            type="url"
                            name="feed_url"
                            class="form-control"
                            required
                            value="<?= e($editingFeed['feed_url'] ?? '') ?>"
                            placeholder="https://example.com/rss.xml"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Feed Status</label>

                        <select name="status" class="form-select">
                            <option value="active"
                                <?= ($editingFeed['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="inactive"
                                <?= ($editingFeed['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?= $editingFeed ? 'Update Feed' : 'Add Feed' ?>
                        </button>

                        <?php if ($editingFeed): ?>
                            <a href="<?= e(url('admin/feeds.php')) ?>"
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <strong>Configured RSS Feeds (<?= count($feeds) ?>)</strong>
        </div>

        <div class="card-body">

            <?php if (!$feeds): ?>

                <p class="text-muted mb-0">No RSS feeds have been added yet.</p>

            <?php else: ?>


<div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">

        <div>
            <h5 class="mb-1">Import News Now</h5>
            <p class="text-muted mb-0">
                Fetch the latest articles from all active RSS feeds.
            </p>
        </div>

        <form method="POST" action="<?= e(url('fetch_rss.php')) ?>"
              onsubmit="return confirm('Start importing news from all active feeds?');">

            <?= csrfField() ?>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat"></i>
                Fetch RSS Now
            </button>

        </form>

    </div>
</div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Feed</th>
                                <th>Publisher</th>
                                
                                <th>Status</th>
                                <th>Last Fetched</th>
                                <th>Imported</th>
                                <th>Health</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($feeds as $feed): ?>

                            <tr>
                                <td>
                                    <strong><?= e($feed['name']) ?></strong>
                                    <div class="small text-muted"
                                         style="max-width:260px;overflow-wrap:anywhere;">
                                        <?= e($feed['feed_url']) ?>
                                    </div>
                                </td>

                                <td><?= e($feed['source_name']) ?></td>


                                <td>
                                    <span class="badge bg-<?= $feed['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= e(ucfirst($feed['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($feed['last_fetched_at']
                                        ? formatNewsDate($feed['last_fetched_at'])
                                        : 'Never') ?>
                                </td>

                                <td>
                                    <div><?= number_format((int)($feed['articles_imported_total'] ?? 0)) ?> total</div>
                                    <small class="text-muted">+<?= number_format((int)($feed['last_imported_count'] ?? 0)) ?> last run</small>
                                </td>

                                <td>
                                    <?php $feedStatus = $feed['last_import_status'] ?? 'never'; ?>
                                    <?php $feedBadge = match ($feedStatus) {
                                        'success' => 'success',
                                        'warning' => 'warning',
                                        'error' => 'danger',
                                        default => 'secondary'
                                    }; ?>
                                    <span class="badge bg-<?= e($feedBadge) ?>">
                                        <?= e(ucfirst($feedStatus)) ?>
                                    </span>
                                    <?php if (!empty($feed['last_error'])): ?>
                                        <div>
                                            <small class="text-danger" title="<?= e($feed['last_error']) ?>">
                                                Last error
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-1">

                                        <a
                                            href="<?= e(url('admin/feeds.php?edit=' . (int) $feed['id'])) ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form method="POST">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="feed_id"
                                                   value="<?= (int) $feed['id'] ?>">

                                            <button class="btn btn-sm btn-outline-secondary">
                                                <?= $feed['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>

                                        <form method="POST"
                                              onsubmit="return confirm('Delete this RSS feed? Existing imported articles will remain.');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="feed_id"
                                                   value="<?= (int) $feed['id'] ?>">

                                            <button class="btn btn-sm btn-outline-danger">
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

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
