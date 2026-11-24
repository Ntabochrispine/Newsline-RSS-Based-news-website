<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application URL
|--------------------------------------------------------------------------
| Change this value if your project folder changes.
*/
if (!defined('BASE_URL')) {
    define('BASE_URL', '/NewslineRSS Website');
}

// Update SITE_URL when deploying to a real domain. Do not include a trailing slash.
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/NewslineRSS Website');
}

function absoluteUrl(string $path = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function createSlug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');

    return $text !== '' ? $text : 'article';
}

function formatNewsDate(?string $date): string
{
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp === false
        ? ''
        : date('M d, Y • h:i A', $timestamp);
}


function textLength(string $text): int
{
    return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
}

function textSubstr(string $text, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, $start, $length, 'UTF-8');
    }

    return $length === null ? substr($text, $start) : substr($text, $start, $length);
}

function createExcerpt(?string $text, int $length = 150): string
{
    $text = trim(strip_tags((string) $text));

    if ($text === '') {
        return '';
    }

    if (textLength($text) <= $length) {
        return $text;
    }

    return textSubstr($text, 0, $length) . '...';
}

function articleUrl(int $id, string $slug): string
{
    return url(
        'article.php?id=' . $id . '&slug=' . urlencode($slug)
    );
}

function categoryUrl(string $slug): string
{
    return url('category.php?slug=' . urlencode($slug));
}

function searchUrl(string $query): string
{
    return url('search.php?q=' . urlencode($query));
}

/*
|--------------------------------------------------------------------------
| CSRF protection
|--------------------------------------------------------------------------
*/

function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrfToken()) . '">';
}

function verifyCsrfToken(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($submitted) ||
        !is_string($stored) ||
        $stored === '' ||
        !hash_equals($stored, $submitted)
    ) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }
}

/*
|--------------------------------------------------------------------------
| Unique article slug
|--------------------------------------------------------------------------
*/

function uniqueArticleSlug(PDO $pdo, string $title, ?int $excludeId = null): string
{
    $base = createSlug($title);
    $slug = $base;
    $counter = 1;

    while (true) {
        $sql = "SELECT id FROM articles WHERE slug = ?";

        $params = [$slug];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $counter++;
        $slug = $base . '-' . $counter;
    }
}

/*
|--------------------------------------------------------------------------
| Featured image upload
|--------------------------------------------------------------------------
*/

function uploadArticleImage(array $file): ?string
{
    if (
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload failed.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('The image must not exceed 2MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            'Only JPG, PNG and WEBP images are allowed.'
        );
    }

    $uploadDirectory = __DIR__ . '/../assets/images/uploads/';

    if (
        !is_dir($uploadDirectory) &&
        !mkdir($uploadDirectory, 0755, true)
    ) {
        throw new RuntimeException('Could not create the upload directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $uploadDirectory . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return 'assets/images/uploads/' . $filename;
}

/*
|--------------------------------------------------------------------------
| Delete an uploaded image from disk
|--------------------------------------------------------------------------
*/

function deleteArticleImage(?string $imagePath): void
{
    if (!$imagePath) {
        return;
    }

    $expectedPrefix = 'assets/images/uploads/';

    if (!str_starts_with($imagePath, $expectedPrefix)) {
        return;
    }

    $fullPath = dirname(__DIR__) . '/' . $imagePath;

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}


/**
 * Convert a stored local image path to an application URL while
 * leaving validated remote image URLs unchanged.
 */
function newsImageUrl(?string $image): ?string
{
    $image = trim((string) $image);

    if ($image === '') {
        return null;
    }

    if (preg_match('~^https?://~i', $image)) {
        return $image;
    }

    if (str_starts_with($image, '/')) {
        return $image;
    }

    return url(ltrim($image, '/'));
}

/*
|--------------------------------------------------------------------------
| Admin activity logging
|--------------------------------------------------------------------------
*/
function logAdminActivity(PDO $pdo, string $action, string $description = '', ?int $targetId = null): void
{
    try {
        // The current audit_logs schema stores the target as entity_type/entity_id.
        $entityType = null;
        if (str_starts_with($action, 'article_') || str_starts_with($action, 'editorial_')) {
            $entityType = 'article';
        } elseif (str_starts_with($action, 'category_')) {
            $entityType = 'category';
        } elseif (str_starts_with($action, 'user_')) {
            $entityType = 'user';
        } elseif (str_starts_with($action, 'feed_') || str_starts_with($action, 'rss_')) {
            $entityType = 'rss_feed';
        }

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs
                (user_id, action, entity_type, entity_id, description, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            currentUserId(),
            $action,
            $entityType,
            $targetId,
            textSubstr($description, 0, 1000),
            substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)
        ]);
    } catch (Throwable $exception) {
        // Logging must never break the operation that was being audited.
        error_log('Audit log error: ' . $exception->getMessage());
    }
}


/** Replace an article's topic assignments with validated active topic categories. */
function saveArticleTopics(PDO $pdo, int $articleId, array $categoryIds, string $assignedBy = 'manual'): void
{
    // The consolidated database uses system/author/admin for the assignment actor.
    // Keep the older application labels accepted by callers and normalize them here.
    $assignedByMap = [
        'manual' => 'system',
        'rss_rules' => 'system',
        'author_review' => 'author',
        'system' => 'system',
        'author' => 'author',
        'admin' => 'admin',
    ];
    $assignedBy = $assignedByMap[$assignedBy] ?? 'system';
    $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds), static fn($id) => $id > 0)));
    if (!$ids) { throw new RuntimeException('Select at least one topic.'); }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $check = $pdo->prepare("SELECT id FROM categories WHERE status='active' AND category_type='topic' AND id IN ($placeholders)");
    $check->execute($ids);
    $valid = array_map('intval', $check->fetchAll(PDO::FETCH_COLUMN));
    if (count($valid) !== count($ids)) { throw new RuntimeException('One or more selected topics are invalid.'); }
    $pdo->prepare('DELETE FROM article_categories WHERE article_id = ?')->execute([$articleId]);
    $insert = $pdo->prepare('INSERT INTO article_categories (article_id, category_id, assigned_by) VALUES (?, ?, ?)');
    foreach ($valid as $categoryId) { $insert->execute([$articleId, $categoryId, $assignedBy]); }
}

function getArticleTopics(PDO $pdo, int $articleId): array
{
    $stmt = $pdo->prepare("SELECT c.id, c.name, c.slug FROM article_categories ac INNER JOIN categories c ON c.id=ac.category_id WHERE ac.article_id=? AND c.status='active' ORDER BY c.name");
    $stmt->execute([$articleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
