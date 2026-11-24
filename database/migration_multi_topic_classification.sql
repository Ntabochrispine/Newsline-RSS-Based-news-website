-- Multi-topic classification migration. Safe for existing installations.
CREATE TABLE IF NOT EXISTS article_categories (
  article_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  assigned_by ENUM('manual','rss_rules','author_review') NOT NULL DEFAULT 'manual',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (article_id, category_id),
  KEY idx_article_categories_category (category_id, article_id),
  CONSTRAINT fk_article_categories_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_article_categories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO article_categories (article_id, category_id, assigned_by)
SELECT a.id, a.category_id, CASE WHEN a.article_type='rss' THEN 'rss_rules' ELSE 'manual' END
FROM articles a
INNER JOIN categories c ON c.id=a.category_id
WHERE a.category_id IS NOT NULL AND c.category_type='topic';
