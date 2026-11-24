<?php
declare(strict_types=1);

/** Publish due scheduled articles. Safe to call on public requests. */
function publishDueArticles(PDO $pdo): int
{
    try {
        $stmt = $pdo->prepare("UPDATE articles SET status='published', published_at=COALESCE(published_at, scheduled_at, NOW()), scheduled_at=NULL, updated_at=NOW() WHERE status IN ('draft','pending') AND deleted_at IS NULL AND scheduled_at IS NOT NULL AND scheduled_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Scheduled publishing error: ' . $e->getMessage());
        return 0;
    }
}
