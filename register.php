<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $destination = currentUserRole() === 'admin' ? 'admin/index.php' : (currentUserRole() === 'author' ? 'admin/articles.php' : 'user/dashboard.php');
    header('Location: ' . url($destination));
    exit;
}

$pageTitle = 'Create Account';

$errors = [];

$firstName = '';
$lastName = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] =
            'Username must contain 3-30 letters, numbers or underscores.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] =
            'Password must contain at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing User
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
               OR email = ?
            LIMIT 1
        ");

        $stmt->execute([
            $username,
            $email
        ]);

        if ($stmt->fetch()) {
            $errors[] =
                'A user with that username or email already exists.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO users (
                first_name,
                last_name,
                username,
                email,
                password,
                role,
                status
            )
            VALUES (?, ?, ?, ?, ?, 'user', 'active')
        ");

        $stmt->execute([
            $firstName,
            $lastName,
            $username,
            $email,
            $hashedPassword
        ]);

        header('Location: login.php?registered=1');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <div class="logo-icon mx-auto mb-3">

                            <i class="bi bi-person-plus"></i>

                        </div>

                        <h1 class="h3 fw-bold">
                            Create Your Account
                        </h1>

                        <p class="text-muted">
                            Join RSS News Portal and publish your own articles.
                        </p>

                    </div>


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

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label
                                    class="form-label"
                                    for="first_name"
                                >
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    id="first_name"
                                    class="form-control"
                                    value="<?= e($firstName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label
                                    class="form-label"
                                    for="last_name"
                                >
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    id="last_name"
                                    class="form-control"
                                    value="<?= e($lastName) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="username"
                            >
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                id="username"
                                class="form-control"
                                value="<?= e($username) ?>"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="email"
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


                        <div class="mb-3">

                            <label
                                class="form-label"
                                for="password"
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

                            <small class="text-muted">
                                Minimum 8 characters.
                            </small>

                        </div>


                        <div class="mb-4">

                            <label
                                class="form-label"
                                for="confirm_password"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-person-plus"></i>

                            Create Account

                        </button>

                    </form>


                    <div class="text-center mt-4">

                        <p class="mb-0">

                            Already have an account?

                            <a href="login.php">
                                Login here
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