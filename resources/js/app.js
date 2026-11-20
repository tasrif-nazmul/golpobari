import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { orderWizard } from './wizard';

window.Alpine = Alpine;
Alpine.plugin(collapse);

// Types slugs into the hero "address bar".
Alpine.data('slugTyper', (slugs = ['korim']) => ({
    text: '',
    init() {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || !slugs.length) { this.text = slugs[0] || ''; return; }
        let i = 0, j = 0, deleting = false;
        const tick = () => {
            const word = slugs[i % slugs.length];
            if (!deleting) {
                this.text = word.slice(0, ++j);
                if (j === word.length) { deleting = true; return setTimeout(tick, 1700); }
            } else {
                this.text = word.slice(0, --j);
                if (j === 0) { deleting = false; i++; }
            }
            setTimeout(tick, deleting ? 45 : 95);
        };
        setTimeout(tick, 600);
    },
}));

Alpine.data('copyText', (value) => ({
    copied: false,
    async copy() {
        try { await navigator.clipboard.writeText(value); } catch (e) {
            const t = document.createElement('textarea'); t.value = value; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove();
        }
        this.copied = true; setTimeout(() => (this.copied = false), 1800);
    },
}));

Alpine.data('orderWizard', orderWizard);

// The site's live template preview popup.
Alpine.data('tplPreview', () => ({
    item: null, loading: true,
    open(d) { this.loading = true; this.item = d; document.documentElement.style.overflow = 'hidden'; },
    close() { if (!this.item) return; this.item = null; document.documentElement.style.overflow = ''; },
}));

// Hero phone: cycles through a few templates.
Alpine.data('heroPhones', (items = []) => ({
    items, i: 0,
    init() { if (items.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) setInterval(() => { this.i = (this.i + 1) % items.length; }, 4200); },
    get cur() { return this.items[this.i] || {}; },
}));

// Little music sampler on the home page.
Alpine.data('trackList', () => ({
    playing: null, audio: null,
    toggle(key, url) {
        if (this.audio) { this.audio.pause(); }
        if (this.playing === key) { this.playing = null; this.audio = null; return; }
        this.audio = new Audio(url); this.audio.volume = 0.7; this.playing = key;
        this.audio.play().catch(() => { this.playing = null; });
        this.audio.onended = () => { this.playing = null; };
    },
}));

// Scroll reveal: a single observer, removed once each element has been seen.
const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }) : null;
const watchReveal = () => document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => io ? io.observe(el) : el.classList.add('is-visible'));
document.addEventListener('DOMContentLoaded', watchReveal);

Alpine.start();
