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

$errors = [];

$title = '';
$excerpt = '';
$content = '';
$categoryId = '';
$selectedCategoryIds = [];
$region = 'kenya';
$action = 'draft';

$categoryStmt = $pdo->query("
    SELECT id, name
    FROM categories
    WHERE status = 'active' AND category_type = 'topic'
    ORDER BY name ASC
");

$categories = $categoryStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $selectedCategoryIds = array_values(array_unique(array_map('intval', (array)($_POST['category_ids'] ?? []))));
    $categoryId = $selectedCategoryIds[0] ?? ''; 
    $region = $_POST['region'] ?? 'kenya';
    $action = $_POST['action'] ?? 'draft';

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
        if ((int)$categoryCheck->fetchColumn() !== count($selectedCategoryIds)) {
            $errors[] = 'Please select valid active topics.';
        }
    }

    if (!in_array($region, ['kenya', 'africa', 'world'], true)) {
        $errors[] = 'Please select a valid geographical section.';
    }

    if (!in_array($action, ['draft', 'pending', 'publish'], true)) {
        $errors[] = 'Invalid submission action.';
    }
    if ($action === 'publish' && currentUserRole() !== 'author') {
        $errors[] = 'Only newsroom authors can publish directly.';
    }

    $imagePath = null;

    if (!$errors) {
        try {
            $imagePath = uploadArticleImage(
                $_FILES['featured_image'] ?? []
            );
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (!$errors) {
        $slug = uniqueArticleSlug($pdo, $title);

        $status = $action === 'publish' ? 'published' : ($action === 'pending' ? 'pending' : 'draft');
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO articles (
                    user_id,
                    category_id,
                    region,
                    title,
                    slug,
                    excerpt,
                    content,
                    featured_image,
                    article_type,
                    status,
                    published_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, 'user', ?, ?
                )
            ");

            $stmt->execute([
                $userId,
                (int) $categoryId,
                $region,
                $title,
                $slug,
                $excerpt,
                $content,
                $imagePath,
                $status,
                $publishedAt
            ]);

            $newArticleId = (int)$pdo->lastInsertId();
            saveArticleTopics($pdo, $newArticleId, $selectedCategoryIds, 'manual');

            header('Location: ' . url('user/dashboard.php?created=1'));
            exit;

        } catch (PDOException $exception) {
            deleteArticleImage($imagePath);
            $errors[] = 'Could not save your article. Please try again.';
        }
    }
}

$pageTitle = 'Create Article';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Create an Article</h2>
                    <p class="text-muted">
                        Write and submit your news article.
                    </p>
                </div>

                <a href="<?= e(url('user/dashboard.php')) ?>"
                   class="btn btn-outline-secondary">
                    Back to Dashboard
                </a>
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

                    <form method="POST"
                          enctype="multipart/form-data">

                        <?= csrfField() ?>

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

                        <div class="mb-3">
                            <label class="form-label">Topics</label>
                            <select name="category_ids[]" class="form-select" multiple size="6" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int)$category['id'] ?>" <?= in_array((int)$category['id'], $selectedCategoryIds, true) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select all relevant topics. Use Ctrl (Windows) or Command (Mac) to select more than one.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Geographical Coverage</label>
                            <select name="region" class="form-select" required>
                                <option value="kenya" <?= $region === 'kenya' ? 'selected' : '' ?>>Kenya</option>
                                <option value="africa" <?= $region === 'africa' ? 'selected' : '' ?>>Africa (excluding Kenya)</option>
                                <option value="world" <?= $region === 'world' ? 'selected' : '' ?>>World / International</option>
                            </select>
                            <small class="text-muted">Choose where the main event or story is geographically relevant.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Featured Image</label>

                            <input
                                type="file"
                                name="featured_image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small class="text-muted">
                                JPG, PNG or WEBP. Maximum size: 2MB.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Article Excerpt</label>

                            <textarea
                                name="excerpt"
                                class="form-control"
                                rows="3"
                                placeholder="Write a short introduction..."
                            ><?= e($excerpt) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Article Content</label>

                            <textarea
                                name="content"
                                class="form-control"
                                rows="12"
                                placeholder="Write your full article here..."
                                required
                            ><?= e($content) ?></textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2">

                            <button type="submit" formaction="<?= e(url('user/preview_article.php')) ?>" formtarget="_blank" class="btn btn-outline-dark">Preview Article</button>

                            <button
                                type="submit"
                                name="action"
                                value="draft"
                                class="btn btn-outline-primary"
                            >
                                Save as Draft
                            </button>

                            <?php if (currentUserRole() === 'author'): ?>
                            <button type="submit" name="action" value="publish" class="btn btn-success">Publish Article</button>
                            <?php endif; ?>

                            <button
                                type="submit"
                                name="action"
                                value="pending"
                                class="btn btn-primary"
                            >
                                Submit for Review
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
