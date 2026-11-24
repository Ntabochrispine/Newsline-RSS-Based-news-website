-- Apply once to rss_news_portal after backing up the database.
USE rss_news_portal;

ALTER TABLE articles
  ADD COLUMN classification_needs_review TINYINT(1) NOT NULL DEFAULT 0 AFTER classification_source,
  ADD COLUMN classification_notes VARCHAR(500) NULL AFTER classification_needs_review,
  ADD KEY idx_articles_classification_review (article_type, classification_needs_review, status);

-- Existing RSS items with weak/no classification should be reviewed.
UPDATE articles
SET classification_needs_review = 1,
    classification_notes = 'Existing RSS article: verify topic and geographical coverage'
WHERE article_type = 'rss'
  AND deleted_at IS NULL
  AND (classification_confidence IS NULL OR classification_confidence < 65);
