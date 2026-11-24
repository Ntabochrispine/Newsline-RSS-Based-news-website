<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('user/dashboard.php'));
    exit;
}

verifyCsrfToken();

$userId = currentUserId();

$articleId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$articleId) {
    header('Location: ' . url('user/dashboard.php'));
    exit;
}

$stmt = $pdo->prepare("
    UPDATE articles
    SET deleted_at = NOW()
    WHERE id = ?
      AND user_id = ?
      AND article_type = 'user'
      AND deleted_at IS NULL
");

$stmt->execute([$articleId, $userId]);

header('Location: ' . url('user/dashboard.php?deleted=1'));
exit;
