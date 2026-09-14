/* ============================================================
   ČLÁNKY — PŘIDÁVEJTE NOVÉ ZPRÁVY TADY

   Jak přidat nový článek:
   1) Zkopírujte jeden celý objekt {...} z pole "articles" níže.
   2) Vložte kopii kamkoli do stejného pole a upravte:
        - slug  → krátké ID bez diakritiky a mezer (použije se v odkazu)
        - date  → formát RRRR-MM-DD (podle data se řadí od nejnovějšího)
        - tag   → kategorie, např. "Aktualita", "Zápas", "Tisková zpráva"
        - title → nadpis
        - content → pole odstavců, každý řetězec je jeden odstavec textu
   3) Uložte soubor — nejnovější článek se automaticky objeví nahoře.
   ============================================================ */
const mediaData = {
    organization: {
        name: "Petr v Kleci",
        url: "https://petrvkleci.fun/",
        description: "",
        sameAs: [
            "https://instagram.petrvkleci.fun",
            "https://youtube.petrvkleci.fun"
        ]
    },

    articles: [
        {
            slug: "housenka-vs-zazvor-2027",
            date: "2026-09-14",
            tag: "Zápas",
            title: "Nový zápas je TADY!",
            content: [
                "Už 1. září 2027 proběhne zápas mezi Markem Housenkou a Antonínem Závorem.",
                "Zápas podle pravidel MMA do 77kg proběhne budete moci sledovat na našem YouTube Petr v Kleci.",
                "Bude se jednat o už třetí zápas pro zatím neporaženého Marka Housenku. Pro Antonína Zázvora to bude premiéra v kleci."
            ]
        },
        //{
        //    slug: "novy-kondicni-kouc",
        //    date: "2026-08-20",
        //    tag: "Aktualita",
        //    title: "Petr posiluje přípravu s novým kondičním koučem",
        //    content: [
        //        "Doplňte text o změně v realizačním týmu a co si od ní Petr slibuje.",
        //        "Můžete přidat citaci, konkrétní čísla z tréninku nebo plán na následující týdny."
        //    ]
        //},
        //{
        //    slug: "spoluprace-sportovni-centrum",
        //    date: "2026-07-30",
        //    tag: "Tisková zpráva",
        //    title: "Petr v kleci navazuje spolupráci s lokálním sportovním centrem",
        //    content: [
        //        "Doplňte oficiální znění tiskové zprávy o nové spolupráci — o co jde a od kdy platí.",
        //        "Poslední odstavec je vhodné místo pro kontakt na tiskového mluvčího nebo odkaz na další informace."
        //  ]
        //}
    ]
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
        const list = currentFilter === 'Vše' ? sorted : sorted.filter(a => a.tag === currentFilter);
        if (!list.length) {
            grid.innerHTML = `<div class="empty-state">V této kategorii zatím nejsou žádné články.</div>`;
            return;
        }
        grid.innerHTML = list.map(a => `
            <a class="article-card" href="#clanek-${a.slug}">
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
        document.getElementById('detailTag').textContent = article.tag;
        document.getElementById('detailDate').textContent = formatDate(article.date);
        document.getElementById('detailTitle').textContent = article.title;
        document.getElementById('detailBody').innerHTML = article.content.map(p => `<p>${p}</p>`).join('');
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
            "item": {
                "@type": "NewsArticle",
                "headline": a.title,
                "datePublished": a.date,
                "articleSection": a.tag,
                "description": excerptOf(a),
                "publisher": { "@type": "Organization", "name": mediaData.organization.name },
                "url": `${location.href.split('#')[0]}#clanek-${a.slug}`
            }
        }))
    };
    [orgLd, itemListLd].forEach(obj => {
        const s = document.createElement('script');
        s.type = 'application/ld+json';
        s.textContent = JSON.stringify(obj);
        document.head.appendChild(s);
    });
});
