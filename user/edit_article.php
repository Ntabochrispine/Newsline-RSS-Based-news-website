<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

$userId = currentUserId();

$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$articleId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$articleId) {
    http_response_code(400);
    exit('Invalid article ID.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM articles
    WHERE id = ?
      AND user_id = ?
      AND article_type = 'user'
      AND deleted_at IS NULL
    LIMIT 1
");

$stmt->execute([$articleId, $userId]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    exit('Article not found or you do not have permission to edit it.');
}

$categoryStmt = $pdo->query("
    SELECT id, name
    FROM categories
    WHERE status = 'active' AND category_type = 'topic'
    ORDER BY name ASC
");

$categories = $categoryStmt->fetchAll();

$errors = [];

$title = $article['title'];
$excerpt = $article['excerpt'] ?? '';
$content = $article['content'] ?? '';
$categoryId = (string) ($article['category_id'] ?? '');
$region = $article['region'] ?? 'kenya';
$selectedCategoryIds = array_map('intval', array_column(getArticleTopics($pdo, (int)$articleId), 'id'));
if (!$selectedCategoryIds && $categoryId !== '') { $selectedCategoryIds = [(int)$categoryId]; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $selectedCategoryIds = array_values(array_unique(array_map('intval', (array)($_POST['category_ids'] ?? []))));
    $categoryId = $selectedCategoryIds[0] ?? ''; 
    $action = $_POST['action'] ?? 'draft';
    $region = (string)($_POST['region'] ?? 'kenya');

    if ($title === '' || textLength($title) < 5) {
        $errors[] = 'The title must contain at least 5 characters.';
    }

    if (textLength($title) > 255) {
        $errors[] = 'The title must not exceed 255 characters.';
    }

    if ($content === '') {
        $errors[] = 'Article content is required.';
    }

    if (!$selectedCategoryIds) {
        $errors[] = 'Please select at least one topic.';
    } else {
        $placeholders = implode(',', array_fill(0, count($selectedCategoryIds), '?'));
        $categoryCheck = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE status='active' AND category_type='topic' AND id IN ($placeholders)");
        $categoryCheck->execute($selectedCategoryIds);
        if ((int)$categoryCheck->fetchColumn() !== count($selectedCategoryIds)) { $errors[] = 'Please select valid active topics.'; }
    }

    if (!in_array($region, ['kenya', 'africa', 'world'], true)) {
        $errors[] = 'Please select a valid geographical section.';
    }

    if (!in_array($action, ['draft', 'pending'], true)) {
        $errors[] = 'Invalid submission action.';
    }

    $newImagePath = null;

    if (!$errors) {
        try {
            $newImagePath = uploadArticleImage(
                $_FILES['featured_image'] ?? []
            );
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (!$errors) {
        $slug = uniqueArticleSlug($pdo, $title, (int) $articleId);

        $imagePath = $newImagePath ?: $article['featured_image'];

        $status = $action === 'pending' ? 'pending' : 'draft';

        try {
            $update = $pdo->prepare("
                UPDATE articles
                SET
                    title = ?,
                    slug = ?,
                    category_id = ?,
                    region = ?,
                    excerpt = ?,
                    content = ?,
                    featured_image = ?,
                    status = ?,
                    published_at = NULL
                WHERE id = ?
                  AND user_id = ?
                  AND deleted_at IS NULL
            ");

            $update->execute([
                $title,
                $slug,
                (int) $categoryId,
                $region,
                $excerpt,
                $content,
                $imagePath,
                $status,
                $articleId,
                $userId
            ]);

            saveArticleTopics($pdo, (int)$articleId, $selectedCategoryIds, 'manual');

            if ($newImagePath) {
                deleteArticleImage($article['featured_image']);
            }

            header('Location: ' . url('user/dashboard.php?updated=1'));
            exit;

        } catch (PDOException $exception) {
            deleteArticleImage($newImagePath);
            $errors[] = 'Could not update your article. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="mb-4">
                <h2>Edit Article</h2>
                <p class="text-muted">
                    Update your article and submit it for review.
                </p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form method="POST" enctype="multipart/form-data">

                        <?= csrfField() ?>

                        <input type="hidden" name="id"
                               value="<?= (int) $articleId ?>">

                        <div class="mb-3">
                            <label class="form-label">Article Title</label>
                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                maxlength="255"
                                value="<?= e($title) ?>"
                                required
                            >
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label">Topics</label>
                                <select name="category_ids[]" class="form-select" multiple size="6" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option
                                            value="<?= (int) $category['id'] ?>"
                                            <?= in_array((int)$category['id'], $selectedCategoryIds, true) ? 'selected' : '' ?>
                                        >
                                            <?= e($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Select all relevant topics. Use Ctrl (Windows) or Command (Mac) for multiple selections.</small>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Geographical Coverage</label>
                                <select name="region" class="form-select" required>
                                    <option value="kenya" <?= $region === 'kenya' ? 'selected' : '' ?>>Kenya</option>
                                    <option value="africa" <?= $region === 'africa' ? 'selected' : '' ?>>Africa</option>
                                    <option value="world" <?= $region === 'world' ? 'selected' : '' ?>>World / International</option>
                                </select>
                            </div>
                        </div>

                        <?php if ($article['featured_image']): ?>
                            <div class="mb-3">
                                <p>Current featured image:</p>
                                <img
                                    src="<?= e(newsImageUrl($article['featured_image']) ?? '') ?>"
                                    alt="Current featured image"
                                    style="max-width:220px; height:auto;"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Replace Featured Image</label>
                            <input
                                type="file"
                                name="featured_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >
                            <small class="text-muted">
                                Leave empty to keep the current image.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea
                                name="excerpt"
                                class="form-control"
                                rows="3"
                            ><?= e($excerpt) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Article Content</label>
                            <textarea
                                name="content"
                                class="form-control"
                                rows="12"
                                required
                            ><?= e($content) ?></textarea>
                        </div>

                        <button type="submit"
                                name="action"
                                value="draft"
                                class="btn btn-outline-primary">
                            Save as Draft
                        </button>

                        <button type="submit"
                                name="action"
                                value="pending"
                                class="btn btn-primary">
                            Submit for Review
                        </button>

                        <a href="<?= e(url('user/dashboard.php')) ?>"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
