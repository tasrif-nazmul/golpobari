// Small, dependency-free behaviour for customer pages.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const bn = (v) => String(v).replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d]);
const isDraft = document.body.hasAttribute('data-draft');
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

// Reveal on scroll
const reveal = () => {
    const els = $$('.t-reveal');
    if (reduce || isDraft || !('IntersectionObserver' in window)) { els.forEach((e) => e.classList.add('is-in')); return; }
    const io = new IntersectionObserver((entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -6% 0px' });
    els.forEach((e) => io.observe(e));
};

// Split a heading into letters that rise one by one: <h1 data-split>. Keeps child elements and word wrapping.
const split = () => $$('[data-split]').forEach((el) => {
    if (reduce || isDraft) return;
    el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
    const graphemes = (t) => (window.Intl && Intl.Segmenter ? Array.from(new Intl.Segmenter('bn', { granularity: 'grapheme' }).segment(t), (x) => x.segment) : Array.from(t));
    let ci = 0;
    const walk = (node) => Array.from(node.childNodes).forEach((n) => {
        if (n.nodeType === 1) { n.setAttribute('aria-hidden', 'true'); walk(n); return; }
        if (n.nodeType !== 3 || !n.textContent.trim()) return;
        const frag = document.createDocumentFragment();
        n.textContent.split(/(\s+)/).forEach((word) => {
            if (!word) return;
            if (/^\s+$/.test(word)) { frag.appendChild(document.createTextNode(' ')); return; }
            const w = document.createElement('span');
            w.style.display = 'inline-block'; w.style.whiteSpace = 'nowrap'; w.setAttribute('aria-hidden', 'true');
            graphemes(word).forEach((ch) => { const c = document.createElement('span'); c.className = 't-char'; c.style.setProperty('--ci', ci++); c.textContent = ch; w.appendChild(c); });
            frag.appendChild(w);
        });
        node.replaceChild(frag, n);
    });
    walk(el);
    requestAnimationFrame(() => el.classList.add('is-split'));
});

// Typewriter for a block of text when it comes into view: <p data-type>
const typewriter = () => $$('[data-type]').forEach((el) => {
    if (reduce || isDraft) return;
    const full = el.textContent; const h = el.offsetHeight;
    el.style.minHeight = h + 'px'; el.textContent = '';
    el.setAttribute('aria-label', full);
    const run = () => {
        let i = 0; const speed = Math.max(8, Math.min(40, 2200 / full.length));
        const tick = () => { el.textContent = full.slice(0, ++i); if (i < full.length) setTimeout(tick, speed); else el.style.minHeight = ''; };
        tick();
    };
    const io = new IntersectionObserver((en) => { if (en[0].isIntersecting) { io.disconnect(); run(); } }, { threshold: 0.4 });
    io.observe(el);
});

// "Together for" counters: <span data-since="2022-02-14" data-unit="days|ymd">
const counters = () => $$('[data-since]').forEach((el) => {
    const since = new Date(el.dataset.since + 'T00:00:00');
    if (isNaN(since)) return;
    const now = new Date();
    if (el.dataset.unit === 'ymd') {
        let y = now.getFullYear() - since.getFullYear(), m = now.getMonth() - since.getMonth(), d = now.getDate() - since.getDate();
        if (d < 0) { m--; d += new Date(now.getFullYear(), now.getMonth(), 0).getDate(); }
        if (m < 0) { y--; m += 12; }
        const parts = { y: Math.max(y, 0), m: Math.max(m, 0), d: Math.max(d, 0) };
        const slots = $$('[data-ymd]', el);
        if (slots.length) slots.forEach((s) => (s.textContent = bn(parts[s.dataset.ymd])));
        else el.textContent = `${bn(parts.y)} বছর ${bn(parts.m)} মাস ${bn(parts.d)} দিন`;
    } else {
        const days = Math.max(0, Math.floor((now - since) / 864e5));
        if (reduce) { el.textContent = bn(days.toLocaleString('en-US')); return; }
        const start = performance.now(), dur = 1600;
        const step = (t) => { const p = Math.min(1, (t - start) / dur); el.textContent = bn(Math.round(days * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US')); if (p < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step);
    }
});

// Countdown to a date (birthday/event). Recurring birthdays roll to the next one.
const countdowns = () => $$('[data-countdown]').forEach((el) => {
    const target = new Date(el.dataset.countdown + 'T00:00:00');
    if (isNaN(target)) return;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    if (el.dataset.recurring) { target.setFullYear(today.getFullYear()); if (target < today) target.setFullYear(today.getFullYear() + 1); }
    const out = el.querySelector('[data-countdown-out]'), done = el.querySelector('[data-countdown-done]');
    const tick = () => {
        const diff = target - new Date();
        const isToday = el.dataset.recurring && target.getTime() === today.getTime();
        if (diff <= 0 || isToday) { if (out) out.hidden = true; if (done) done.hidden = false; return; }
        const d = Math.floor(diff / 864e5), h = Math.floor(diff / 36e5) % 24, m = Math.floor(diff / 6e4) % 60, s = Math.floor(diff / 1e3) % 60;
        $$('[data-cd]', el).forEach((n) => (n.textContent = bn(String({ d, h, m, s }[n.dataset.cd]).padStart(2, '0'))));
        setTimeout(tick, 1000);
    };
    tick();
});

/* ---------- celebration effects ---------- */
const palette = () => (document.querySelector('[data-confetti]')?.dataset.confetti || '#ffb703,#e9839f,#2b2d8f,#2f6b4f').split(',');

// Confetti burst (canvas, ~2.5s). Optional emoji shapes.
export const confetti = (opts = {}) => {
    if (reduce) return;
    const c = document.createElement('canvas'); c.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:95';
    document.body.appendChild(c); const ctx = c.getContext('2d');
    const W = (c.width = innerWidth), H = (c.height = innerHeight);
    const colors = opts.colors || palette();
    const ox = opts.x ?? W / 2, oy = opts.y ?? H * 0.35;
    const emoji = opts.emoji ? [].concat(opts.emoji) : null;
    const parts = Array.from({ length: opts.count || Math.min(160, Math.round(W / 3.5)) }, () => ({ x: ox + (Math.random() - 0.5) * 40, y: oy, vx: (Math.random() - 0.5) * 16, vy: -Math.random() * 15 - 4, s: Math.random() * 7 + 5, r: Math.random() * 6, c: colors[Math.floor(Math.random() * colors.length)], e: emoji ? emoji[Math.floor(Math.random() * emoji.length)] : null }));
    const t0 = performance.now();
    const frame = (t) => {
        ctx.clearRect(0, 0, W, H);
        parts.forEach((p) => {
            p.vy += 0.36; p.vx *= 0.99; p.x += p.vx; p.y += p.vy; p.r += 0.08;
            ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.r);
            if (p.e) { ctx.font = `${p.s * 3}px serif`; ctx.fillText(p.e, 0, 0); } else { ctx.fillStyle = p.c; ctx.fillRect(-p.s / 2, -p.s / 4, p.s, p.s / 2); }
            ctx.restore();
        });
        if (t - t0 < 2800) requestAnimationFrame(frame); else c.remove();
    };
    requestAnimationFrame(frame);
};

// Little floating emoji from a point: <button data-burst="❤">
const floatUp = (x, y, ch = '❤', n = 10) => {
    if (reduce) return;
    for (let i = 0; i < n; i++) {
        const s = document.createElement('span');
        s.className = 't-float'; s.textContent = ch;
        s.style.left = x + 'px'; s.style.top = y + 'px';
        s.style.setProperty('--dx', (Math.random() - 0.5) * 160 + 'px');
        s.style.setProperty('--rot', (Math.random() - 0.5) * 60 + 'deg');
        s.style.animationDelay = i * 45 + 'ms';
        s.style.fontSize = 14 + Math.random() * 20 + 'px';
        document.body.appendChild(s);
        setTimeout(() => s.remove(), 2200);
    }
};
const bursts = () => $$('[data-burst]').forEach((b) => b.addEventListener('click', (e) => {
    const r = b.getBoundingClientRect();
    floatUp(r.left + r.width / 2, r.top, b.dataset.burst || '❤', 14);
}));

/* ---------- opening gate: <div data-gate> … <button data-gate-open> ---------- */
const gate = () => {
    const g = document.querySelector('[data-gate]'); if (!g) return;
    if (isDraft) { g.remove(); return; }
    document.documentElement.classList.add('has-gate');
    const open = () => {
        g.classList.add('is-open');
        document.documentElement.classList.remove('has-gate');
        setTimeout(() => { g.remove(); document.dispatchEvent(new CustomEvent('gb:opened')); }, reduce ? 0 : 1100);
        if (g.dataset.gate === 'confetti') setTimeout(() => confetti(), 350);
        if (g.dataset.gate === 'hearts') setTimeout(() => confetti({ emoji: ['❤', '💕', '🌸'], count: 50 }), 350);
    };
    g.querySelector('[data-gate-open]')?.addEventListener('click', open, { once: true });
    g.querySelector('[data-gate-open]')?.focus({ preventScroll: true });
};

/* ---------- envelope (love letter) ---------- */
const envelopes = () => $$('[data-envelope]').forEach((env) => {
    const open = () => {
        if (env.classList.contains('is-open')) return;
        env.classList.add('is-open');
        const target = document.querySelector(env.dataset.envelope);
        setTimeout(() => { if (target) { target.hidden = false; reveal(); typewriter(); target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); } }, reduce ? 0 : 1000);
    };
    env.addEventListener('click', open);
    env.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
    if (isDraft) { const target = document.querySelector(env.dataset.envelope); if (target) target.hidden = false; env.classList.add('is-open'); }
});

/* ---------- flip / toggle cards: <button data-flip> ---------- */
const flips = () => $$('[data-flip]').forEach((c) => c.addEventListener('click', () => {
    c.classList.toggle('is-flipped');
    c.setAttribute('aria-pressed', c.classList.contains('is-flipped'));
}));
const shuffles = () => $$('[data-shuffle]').forEach((btn) => btn.addEventListener('click', () => {
    const grid = document.querySelector(btn.dataset.shuffle); if (!grid) return;
    const cards = Array.from(grid.children);
    cards.forEach((c) => c.classList.remove('is-flipped'));
    const pick = cards[Math.floor(Math.random() * cards.length)];
    cards.sort(() => Math.random() - 0.5).forEach((c) => grid.appendChild(c));
    setTimeout(() => { pick.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }); pick.classList.add('is-flipped'); }, 300);
}));

/* ---------- scratch card: <div data-scratch> canvas covers the content ---------- */
const scratches = () => $$('[data-scratch]').forEach((box) => {
    if (isDraft) { box.classList.add('is-scratched'); return; }
    const c = document.createElement('canvas'); c.className = 't-scratch__canvas';
    box.appendChild(c);
    const ctx = c.getContext('2d');
    const paint = () => {
        const r = box.getBoundingClientRect(); const dpr = Math.min(2, devicePixelRatio || 1);
        c.width = r.width * dpr; c.height = r.height * dpr; ctx.scale(dpr, dpr);
        const g = ctx.createLinearGradient(0, 0, r.width, r.height);
        g.addColorStop(0, '#c9c9c9'); g.addColorStop(0.5, '#efefef'); g.addColorStop(1, '#b5b5b5');
        ctx.fillStyle = g; ctx.fillRect(0, 0, r.width, r.height);
        ctx.fillStyle = '#6b6b6b'; ctx.font = "600 18px 'Hind Siliguri', sans-serif"; ctx.textAlign = 'center';
        ctx.fillText(box.dataset.scratch || 'এখানে ঘষে দেখো ✨', r.width / 2, r.height / 2 + 6);
        ctx.globalCompositeOperation = 'destination-out';
    };
    paint();
    let down = false, strokes = 0;
    const scratch = (e) => {
        if (!down) return;
        const r = c.getBoundingClientRect(); const p = e.touches ? e.touches[0] : e;
        ctx.beginPath(); ctx.arc(p.clientX - r.left, p.clientY - r.top, 22, 0, Math.PI * 2); ctx.fill();
        if (++strokes % 12 === 0) check();
    };
    const check = () => {
        const d = ctx.getImageData(0, 0, c.width, c.height).data; let clear = 0;
        for (let i = 3; i < d.length; i += 64) if (d[i] === 0) clear++;
        if (clear / (d.length / 64) > 0.45) done();
    };
    const done = () => { if (box.classList.contains('is-scratched')) return; box.classList.add('is-scratched'); const r = box.getBoundingClientRect(); confetti({ y: r.top + r.height / 2 }); };
    c.addEventListener('pointerdown', (e) => { down = true; c.setPointerCapture?.(e.pointerId); scratch(e); });
    c.addEventListener('pointermove', scratch);
    c.addEventListener('pointerup', () => (down = false));
    c.addEventListener('pointercancel', () => (down = false));
    box.querySelector('[data-scratch-skip]')?.addEventListener('click', done);
    if (reduce) done();
});

/* ---------- gift box: <button data-giftbox> opens and reveals [data-gift-reveal] ---------- */
const giftboxes = () => $$('[data-giftbox]').forEach((b) => {
    const open = () => {
        if (b.classList.contains('is-open')) return;
        b.classList.add('is-open');
        const r = b.getBoundingClientRect();
        setTimeout(() => confetti({ y: r.top + r.height * 0.3 }), 300);
        const t = document.querySelector(b.dataset.giftbox);
        setTimeout(() => { if (t) { t.hidden = false; reveal(); t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); } }, reduce ? 0 : 1200);
    };
    b.addEventListener('click', open);
    if (isDraft) { b.classList.add('is-open'); const t = document.querySelector(b.dataset.giftbox); if (t) t.hidden = false; }
});

/* ---------- birthday cake candles: tap each flame ---------- */
const candles = () => $$('[data-candles]').forEach((cake) => {
    const flames = $$('[data-flame]', cake);
    const wish = document.querySelector(cake.dataset.candles);
    const blow = (f) => {
        if (f.classList.contains('is-out')) return;
        f.classList.add('is-out');
        if (flames.every((x) => x.classList.contains('is-out'))) {
            const r = cake.getBoundingClientRect();
            confetti({ y: r.top });
            if (wish) { wish.hidden = false; wish.classList.add('is-in'); }
        }
    };
    flames.forEach((f) => f.addEventListener('click', (e) => { e.stopPropagation(); blow(f); }));
    cake.addEventListener('click', () => flames.forEach((f, i) => setTimeout(() => blow(f), i * 120)));
});

/* ---------- proposal: "No" runs away, "Yes" celebrates ---------- */
const proposal = () => {
    const box = document.querySelector('[data-proposal]'); if (!box) return;
    const no = box.querySelector('[data-no]'), yes = box.querySelector('[data-yes]');
    const lines = ['সত্যি?', 'আরেকবার ভাবো 🥺', 'প্লিজ?', 'এভাবে বলো না!', 'মন ভেঙে যাবে…', 'ঠিক আছে, হ্যাঁ-ই চাপো 😌'];
    let tries = 0;
    const dodge = (e) => {
        e?.preventDefault();
        tries++;
        const area = box.getBoundingClientRect();
        const bw = no.offsetWidth, bh = no.offsetHeight;
        const x = Math.random() * Math.max(0, area.width - bw), y = Math.random() * Math.max(0, Math.min(area.height, 260) - bh);
        no.style.position = 'absolute'; no.style.left = x + 'px'; no.style.top = y + 'px';
        no.textContent = lines[Math.min(tries - 1, lines.length - 1)];
        yes.style.transform = `scale(${Math.min(1 + tries * 0.18, 2)})`;
        if (tries > 6) no.remove();
    };
    no?.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') dodge(e); });
    no?.addEventListener('click', dodge);
    yes?.addEventListener('click', () => {
        box.classList.add('is-yes');
        confetti({ emoji: ['💍', '❤', '💖', '🌹'], count: 70 });
        setTimeout(() => confetti(), 500);
        const t = document.querySelector(yes.dataset.yes);
        if (t) { t.hidden = false; reveal(); setTimeout(() => t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }), 400); }
    });
    if (isDraft) { const t = document.querySelector(yes?.dataset.yes); if (t) t.hidden = false; }
};

/* ---------- step-by-step lines: <div data-seq> children + [data-seq-next] ---------- */
const sequences = () => $$('[data-seq]').forEach((seq) => {
    const items = $$('[data-seq-item]', seq); const next = seq.querySelector('[data-seq-next]');
    if (!items.length) return;
    if (isDraft) { items.forEach((i) => i.classList.add('is-shown')); next?.remove(); seq.classList.add('is-done'); return; }
    let i = 0;
    items[0].classList.add('is-shown', 'is-current');
    const after = document.querySelector(seq.dataset.seq);
    if (after) after.hidden = true;
    next?.addEventListener('click', () => {
        items[i].classList.remove('is-current');
        i++;
        if (i < items.length) { items[i].classList.add('is-shown', 'is-current'); items[i].scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' }); }
        if (i >= items.length - 1) {
            next.textContent = next.dataset.lastLabel || 'এবার আসল কথা →';
        }
        if (i >= items.length) {
            next.remove(); seq.classList.add('is-done');
            if (after) { after.hidden = false; reveal(); after.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' }); }
        }
    });
});

/* ---------- light a lamp (tribute) ---------- */
const lamps = () => $$('[data-lamp]').forEach((btn) => btn.addEventListener('click', () => {
    const wrap = document.querySelector(btn.dataset.lamp); if (!wrap) return;
    const l = document.createElement('span'); l.className = 't-diya is-lit'; l.setAttribute('aria-hidden', 'true');
    l.innerHTML = '<i></i>';
    wrap.appendChild(l);
    const n = wrap.children.length;
    const out = document.querySelector('[data-lamp-count]'); if (out) out.textContent = bn(n);
    btn.querySelector('span') && (btn.querySelector('span').textContent = 'আরেকটি প্রদীপ জ্বালান');
}));

/* ---------- copy to clipboard: <button data-copy="01700…"> ---------- */
const copies = () => $$('[data-copy]').forEach((b) => b.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(b.dataset.copy); } catch (e) { return; }
    const old = b.textContent; b.textContent = 'কপি হয়েছে ✓'; setTimeout(() => (b.textContent = old), 1800);
}));

/* ---------- SVG path that draws itself as you scroll: <path data-draw> ---------- */
const draws = () => {
    const paths = $$('[data-draw]'); if (!paths.length) return;
    paths.forEach((p) => { const L = p.getTotalLength(); p.style.strokeDasharray = L; p.style.strokeDashoffset = reduce ? 0 : L; p._L = L; });
    if (reduce) return;
    const update = () => paths.forEach((p) => {
        const host = p.closest('[data-draw-host]') || p.ownerSVGElement; const r = host.getBoundingClientRect();
        const prog = Math.min(1, Math.max(0, (innerHeight * 0.8 - r.top) / (r.height || 1)));
        p.style.strokeDashoffset = p._L * (1 - prog);
    });
    addEventListener('scroll', update, { passive: true }); update();
};

/* ---------- gentle 3D tilt on photos: <div data-tilt> ---------- */
const tilts = () => {
    if (reduce || !matchMedia('(pointer: fine)').matches) return;
    $$('[data-tilt]').forEach((el) => {
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect(); const x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
            el.style.transform = `perspective(900px) rotateY(${x * 10}deg) rotateX(${-y * 10}deg)`;
        });
        el.addEventListener('pointerleave', () => (el.style.transform = ''));
    });
};

/* ---------- scroll progress line: <div data-progress> ---------- */
const progress = () => {
    const bar = document.querySelector('[data-progress]'); if (!bar) return;
    const upd = () => { const h = document.documentElement; bar.style.transform = `scaleX(${h.scrollTop / Math.max(1, h.scrollHeight - h.clientHeight)})`; };
    addEventListener('scroll', upd, { passive: true }); upd();
};

/* ---------- lightbox for every [data-gallery] ---------- */
const lightbox = () => {
    const lb = document.querySelector('[data-lightbox]'); if (!lb) return;
    const img = lb.querySelector('[data-lb-img]'), cap = lb.querySelector('[data-lb-caption]');
    let list = [], idx = 0, lastFocus = null;
    const show = (i) => { idx = (i + list.length) % list.length; img.src = list[idx].dataset.src; cap.textContent = list[idx].dataset.caption || ''; };
    $$('[data-gallery]').forEach((g) => $$('[data-lb-open]', g).forEach((btn, i, all) => btn.addEventListener('click', () => {
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

document.documentElement.classList.remove('no-js');
const boot = () => {
    split(); reveal(); typewriter(); counters(); countdowns(); bursts(); envelopes(); flips(); shuffles(); scratches();
    giftboxes(); candles(); proposal(); sequences(); lamps(); copies(); draws(); tilts(); progress(); lightbox(); gate();
    // Pages without a gate still get their confetti welcome.
    if (!document.querySelector('[data-gate]') && document.querySelector('[data-confetti]') && !isDraft) setTimeout(() => confetti(), 500);
    window.gbConfetti = confetti;
};
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
