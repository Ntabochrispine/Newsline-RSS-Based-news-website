<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['author']);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
    $action = (string)($_POST['action'] ?? '');

    if (!$articleId) {
        $errors[] = 'Invalid article selected.';
    } else {
        $find = $pdo->prepare("
            SELECT id, article_type, status
            FROM articles
            WHERE id = ? AND deleted_at IS NULL
            LIMIT 1
        ");
        $find->execute([$articleId]);
        $article = $find->fetch();

        if (!$article) {
            $errors[] = 'The article could not be found.';
        } else {
            try {
                if ($action === 'approve') {
                    $stmt = $pdo->prepare("
                        UPDATE articles
                        SET status = 'published',
                            published_at = COALESCE(published_at, NOW()),
                            updated_at = NOW()
                        WHERE id = ? AND deleted_at IS NULL
                    ");
                    $stmt->execute([$articleId]);
                    $success = 'Article published successfully.';
                    logAdminActivity($pdo, 'article_publish', 'Published article: ' . $articleId, $articleId);
                } elseif ($action === 'reject') {
                    $stmt = $pdo->prepare("
                        UPDATE articles
                        SET status = 'rejected', updated_at = NOW()
                        WHERE id = ? AND deleted_at IS NULL
                    ");
                    $stmt->execute([$articleId]);
                    $success = 'Article rejected.';
                    logAdminActivity($pdo, 'article_reject', 'Rejected article: ' . $articleId, $articleId);
                } elseif ($action === 'draft') {
                    $stmt = $pdo->prepare("
                        UPDATE articles
                        SET status = 'draft', published_at = NULL, updated_at = NOW()
                        WHERE id = ? AND deleted_at IS NULL
                    ");
                    $stmt->execute([$articleId]);
                    $success = 'Article returned to draft.';
                    logAdminActivity($pdo, 'article_draft', 'Returned article to draft: ' . $articleId, $articleId);
                } elseif ($action === 'delete') {
                    $stmt = $pdo->prepare("
                        UPDATE articles
                        SET deleted_at = NOW(), updated_at = NOW()
                        WHERE id = ? AND deleted_at IS NULL
                    ");
                    $stmt->execute([$articleId]);
                    $success = 'Article moved to the deleted state.';
                    logAdminActivity($pdo, 'article_delete', 'Soft-deleted article: ' . $articleId, $articleId);
                } else {
                    throw new RuntimeException('Unknown moderation action.');
                }
            } catch (PDOException $exception) {
                error_log('Article moderation error: ' . $exception->getMessage());
                $errors[] = 'The article could not be updated. Please try again.';
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

$status = (string)($_GET['status'] ?? '');
$type = (string)($_GET['type'] ?? '');
$search = trim((string)($_GET['search'] ?? ''));

$allowedStatuses = ['draft', 'pending', 'published', 'rejected'];
$allowedTypes = ['user', 'rss'];

$where = ['a.deleted_at IS NULL'];
$params = [];

if (in_array($status, $allowedStatuses, true)) {
    $where[] = 'a.status = ?';
    $params[] = $status;
}
if (in_array($type, $allowedTypes, true)) {
    $where[] = 'a.article_type = ?';
    $params[] = $type;
}
if ($search !== '') {
    $where[] = '(a.title LIKE ? OR a.source_name LIKE ? OR u.username LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}

$sql = "
    SELECT a.id, a.title, a.slug, a.article_type, a.status,
           a.source_name, a.created_at, a.published_at,
           c.name AS category_name,
           u.first_name, u.last_name, u.username
    FROM articles a
    LEFT JOIN categories c ON c.id = a.category_id
    LEFT JOIN users u ON u.id = a.user_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY
        CASE a.status WHEN 'pending' THEN 0 ELSE 1 END,
        a.created_at DESC
    LIMIT 200
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$pageTitle = 'Manage Articles';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h2">Article Management</h1>
            <p class="text-muted mb-0">Review user submissions and manage published content.</p>
        </div>
        <a href="<?= e(url('index.php')) ?>" class="btn btn-outline-secondary">View Website</a>
        <a href="<?= e(url('user/create_article.php')) ?>" class="btn btn-primary">Write Article</a>
        <a href="<?= e(url('admin/classification_review.php')) ?>" class="btn btn-outline-warning">Classification Review</a>
        <a href="<?= e(url('admin/editorial_tools.php')) ?>" class="btn btn-outline-dark">Editorial Tools</a>
        <form method="POST" action="<?= e(url('admin/reclassify_rss.php')) ?>" onsubmit="return confirm('Reclassify all RSS articles that have not been manually corrected?');"><?= csrfField() ?><button class="btn btn-outline-primary" type="submit">Reclassify Existing RSS News</button></form>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php if (isset($_GET['reclassified'])): ?><div class="alert alert-success">Reclassified <?= (int)($_SESSION['rss_reclassified_count'] ?? 0) ?> RSS articles. Manually corrected articles were preserved.</div><?php unset($_SESSION['rss_reclassified_count']); endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-lg-5">
                    <input type="search" name="search" class="form-control"
                           placeholder="Search title, source or author..."
                           value="<?= e($search) ?>">
                </div>
                <div class="col-md-3 col-lg-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach ($allowedStatuses as $option): ?>
                            <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                                <?= e(ucfirst($option)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <select name="type" class="form-select">
                        <option value="">All types</option>
                        <option value="user" <?= $type === 'user' ? 'selected' : '' ?>>User articles</option>
                        <option value="rss" <?= $type === 'rss' ? 'selected' : '' ?>>RSS articles</option>
                    </select>
                </div>
                <div class="col-md-3 col-lg-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="<?= e(url('admin/articles.php')) ?>">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Articles (<?= count($articles) ?> shown, maximum 200)</strong>
        </div>
        <div class="card-body">
            <?php if (!$articles): ?>
                <p class="text-muted mb-0">No articles match these filters.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Article</th><th>Author / Source</th><th>Category</th>
                                <th>Type</th><th>Status</th><th>Created</th><th>Moderation</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articles as $article): ?>
                            <?php
                            $author = trim((string)($article['first_name'] ?? '') . ' ' . (string)($article['last_name'] ?? ''));
                            if ($author === '') {
                                $author = (string)($article['source_name'] ?: 'RSS source');
                            }
                            $badge = match ($article['status']) {
                                'published' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong><?= e($article['title']) ?></strong>
                                    <?php if ($article['status'] === 'published'): ?>
                                        <div><a class="small" target="_blank" rel="noopener"
                                            href="<?= e(articleUrl((int)$article['id'], $article['slug'])) ?>">View article</a></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($author) ?></td>
                                <td><?= e($article['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= e(strtoupper($article['article_type'])) ?></td>
                                <td><span class="badge bg-<?= e($badge) ?>"><?= e(ucfirst($article['status'])) ?></span></td>
                                <td><?= e(formatNewsDate($article['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/edit_article.php?id=' . (int)$article['id'])) ?>">Edit</a>
                                        <?php if ($article['status'] !== 'published'): ?>
                                            <form method="POST">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button class="btn btn-sm btn-success" type="submit">Publish</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($article['status'] !== 'rejected'): ?>
                                            <form method="POST">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-sm btn-outline-warning" type="submit">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($article['status'] !== 'draft'): ?>
                                            <form method="POST">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                                                <input type="hidden" name="action" value="draft">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">Draft</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Move this article to deleted state?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
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
