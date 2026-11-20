import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global confirm dialog: <form data-confirm="Are you sure?"> or <button data-confirm>
Alpine.data('confirmHost', () => ({
    open: false, message: '', danger: false, form: null, submitter: null,
    init() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const msg = (e.submitter && e.submitter.dataset.confirm) || form.dataset.confirm;
            if (!msg || form.dataset.confirmed === '1') return;
            e.preventDefault();
            this.message = msg; this.danger = (e.submitter?.dataset.danger ?? form.dataset.danger) !== undefined;
            this.form = form; this.submitter = e.submitter || null; this.open = true;
            this.$nextTick(() => this.$refs.ok?.focus());
        }, true);
    },
    ok() {
        if (!this.form) return;
        this.form.dataset.confirmed = '1';
        if (this.submitter && this.submitter.name) {
            const h = document.createElement('input'); h.type = 'hidden'; h.name = this.submitter.name; h.value = this.submitter.value; this.form.appendChild(h);
        }
        this.form.submit();
    },
    cancel() { this.open = false; this.form = null; },
}));

async function copyToClipboard(text) {
    try { await navigator.clipboard.writeText(text); return true; } catch (e) {
        // Older phones / http: fall back to a hidden textarea.
        const t = document.createElement('textarea'); t.value = text; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select(); let ok = false; try { ok = document.execCommand('copy'); } catch (_) {} t.remove(); return ok;
    }
}

Alpine.data('copyText', (value, toast = 'Copied') => ({
    copied: false,
    async copy(v) {
        await copyToClipboard(v ?? value);
        this.copied = true; window.dispatchEvent(new CustomEvent('toast', { detail: toast }));
        setTimeout(() => (this.copied = false), 1500);
    },
}));

// Native share sheet on phones (WhatsApp, Messenger…), copy elsewhere.
Alpine.data('shareLink', (url, text) => ({
    canShare: !!navigator.share,
    async share() {
        if (navigator.share) { try { await navigator.share({ text, url }); return; } catch (e) { if (e.name === 'AbortError') return; } }
        await copyToClipboard(text + ' ' + url); window.dispatchEvent(new CustomEvent('toast', { detail: 'Message copied' }));
    },
}));

// Template field editor (admin > templates)
Alpine.data('fieldEditor', (initial = [], types = {}) => ({
    fields: initial.map((f) => ({ ...f, options: (f.options && !Array.isArray(f.options)) ? { ...f.options } : {}, _open: false, _choices: (f.options?.choices || []).join(', '), _sub: JSON.stringify(f.options?.fields || [], null, 0) })),
    types,
    add() { this.fields.push({ key: '', label: '', type: 'text', section: 'info', placeholder: '', help: '', required: false, max_length: null, options: {}, _open: true, _choices: '', _sub: '[]' }); },
    remove(i) { this.fields.splice(i, 1); },
    move(i, d) { const j = i + d; if (j < 0 || j >= this.fields.length) return; [this.fields[i], this.fields[j]] = [this.fields[j], this.fields[i]]; },
    slugKey(f) { if (!f.key && f.label) f.key = f.label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '').slice(0, 50); },
    get json() {
        return JSON.stringify(this.fields.map((f) => {
            const options = { ...(f.options || {}) };
            if (f.type === 'select') options.choices = f._choices.split(',').map((s) => s.trim()).filter(Boolean);
            if (f.type === 'repeater') { try { options.fields = JSON.parse(f._sub || '[]'); } catch (e) { options.fields = []; } }
            if (f.type === 'gallery' || f.type === 'repeater') options.max = Number(options.max) || null;
            return { key: f.key, label: f.label, type: f.type, section: ['image', 'gallery'].includes(f.type) ? 'photos' : 'info', placeholder: f.placeholder, help: f.help, required: !!f.required, max_length: f.max_length ? Number(f.max_length) : null, options };
        }));
    },
}));

Alpine.start();
