
const rosterData = [
    { slug: 'dejvi',     name: 'Dejví',     photo: '../img/dejvi.png',     fights: 1, wins: 1, losses: 0, draws: 0 },
    { slug: 'housenka',  name: 'Housenka',  photo: '../img/housenka.png',  fights: 2, wins: 2, losses: 0, draws: 0 },
    { slug: 'houser',    name: 'Houser',    photo: '../img/houser.png',    fights: 3, wins: 0, losses: 3, draws: 0 },
    { slug: 'tasemnice', name: 'Tasemnice', photo: '../img/tasemnice.png', fights: 2, wins: 1, losses: 1, draws: 0 },
    { slug: 'zazvor',    name: 'Zazvor',    photo: '../img/zazvor.png',    fights: 0, wins: 0, losses: 0, draws: 0 }
];

function fightsWord(n) {
    if (n === 1) return 'zápas';
    if (n >= 2 && n <= 4) return 'zápasy';
    return 'zápasů';
}

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('rosterGrid');

    // seřadí od nejvíc zápasů; při shodě zůstává pořadí z pole výše
    const sorted = [...rosterData].sort((a, b) => b.fights - a.fights);

    grid.innerHTML = sorted.map((f, i) => `
        <a class="roster-card" href="${f.slug}/">
            <span class="roster-rank">#${i + 1}</span>
            <div class="roster-photo-wrap">
                <img src="${f.photo}" alt="${f.name}" onerror="this.style.opacity='0';">
            </div>
            <h3 class="roster-name">${f.name}</h3>
            <div class="roster-record">
                <div><div class="num win">${f.wins}</div><div class="lbl">Výhry</div></div>
                <div><div class="num loss">${f.losses}</div><div class="lbl">Prohry</div></div>
                <div><div class="num">${f.draws}</div><div class="lbl">Remízy</div></div>
            </div>
            <div class="roster-fights">${f.fights} ${fightsWord(f.fights)}</div>
        </a>
    `).join('');

    // --- mobilní menu (stejné chování jako na zbytku webu) ---
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
});
