// Small, dependency-free behaviour for customer pages.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const bn = (v) => String(v).replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d]);

// Reveal on scroll
const reveal = () => {
    const els = document.querySelectorAll('.t-reveal');
    if (reduce || !('IntersectionObserver' in window)) { els.forEach((e) => e.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -6% 0px' });
    els.forEach((e) => io.observe(e));
};

// "Together for" counters: <span data-since="2022-02-14" data-unit="days|ymd">
const counters = () => document.querySelectorAll('[data-since]').forEach((el) => {
    const since = new Date(el.dataset.since + 'T00:00:00');
    if (isNaN(since)) return;
    const now = new Date();
    if (el.dataset.unit === 'ymd') {
        let y = now.getFullYear() - since.getFullYear(), m = now.getMonth() - since.getMonth(), d = now.getDate() - since.getDate();
        if (d < 0) { m--; d += new Date(now.getFullYear(), now.getMonth(), 0).getDate(); }
        if (m < 0) { y--; m += 12; }
        el.textContent = `${bn(Math.max(y, 0))} বছর ${bn(Math.max(m, 0))} মাস ${bn(Math.max(d, 0))} দিন`;
    } else {
        const days = Math.max(0, Math.floor((now - since) / 864e5));
        if (reduce) { el.textContent = bn(days.toLocaleString('en-US')); return; }
        const start = performance.now(), dur = 1400;
        const step = (t) => { const p = Math.min(1, (t - start) / dur); el.textContent = bn(Math.round(days * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US')); if (p < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step);
    }
});

// Countdown to a date (birthday/event). Recurring birthdays roll to the next one.
const countdowns = () => document.querySelectorAll('[data-countdown]').forEach((el) => {
    let target = new Date(el.dataset.countdown + 'T00:00:00');
    if (isNaN(target)) return;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    if (el.dataset.recurring) { target.setFullYear(today.getFullYear()); if (target < today) target.setFullYear(today.getFullYear() + 1); }
    const out = el.querySelector('[data-countdown-out]'), done = el.querySelector('[data-countdown-done]');
    const tick = () => {
        const diff = target - new Date();
        const isToday = el.dataset.recurring && target.getTime() === today.getTime();
        if (diff <= 0 || isToday) { if (out) out.hidden = true; if (done) done.hidden = false; return; }
        const d = Math.floor(diff / 864e5), h = Math.floor(diff / 36e5) % 24, m = Math.floor(diff / 6e4) % 60, s = Math.floor(diff / 1e3) % 60;
        el.querySelectorAll('[data-cd]').forEach((n) => (n.textContent = bn(String({ d, h, m, s }[n.dataset.cd]).padStart(2, '0'))));
        setTimeout(tick, 1000);
    };
    tick();
});

// Envelope
const envelopes = () => document.querySelectorAll('[data-envelope]').forEach((env) => {
    const open = () => {
        env.classList.add('is-open');
        const target = document.querySelector(env.dataset.envelope);
        setTimeout(() => { if (target) { target.hidden = false; target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); } }, reduce ? 0 : 900);
    };
    env.addEventListener('click', open);
    env.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
});

// Lightbox for every [data-gallery]
const lightbox = () => {
    const lb = document.querySelector('[data-lightbox]'); if (!lb) return;
    const img = lb.querySelector('[data-lb-img]'), cap = lb.querySelector('[data-lb-caption]');
    let list = [], idx = 0, lastFocus = null;
    const show = (i) => { idx = (i + list.length) % list.length; img.src = list[idx].dataset.src; cap.textContent = list[idx].dataset.caption || ''; };
    document.querySelectorAll('[data-gallery]').forEach((g) => g.querySelectorAll('[data-lb-open]').forEach((btn, i, all) => btn.addEventListener('click', () => {
        list = Array.from(all); lastFocus = btn; show(i); lb.hidden = false; document.body.style.overflow = 'hidden'; lb.querySelector('[data-lb-close]').focus();
    })));
    const close = () => { lb.hidden = true; document.body.style.overflow = ''; img.src = ''; lastFocus?.focus(); };
    lb.querySelector('[data-lb-close]').addEventListener('click', close);
    lb.querySelector('[data-lb-prev]').addEventListener('click', () => show(idx - 1));
    lb.querySelector('[data-lb-next]').addEventListener('click', () => show(idx + 1));
    lb.addEventListener('click', (e) => { if (e.target === lb) close(); });
    document.addEventListener('keydown', (e) => { if (lb.hidden) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowLeft') show(idx - 1); if (e.key === 'ArrowRight') show(idx + 1); });
    let x0 = null;
    lb.addEventListener('touchstart', (e) => (x0 = e.touches[0].clientX), { passive: true });
    lb.addEventListener('touchend', (e) => { if (x0 === null) return; const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) show(idx + (dx < 0 ? 1 : -1)); x0 = null; });
};

// One short confetti burst (canvas, ~1.5s), skipped for reduced motion.
const confetti = () => {
    const host = document.querySelector('[data-confetti]'); if (!host || reduce) return;
    const c = document.createElement('canvas'); c.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:60';
    document.body.appendChild(c); const ctx = c.getContext('2d');
    const W = (c.width = innerWidth), H = (c.height = innerHeight);
    const colors = (host.dataset.confetti || '#ffb703,#e9839f,#2b2d8f,#2f6b4f').split(',');
    const parts = Array.from({ length: Math.min(140, Math.round(W / 4)) }, () => ({ x: W / 2 + (Math.random() - .5) * W * .3, y: H * .35, vx: (Math.random() - .5) * 14, vy: -Math.random() * 14 - 4, s: Math.random() * 7 + 4, r: Math.random() * 6, c: colors[Math.floor(Math.random() * colors.length)] }));
    const t0 = performance.now();
    const frame = (t) => {
        ctx.clearRect(0, 0, W, H);
        parts.forEach((p) => { p.vy += .38; p.vx *= .99; p.x += p.vx; p.y += p.vy; p.r += .1; ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.r); ctx.fillStyle = p.c; ctx.fillRect(-p.s / 2, -p.s / 4, p.s, p.s / 2); ctx.restore(); });
        if (t - t0 < 2600) requestAnimationFrame(frame); else c.remove();
    };
    requestAnimationFrame(frame);
};

document.documentElement.classList.remove('no-js');
const boot = () => { reveal(); counters(); countdowns(); envelopes(); lightbox(); setTimeout(confetti, 400); };
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
