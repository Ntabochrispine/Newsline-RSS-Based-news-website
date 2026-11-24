-- RSS News Portal database schema
-- Import only when creating a NEW installation. Back up an existing database first.
CREATE DATABASE IF NOT EXISTS rss_news_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rss_news_portal;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 first_name VARCHAR(100) NOT NULL,
 last_name VARCHAR(100) NOT NULL,
 username VARCHAR(30) NOT NULL,
 email VARCHAR(190) NOT NULL,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','author','user') NOT NULL DEFAULT 'user',
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_users_username (username),
 UNIQUE KEY uq_users_email (email), KEY idx_users_role_status (role,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 name VARCHAR(100) NOT NULL,
 slug VARCHAR(150) NOT NULL,
 description VARCHAR(255) NULL,
 category_type ENUM('topic','legacy_geo') NOT NULL DEFAULT 'topic',
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_categories_slug (slug), KEY idx_categories_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rss_feeds (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 name VARCHAR(150) NOT NULL,
 feed_url VARCHAR(2048) NOT NULL,
 source_name VARCHAR(150) NOT NULL,
 category_id INT UNSIGNED NULL,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 last_fetched_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), KEY idx_rss_feeds_status (status), KEY idx_rss_feeds_category (category_id),
 CONSTRAINT fk_rss_feeds_category FOREIGN KEY (category_id) REFERENCES categories(id)
 ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 user_id INT UNSIGNED NULL,
 category_id INT UNSIGNED NULL,
 region ENUM('kenya','africa','world') NOT NULL DEFAULT 'world',
 country_code CHAR(2) NULL,
 classification_confidence DECIMAL(5,2) NULL,
 classification_source ENUM('manual','rss_rules','author_review') NOT NULL DEFAULT 'manual',
 is_featured TINYINT(1) NOT NULL DEFAULT 0,
 is_breaking TINYINT(1) NOT NULL DEFAULT 0,
 scheduled_at DATETIME NULL,
 rss_feed_id INT UNSIGNED NULL,
 title VARCHAR(255) NOT NULL,
 slug VARCHAR(300) NOT NULL,
 excerpt TEXT NULL,
 content LONGTEXT NULL,
 featured_image VARCHAR(2048) NULL,
 source_name VARCHAR(150) NULL,
 source_url VARCHAR(2048) NULL,
 article_type ENUM('user','rss') NOT NULL DEFAULT 'user',
 status ENUM('draft','pending','published','rejected') NOT NULL DEFAULT 'draft',
 published_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 deleted_at DATETIME NULL,
 PRIMARY KEY (id), UNIQUE KEY uq_articles_slug (slug),
 KEY idx_articles_status_date (status,published_at),
 KEY idx_articles_category_status (category_id,status),
 KEY idx_articles_region_status_date (region,status,published_at),
 KEY idx_articles_featured (is_featured,is_breaking,status),
 KEY idx_articles_user (user_id),
 KEY idx_articles_feed_url (rss_feed_id,source_url(191)),
 CONSTRAINT fk_articles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT fk_articles_feed FOREIGN KEY (rss_feed_id) REFERENCES rss_feeds(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS article_views (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 article_id INT UNSIGNED NOT NULL,
 ip_address VARCHAR(45) NULL,
 user_agent VARCHAR(500) NULL,
 viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (id), KEY idx_article_views_article_date (article_id,viewed_at),
 CONSTRAINT fk_article_views_article FOREIGN KEY (article_id) REFERENCES articles(id)
 ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categories (name,slug,description,status) VALUES
('World','world','International news and global developments.','active'),
('Africa','africa','News from across the African continent.','active'),
('Kenya','kenya','News and current affairs from Kenya.','active'),
('Politics','politics','Political news, policy and governance.','active'),
('Business','business','Business, finance and economic news.','active'),
('Technology','technology','Technology, innovation and digital news.','active'),
('Sports','sports','Sports news and competitions.','active'),
('Health','health','Health, medicine and public health news.','active'),
('Entertainment','entertainment','Entertainment, culture and lifestyle news.','active'),
('Education','education','Education news and learning.','active'),
('Science','science','Science, research and discovery.','active'),
('Lifestyle','lifestyle','Lifestyle and human-interest stories.','active'),
('Environment','environment','Environment and climate news.','active'),
('Security','security','Security and public safety news.','active'),
('General','general','General news and stories.','active');
UPDATE categories SET category_type='legacy_geo' WHERE slug IN ('world','africa','kenya');


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
