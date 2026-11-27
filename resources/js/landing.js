const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/* 1. Reveal bertahap: satu observer untuk semua elemen ber-atribut data-reveal. */
const revealTargets = document.querySelectorAll('[data-reveal]');

if (reduceMotion.matches || !('IntersectionObserver' in window)) {
    revealTargets.forEach((el) => el.classList.add('is-revealed'));
} else {
    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        }
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.15 });

    revealTargets.forEach((el) => observer.observe(el));
    document.body.classList.add('reveal-ready');
}

/* 2. Spotlight kursor pada kartu bento, dibatasi satu frame per gerakan. */
if (!reduceMotion.matches && window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('.bento-card').forEach((card) => {
        let frame = 0;

        card.addEventListener('pointermove', (event) => {
            if (frame) return;

            frame = requestAnimationFrame(() => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--sx', `${event.clientX - rect.left}px`);
                card.style.setProperty('--sy', `${event.clientY - rect.top}px`);
                frame = 0;
            });
        });
    });
}

const navbar = document.querySelector('.hero-nav');
const toggle = navbar.querySelector('.nav-toggle');
const desktop = window.matchMedia('(width > 860px)');

const setMenuOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
};

const menuIsOpen = () => toggle.getAttribute('aria-expanded') === 'true';

toggle.addEventListener('click', () => setMenuOpen(!menuIsOpen()));
desktop.addEventListener('change', () => setMenuOpen(false));

navbar.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || !menuIsOpen()) return;
    setMenuOpen(false);
    toggle.focus();
});

navbar.addEventListener('click', (event) => {
    const link = event.target.closest('a[href^="#"]');
    if (!link || !menuIsOpen()) return;
    setMenuOpen(false);
    const target = document.getElementById(link.hash.slice(1));
    target?.setAttribute('tabindex', '-1');
    target?.focus({ preventScroll: true });
});

document.addEventListener('pointerdown', (event) => {
    if (menuIsOpen() && !navbar.contains(event.target)) setMenuOpen(false);
});

/* 3. Satu pembaruan per frame untuk efek gulir landing page. */
const ruler = document.querySelector('.scroll-ruler');
const stage = document.querySelector('.hero-stage');
let scrollFrame = 0;

const updateScroll = () => {
    scrollFrame = 0;

    const root = document.documentElement;
    const distance = root.scrollHeight - root.clientHeight;
    const progress = distance > 0 ? Math.min(100, Math.max(0, window.scrollY / distance * 100)) : 0;

    navbar.classList.toggle('is-scrolled', window.scrollY > 12);
    ruler.style.setProperty('--scroll-progress', `${progress}%`);
    ruler.firstElementChild.textContent = `${Math.round(progress)}%`;

    if (desktop.matches && !reduceMotion.matches) {
        const entry = 1 - stage.getBoundingClientRect().top / root.clientHeight;
        stage.style.setProperty('--par', Math.min(36, Math.max(0, entry * 36)).toFixed(1));
    } else {
        stage.style.removeProperty('--par');
    }
};

const scheduleScroll = () => {
    if (!scrollFrame) scrollFrame = requestAnimationFrame(updateScroll);
};

window.addEventListener('scroll', scheduleScroll, { passive: true });
window.addEventListener('resize', scheduleScroll);
scheduleScroll();
