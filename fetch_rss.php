<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

/*
|--------------------------------------------------------------------------
| RSS downloading
|--------------------------------------------------------------------------
*/

function downloadRssFeed(string $feedUrl): string
{
    if (
        !filter_var($feedUrl, FILTER_VALIDATE_URL) ||
        !in_array(
            strtolower((string) parse_url($feedUrl, PHP_URL_SCHEME)),
            ['http', 'https'],
            true
        )
    ) {
        throw new RuntimeException('Invalid RSS feed URL.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException(
            'PHP cURL is not enabled. Enable the curl extension in php.ini.'
        );
    }

    $ch = curl_init($feedUrl);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_MAXFILESIZE => 5 * 1024 * 1024,
        CURLOPT_USERAGENT => 'RSSNewsPortal/1.0',
        CURLOPT_HTTPHEADER => [
            'Accept: application/rss+xml, application/atom+xml, application/xml, text/xml, */*'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Feed download failed: ' . $error);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException(
            'The feed returned HTTP status ' . $httpCode . '.'
        );
    }

    if (strlen($response) > 5 * 1024 * 1024) {
        throw new RuntimeException('The RSS feed is larger than 5MB.');
    }

    return $response;
}

/*
|--------------------------------------------------------------------------
| XML helper functions
|--------------------------------------------------------------------------
*/

function rssFirstValue(SimpleXMLElement $item, array $expressions): string
{
    foreach ($expressions as $expression) {
        $matches = $item->xpath($expression);

        if ($matches && isset($matches[0])) {
            $value = trim((string) $matches[0]);

            if ($value !== '') {
                return $value;
            }
        }
    }

    return '';
}

function rssExtractImage(SimpleXMLElement $item, string $description): ?string
{
    $image = rssFirstValue($item, [
        './enclosure/@url',
        './*[local-name()="content"]/@url',
        './*[local-name()="thumbnail"]/@url',
        './*[local-name()="image"]/@url',
        './*[local-name()="image"]'
    ]);

    if ($image !== '' && filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }

    if (
        preg_match(
            '/<img[^>]+src=["\']([^"\']+)["\']/i',
            $description,
            $matches
        )
    ) {
        $candidate = html_entity_decode(
            $matches[1],
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            return $candidate;
        }
    }

    return null;
}

function rssExtractContent(SimpleXMLElement $item): string
{
    $content = rssFirstValue($item, [
        './*[local-name()="encoded"]',
        './description',
        './*[local-name()="content"]',
        './summary',
        './*[local-name()="summary"]'
    ]);

    return trim($content);
}

function rssExtractDate(SimpleXMLElement $item): ?string
{
    $date = rssFirstValue($item, [
        './pubDate',
        './published',
        './updated',
        './*[local-name()="date"]'
    ]);

    if ($date === '') {
        return null;
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

/*
|--------------------------------------------------------------------------
| Parse RSS and Atom feeds
|--------------------------------------------------------------------------
*/

function parseRssItems(string $xmlContent): array
{
    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('PHP SimpleXML is not enabled. Enable the simplexml extension.');
    }

    libxml_use_internal_errors(true);

    $xml = simplexml_load_string(
        $xmlContent,
        SimpleXMLElement::class,
        LIBXML_NONET | LIBXML_NOCDATA
    );

    if (!$xml) {
        libxml_clear_errors();
        throw new RuntimeException('The feed contains invalid XML.');
    }

    $items = [];

    $entries = $xml->xpath(
        '//*[local-name()="item" or local-name()="entry"]'
    );

    if (!$entries) {
        throw new RuntimeException(
            'No RSS items or Atom entries were found.'
        );
    }

    foreach ($entries as $item) {
        $title = rssFirstValue($item, ['./title']);

        if ($title === '') {
            continue;
        }

        $link = rssFirstValue($item, [
            './link[@rel="alternate"]/@href',
            './link/@href',
            './link',
            './guid'
        ]);

        $description = rssExtractContent($item);

        $image = rssExtractImage($item, $description);

        $plainText = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                html_entity_decode(
                    strip_tags($description),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                )
            )
        );

        $items[] = [
            'title' => trim($title),
            'url' => filter_var($link, FILTER_VALIDATE_URL)
                ? $link
                : null,
            'content' => $description,
            'excerpt' => $plainText !== ''
                ? createExcerpt($plainText, 300)
                : '',
            'image' => $image,
            'published_at' => rssExtractDate($item)
        ];
    }

    return $items;
}

/**
 * Lightweight, explainable RSS classifier. It assigns one topic and one
 * geographical region; uncertain topic matches fall back to General.
 */
function classifyRssArticle(PDO $pdo, array $item): array
{
    $title = strtolower(trim((string)($item['title'] ?? '')));
    $excerpt = strtolower(trim((string)($item['excerpt'] ?? '')));
    $body = strtolower(trim(strip_tags((string)($item['content'] ?? ''))));

    // Country names and commonly used capital/city names. Title and excerpt
    // matches carry more weight than a passing mention deep in the article.
    $africanCountries = [
        'KE'=>['kenya','nairobi','mombasa','kisumu','nakuru','eldoret','kiambu'],
        'NG'=>['nigeria','lagos','abuja','kano'], 'ZA'=>['south africa','johannesburg','cape town','pretoria'],
        'UG'=>['uganda','kampala'], 'TZ'=>['tanzania','dar es salaam','dodoma'],
        'GH'=>['ghana','accra'], 'ET'=>['ethiopia','addis ababa'], 'EG'=>['egypt','cairo'],
        'RW'=>['rwanda','kigali'], 'CD'=>['democratic republic of the congo','dr congo','kinshasa'],
        'CG'=>['republic of the congo','brazzaville'], 'SD'=>['sudan','khartoum'],
        'SS'=>['south sudan','juba'], 'SN'=>['senegal','dakar'], 'MA'=>['morocco','rabat','casablanca'],
        'ZW'=>['zimbabwe','harare'], 'ZM'=>['zambia','lusaka'], 'MW'=>['malawi','lilongwe'],
        'SO'=>['somalia','mogadishu'], 'CM'=>['cameroon','yaounde','douala'], 'CI'=>['ivory coast','cote d ivoire','abidjan'],
        'MZ'=>['mozambique','maputo'], 'AO'=>['angola','luanda'], 'BW'=>['botswana','gaborone'],
        'NA'=>['namibia','windhoek'], 'DZ'=>['algeria','algiers'], 'TN'=>['tunisia','tunis'],
        'LY'=>['libya','tripoli'], 'ML'=>['mali','bamako'], 'NE'=>['niger','niamey'],
        'BF'=>['burkina faso','ouagadougou'], 'TD'=>['chad','ndjamena'], 'CF'=>['central african republic','bangui'],
        'GA'=>['gabon','libreville'], 'GQ'=>['equatorial guinea','malabo'], 'GM'=>['gambia','banjul'],
        'GN'=>['guinea','conakry'], 'GW'=>['guinea bissau','bissau'], 'SL'=>['sierra leone','freetown'],
        'LR'=>['liberia','monrovia'], 'TG'=>['togo','lome'], 'BJ'=>['benin','porto novo','cotonou'],
        'ER'=>['eritrea','asmara'], 'DJ'=>['djibouti'], 'BI'=>['burundi','gitega'],
        'LS'=>['lesotho','maseru'], 'SZ'=>['eswatini','mbabane','manzini'], 'MU'=>['mauritius','port louis'],
        'SC'=>['seychelles','victoria seychelles'], 'KM'=>['comoros','moroni'], 'CV'=>['cape verde','praia'],
        'ST'=>['sao tome','sao tome and principe'],
    ];

    $containsTerm = static function(string $haystack, string $term): bool {
        return (bool)preg_match('/(?<![\pL\pN])'.preg_quote($term,'/').'(?![\pL\pN])/u', $haystack);
    };

    $countryScores = [];
    foreach ($africanCountries as $code => $terms) {
        $score = 0;
        foreach ($terms as $term) {
            if ($containsTerm($title, $term)) $score += 6;
            if ($containsTerm($excerpt, $term)) $score += 3;
            if ($containsTerm($body, $term)) $score += 1;
        }
        if ($score > 0) $countryScores[$code] = $score;
    }
    arsort($countryScores);
    $countryCode = $countryScores ? (string)array_key_first($countryScores) : null;
    $countryScore = $countryScores ? (int)reset($countryScores) : 0;

    $africaTermScore = 0;
    foreach (['africa','african union','african countries','sub-saharan africa','west africa','east africa','north africa','southern africa'] as $term) {
        if ($containsTerm($title, $term)) $africaTermScore += 6;
        if ($containsTerm($excerpt, $term)) $africaTermScore += 3;
        if ($containsTerm($body, $term)) $africaTermScore += 1;
    }
    if ($countryCode === 'KE') {
        $region = 'kenya';
        $geoConfidence = min(98, 65 + $countryScore * 4);
    } elseif ($countryCode !== null) {
        $region = 'africa';
        $geoConfidence = min(98, 65 + $countryScore * 4);
    } elseif ($africaTermScore >= 6) {
        $region = 'africa';
        $geoConfidence = min(85, 45 + $africaTermScore * 2);
    } else {
        $region = 'world';
        // Unknown is not necessarily confidently international.
        $geoConfidence = 45;
    }

    $topicRules = [
        'politics'=>['election','president','parliament','government','minister','political','vote','campaign','senate','opposition','politician'],
        'business'=>['business','economy','economic','company','companies','market','markets','bank','investment','inflation','trade','stock exchange','earnings'],
        'technology'=>['technology','tech industry','artificial intelligence','software','cybersecurity','digital','smartphone','startup','internet','robotics'],
        'sports'=>['football','soccer','sport','sports','premier league','champions league','rugby','cricket','tennis','athletics','tournament'],
        'health'=>['health','hospital','disease','medical','medicine','doctor','vaccine','outbreak','public health'],
        'entertainment'=>['entertainment','celebrity','film','movie','music','actor','actress','concert','television'],
        'education'=>['education','school','university','student','teacher','exam','curriculum','college'],
        'science'=>['science','research','scientist','space mission','discovery','laboratory'],
        'environment'=>['environment','climate change','pollution','wildlife','conservation','flood','drought','deforestation'],
        'security'=>['security','police','crime','attack','terrorism','military','war','violence','court','arrest'],
        'lifestyle'=>['lifestyle','fashion','travel','food','relationship','wellness'],
    ];
    $topicScores = [];
    foreach ($topicRules as $slug => $terms) {
        $score = 0;
        foreach ($terms as $term) {
            if ($containsTerm($title, $term)) $score += 5;
            if ($containsTerm($excerpt, $term)) $score += 2;
            if ($containsTerm($body, $term)) $score += 1;
        }
        if ($score > 0) $topicScores[$slug] = $score;
    }
    arsort($topicScores);
    $matchedSlug = $topicScores ? (string)array_key_first($topicScores) : 'general';
    $topScore = $topicScores ? (int)reset($topicScores) : 0;
    $secondScore = count($topicScores) > 1 ? (int)array_values($topicScores)[1] : 0;
    $topicConfidence = $topScore === 0 ? 30 : min(98, 48 + ($topScore * 3) + max(0, $topScore - $secondScore));
    if ($topScore > 0 && $secondScore > 0 && ($topScore - $secondScore) <= 2) {
        $topicConfidence = min($topicConfidence, 62);
    }

    // Assign every reasonably supported topic, not only the highest-scoring one.
    $matchedSlugs = array_values(array_filter(array_keys($topicScores), static function ($slug) use ($topicScores, $topScore) {
        return $topicScores[$slug] >= 3 && ($topicScores[$slug] >= ($topScore * 0.45));
    }));
    if (!$matchedSlugs) { $matchedSlugs = ['general']; }
    $placeholders = implode(',', array_fill(0, count($matchedSlugs), '?'));
    $category = $pdo->prepare("SELECT id, slug FROM categories WHERE slug IN ($placeholders) AND status='active' AND category_type='topic'");
    $category->execute($matchedSlugs);
    $matchedCategories = $category->fetchAll(PDO::FETCH_ASSOC);
    $categoryIds = array_map('intval', array_column($matchedCategories, 'id'));
    $primaryCategoryId = null;
    foreach ($matchedCategories as $matchedCategory) {
        if ($matchedCategory['slug'] === $matchedSlug) { $primaryCategoryId = (int)$matchedCategory['id']; break; }
    }
    if (!$primaryCategoryId && $categoryIds) { $primaryCategoryId = $categoryIds[0]; }

    $confidence = min($topicConfidence, $geoConfidence);
    $needsReview = ($topicConfidence < 65 || $geoConfidence < 60 || $matchedSlug === 'general');
    $notes = [];
    if ($topicConfidence < 65) $notes[] = 'Topic classification is uncertain';
    if ($geoConfidence < 60) $notes[] = 'Geographical coverage needs confirmation';

    return [
        'category_id' => $primaryCategoryId,
        'category_ids' => $categoryIds,
        'region' => $region,
        'country_code' => $countryCode,
        'confidence' => $confidence,
        'needs_review' => $needsReview ? 1 : 0,
        'notes' => $notes ? implode('; ', $notes) : 'Automatic classification matched clear signals',
        'source' => 'rss_rules'
    ];
}

/*
|--------------------------------------------------------------------------
| Import one feed
|--------------------------------------------------------------------------
*/

function importOneFeed(PDO $pdo, array $feed): array
{
    $result = [
        'feed' => $feed['name'],
        'new' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    $xmlContent = downloadRssFeed($feed['feed_url']);
    $items = parseRssItems($xmlContent);

    if (!$items) {
        throw new RuntimeException('No usable articles were found.');
    }

    $insert = $pdo->prepare("
        INSERT INTO articles (
            user_id,
            category_id,
            region,
            country_code,
            classification_confidence,
            classification_source,
            classification_needs_review,
            classification_notes,
            rss_feed_id,
            title,
            slug,
            excerpt,
            content,
            featured_image,
            source_name,
            source_url,
            article_type,
            status,
            published_at
        ) VALUES (
            NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rss', 'published', ?
        )
    ");

    $checkUrl = $pdo->prepare("
        SELECT id
        FROM articles
        WHERE rss_feed_id = ?
          AND source_url = ?
          AND deleted_at IS NULL
        LIMIT 1
    ");

    $checkTitle = $pdo->prepare("
        SELECT id
        FROM articles
        WHERE rss_feed_id = ?
          AND title = ?
          AND deleted_at IS NULL
        LIMIT 1
    ");

    foreach ($items as $item) {
        try {
            if ($item['url'] !== null) {
                $checkUrl->execute([
                    $feed['id'],
                    $item['url']
                ]);

                if ($checkUrl->fetch()) {
                    $result['duplicates']++;
                    continue;
                }
            } else {
                $checkTitle->execute([
                    $feed['id'],
                    $item['title']
                ]);

                if ($checkTitle->fetch()) {
                    $result['duplicates']++;
                    continue;
                }
            }

            $classification = classifyRssArticle($pdo, $item);
            $slug = uniqueArticleSlug($pdo, $item['title']);

            $publishedAt = $item['published_at']
                ?? date('Y-m-d H:i:s');

            $insert->execute([
                $classification['category_id'],
                $classification['region'],
                $classification['country_code'],
                $classification['confidence'],
                $classification['source'],
                $classification['needs_review'],
                $classification['notes'],
                $feed['id'],
                $item['title'],
                $slug,
                $item['excerpt'],
                $item['content'],
                $item['image'],
                $feed['source_name'],
                $item['url'],
                $publishedAt
            ]);

            $newArticleId = (int)$pdo->lastInsertId();
            if (!empty($classification['category_ids'])) {
                saveArticleTopics($pdo, $newArticleId, $classification['category_ids'], 'rss_rules');
            }

            $result['new']++;

        } catch (PDOException $exception) {
            $result['errors'][] = 'Could not import "' .
                $item['title'] . '".';
        }
    }

    $status = !empty($result['errors'])
        ? ($result['new'] > 0 ? 'warning' : 'error')
        : 'success';

    $lastError = !empty($result['errors'])
        ? textSubstr(implode(' | ', $result['errors']), 0, 2000)
        : null;

    $updateFeed = $pdo->prepare("
        UPDATE rss_feeds
        SET last_fetched_at = NOW(),
            articles_imported_total = articles_imported_total + ?,
            last_imported_count = ?,
            last_import_status = ?,
            last_error = ?
        WHERE id = ?
    ");

    $updateFeed->execute([
        $result['new'],
        $result['new'],
        $status,
        $lastError,
        $feed['id']
    ]);

    return $result;
}

/*
|--------------------------------------------------------------------------
| Import all active feeds
|--------------------------------------------------------------------------
*/

function runRssImport(PDO $pdo): array
{
    $feeds = $pdo->query("
        SELECT *
        FROM rss_feeds
        WHERE status = 'active'
        ORDER BY id ASC
    ")->fetchAll();

    $summary = [
        'feeds_checked' => count($feeds),
        'total_new' => 0,
        'total_duplicates' => 0,
        'feed_results' => [],
        'feed_errors' => []
    ];

    foreach ($feeds as $feed) {
        try {
            $result = importOneFeed($pdo, $feed);

            $summary['total_new'] += $result['new'];
            $summary['total_duplicates'] += $result['duplicates'];

            $summary['feed_results'][] = $result;

        } catch (Throwable $exception) {
            try {
                $errorUpdate = $pdo->prepare("
                    UPDATE rss_feeds
                    SET last_fetched_at = NOW(),
                        last_imported_count = 0,
                        last_import_status = 'error',
                        last_error = ?
                    WHERE id = ?
                ");
                $errorUpdate->execute([
                    textSubstr($exception->getMessage(), 0, 2000),
                    $feed['id']
                ]);
            } catch (Throwable $logException) {
                error_log('Could not update RSS feed error status: ' . $logException->getMessage());
            }

            $summary['feed_errors'][] = [
                'feed' => $feed['name'],
                'error' => $exception->getMessage()
            ];
        }
    }

    return $summary;
}

/*
|--------------------------------------------------------------------------
| Automatic fetch lock and interval
|--------------------------------------------------------------------------
*/

function rssFetchIsDue(PDO $pdo, int $intervalMinutes = 5): bool
{
    $activeFeedCount = (int)$pdo->query(
        "SELECT COUNT(*) FROM rss_feeds WHERE status = 'active'"
    )->fetchColumn();

    if ($activeFeedCount === 0) {
        return false;
    }

    $lastFetch = $pdo->query("
        SELECT MAX(last_fetched_at)
        FROM rss_feeds
        WHERE status = 'active'
    ")->fetchColumn();

    if (!$lastFetch) {
        return true;
    }

    $lastTimestamp = strtotime((string) $lastFetch);

    if ($lastTimestamp === false) {
        return true;
    }

    return time() >= ($lastTimestamp + ($intervalMinutes * 60));
}

/*
|--------------------------------------------------------------------------
| Manual admin request
|--------------------------------------------------------------------------
*/

if (
    PHP_SAPI !== 'cli' &&
    basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'fetch_rss.php'
) {
    require_once __DIR__ . '/includes/auth.php';
    requireRole(['admin']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Use the RSS management page to start an import.');
    }

    verifyCsrfToken();

    $lockAcquired = false;
    $redirectUrl = url('admin/feeds.php?fetched=1');

    try {
        $lock = $pdo->query(
            "SELECT GET_LOCK('rss_news_portal_fetch', 0)"
        )->fetchColumn();

        if ((int)$lock !== 1) {
            throw new RuntimeException('An RSS import is already running. Try again shortly.');
        }

        $lockAcquired = true;
        $_SESSION['rss_import_summary'] = runRssImport($pdo);
    } catch (Throwable $exception) {
        error_log('Manual RSS import error: ' . $exception->getMessage());
        $_SESSION['rss_import_error'] = 'RSS import failed. Check the PHP error log for details.';
        $redirectUrl = url('admin/feeds.php?fetch_error=1');
    }

    if ($lockAcquired) {
        try {
            $pdo->query("SELECT RELEASE_LOCK('rss_news_portal_fetch')");
        } catch (Throwable $releaseError) {
            error_log('Could not release RSS import lock: ' . $releaseError->getMessage());
        }
    }

    header('Location: ' . $redirectUrl);
    exit;
}
