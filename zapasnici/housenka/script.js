const fighterData = {
    name: "Marek Housenka",
    nickname: "„Housenka“",
    photoUrl: "", // sem vložte cestu/URL k fotce, např. "petr.jpg" — necháte-li prázdné, zobrazí se zástupný obrázek

    weightClass: "Velterová váha (77,1 kg)",
    weightKg: "77 kg",
    height: "194 cm",
    team: "Petr v Kleci TEAM",
    age: "17",

    record: { wins: 2, losses: 0, draws: 0 },
    methods: { ko: 1, sub: 0, dec: 0, xx: 1 }, // KO/TKO, submise, na body — mělo by dát dohromady počet výher

    recentFights: [
        { result: "coming", opponent: "Antonín Zázvor",method: "?",               round: "?",       event: "Petr v Kleci ?", date: "1. 9. 2027" },
        { result: "win",    opponent: "David Houser",  method: "Vzdání soupeře",  round: "1. kolo", event: "Petr v Kleci 2", date: "28. 10. 2025" },
        { result: "win",    opponent: "David Houser",  method: "KO",              round: "2. kolo", event: "Petr v Kleci 1", date: "27. 9. 2024" },     
    ]
};

/* ============================================================
   VYKRESLENÍ — není potřeba upravovat
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    // --- Hero texty ---
    document.getElementById('heroName').textContent = fighterData.name;
    document.getElementById('heroNick').textContent = fighterData.nickname;

    const r = fighterData.record;
    const total = r.wins + r.losses + r.draws;
    const winRate = total ? Math.round((r.wins / total) * 100) : 0;

    const heroRecord = document.getElementById('heroRecord');
    heroRecord.innerHTML = `
        <div><div class="num win">${r.wins}</div><div class="lbl">Výhry</div></div>
        <div><div class="num loss">${r.losses}</div><div class="lbl">Prohry</div></div>
        <div><div class="num">${r.draws}</div><div class="lbl">Remízy</div></div>
    `;

    // --- Fotka ---
    if (fighterData.photoUrl) {
        const img = document.createElement('img');
        img.src = fighterData.photoUrl;
        img.alt = fighterData.name;
        document.getElementById('heroPhotoPlaceholder').replaceWith(img);
    }

    

    // --- Donut graf W/P/R ---
    const segments = [
        { key: 'Výhry',  value: r.wins,   color: 'var(--accent)' },
        { key: 'Prohry', value: r.losses, color: 'var(--accent-warn)' },
        { key: 'Remízy', value: r.draws,  color: 'var(--text-muted)' }
    ].filter(s => s.value > 0);

    const radius = 80, circumference = 2 * Math.PI * radius;
    let offset = 0;
    const svgNS = 'http://www.w3.org/2000/svg';
    const group = document.getElementById('donutSegments');
    segments.forEach(seg => {
        const len = (seg.value / total) * circumference;
        const circle = document.createElementNS(svgNS, 'circle');
        circle.setAttribute('cx', 100);
        circle.setAttribute('cy', 100);
        circle.setAttribute('r', radius);
        circle.setAttribute('stroke', seg.color);
        circle.setAttribute('stroke-dasharray', `${len} ${circumference - len}`);
        circle.setAttribute('stroke-dashoffset', -offset);
        circle.style.transition = 'stroke-dasharray 1s ease';
        group.appendChild(circle);
        offset += len;
    });

    document.getElementById('donutLegend').innerHTML = segments.map(s => `
        <div class="legend-item"><span class="legend-dot" style="background:${s.color}"></span>${s.key}: <b>${s.value}</b></div>
    `).join('');

    // --- Způsoby vítězství ---
    const m = fighterData.methods;
    const maxM = Math.max(m.ko, m.sub, m.dec, m.xx, 1);
    const methodData = [
        { lbl: 'KO / TKO',  val: m.ko,  color: 'var(--accent)' },
        { lbl: 'Submise',   val: m.sub, color: 'var(--accent-soft)' },
        { lbl: 'Na body',   val: m.dec, color: 'var(--text-muted)' },
        { lbl: 'Vzdání soupeře', val: m.xx, color: 'var(--text-muted)'}
    ];
    document.getElementById('methodRows').innerHTML = methodData.map(row => `
        <div class="method-row">
            <div class="m-top"><span>${row.lbl}</span><b>${row.val}</b></div>
            <div class="m-bar-track"><div class="m-bar-fill" data-width="${(row.val / maxM) * 100}" style="background:${row.color}"></div></div>
        </div>
    `).join('');

    // --- Poslední zápasy ---
    const resultMap = { win: { badge: 'V', cls: 'win' }, loss: { badge: 'P', cls: 'loss' }, draw: { badge: 'R', cls: 'draw' }, coming: { badge: '?', cls: 'coming' }};
    document.getElementById('fightList').innerHTML = fighterData.recentFights.map(f => {
        const rm = resultMap[f.result];
        return `
        <div class="fight-row">
            <div class="badge ${rm.cls}">${rm.badge}</div>
            <div class="fight-opponent">${f.opponent}<small>${f.round}</small></div>
            <div class="fight-method">${f.method}</div>
            <div></div>
            <div class="fight-event"><b>${f.event}</b>${f.date}</div>
        </div>`;
    }).join('');

    // --- Rychlá fakta (O mně) ---
    document.getElementById('factsPanel').innerHTML = `
        <div class="frow"><span>Váhová kategorie</span><span>${fighterData.weightClass}</span></div>
        <div class="frow"><span>Tým / gym</span><span>${fighterData.team}</span></div>
        <div class="frow"><span>Věk</span><span>${fighterData.age}</span></div>
        <div class="frow"><span>Bilance</span><span>${r.wins}-${r.losses}-${r.draws}</span></div>
        <div class="frow"><span>Úspěšnost</span><span>${winRate} %</span></div>
    `;

    // --- Animace při scrollu ---
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealEls = document.querySelectorAll('.reveal');
    const barEls = document.querySelectorAll('.m-bar-fill');

    if (reduceMotion) {
        revealEls.forEach(el => el.classList.add('in'));
        barEls.forEach(el => el.style.width = el.dataset.width + '%');
        document.getElementById('winRateNum').textContent = winRate + ' %';
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealEls.forEach(el => io.observe(el));

        // rozjede pruhy metod a počítadlo úspěšnosti, jakmile je sekce vidět
        const statsSection = document.getElementById('stats');
        const statsIO = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    barEls.forEach(el => el.style.width = el.dataset.width + '%');
                    let cur = 0;
                    const target = winRate;
                    const step = () => {
                        cur += Math.max(1, Math.round(target / 30));
                        if (cur >= target) cur = target;
                        document.getElementById('winRateNum').textContent = cur + ' %';
                        if (cur < target) requestAnimationFrame(step);
                    };
                    requestAnimationFrame(step);
                    statsIO.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        statsIO.observe(statsSection);
    }

    // --- Mobilní menu ---
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

    // --- Aktivní odkaz v navigaci dle scrollu ---
    const sections = ['hero', 'stats', 'fights',].map(id => document.getElementById(id));
    const navA = document.querySelectorAll('.nav-links a');
    const navIO = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const link = document.querySelector(`.nav-links a[href="#${entry.target.id}"]`);
            if (!link) return;
            if (entry.isIntersecting) {
                navA.forEach(a => a.classList.remove('active'));
                link.classList.add('active');
            }
        });
    }, { threshold: 0.5 });
    sections.forEach(sec => sec && navIO.observe(sec));
});