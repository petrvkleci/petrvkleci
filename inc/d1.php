<?php
/**
 * Pomocné funkce pro komunikaci s Cloudflare D1 přes jeho REST API.
 * Dokumentace: https://developers.cloudflare.com/api/operations/cloudflare-d1-query-database
 *
 * D1 je jinak určená hlavně pro Cloudflare Workers, ale dá se k ní přistupovat
 * i odsud přes běžné HTTPS volání (REST API) — je to trochu pomalejší
 * (každý dotaz jde přes internet na Cloudflare), proto veřejná stránka
 * media.php výsledek na chvíli ukládá do malé cache (viz d1_fetch_published_articles).
 */

function d1_query(string $sql, array $params = [])
{
    $url = sprintf(
        'https://api.cloudflare.com/client/v4/accounts/%s/d1/database/%s/query',
        CF_ACCOUNT_ID,
        CF_DATABASE_ID
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . CF_API_TOKEN,
        ],
        CURLOPT_POSTFIELDS => json_encode(['sql' => $sql, 'params' => $params]),
        CURLOPT_TIMEOUT    => 15,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || $response === false) {
        error_log('D1: chyba spojení — ' . $curlErr);
        return false;
    }

    $data = json_decode($response, true);
    if (empty($data['success'])) {
        error_log('D1: chyba dotazu — ' . json_encode($data['errors'] ?? $data));
        return false;
    }

    return $data['result'][0]['results'] ?? [];
}

/** Převede nadpis na ascii "slug" použitý v odkazu (#clanek-slug). */
function make_slug(string $text): string
{
    $map = [
        'á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n','ó'=>'o',
        'ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z',
    ];
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'clanek';
}

/** Zpracuje volitelně nahranou fotku. Vrátí URL cestu, null (nic nenahráno) nebo false (chyba). */
function handle_photo_upload(array $file)
{
    if (empty($file['name'])) {
        return null; // fotka je volitelná
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return false; // max 5 MB
    }

    $allowedExt = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowedExt[$ext])) {
        return false;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = 'clanek-' . date('Ymd-His') . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
        return false;
    }

    return UPLOAD_URL_PREFIX . $filename;
}

function d1_insert_article(
    string $slug,
    string $title,
    string $tag,
    string $date,
    string $content,
    ?string $image,
    ?string $linkUrl,
    int $published
): bool {
    $sql = 'INSERT INTO articles (slug, title, tag, article_date, content, image, link_url, published)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
    $result = d1_query($sql, [$slug, $title, $tag, $date, $content, $image, $linkUrl, $published]);
    return $result !== false;
}

function d1_delete_article(int $id): bool
{
    return d1_query('DELETE FROM articles WHERE id = ?', [$id]) !== false;
}

function d1_toggle_published(int $id, int $published): bool
{
    return d1_query('UPDATE articles SET published = ? WHERE id = ?', [$published, $id]) !== false;
}

/** Pro administraci — úplně všechny články (i koncepty), bez cache. */
function d1_fetch_all_articles(): array
{
    $rows = d1_query('SELECT * FROM articles ORDER BY article_date DESC, id DESC');
    return $rows ?: [];
}

/** Pro veřejnou stránku — jen zveřejněné články, s krátkou souborovou cache. */
function d1_fetch_published_articles(): array
{
    $cacheFile = ARTICLES_CACHE_FILE;

    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < ARTICLES_CACHE_TTL)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $rows = d1_query('SELECT * FROM articles WHERE published = 1 ORDER BY article_date DESC, id DESC');
    $rows = $rows ?: [];

    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    @file_put_contents($cacheFile, json_encode($rows));

    return $rows;
}

function clear_articles_cache(): void
{
    if (is_file(ARTICLES_CACHE_FILE)) {
        @unlink(ARTICLES_CACHE_FILE);
    }
}
