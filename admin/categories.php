<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'save') {
            $id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: null;
            $name = trim((string)($_POST['name'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $status = (string)($_POST['status'] ?? 'active');

            if ($name === '' || textLength($name) > 100) {
                throw new RuntimeException('Category name is required and must not exceed 100 characters.');
            }
            if (!in_array($status, ['active', 'inactive'], true)) {
                throw new RuntimeException('Invalid category status.');
            }

            $baseSlug = createSlug($name);
            $slug = $baseSlug;
            $suffix = 2;
            while (true) {
                if ($id) {
                    $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1");
                    $check->execute([$slug, $id]);
                } else {
                    $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
                    $check->execute([$slug]);
                }
                if (!$check->fetch()) {
                    break;
                }
                $slug = $baseSlug . '-' . $suffix++;
            }

            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE categories
                    SET name = ?, slug = ?, description = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $status, $id]);
                $success = 'Category updated successfully.';
                logAdminActivity($pdo, 'category_update', 'Updated category: ' . $name, $id);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, slug, description, status)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $status]);
                $success = 'Category created successfully.';
                logAdminActivity($pdo, 'category_create', 'Created category: ' . $name, (int)$pdo->lastInsertId());
            }
        } elseif ($action === 'toggle') {
            $id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Invalid category selected.');
            }
            $stmt = $pdo->prepare("
                UPDATE categories
                SET status = IF(status = 'active', 'inactive', 'active'),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $success = 'Category status updated.';
            logAdminActivity($pdo, 'category_toggle', 'Toggled category status: ' . $id, (int)$id);
        } else {
            throw new RuntimeException('Unknown category action.');
        }
    } catch (RuntimeException $exception) {
        $errors[] = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log('Category management error: ' . $exception->getMessage());
        $errors[] = 'The category could not be saved. Check that its name is unique.';
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = null;
if ($editId) {
    $editStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $editStmt->execute([$editId]);
    $editing = $editStmt->fetch() ?: null;
}

$categories = $pdo->query("
    SELECT c.*, COUNT(a.id) AS article_count
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id AND a.deleted_at IS NULL
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="h2">Category Management</h1>
            <p class="text-muted mb-0">Create and organize the categories used by the news portal.</p>
        </div>
        <a href="<?= e(url('admin/index.php')) ?>" class="btn btn-outline-secondary">Admin Dashboard</a>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0"><?= $editing ? 'Edit Category' : 'Add Category' ?></h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="category_id" value="<?= (int)($editing['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="category_name">Category name</label>
                        <input id="category_name" class="form-control" name="name" maxlength="100"
                               value="<?= e($editing['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="category_description">Description</label>
                        <input id="category_description" class="form-control" name="description"
                               value="<?= e($editing['description'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="category_status">Status</label>
                        <select id="category_status" name="status" class="form-select">
                            <option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?= $editing ? 'Save Changes' : 'Create Category' ?></button>
                        <?php if ($editing): ?>
                            <a class="btn btn-outline-secondary" href="<?= e(url('admin/categories.php')) ?>">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Existing Categories (<?= count($categories) ?>)</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Slug</th><th>Articles</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><strong><?= e($category['name']) ?></strong></td>
                            <td><?= e($category['slug']) ?></td>
                            <td><?= (int)$category['article_count'] ?></td>
                            <td><span class="badge bg-<?= $category['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($category['status'])) ?></span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= e(url('admin/categories.php?edit=' . (int)$category['id'])) ?>">Edit</a>
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">
                                            <?= $category['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
