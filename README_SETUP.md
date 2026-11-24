# RSS News Portal

PHP + MySQL news portal designed for XAMPP on Windows.

## Local setup
1. Copy this project folder to `C:\xampp\htdocs\rss-news-portal`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. For a fresh installation only, import `database/schema.sql`.
   - If your existing `rss_news_portal` database already contains your articles and feeds, do NOT import the schema over it. Back up the database first.
5. Confirm `config/database.php` uses database `rss_news_portal`, user `root`, and the correct password.
6. Open `http://localhost/rss-news-portal/`.

## PHP extensions
Enable `pdo_mysql`, `curl`, `simplexml`, `fileinfo`, and `mbstring` in the active XAMPP `php.ini`, then restart Apache.

## First administrator
1. Register an account.
2. In phpMyAdmin, run the following query using your registered email:
```sql
UPDATE users SET role = 'admin', status = 'active'
WHERE email = 'your-email@example.com';
```
3. Log out and log in again.

## RSS importing
- Add feeds from Admin Dashboard → Manage RSS Feeds.
- Use **Fetch RSS Now** for a manual import.
- The homepage checks whether an import is due every five minutes. It runs only when someone visits the homepage; it is not a background service.
- MySQL named locks prevent simultaneous manual and homepage imports.
- A hosted server can use a cron job for true background importing.

## Implemented
- Registration/login with password hashing and CSRF tokens.
- User article creation, drafts, pending review, editing and soft deletion.
- Admin/editor article moderation.
- Admin user/role management.
- Admin RSS feed management and RSS/Atom importer.
- Admin category management.
- Homepage latest/trending sections.
- Article pages, category pages, search and pagination.
- Article view tracking and original-source links.
- Responsive Bootstrap layout.
- Article social-sharing links.
- Public RSS output at `rss.php` for readers/feed aggregators.
- Dynamic XML sitemap at `sitemap.php`.
- Admin-only system health check.

## SEO and publishing feeds
- Public RSS feed: `http://localhost/rss-news-portal/rss.php`.
- XML sitemap: `http://localhost/rss-news-portal/sitemap.php`.
- Before deployment, update `SITE_URL` in `includes/functions.php` to the real HTTPS domain and update the sitemap URL in `robots.txt`.
- The sample `robots.txt` is configured for local XAMPP. Replace its sitemap host when deploying.

## Notes
- The ZIP did not include a phpMyAdmin database export, so the live database and existing imported rows could not be inspected. The schema file is for a fresh installation only.
- RSS article HTML is displayed as text to avoid executing untrusted feed markup.
- The RSS downloader needs additional SSRF protections before exposing feed management on a public production server.
- Remote publisher images may become unavailable if the publisher removes them.


## Audit log migration
For an existing installation, import `database/migration_audit_logs.sql` once in phpMyAdmin. It adds the administrator activity log without deleting existing data. Admins can view it at `admin/activity.php`.


## Newsroom roles and automatic RSS classification update

For an existing installation, back up the database and import `database/migration_newsroom_roles_classification.sql` once. This migrates legacy `editor` accounts to `author`, adds geographical classification fields, and marks old World/Africa/Kenya category records as legacy geographic entries. Do not re-import `database/schema.sql` into an existing database.

The three roles are now `admin`, `author`, and `user`:
- Admin: user/role management, RSS feed management, system monitoring and analytics.
- Author: editorial newsroom, moderation, publishing, and correction of RSS classifications/content.
- User: create drafts and submit articles for author approval.

RSS feeds no longer take a default category. The importer applies a rules-based topic and geography classifier to each article. RSS stories are automatically published. Authors can edit, reclassify, or soft-delete them. The classifier is a first-pass rules engine, not an AI model; low-confidence or incorrect classifications should be reviewed by an author.

After applying the migration, log in as an author and open `admin/articles.php`. Use **Reclassify Existing RSS News** once to classify articles already imported before this update. Articles manually corrected by an author are excluded from that batch.


## Classification review update

After installing the newsroom roles/classification migration, import `database/migration_classification_review.sql` once. It adds a review flag and explanation for uncertain RSS classifications. Authors can open `admin/classification_review.php` and correct topic, geographical coverage and country code. RSS articles remain automatically published; the review queue does not unpublish them. Do not re-import the full schema over an existing database.


## Multi-topic article classification update
For an existing installation, import `database/migration_multi_topic_classification.sql` once after the newsroom/classification migrations. This creates the `article_categories` mapping table and copies each existing article's primary topic into it. Do not re-import `schema.sql`. Authors and contributors can now assign multiple topic categories to an article; the existing `category_id` remains the primary/legacy category for compatibility.
