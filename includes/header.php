<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

if (!isset($pageTitle) || trim((string)$pageTitle) === '') {
    $pageTitle = 'RSS News Portal';
}

$loggedIn = isLoggedIn();
$userRole = currentUserRole();
$isStaff = in_array($userRole, ['admin', 'author'], true);
$dashboardPath = $userRole === 'admin' ? 'admin/index.php' : ($userRole === 'author' ? 'admin/articles.php' : 'user/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($metaDescription ?? 'Latest news from Kenya, Africa and around the world.') ?>">
    <?php if (!empty($canonicalUrl)): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= e($openGraphType ?? 'website') ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?> | RSS News Portal">
    <meta property="og:description" content="<?= e($metaDescription ?? 'Latest news from Kenya, Africa and around the world.') ?>">
    <?php if (!empty($metaImage)): ?><meta property="og:image" content="<?= e($metaImage) ?>"><?php endif; ?>
    <link rel="alternate" type="application/rss+xml" title="RSS News Portal - Latest News" href="<?= e(url('rss.php')) ?>">
    <title><?= e($pageTitle) ?> | RSS News Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>

<header class="main-header">
    <div class="container">
        <div class="row align-items-center py-3 g-3">
            <div class="col-lg-4 col-md-5">
                <a href="<?= e(url('index.php')) ?>" class="site-logo text-decoration-none">
                    <span class="logo-icon"><i class="bi bi-newspaper"></i></span>
                    <span>RSS NEWS</span>
                </a>
            </div>
            <div class="col-lg-5 col-md-7">
                <form action="<?= e(url('search.php')) ?>" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="search" name="q" class="form-control"
                               placeholder="Search news..." required
                               value="<?= e($_GET['q'] ?? '') ?>">
                        <button class="btn btn-primary" type="submit" aria-label="Search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-lg-end">
                <?php if ($loggedIn): ?>
                    <?php if (!$isStaff): ?>
                        <a href="<?= e(url('user/create_article.php')) ?>" class="btn btn-outline-primary me-2">
                            <i class="bi bi-pencil-square"></i> Write Article
                        </a>
                    <?php endif; ?>
                    <a href="<?= e(url($dashboardPath)) ?>" class="btn btn-primary">
                        <i class="bi bi-person-circle"></i> My Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= e(url('register.php')) ?>" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Join Us
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<nav class="navbar navbar-expand-lg navbar-dark main-navbar">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavigation" aria-controls="mainNavigation"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavigation">
            <?php
            $navigationCategories = [];
            try {
                $navigationStmt = $pdo->query("
                    SELECT name, slug
                    FROM categories
                    WHERE status = 'active'
                      AND category_type = 'topic'
                      AND slug NOT IN ('kenya', 'africa', 'world')
                    ORDER BY name ASC
                    LIMIT 12
                ");
                $navigationCategories = $navigationStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $navigationError) {
                error_log('Navigation categories error: ' . $navigationError->getMessage());
            }
            ?>

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('index.php')) ?>"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('latest.php')) ?>">Latest</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('latest.php?region=kenya')) ?>">Kenya</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('latest.php?region=africa')) ?>">Africa</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('latest.php?region=world')) ?>">World</a></li>
                <?php foreach ($navigationCategories as $navigationCategory): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(categoryUrl($navigationCategory['slug'])) ?>">
                            <?= e($navigationCategory['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex gap-2">
                <?php if ($loggedIn): ?>
                    <a href="<?= e(url('logout.php')) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= e(url('login.php')) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-person"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main>
