-- RSS monitoring fields for an existing installation.
-- Import once after migration_audit_logs.sql.
USE rss_news_portal;

ALTER TABLE rss_feeds
    ADD COLUMN articles_imported_total INT UNSIGNED NOT NULL DEFAULT 0 AFTER last_fetched_at,
    ADD COLUMN last_imported_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER articles_imported_total,
    ADD COLUMN last_import_status ENUM('never','success','warning','error') NOT NULL DEFAULT 'never' AFTER last_imported_count,
    ADD COLUMN last_error TEXT NULL AFTER last_import_status;
