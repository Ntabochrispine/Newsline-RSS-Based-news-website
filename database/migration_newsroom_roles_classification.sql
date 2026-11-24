-- Apply once to the existing rss_news_portal database after taking a backup.
USE rss_news_portal;

-- Standardize the three newsroom roles. Existing editors become authors.
UPDATE users SET role = 'author' WHERE role = 'editor';

-- Separate article topic from geographical coverage.
ALTER TABLE articles
  ADD COLUMN region ENUM('kenya','africa','world') NOT NULL DEFAULT 'world' AFTER category_id,
  ADD COLUMN country_code CHAR(2) NULL AFTER region,
  ADD COLUMN classification_confidence DECIMAL(5,2) NULL AFTER country_code,
  ADD COLUMN classification_source ENUM('manual','rss_rules','author_review') NOT NULL DEFAULT 'manual' AFTER classification_confidence,
  ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER classification_source,
  ADD COLUMN is_breaking TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured,
  ADD COLUMN scheduled_at DATETIME NULL AFTER is_breaking,
  ADD KEY idx_articles_region_status_date (region,status,published_at),
  ADD KEY idx_articles_featured (is_featured,is_breaking,status);

-- Existing legacy geographical categories are kept for data integrity, but hidden from topic menus.
ALTER TABLE categories
  ADD COLUMN category_type ENUM('topic','legacy_geo') NOT NULL DEFAULT 'topic' AFTER description;
UPDATE categories SET category_type='legacy_geo' WHERE slug IN ('world','africa','kenya');

-- New feeds no longer require a feed-level topic.
ALTER TABLE rss_feeds MODIFY category_id INT UNSIGNED NULL;

-- Ensure a general topic exists for articles that cannot be confidently categorized.
INSERT INTO categories (name,slug,description,status,category_type)
SELECT 'General','general','General news and stories.','active','topic'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug='general');

INSERT INTO categories (name,slug,description,status,category_type)
SELECT 'Environment','environment','Environment and climate news.','active','topic' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug='environment');
INSERT INTO categories (name,slug,description,status,category_type)
SELECT 'Security','security','Security and public safety news.','active','topic' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug='security');
