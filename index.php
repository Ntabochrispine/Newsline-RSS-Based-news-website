<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Home';

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/fetch_rss.php';
require_once __DIR__ . '/includes/scheduler.php';
publishDueArticles($pdo);

try {
    if (rssFetchIsDue($pdo, 5)) {
        $lock = $pdo->query(
            "SELECT GET_LOCK('rss_news_portal_fetch', 0)"
        )->fetchColumn();

        if ((int) $lock === 1) {
            try {
                runRssImport($pdo);
            } finally {
                $pdo->query(
                    "SELECT RELEASE_LOCK('rss_news_portal_fetch')"
                );
            }
        }
    }
} catch (Throwable $exception) {
    // Keep the public homepage available if an RSS source fails.
    error_log('RSS automatic import error: ' . $exception->getMessage());
}

require_once __DIR__ . '/includes/header.php';



// Latest published articles
$stmt = $pdo->prepare("
    SELECT
        a.*,
        c.name AS category_name,
        c.slug AS category_slug
    FROM articles a
    LEFT JOIN categories c
        ON c.id = a.category_id
    WHERE a.status = 'published'
      AND a.deleted_at IS NULL
    ORDER BY
        a.published_at DESC,
        a.created_at DESC
    LIMIT 12
");

$stmt->execute();

$latestArticles = $stmt->fetchAll();


// Trending articles
$trendingStmt = $pdo->prepare("
    SELECT
        a.id,
        a.title,
        a.slug,
        COUNT(av.id) AS view_count
    FROM articles a
    LEFT JOIN article_views av
        ON av.article_id = a.id
    WHERE a.status = 'published'
      AND a.deleted_at IS NULL
    GROUP BY
        a.id,
        a.title,
        a.slug
    ORDER BY view_count DESC
    LIMIT 5
");

$trendingStmt->execute();

$trendingArticles = $trendingStmt->fetchAll();


// Categories
$categoryStmt = $pdo->prepare("
    SELECT
        id,
        name,
        slug
    FROM categories
    WHERE status = 'active' AND category_type = 'topic'
    ORDER BY name ASC
");

$categoryStmt->execute();

$categories = $categoryStmt->fetchAll();

?>

<div class="container py-4">


    <!-- =================================
         HERO / LATEST NEWS
    ================================== -->

    <div class="section-title">

        <h2>
            Latest News
        </h2>

        <a href="<?= e(url('latest.php')) ?>">
            View All
        </a>

    </div>


    <?php if (!empty($latestArticles)): ?>

        <?php $hero = $latestArticles[0]; ?>

        <div class="row g-4 mb-5">

            <div class="col-lg-8">

                <article class="hero-news">

                    <div class="hero-news-content">

                        <?php if (!empty($hero['category_name'])): ?>

                            <span class="news-category text-white">
                                <?= e($hero['category_name']) ?>
                            </span>

                        <?php endif; ?>


                        <h1>

                            <a href="<?= e(
                                articleUrl(
                                    (int) $hero['id'],
                                    $hero['slug']
                                )
                            ) ?>">

                                <?= e($hero['title']) ?>

                            </a>

                        </h1>


                        <p class="mb-2">

                            <?= e(
                                createExcerpt(
                                    $hero['excerpt'] ?: $hero['content'],
                                    180
                                )
                            ) ?>

                        </p>


                        <div class="news-meta text-white">

                            <?= e(
                                formatNewsDate(
                                    $hero['published_at']
                                    ?: $hero['created_at']
                                )
                            ) ?>

                        </div>

                    </div>

                </article>

            </div>


            <!-- Trending -->

            <div class="col-lg-4">

                <div class="section-title">

                    <h2>
                        Trending
                    </h2>

                </div>


                <?php if (!empty($trendingArticles)): ?>

                    <?php foreach (
                        $trendingArticles as $index => $trending
                    ): ?>

                        <div class="trending-item">

                            <div class="trending-number">

                                <?= $index + 1 ?>

                            </div>


                            <div>

                                <h3 class="trending-title">

                                    <a href="<?= e(
                                        articleUrl(
                                            (int) $trending['id'],
                                            $trending['slug']
                                        )
                                    ) ?>">

                                        <?= e($trending['title']) ?>

                                    </a>

                                </h3>


                                <small class="text-muted">

                                    <?= (int) $trending['view_count'] ?>

                                    views

                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="alert alert-light border">

                        No trending articles yet.

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- =================================
             LATEST ARTICLE GRID
        ================================== -->

        <div class="section-title">

            <h2>
                More Latest News
            </h2>

        </div>


        <div class="row g-4 mb-5">

            <?php foreach (
                array_slice($latestArticles, 1, 6)
                as $article
            ): ?>

                <div class="col-lg-4 col-md-6">

                    <article class="news-card">

                        <?php if (!empty($article['featured_image'])): ?>

                            <img
                                src="<?= e(newsImageUrl($article['featured_image']) ?? '') ?>"
                                alt="<?= e($article['title']) ?>"
                                class="news-card-image"
                            >

                        <?php else: ?>

                            <div
                                class="news-card-image d-flex align-items-center justify-content-center"
                            >

                                <i
                                    class="bi bi-newspaper"
                                    style="font-size: 50px;"
                                ></i>

                            </div>

                        <?php endif; ?>


                        <div class="news-card-body">

                            <?php if (
                                !empty($article['category_name'])
                            ): ?>

                                <span class="news-category">

                                    <?= e(
                                        $article['category_name']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <h3 class="news-title">

                                <a href="<?= e(
                                    articleUrl(
                                        (int) $article['id'],
                                        $article['slug']
                                    )
                                ) ?>">

                                    <?= e($article['title']) ?>

                                </a>

                            </h3>


                            <p class="news-excerpt">

                                <?= e(
                                    createExcerpt(
                                        $article['excerpt']
                                        ?: $article['content']
                                    )
                                ) ?>

                            </p>


                            <div class="news-meta">

                                <?= e(
                                    formatNewsDate(
                                        $article['published_at']
                                        ?: $article['created_at']
                                    )
                                ) ?>

                            </div>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>

        </div>


    <?php else: ?>

        <div class="alert alert-info">

            <h4 class="alert-heading">
                No news articles yet.
            </h4>

            <p class="mb-0">

                RSS articles and user-published articles
                will appear here once they are available.

            </p>

        </div>

    <?php endif; ?>


    <!-- =================================
         CATEGORY SECTIONS
    ================================== -->

    <?php foreach ($categories as $category): ?>

        <?php

        $categoryArticlesStmt = $pdo->prepare("
            SELECT
                a.*,
                c.name AS category_name,
                c.slug AS category_slug
            FROM articles a
            LEFT JOIN categories c
                ON c.id = a.category_id
            WHERE a.status = 'published'
              AND a.deleted_at IS NULL
              AND (a.category_id = ? OR EXISTS (SELECT 1 FROM article_categories ac WHERE ac.article_id=a.id AND ac.category_id=?))
            ORDER BY
                a.published_at DESC,
                a.created_at DESC
            LIMIT 3
        ");

        $categoryArticlesStmt->execute([
            $category['id'], $category['id']
        ]);

        $categoryArticles =
            $categoryArticlesStmt->fetchAll();

        ?>

        <?php if (!empty($categoryArticles)): ?>

            <section class="mb-5">

                <div class="section-title">

                    <h2>
                        <?= e($category['name']) ?>
                    </h2>

                    <a href="<?= e(
                        categoryUrl($category['slug'])
                    ) ?>">

                        View More

                    </a>

                </div>


                <div class="row g-4">

                    <?php foreach (
                        $categoryArticles as $article
                    ): ?>

                        <div class="col-lg-4 col-md-6">

                            <article class="news-card">

                                <?php if (
                                    !empty(
                                        $article['featured_image']
                                    )
                                ): ?>

                                    <img
                                        src="<?= e(newsImageUrl($article['featured_image']) ?? '') ?>"
                                        alt="<?= e(
                                            $article['title']
                                        ) ?>"
                                        class="news-card-image"
                                    >

                                <?php else: ?>

                                    <div
                                        class="news-card-image d-flex align-items-center justify-content-center"
                                    >

                                        <i
                                            class="bi bi-newspaper"
                                            style="font-size: 50px;"
                                        ></i>

                                    </div>

                                <?php endif; ?>


                                <div class="news-card-body">

                                    <span class="news-category">

                                        <?= e(
                                            $article[
                                                'category_name'
                                            ]
                                        ) ?>

                                    </span>


                                    <h3 class="news-title">

                                        <a href="<?= e(
                                            articleUrl(
                                                (int) $article['id'],
                                                $article['slug']
                                            )
                                        ) ?>">

                                            <?= e(
                                                $article['title']
                                            ) ?>

                                        </a>

                                    </h3>


                                    <p class="news-excerpt">

                                        <?= e(
                                            createExcerpt(
                                                $article['excerpt']
                                                ?: $article['content']
                                            )
                                        ) ?>

                                    </p>


                                    <div class="news-meta">

                                        <?= e(
                                            formatNewsDate(
                                                $article[
                                                    'published_at'
                                                ]
                                                ?: $article[
                                                    'created_at'
                                                ]
                                            )
                                        ) ?>

                                    </div>

                                </div>

                            </article>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>

    <?php endforeach; ?>

</div>

<?php

require_once __DIR__ . '/includes/footer.php';