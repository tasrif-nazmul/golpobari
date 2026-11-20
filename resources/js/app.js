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

// Scroll reveal: a single observer, removed once each element has been seen.
const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }) : null;
const watchReveal = () => document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => io ? io.observe(el) : el.classList.add('is-visible'));
document.addEventListener('DOMContentLoaded', watchReveal);

Alpine.start();
