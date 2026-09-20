<?php
/**
 * KONFIGURACE
 * -----------
 * Postupujte podle navod-cloudflare-d1.md — tam je krok za krokem vysvětlené,
 * kde všechny tyhle hodnoty najdete / jak je vygenerovat.
 *
 * Tento soubor obsahuje citlivé údaje (API token, hash hesla).
 * Nikdy ho nedávejte nikam, kam by se dalo přistupovat přes prohlížeč
 * (soubor .htaccess vedle v /inc/ to blokuje, pokud běžíte na Apache).
 */

// --- Přístup ke Cloudflare D1 (krok 2–4 v návodu) ---
define('CF_ACCOUNT_ID',  'f8487bfe6e621ac8bf67327e014ddc41');
define('CF_DATABASE_ID', '786c46de-7bb3-4e3d-b3ac-30b4ab338a9b');
define('CF_API_TOKEN',   'cfut_LVbfpIJhoGe3JLYUF6yUVqwBNotRnMFotYdApq1S1da63281');

// --- Heslo do administrace (krok 6 v návodu) ---
// Nikdy sem nedávejte heslo v čitelné podobě — jen vygenerovaný hash.
define('ADMIN_PASSWORD_HASH', 'SEM_VLOZTE_VYGENEROVANY_HASH');

// --- Kategorie, ze kterých se vybírá při přidávání článku ---
define('ARTICLE_TAGS', ['Aktualita', 'Zápas', 'Tisková zpráva', 'Trénink', 'Rozhovor', 'Ostatní']);

// --- Kam se ukládají nahrané fotky k článkům ---
define('UPLOAD_DIR', __DIR__ . '/../img/media/');   // fyzická cesta na serveru
define('UPLOAD_URL_PREFIX', '/img/media/');         // jak se cesta zapíše na webu

// --- Krátký "cache" soubor, aby se veřejná stránka media.php neptala databáze při každé návštěvě ---
define('ARTICLES_CACHE_FILE', __DIR__ . '/../cache/articles.json');
define('ARTICLES_CACHE_TTL', 60); // v sekundách
