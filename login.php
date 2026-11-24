<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {

    if (
        in_array(
            currentUserRole(),
            ['admin', 'author'],
            true
        )
    ) {
        header('Location: ' . url('admin/index.php'));
    } else {
        header('Location: ' . url('user/dashboard.php'));
    }

    exit;
}

$pageTitle = 'Login';

$errors = [];

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    $email = trim($_POST['email'] ?? '');

    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }


    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT
                id,
                first_name,
                last_name,
                username,
                email,
                password,
                role,
                status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch();


        if (
            !$user ||
            !password_verify(
                $password,
                $user['password']
            )
        ) {

            $errors[] =
                'Invalid email or password.';

        } elseif ($user['status'] !== 'active') {

            $errors[] =
                'Your account is currently inactive.';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] =
                (int) $user['id'];

            $_SESSION['user_name'] =
                $user['first_name'] . ' ' . $user['last_name'];

            $_SESSION['username'] =
                $user['username'];

            $_SESSION['user_email'] =
                $user['email'];

            $_SESSION['user_role'] =
                $user['role'];

            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $rehash = password_hash($password, PASSWORD_DEFAULT);
                $rehashStmt = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
                $rehashStmt->execute([$rehash, (int)$user['id']]);
            }


            if ($user['role'] === 'admin') {
                header('Location: ' . url('admin/index.php'));
            } elseif ($user['role'] === 'author') {
                header('Location: ' . url('admin/articles.php'));
            } else {

                header('Location: ' . url('user/dashboard.php'));

            }

            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-5 col-md-7">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <div class="logo-icon mx-auto mb-3">

                            <i class="bi bi-person"></i>

                        </div>

                        <h1 class="h3 fw-bold">
                            Login
                        </h1>

                        <p class="text-muted">
                            Access your RSS News Portal account.
                        </p>

                    </div>


                    <?php if (
                        isset($_GET['registered'])
                        && $_GET['registered'] === '1'
                    ): ?>

                        <div class="alert alert-success">

                            Account created successfully.

                            You can now log in.

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($errors)): ?>

                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                <?php foreach ($errors as $error): ?>

                                    <li>
                                        <?= e($error) ?>
                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                    <?php endif; ?>


                    <form method="POST">

                        <?= csrfField() ?>

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= e($email) ?>"
                                required
                            >

                        </div>


                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-box-arrow-in-right"></i>

                            Login

                        </button>

                    </form>


                    <div class="text-center mt-4">

                        <p class="mb-0">

                            Don't have an account?

                            <a href="register.php">
                                Create an account
                            </a>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php

require_once __DIR__ . '/includes/footer.php';