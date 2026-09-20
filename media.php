<?php
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/d1.php';

$rows = d1_fetch_published_articles();

// Převod řádků z databáze do tvaru, který čeká JS na stránce (stejně jako dřív, jen teď z DB).
$jsArticles = array_map(function ($row) {
    $paragraphs = preg_split('/\n\s*\n/', trim($row['content']));
    $paragraphs = array_values(array_filter(array_map('trim', $paragraphs)));
    return [
        'slug'     => $row['slug'],
        'date'     => $row['article_date'],
        'tag'      => $row['tag'],
        'title'    => $row['title'],
        'image'    => $row['image'] ?: null,
        'linkUrl'  => $row['link_url'] ?: null,
        'content'  => $paragraphs,
    ];
}, $rows);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Petr v kleci Media — Aktuality a zprávy</title>
<meta name="description" content="Oficiální zdroj aktualit, zpráv a tiskových informací o MMA zápasníkovi Petrovi a projektu Petr v kleci.">
<meta property="og:title" content="Petr v kleci Media">
<meta property="og:description" content="Aktuality, zápasy a tiskové zprávy o MMA zápasníkovi Petrovi.">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>

/* ============================================
   1) PROMĚNNÉ / ZÁKLAD — stejné jako na ostatních stránkách
   ============================================ */
:root {
    --bg-color: #1a1622;
    --surface-color: #272236;
    --surface-hover: #322b46;
    --text-main: #f4f1f8;
    --text-muted: #a39bb3;
    --accent: #9d4edd;
    --accent-glow: rgba(157, 78, 221, 0.6);
    --glass-border: rgba(157, 78, 221, 0.3);
    --nav-height: 70px;

    --accent-warn: #ff5c6c;
    --accent-soft: #c299f0;

    --radius-lg: 20px;
    --radius-md: 14px;
    --radius-sm: 10px;
    --container: 1120px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

body {
    background: var(--bg-color);
    color: var(--text-main);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    overflow-x: hidden;
}

img { max-width: 100%; display: block; }
a { color: inherit; text-decoration: none; }

.wrap { max-width: var(--container); margin: 0 auto; padding: 0 24px; }

h1, h2, h3, .display {
    font-family: 'Bebas Neue', 'Arial Narrow', sans-serif;
    font-weight: 400;
    letter-spacing: 0.02em;
    line-height: 0.95;
}

::selection { background: var(--accent); color: #fff; }
:focus-visible { outline: 2px solid var(--accent-soft); outline-offset: 3px; }

.bg-pattern {
    position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.5;
    background-image:
        linear-gradient(var(--glass-border) 1px, transparent 1px),
        linear-gradient(90deg, var(--glass-border) 1px, transparent 1px);
    background-size: 64px 64px;
    -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 10%, transparent 70%);
            mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 10%, transparent 70%);
}

.panel {
    background: var(--surface-color);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
}

.btn {
    padding: 13px 26px;
    font-weight: 600;
    font-size: 0.94rem;
    border-radius: var(--radius-sm);
    transition: box-shadow 0.25s ease, transform 0.15s ease, background 0.2s ease;
    display: inline-block;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { box-shadow: 0 0 26px var(--accent-glow); transform: translateY(-2px); }
.btn-ghost { background: transparent; border: 1px solid var(--glass-border); color: var(--text-main); }
.btn-ghost:hover { background: var(--surface-hover); border-color: var(--accent); }

.reveal { opacity: 0; transform: translateY(18px); transition: opacity 0.7s ease, transform 0.7s ease; }
.reveal.in { opacity: 1; transform: translateY(0); }

@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
}

/* ============================================
   2) NAVIGACE — sdílené napříč weby, neměnit
   ============================================ */
nav {
    position: fixed;
    top: 0; width: 100%; height: var(--nav-height);
    padding: 0 40px;
    display: flex; justify-content: space-between; align-items: center;
    background: rgba(26, 22, 34, 0.9);
    z-index: 1000;
    border-bottom: 1px solid var(--glass-border);
}

.logo-container { height: 40px; }
.logo-img { height: 100%; width: auto; object-fit: contain; }

.nav-links { display: flex; gap: 30px; }
.nav-links a {
    color: var(--text-main); text-decoration: none; font-weight: 800;
    font-size: 0.85rem; letter-spacing: 2px; text-transform: uppercase;
    position: relative; padding: 5px 0; transition: color 0.3s;
}
.nav-links a:hover { color: var(--accent); text-shadow: 0 0 10px var(--accent-glow); }

/* --- doplňkové, jen pro tuto stránku --- */
.nav-links a.active { color: var(--accent); text-shadow: 0 0 10px var(--accent-glow); }
#logo-text { display: none; font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: 0.06em; color: var(--text-main); }

.nav-toggle { display: none; flex-direction: column; gap: 5px; background: none; border: none; cursor: pointer; padding: 8px; }
.nav-toggle span { width: 24px; height: 2px; background: var(--text-main); display: block; }

@media (max-width: 700px) {
    .nav-links { display: none; }
    .nav-links.open {
        display: flex; position: fixed; top: var(--nav-height); left: 0; right: 0; bottom: 0;
        background: var(--bg-color); flex-direction: column; align-items: flex-start;
        padding: 2.5rem 24px; gap: 1.6rem; z-index: 999;
    }
    .nav-links.open a { font-size: 1.3rem; }
    .nav-toggle { display: flex; }
}

/* ============================================
   3) HUB — hlavička sekce a filtr
   ============================================ */
main { position: relative; z-index: 1; padding-top: var(--nav-height); }

.hub-hero { padding: 5rem 0 3rem; }
.hub-hero .hero-tag {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--accent-soft); font-size: 0.92rem; font-weight: 500; margin-bottom: 1rem;
}
.hub-hero .hero-tag::before {
    content: ''; width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent); box-shadow: 0 0 10px var(--accent-glow); display: inline-block;
}
.hub-hero h1 { font-size: clamp(2.6rem, 6vw, 4.4rem); text-transform: uppercase; }
.hub-hero .lead { color: var(--text-muted); max-width: 62ch; margin-top: 1rem; font-size: 1.05rem; }

.about-note {
    margin-top: 2rem;
    padding: 1.4rem 1.6rem;
    max-width: 70ch;
}
.about-note p { color: var(--text-muted); font-size: 0.92rem; }
.about-note p + p { margin-top: 0.6rem; }
.about-note b { color: var(--text-main); }

.filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin: 2.6rem 0 2.2rem; }
.filter-chip {
    padding: 8px 18px;
    font-size: 0.85rem;
    font-weight: 600;
    background: var(--surface-color);
    border: 1px solid var(--glass-border);
    border-radius: 999px;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
}
.filter-chip:hover { color: var(--text-main); border-color: var(--accent); }
.filter-chip.active { background: var(--accent); border-color: var(--accent); color: #fff; }

/* ============================================
   4) MŘÍŽKA ČLÁNKŮ
   ============================================ */
.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.4rem;
    padding-bottom: 6rem;
}

.article-card {
    display: block;
    padding: 1.7rem;
    background: var(--surface-color);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}
.article-card:hover { transform: translateY(-4px); border-color: var(--accent); background: var(--surface-hover); }

.article-card-img {
    width: 100%; aspect-ratio: 16 / 10; object-fit: cover;
    border-radius: var(--radius-md); margin-bottom: 1.2rem;
}

.article-meta { display: flex; align-items: center; gap: 10px; margin-bottom: 0.9rem; }
.article-tag {
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
    color: var(--accent-soft); background: rgba(157, 78, 221, 0.14);
    padding: 4px 10px; border-radius: 999px;
}
.article-date { font-size: 0.8rem; color: var(--text-muted); }

.article-card h2 { font-size: 1.55rem; text-transform: none; letter-spacing: 0; margin-bottom: 0.6rem; }
.article-excerpt {
    color: var(--text-muted); font-size: 0.92rem;
    display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}
.article-more { display: inline-block; margin-top: 1rem; color: var(--accent-soft); font-size: 0.86rem; font-weight: 600; }

.empty-state { color: var(--text-muted); padding: 3rem 0; text-align: center; }

/* ============================================
   5) DETAIL ČLÁNKU
   ============================================ */
#articleView { display: none; padding-bottom: 6rem; }
.article-back {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--accent-soft); font-size: 0.9rem; font-weight: 600; margin-bottom: 2rem;
}
.article-back:hover { color: var(--accent); }

#detailImageWrap img {
    width: 100%; max-height: 420px; object-fit: cover;
    border-radius: var(--radius-lg); margin-bottom: 2rem;
}

.article-header { max-width: 70ch; margin-bottom: 2.2rem; }
.article-header h1 { font-size: clamp(2.2rem, 5vw, 3.4rem); text-transform: none; letter-spacing: 0; margin-top: 0.8rem; }

.article-body { max-width: 68ch; }
.article-body p { color: var(--text-muted); margin-bottom: 1.2rem; font-size: 1.02rem; }
.article-body p:first-of-type { color: var(--text-main); font-size: 1.1rem; }

#detailLinkWrap { margin-top: 1.6rem; }

@media (max-width: 640px) {
    .articles-grid { grid-template-columns: 1fr; }
}

/* ============================================
   6) PATIČKA — sdílená napříč weby, neměnit
   ============================================ */
footer {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 50px;
    background: #0f0d14;
    border-top: 1px solid var(--glass-border);
    color: #4b4263;
    font-size: 0.9rem;
    letter-spacing: 4px;
    text-transform: uppercase;
    font-weight: 800;
}

.footer-container { display: flex; flex-direction: column; }
.footer-contact { margin: 10px 0 30px 0; }
.footer-contact a { text-decoration: none; transition: .3s; color: var(--text-muted); }
.footer-contact a:hover { text-shadow: 0 0 10px var(--accent-glow); color: var(--accent); }

</style>
</head>
<body>

<div class="bg-pattern" aria-hidden="true"></div>

<!-- ============ NAVIGACE ============ -->
<nav>
    <div class="logo-container">
        <a href="../../#" class="logo-img" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
            <img src="../../img/logo-transparent.png" alt="Petr v kleci Logo" class="logo-img" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
        </a>
        <span id="logo-text">PETR V KLECI</span>
    </div>

    <div class="nav-links" id="navLinks">
        <a href="../../#">Domů</a>
        <a href="https://petrvkleci.fun/live/" target="_blank">Živě</a>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Otevřít menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

<main>
    <!-- ============ PŘEHLED (HUB) ============ -->
    <section id="hubView">
        <div class="wrap">
            <div class="hub-hero reveal">
                <div class="hero-tag">Petr v kleci Media</div>
                <h1>Aktuality &amp; zprávy</h1>
                <p class="lead">
                    Oficiální novinky z přípravy, zápasů a zákulisí projektu Petr v kleci —
                    jedno místo pro fanoušky, novináře i partnery.
                </p>
                <div class="about-note panel">
                    <p><b>O tomto webu:</b> Petr v kleci je profil a mediální hub MMA zápasníka Petra.
                    Tato stránka slouží jako oficiální a průběžně aktualizovaný zdroj informací o jeho
                    kariéře, zápasech a veřejném dění.</p>
                    <p>Každý článek níže je opatřen datem a kategorií a odpovídá aktuálnímu stavu k danému dni zveřejnění.</p>
                </div>
            </div>

            <div class="filter-bar reveal" id="filterBar"></div>
            <div class="articles-grid reveal" id="articlesGrid"></div>
        </div>
    </section>

    <!-- ============ DETAIL ČLÁNKU ============ -->
    <section id="articleView">
        <div class="wrap">
            <a href="#" class="article-back" id="backLink">&larr; Zpět na přehled</a>
            <div id="detailImageWrap"></div>
            <div class="article-header">
                <div class="article-meta">
                    <span class="article-tag" id="detailTag"></span>
                    <span class="article-date" id="detailDate"></span>
                </div>
                <h1 id="detailTitle"></h1>
            </div>
            <div class="article-body" id="detailBody"></div>
            <div id="detailLinkWrap"></div>
            <a href="#" class="btn btn-ghost" id="backLinkBottom" style="margin-top:1.4rem;">&larr; Zpět na přehled</a>
        </div>
    </section>
</main>

<!-- ============ PATIČKA ============ -->
<footer class="footer">
    <div class="footer-container">

        <div class="footer-contact">
            <a href="../files/prohlaseni_petr_v_kleci.pdf" target="_blank">Disclaimer</a> |
            <a href="mailto:petrvkleci@petrvkleci.fun">E-Mail</a> |
            <a href="https://instagram.petrvkleci.fun" target="_blank">Instagram</a> |
            <a href="https://youtube.petrvkleci.fun" target="_blank">YouTube</a>
        </div>

        <div class="footer-bottom">
            <p>© 2026 PETR V KLECI • All rights reserved</p>
        </div>

    </div>
</footer>

<script>
/* Data teď táhneme z Cloudflare D1 přes media.php (viz inc/d1.php) —
   sem se jen vloží výsledek jako obyčejný JSON. Nic tu ručně needitujte,
   nové články přidávejte v /admin/. */
const mediaData = {
    organization: {
        name: "Petr v kleci",
        url: "https://petrvkleci.fun/",
        description: "Petr v kleci je profil a mediální hub MMA zápasníka Petra – přináší aktuality, zápasy a tiskové zprávy.",
        sameAs: [
            "https://instagram.petrvkleci.fun",
            "https://youtube.petrvkleci.fun"
        ]
    },
    articles: <?php echo json_encode($jsArticles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('articlesGrid');
    const filterBar = document.getElementById('filterBar');
    const hubView = document.getElementById('hubView');
    const articleView = document.getElementById('articleView');
    const defaultTitle = document.title;

    const sorted = [...mediaData.articles].sort((a, b) => new Date(b.date) - new Date(a.date));
    let currentFilter = 'Vše';

    function formatDate(iso) {
        const d = new Date(iso + 'T00:00:00');
        return d.toLocaleDateString('cs-CZ', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function excerptOf(article) {
        const text = article.content[0] || '';
        return text.length > 150 ? text.slice(0, 150).trim() + '…' : text;
    }

    function renderFilters() {
        if (!sorted.length) { filterBar.innerHTML = ''; return; }
        const tags = ['Vše', ...new Set(mediaData.articles.map(a => a.tag))];
        filterBar.innerHTML = tags.map(t =>
            `<button class="filter-chip${t === currentFilter ? ' active' : ''}" data-tag="${t}">${t}</button>`
        ).join('');
        filterBar.querySelectorAll('.filter-chip').forEach(btn => {
            btn.addEventListener('click', () => {
                currentFilter = btn.dataset.tag;
                renderFilters();
                renderGrid();
            });
        });
    }

    function renderGrid() {
        if (!sorted.length) {
            grid.innerHTML = `<div class="empty-state">Zatím tu nejsou žádné články — první přidáte v administraci.</div>`;
            return;
        }
        const list = currentFilter === 'Vše' ? sorted : sorted.filter(a => a.tag === currentFilter);
        if (!list.length) {
            grid.innerHTML = `<div class="empty-state">V této kategorii zatím nejsou žádné články.</div>`;
            return;
        }
        grid.innerHTML = list.map(a => `
            <a class="article-card" href="#clanek-${a.slug}">
                ${a.image ? `<img class="article-card-img" src="${a.image}" alt="">` : ''}
                <div class="article-meta">
                    <span class="article-tag">${a.tag}</span>
                    <span class="article-date">${formatDate(a.date)}</span>
                </div>
                <h2>${a.title}</h2>
                <p class="article-excerpt">${excerptOf(a)}</p>
                <span class="article-more">Číst dál →</span>
            </a>
        `).join('');
    }

    function showArticle(slug) {
        const article = sorted.find(a => a.slug === slug);
        if (!article) { location.hash = ''; return; }
        document.getElementById('detailImageWrap').innerHTML = article.image
            ? `<img src="${article.image}" alt="">` : '';
        document.getElementById('detailTag').textContent = article.tag;
        document.getElementById('detailDate').textContent = formatDate(article.date);
        document.getElementById('detailTitle').textContent = article.title;
        document.getElementById('detailBody').innerHTML = article.content.map(p => `<p>${p}</p>`).join('');
        document.getElementById('detailLinkWrap').innerHTML = article.linkUrl
            ? `<a href="${article.linkUrl}" target="_blank" rel="noopener" class="btn btn-primary">Zdroj / více informací →</a>` : '';
        document.title = `${article.title} — Petr v kleci Media`;
        hubView.style.display = 'none';
        articleView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'auto' });
    }

    function showHub() {
        document.title = defaultTitle;
        articleView.style.display = 'none';
        hubView.style.display = 'block';
    }

    function checkHash() {
        const hash = decodeURIComponent(location.hash.slice(1));
        if (hash.startsWith('clanek-')) {
            showArticle(hash.replace('clanek-', ''));
        } else {
            showHub();
        }
    }

    window.addEventListener('hashchange', checkHash);
    document.getElementById('backLink').addEventListener('click', (e) => { e.preventDefault(); location.hash = ''; });
    document.getElementById('backLinkBottom').addEventListener('click', (e) => { e.preventDefault(); location.hash = ''; });

    renderFilters();
    renderGrid();
    checkHash();

    // --- reveal animace ---
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealEls = document.querySelectorAll('.reveal');
    if (reduceMotion) {
        revealEls.forEach(el => el.classList.add('in'));
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('in'); io.unobserve(entry.target); }
            });
        }, { threshold: 0.1 });
        revealEls.forEach(el => io.observe(el));
    }

    // --- mobilní menu ---
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    navToggle.addEventListener('click', () => {
        const isOpen = navLinks.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', isOpen);
    });
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        navLinks.classList.remove('open');
        navToggle.setAttribute('aria-expanded', false);
    }));

    // --- JSON-LD strukturovaná data (pro AI a vyhledávače) ---
    const orgLd = {
        "@context": "https://schema.org",
        "@type": "SportsOrganization",
        "name": mediaData.organization.name,
        "url": mediaData.organization.url,
        "sameAs": mediaData.organization.sameAs,
        "description": mediaData.organization.description
    };
    const itemListLd = {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "itemListElement": sorted.map((a, i) => ({
            "@type": "ListItem",
            "position": i + 1,
            "item": Object.assign({
                "@type": "NewsArticle",
                "headline": a.title,
                "datePublished": a.date,
                "articleSection": a.tag,
                "description": excerptOf(a),
                "publisher": { "@type": "Organization", "name": mediaData.organization.name },
                "url": `${location.href.split('#')[0]}#clanek-${a.slug}`
            }, a.image ? { "image": location.origin + a.image } : {})
        }))
    };
    [orgLd, itemListLd].forEach(obj => {
        const s = document.createElement('script');
        s.type = 'application/ld+json';
        s.textContent = JSON.stringify(obj);
        document.head.appendChild(s);
    });
});
</script>
</body>
</html>
