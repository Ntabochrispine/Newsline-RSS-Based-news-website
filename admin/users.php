<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin']);

$currentAdminId = currentUserId();
$errors = [];

$allowedRoles = ['admin', 'author', 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (!$targetId) {
        $errors[] = 'Invalid user selected.';
    } else {
        $find = $pdo->prepare("
            SELECT id, role, status
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $find->execute([$targetId]);
        $target = $find->fetch();

        if (!$target) {
            $errors[] = 'User account not found.';
        } elseif ($targetId === $currentAdminId) {
            $errors[] = 'You cannot change your own role or account status here.';
        } else {
            try {
                if ($action === 'change_role') {
                    $newRole = $_POST['role'] ?? '';

                    if (!in_array($newRole, $allowedRoles, true)) {
                        throw new RuntimeException('Invalid role selected.');
                    }

                    if (
                        $target['role'] === 'admin' &&
                        $newRole !== 'admin' &&
                        $target['status'] === 'active'
                    ) {
                        $countAdmins = $pdo->query("
                            SELECT COUNT(*)
                            FROM users
                            WHERE role = 'admin'
                              AND status = 'active'
                        ")->fetchColumn();

                        if ((int) $countAdmins <= 1) {
                            throw new RuntimeException(
                                'You cannot remove the last active administrator.'
                            );
                        }
                    }

                    $update = $pdo->prepare("
                        UPDATE users
                        SET role = ?
                        WHERE id = ?
                    ");

                    $update->execute([$newRole, $targetId]);

                } elseif ($action === 'toggle_status') {
                    $newStatus = $target['status'] === 'active'
                        ? 'inactive'
                        : 'active';

                    if (
                        $target['role'] === 'admin' &&
                        $newStatus === 'inactive'
                    ) {
                        $countAdmins = $pdo->query("
                            SELECT COUNT(*)
                            FROM users
                            WHERE role = 'admin'
                              AND status = 'active'
                        ")->fetchColumn();

                        if ((int) $countAdmins <= 1) {
                            throw new RuntimeException(
                                'You cannot deactivate the last active administrator.'
                            );
                        }
                    }

                    $update = $pdo->prepare("
                        UPDATE users
                        SET status = ?
                        WHERE id = ?
                    ");

                    $update->execute([$newStatus, $targetId]);

                } else {
                    throw new RuntimeException('Unknown action.');
                }

                header('Location: ' . url('admin/users.php?updated=1'));
                exit;

            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT
        id,
        first_name,
        last_name,
        username,
        email,
        role,
        status,
        created_at
    FROM users
";

$params = [];

if ($search !== '') {
    $sql .= "
        WHERE first_name LIKE ?
           OR last_name LIKE ?
           OR username LIKE ?
           OR email LIKE ?
    ";

    $term = '%' . $search . '%';

    $params = [$term, $term, $term, $term];
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2>User Management</h2>
            <p class="text-muted mb-0">
                Manage accounts, roles and account access.
            </p>
        </div>

        <a href="<?= e(url('admin/index.php')) ?>"
           class="btn btn-outline-secondary">
            Admin Dashboard
        </a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            User account updated successfully.
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" class="row g-2">
                <div class="col-md-9">
                    <input
                        type="search"
                        name="search"
                        class="form-control"
                        placeholder="Search by name, username or email..."
                        value="<?= e($search) ?>"
                    >
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        Search
                    </button>

                    <a href="<?= e(url('admin/users.php')) ?>"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <strong>Registered Accounts (<?= count($users) ?>)</strong>
        </div>

        <div class="card-body">

            <?php if (!$users): ?>

                <p class="text-muted mb-0">No users found.</p>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Manage</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($users as $user): ?>

                            <tr>
                                <td>
                                    <?= e($user['first_name'] . ' ' . $user['last_name']) ?>

                                    <?php if ((int) $user['id'] === $currentAdminId): ?>
                                        <span class="badge bg-info">You</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= e($user['username']) ?></td>

                                <td><?= e($user['email']) ?></td>

                                <td>
                                    <?php if ((int) $user['id'] === $currentAdminId): ?>

                                        <span class="badge bg-primary">
                                            <?= e(ucfirst($user['role'])) ?>
                                        </span>

                                    <?php else: ?>

                                        <form method="POST" class="d-flex gap-1">
                                            <?= csrfField() ?>

                                            <input type="hidden" name="user_id"
                                                   value="<?= (int) $user['id'] ?>">

                                            <select name="role"
                                                    class="form-select form-select-sm">

                                                <?php foreach ($allowedRoles as $role): ?>
                                                    <option
                                                        value="<?= e($role) ?>"
                                                        <?= $user['role'] === $role ? 'selected' : '' ?>
                                                    >
                                                        <?= e(ucfirst($role)) ?>
                                                    </option>
                                                <?php endforeach; ?>

                                            </select>

                                            <button
                                                type="submit"
                                                name="action"
                                                value="change_role"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Save
                                            </button>
                                        </form>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= e(ucfirst($user['status'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(formatNewsDate($user['created_at'])) ?>
                                </td>

                                <td>
                                    <?php if ((int) $user['id'] !== $currentAdminId): ?>

                                        <form method="POST"
                                              onsubmit="return confirm('Change this account status?');">

                                            <?= csrfField() ?>

                                            <input type="hidden" name="user_id"
                                                   value="<?= (int) $user['id'] ?>">

                                            <button
                                                type="submit"
                                                name="action"
                                                value="toggle_status"
                                                class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                            >
                                                <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="text-muted">Protected</span>

                                    <?php endif; ?>
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
