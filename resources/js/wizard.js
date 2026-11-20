/*
 * GolpoBari order wizard. Everything shown here is re-validated and re-priced on the server.
 * Progress (except payment details) is kept in localStorage so a refresh doesn't lose work.
 */
const STORE_KEY = 'golpobari.order.v1';
const bn = (v) => String(v).replace(/\d/g, (d) => '০১২৩৪৫৬৭৮৯'[d]);
const money = (v) => '৳' + bn(Number(v).toLocaleString('en-US', { maximumFractionDigits: 2 }));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const BD_MOBILE = /^01[3-9]\d{8}$/;
const normPhone = (p) => {
    let s = String(p || '').replace(/[০-৯]/g, (d) => '০১২৩৪৫৬৭৮৯'.indexOf(d)).replace(/\D/g, '');
    if (s.startsWith('880')) s = s.slice(2); else if (s.startsWith('88') && s.length === 13) s = s.slice(2);
    return s;
};

export const STEPS = [
    { group: 1, title: 'কার জন্য বানাবেন?', sub: 'একটা ক্যাটাগরি বেছে নিন' },
    { group: 2, title: 'টেমপ্লেট বাছুন', sub: 'প্রিভিউ দেখে পছন্দেরটা বেছে নিন' },
    { group: 3, title: 'গল্পটা বলুন', sub: 'অংশ ধরে ধরে লিখুন আর ছবি দিন। প্রিভিউতে দেখবেন কোনটা কোথায় বসছে।' },
    { group: 3, title: 'লিংক ও যোগাযোগ', sub: 'পেজের ঠিকানা আর আপনার নম্বর' },
    { group: 3, title: 'একবার দেখে নিন', sub: 'আপনার তথ্য দিয়ে তৈরি প্রিভিউ' },
    { group: 4, title: 'কতদিনের জন্য?', sub: 'প্যাকেজ বেছে নিন' },
    { group: 4, title: 'কুপন আছে?', sub: 'না থাকলে এগিয়ে যান' },
    { group: 4, title: 'bKash-এ পেমেন্ট করুন', sub: 'Send Money করে Transaction ID রাখুন' },
    { group: 4, title: 'পেমেন্টের তথ্য দিন', sub: 'যে নম্বর থেকে টাকা পাঠিয়েছেন' },
    { group: 5, title: 'শেষবার মিলিয়ে নিন', sub: 'সব ঠিক থাকলে জমা দিন' },
];
export const GROUPS = ['ক্যাটাগরি', 'টেমপ্লেট', 'তথ্য', 'পেমেন্ট', 'জমা'];

export function orderWizard(boot) {
    return {
        boot, steps: STEPS, groups: GROUPS, bn, money,
        step: 1,
        categorySlug: null, templateSlug: null, occasion: '',
        content: {}, customer: { name: '', phone: '', email: '' },
        slug: '', slugState: { ok: null, message: '', checking: false },
        note: '',
        media: {},
        packageSlug: null,
        coupon: { code: '', applied: null, message: '', error: false, loading: false },
        payment: { sender: '', trx: '', amount: '' },
        confirm: false, agree: false,
        errors: {}, stepError: '', submitting: false,
        previewHtml: '', previewLoading: false, previewModal: null,
        playing: null, _sample: null,
        activeField: null, previewOpen: false, liveLoading: false, liveDirty: true, _liveT: null,

        init() {
            this.restore();
            const pre = this.boot.preselect || {};
            if (pre.template && this.findTemplate(pre.template)) {
                const cat = this.boot.catalog.find((c) => c.templates.some((t) => t.slug === pre.template));
                if (this.templateSlug !== pre.template) { this.categorySlug = cat.slug; this.selectTemplate(pre.template, false); this.step = 3; }
            }
            if (this.musicField && !this.content[this.musicField.key]) this.content[this.musicField.key] = this.musicField.placeholder || '';
            if (pre.package && this.boot.packages.some((p) => p.slug === pre.package)) this.packageSlug = pre.package;
            if (!this.packageSlug) this.packageSlug = (this.boot.packages.find((p) => p.featured) || this.boot.packages[0] || {}).slug || null;

            ['step', 'categorySlug', 'templateSlug', 'occasion', 'content', 'customer', 'slug', 'note', 'media', 'packageSlug', 'coupon.code'].forEach((k) => this.$watch(k, () => this.save()));
            this.$watch('step', (s) => {
                this.stepError = '';
                window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
                if (s === 5) this.loadPreview();
                if (s === 3) { this.liveDirty = true; this.$nextTick(() => this.isDesktop() && this.refreshLive()); }
                if (s !== 3) this.previewOpen = false;
                if (s === 9 && !this.payment.amount) this.payment.amount = String(this.totals.final);
            });
            this.$watch('content', () => this.scheduleLive());
            this.$watch('media', () => this.scheduleLive());
            this.$watch('previewOpen', (o) => { document.documentElement.style.overflow = o ? 'hidden' : ''; });
            if (this.step === 3) this.$nextTick(() => this.isDesktop() && this.refreshLive());
            window.addEventListener('beforeunload', (e) => { if (this.uploading) { e.preventDefault(); e.returnValue = ''; } });
        },

        /* ---------------- state helpers ---------------- */
        get category() { return this.boot.catalog.find((c) => c.slug === this.categorySlug) || null; },
        get template() { return this.findTemplate(this.templateSlug); },
        findTemplate(slug) { for (const c of this.boot.catalog) { const t = c.templates.find((x) => x.slug === slug); if (t) return t; } return null; },
        get textFields() { return (this.template?.fields || []).filter((f) => !['image', 'gallery'].includes(f.type)); },
        get musicField() { return (this.template?.fields || []).find((f) => f.type === 'music') || null; },
        get mediaFields() { return (this.template?.fields || []).filter((f) => ['image', 'gallery'].includes(f.type)); },
        /* The template's page map: sections from top to bottom, each with its fields. */
        get sections() {
            const t = this.template; if (!t) return [];
            const secs = t.guide?.sections?.length ? t.guide.sections : [{ n: 1, total: 1, icon: '📝', title: 'আপনার পেজের তথ্য', where: 'পুরো পেজ জুড়ে', fields: t.fields.map((f) => f.key) }];
            return secs;
        },
        fieldBy(key) { return (this.template?.fields || []).find((f) => f.key === key) || null; },
        isMedia(f) { return ['image', 'gallery'].includes(f.type); },
        exampleText(f) {
            if (!f) return '';
            const e = this.template?.guide?.examples?.[f.key];
            if (!e) return f.placeholder || '';
            if (e.text) return e.text;
            if (e.row) return Object.values(e.row).filter((v) => v && String(v).trim()).join(' · ') + (e.count > 1 ? `  (এরকম ${bn(e.count)}টা)` : '');
            return '';
        },
        exampleImage(f) { return this.template?.guide?.examples?.[f.key]?.image || null; },
        rowExample(f, sub) { const r = this.template?.guide?.examples?.[f.key]?.row; return r && r[sub] ? 'যেমন: ' + r[sub] : ''; },
        get package() { return this.boot.packages.find((p) => p.slug === this.packageSlug) || null; },
        get photoCount() { return Object.entries(this.media).filter(([k]) => k !== 'music').flatMap(([, v]) => v).filter((m) => m.id).length; },
        get uploading() { return Object.values(this.media).flat().some((m) => m.uploading); },
        get photoLimit() {
            return Math.min(this.boot.limits.max_files || 999, this.template?.max_photos || 999, this.package?.max_photos || 999);
        },
        packageLimit(p) { return Math.min(this.boot.limits.max_files || 999, this.template?.max_photos || 999, p.max_photos || 999); },
        get totals() {
            const original = this.package ? this.package.price : 0;
            const a = this.coupon.applied && this.coupon.applied.package === this.packageSlug ? this.coupon.applied : null;
            return { original, discount: a ? a.discount : 0, final: a ? a.final : original };
        },
        get currentGroup() { return this.steps[this.step - 1].group; },

        selectCategory(slug) {
            if (this.categorySlug !== slug) { this.templateSlug = null; this.occasion = ''; }
            this.categorySlug = slug;
            this.next();
        },
        selectTemplate(slug, advance = true) {
            const prev = this.template;
            this.templateSlug = slug;
            const t = this.template;
            // Keep values for fields that exist in both templates; set up empty repeaters.
            const content = {};
            t.fields.forEach((f) => {
                if (f.type === 'repeater') content[f.key] = Array.isArray(this.content[f.key]) ? this.content[f.key] : [];
                else if (f.type === 'music') content[f.key] = this.content[f.key] || f.placeholder || '';
                else if (!['image', 'gallery'].includes(f.type)) content[f.key] = this.content[f.key] ?? '';
            });
            this.content = content;
            const media = {};
            this.mediaFields.forEach((f) => (media[f.key] = this.media[f.key] || []));
            if (this.musicField) media.music = this.media.music || [];
            this.media = media;
            if (prev && prev.slug !== slug) this.previewHtml = '';
            if (advance) this.next();
        },
        addRow(field) {
            const row = {}; (field.options.fields || []).forEach((s) => (row[s.key] = ''));
            if (!Array.isArray(this.content[field.key])) this.content[field.key] = [];
            if (this.content[field.key].length < (field.options.max || 20)) this.content[field.key].push(row);
        },
        removeRow(field, i) { this.content[field.key].splice(i, 1); },
        moveRow(field, i, d) {
            const a = this.content[field.key]; const j = i + d; if (j < 0 || j >= a.length) return;
            [a[i], a[j]] = [a[j], a[i]];
        },

        /* ---------------- navigation & validation ---------------- */
        next() {
            const err = this.validateStep(this.step);
            if (err) { this.stepError = err; this.$nextTick(() => (Array.from(document.querySelectorAll('[aria-invalid="true"], .field-error')).find((e) => e.offsetParent && (e.getAttribute('aria-invalid') === 'true' || e.textContent.trim())) || document.querySelector('.js-step-error'))?.scrollIntoView({ block: 'center', behavior: 'smooth' })); return; }
            if (this.step < this.steps.length) this.step++;
        },
        back() { if (this.step > 1) this.step--; },
        goTo(n) { if (n < this.step) this.step = n; },

        fieldError(key) { return this.errors['content.' + key] || this.errors[key] || ''; },
        validateStep(n) {
            this.errors = {};
            if (n === 1 && !this.category) return 'একটা ক্যাটাগরি বেছে নিন।';
            if (n === 2 && !this.template) return 'একটা টেমপ্লেট বেছে নিন।';
            if (n === 3) {
                this.textFields.forEach((f) => {
                    const v = this.content[f.key];
                    const empty = f.type === 'repeater' ? !(v || []).some((r) => Object.values(r).some((x) => String(x).trim())) : !String(v ?? '').trim();
                    if (f.required && empty) this.errors['content.' + f.key] = `"${f.label}" দিতে হবে।`;
                    else if (!empty && f.type === 'email' && !/^\S+@\S+\.\S+$/.test(v)) this.errors['content.' + f.key] = 'সঠিক ইমেইল দিন।';
                    else if (!empty && f.type === 'url' && !/^https?:\/\/\S+$/i.test(v)) this.errors['content.' + f.key] = 'লিংক https:// দিয়ে শুরু করুন।';
                    else if (!empty && f.max_length && String(v).length > f.max_length) this.errors['content.' + f.key] = `সর্বোচ্চ ${bn(f.max_length)} অক্ষর।`;
                });
                this.mediaFields.forEach((f) => {
                    if (f.required && !(this.media[f.key] || []).some((m) => m.id)) this.errors['media.' + f.key] = `"${f.label}" এর জন্য অন্তত একটি ছবি দিন।`;
                });
                if (Object.keys(this.errors).length) return 'কিছু তথ্য বাকি বা ভুল আছে। লাল লেখা ঘরগুলো দেখুন।';
                if (this.uploading) return 'ছবি আপলোড শেষ হওয়া পর্যন্ত একটু অপেক্ষা করুন।';
                if (this.photoCount > this.photoLimit) return `সর্বোচ্চ ${bn(this.photoLimit)}টি ছবি দেওয়া যাবে।`;
            }
            if (n === 4) {
                if (this.customer.name.trim().length < 2) this.errors.customer_name = 'আপনার নাম দিন।';
                if (!BD_MOBILE.test(normPhone(this.customer.phone))) this.errors.customer_phone = 'সঠিক মোবাইল নম্বর দিন (01XXXXXXXXX)।';
                if (this.customer.email && !/^\S+@\S+\.\S+$/.test(this.customer.email)) this.errors.customer_email = 'সঠিক ইমেইল দিন।';
                if (this.slug && this.slugState.ok === false) this.errors.desired_slug = this.slugState.message;
                if (Object.keys(this.errors).length) return 'কিছু তথ্য বাকি বা ভুল আছে। লাল চিহ্নিত ঘরগুলো দেখুন।';
            }
            if (n === 6) {
                if (!this.package) return 'একটা প্যাকেজ বেছে নিন।';
                if (this.photoCount > this.photoLimit) return `এই প্যাকেজে সর্বোচ্চ ${bn(this.photoLimit)}টি ছবি। বড় প্যাকেজ নিন বা কিছু ছবি বাদ দিন।`;
            }
            if (n === 8 && !this.boot.bkash.number) return 'পেমেন্ট নম্বর এখনো সেট করা হয়নি। অনুগ্রহ করে আমাদের সাথে যোগাযোগ করুন।';
            if (n === 9) {
                if (!BD_MOBILE.test(normPhone(this.payment.sender))) this.errors.payment_sender = 'যে bKash নম্বর থেকে পাঠিয়েছেন সেটা দিন।';
                const trx = this.payment.trx.replace(/\s+/g, '').toUpperCase();
                if (!/^[A-Z0-9]{8,20}$/.test(trx)) this.errors.payment_trx = 'Transaction ID ঠিকমতো দিন (যেমন 9BK7XXXXXX)।';
                if (!(Number(this.payment.amount) > 0)) this.errors.payment_amount = 'কত টাকা পাঠিয়েছেন লিখুন।';
                if (Object.keys(this.errors).length) return 'পেমেন্টের তথ্য ঠিক করুন।';
            }
            if (n === 10 && !this.confirm) return '"সব তথ্য ঠিক আছে" টিক দিন।';
            return '';
        },

        /* ---------------- slug ---------------- */
        slugTimer: null,
        onSlugInput() {
            this.slug = this.slug.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-/, '').slice(0, 60);
            this.slugState = { ok: null, message: '', checking: !!this.slug };
            clearTimeout(this.slugTimer);
            if (!this.slug) return;
            this.slugTimer = setTimeout(() => this.checkSlug(), 450);
        },
        async checkSlug() {
            try {
                const r = await this.post(this.boot.urls.slug, { slug: this.slug });
                const j = await r.json();
                this.slugState = { ok: j.ok, message: j.message, checking: false };
            } catch (e) { this.slugState = { ok: null, message: '', checking: false }; }
        },

        /* ---------------- uploads ---------------- */
        pick(field) { document.getElementById('file_' + field.key)?.click(); },
        async onFiles(field, ev) {
            const files = Array.from(ev.target.files || []); ev.target.value = '';
            if (!this.media[field.key]) this.media[field.key] = [];
            const single = field.type === 'image';
            for (const file of files.slice(0, single ? 1 : 50)) {
                if (single && this.media[field.key].length) await this.removePhoto(field, 0);
                if (this.photoCount >= this.photoLimit) { this.stepError = `সর্বোচ্চ ${bn(this.photoLimit)}টি ছবি দেওয়া যাবে।`; break; }
                const item = { key: Math.random().toString(36).slice(2), id: null, thumb: URL.createObjectURL(file), caption: '', uploading: true, progress: 0, error: '' };
                this.media[field.key].push(item);
                const ref = this.media[field.key][this.media[field.key].length - 1];
                this.uploadOne(field, file, ref);
            }
        },
        async uploadOne(field, file, item) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const okType = /^image\/(jpeg|png|webp)$/.test(file.type) || this.boot.limits.types.includes(ext);
            if (!okType) { item.uploading = false; item.error = 'শুধু JPG, PNG বা WebP'; return; }
            let blob = file;
            try { blob = await this.shrink(file); } catch (e) { blob = file; }
            if (blob.size > this.boot.limits.max_mb * 1024 * 1024) { item.uploading = false; item.error = `${bn(this.boot.limits.max_mb)} MB-এর বেশি`; return; }
            const fd = new FormData();
            fd.append('file', blob, (file.name || 'photo').replace(/\.[^.]+$/, '') + (blob.type === 'image/jpeg' ? '.jpg' : '.' + (ext || 'jpg')));
            fd.append('field', field.key);
            await new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.boot.urls.upload);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = (e) => { if (e.lengthComputable) item.progress = Math.round((e.loaded / e.total) * 100); };
                xhr.onload = () => {
                    let j = {}; try { j = JSON.parse(xhr.responseText); } catch (e) {}
                    if (xhr.status >= 200 && xhr.status < 300) { item.id = j.id; item.thumb = j.thumb; }
                    else item.error = j.message || (j.errors && Object.values(j.errors)[0]?.[0]) || (xhr.status === 429 ? 'একটু পরে চেষ্টা করুন' : xhr.status === 413 ? 'ফাইলটি অনেক বড়' : 'আপলোড হয়নি');
                    item.uploading = false; resolve();
                };
                xhr.onerror = () => { item.error = 'ইন্টারনেট সংযোগ দেখুন'; item.uploading = false; resolve(); };
                xhr.send(fd);
            });
        },
        /* ---------------- background music ---------------- */
        musicLabel(v) {
            if (v === 'custom') return 'নিজের গান';
            if (v === 'none' || !v) return 'গান ছাড়া';
            const t = (this.boot.tracks || []).find((x) => x.key === v); return t ? t.icon + ' ' + t.title : v;
        },
        toggleSample(t) {
            if (this.playing === t.key) return this.stopSample();
            this.stopSample();
            if (t.key !== 'custom' && this.musicField) this.content[this.musicField.key] = t.key;
            const a = new Audio(t.url); a.volume = 0.7; this._sample = a; this.playing = t.key;
            a.play().catch(() => { this.playing = null; });
            a.onended = () => { this.playing = null; };
            clearTimeout(this._sampleT); this._sampleT = setTimeout(() => this.stopSample(), 30000);
        },
        stopSample() { if (this._sample) { this._sample.pause(); this._sample = null; } this.playing = null; },
        async onMusic(field, ev) {
            const file = (ev.target.files || [])[0]; ev.target.value = ''; if (!file) return;
            this.stopSample();
            if ((this.media.music || []).some((m) => m.id)) await this.removeMusic(field, false);
            const item = { key: Math.random().toString(36).slice(2), id: null, url: null, name: file.name, uploading: true, progress: 0, error: '' };
            this.media.music = [item];
            const ref = this.media.music[0];
            this.content[field.key] = 'custom';
            const okType = /^audio\//.test(file.type) || /\.(mp3|m4a|aac|ogg|wav)$/i.test(file.name);
            if (!okType) { ref.uploading = false; ref.error = 'শুধু MP3 বা M4A গান দিন'; return; }
            if (file.size > this.boot.limits.audio_mb * 1024 * 1024) { ref.uploading = false; ref.error = `${bn(this.boot.limits.audio_mb)} MB-এর বেশি বড়`; return; }
            const fd = new FormData(); fd.append('file', file, file.name); fd.append('field', 'music');
            await new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.boot.urls.upload);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf()); xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = (e) => { if (e.lengthComputable) ref.progress = Math.round((e.loaded / e.total) * 100); };
                xhr.onload = () => {
                    let j = {}; try { j = JSON.parse(xhr.responseText); } catch (e) {}
                    if (xhr.status >= 200 && xhr.status < 300) { ref.id = j.id; ref.url = j.url; ref.name = j.name || ref.name; }
                    else ref.error = j.message || (j.errors && Object.values(j.errors)[0]?.[0]) || (xhr.status === 413 ? 'ফাইলটি অনেক বড়' : 'আপলোড হয়নি');
                    ref.uploading = false; resolve();
                };
                xhr.onerror = () => { ref.error = 'ইন্টারনেট সংযোগ দেখুন'; ref.uploading = false; resolve(); };
                xhr.send(fd);
            });
        },
        async removeMusic(field, reset = true) {
            this.stopSample();
            const item = (this.media.music || [])[0];
            this.media.music = [];
            if (reset && this.content[field.key] === 'custom') this.content[field.key] = field.placeholder || 'none';
            if (item?.id) { try { await fetch(`${this.boot.urls.deleteUpload}/${item.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } }); } catch (e) {} }
        },
        /* Downscale big phone photos before upload: faster on mobile data, lighter on the server. */
        async shrink(file, max = 2400) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 900 * 1024) return file;
            const bmp = await (window.createImageBitmap ? createImageBitmap(file, { imageOrientation: 'from-image' }) : Promise.reject());
            const scale = Math.min(1, max / Math.max(bmp.width, bmp.height));
            if (scale === 1 && file.size < 3 * 1024 * 1024) return file;
            const c = document.createElement('canvas');
            c.width = Math.round(bmp.width * scale); c.height = Math.round(bmp.height * scale);
            c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
            const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
            const out = await new Promise((r) => c.toBlob(r, type, 0.88));
            return out && out.size < file.size ? out : file;
        },
        async removePhoto(field, i) {
            const item = this.media[field.key][i];
            this.media[field.key].splice(i, 1);
            if (item?.id) { try { await fetch(`${this.boot.urls.deleteUpload}/${item.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } }); } catch (e) {} }
        },
        movePhoto(field, i, d) {
            const a = this.media[field.key]; const j = i + d; if (j < 0 || j >= a.length) return;
            [a[i], a[j]] = [a[j], a[i]];
        },
        photoBroken(field, i) {
            const it = this.media[field.key][i];
            if (it && it.id && !it.uploading) { this.media[field.key].splice(i, 1); }
        },

        /* ---------------- preview ---------------- */
        async loadPreview() {
            if (!this.template) return;
            this.previewLoading = true;
            try {
                const r = await this.post(this.boot.urls.preview, { template: this.templateSlug, content: this.content, media: this.mediaPayload(), captions: this.captionsPayload() }, 'text/html');
                this.previewHtml = r.ok ? await r.text() : '<p style="font-family:sans-serif;padding:2rem">প্রিভিউ লোড হয়নি। আবার চেষ্টা করুন।</p>';
            } catch (e) { this.previewHtml = '<p style="font-family:sans-serif;padding:2rem">ইন্টারনেট সংযোগ দেখুন।</p>'; }
            this.previewLoading = false;
        },

        /* ---------------- live preview (step 3) ---------------- */
        isDesktop() { return window.matchMedia('(min-width: 1024px)').matches; },
        scheduleLive() {
            if (this.step !== 3) return;
            this.liveDirty = true;
            clearTimeout(this._liveT);
            if (!this.isDesktop() && !this.previewOpen) return; // phones fetch when the sheet opens
            this._liveT = setTimeout(() => this.refreshLive(), 1100);
        },
        openPreview() {
            this.previewOpen = true;
            if (this.liveDirty || !this.$refs.live?.srcdoc) this.refreshLive();
            else this.$nextTick(() => this.highlight(this.activeField));
        },
        async refreshLive() {
            const frame = this.$refs.live; if (!this.template || !frame) return;
            this.liveDirty = false; this.liveLoading = true;
            let y = 0; try { y = frame.contentWindow?.scrollY || 0; } catch (e) {}
            try {
                const r = await this.post(this.boot.urls.preview, { template: this.templateSlug, content: this.content, media: this.mediaPayload(), captions: this.captionsPayload(), fill: true }, 'text/html');
                if (!r.ok) { this.liveLoading = false; return; }
                const html = await r.text();
                frame.onload = () => {
                    this.liveLoading = false;
                    try { frame.contentWindow.scrollTo(0, y); } catch (e) {}
                    if (this.activeField) this.highlight(this.activeField, !!this._wantScroll);
                    this._wantScroll = false;
                };
                frame.srcdoc = html;
            } catch (e) { this.liveLoading = false; }
        },
        focusField(key) {
            if (this.activeField === key) return;
            this.activeField = key;
            if (this.isDesktop()) this.highlight(key);
        },
        showOnPage(key) {
            this.activeField = key;
            if (this.isDesktop()) { if (this.liveDirty) { this._wantScroll = true; this.refreshLive(); } else this.highlight(key); return; }
            this._wantScroll = true;
            this.openPreview();
        },
        /* Outline the element showing this field inside the preview and scroll it into view. */
        highlight(key, scroll = true) {
            const doc = this.$refs.live?.contentDocument; if (!key || !doc || !doc.body) return;
            doc.querySelectorAll('.gb-hl').forEach((e) => e.classList.remove('gb-hl'));
            const el = doc.querySelector(`[data-f~="${(window.CSS && CSS.escape) ? CSS.escape(key) : key}"]`) || this.findByText(doc, key);
            let tag = doc.getElementById('gb-tag');
            if (!el) { if (tag) tag.style.display = 'none'; return; }
            el.classList.add('gb-hl');
            if (!tag) {
                tag = doc.createElement('div'); tag.id = 'gb-tag';
                tag.style.cssText = 'position:absolute;z-index:99;pointer-events:none;background:#e2553f;color:#fff;font:700 12px/1 "Hind Siliguri",sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 6px 16px -6px rgba(0,0,0,.5);white-space:nowrap';
                doc.body.appendChild(tag);
            }
            const r = el.getBoundingClientRect(), win = doc.defaultView;
            tag.textContent = '👇 ' + (this.fieldBy(key)?.label || '') + ' এখানে বসবে';
            tag.style.display = 'block';
            tag.style.top = Math.max(0, r.top + win.scrollY - 30) + 'px';
            tag.style.left = Math.max(6, Math.min(r.left + win.scrollX, win.innerWidth - tag.offsetWidth - 6)) + 'px';
            if (scroll) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        },
        findByText(doc, key) {
            const v = this.content[key];
            let s = typeof v === 'string' && v.trim() ? v.trim() : this.exampleText(this.fieldBy(key));
            s = (s || '').split('\n')[0].slice(0, 22).trim();
            if (s.length < 3) return null;
            const w = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);
            let n; while ((n = w.nextNode())) if (n.textContent.includes(s)) return n.parentElement;
            return null;
        },

        /* ---------------- coupon ---------------- */
        async applyCoupon() {
            const code = this.coupon.code.trim().toUpperCase();
            if (!code) { this.coupon.message = 'কুপন কোড লিখুন।'; this.coupon.error = true; return; }
            this.coupon.loading = true;
            try {
                const r = await this.post(this.boot.urls.coupon, { code, package: this.packageSlug, phone: normPhone(this.customer.phone) });
                const j = await r.json();
                if (r.ok && j.ok) { this.coupon.applied = { code: j.code, discount: j.discount, final: j.final, package: this.packageSlug }; this.coupon.code = j.code; this.coupon.error = false; }
                else { this.coupon.applied = null; this.coupon.error = true; }
                this.coupon.message = j.message || (r.status === 429 ? 'অনেকবার চেষ্টা হয়েছে, একটু পরে আবার দিন।' : 'কুপনটি যাচাই করা যায়নি।');
            } catch (e) { this.coupon.message = 'ইন্টারনেট সংযোগ দেখুন।'; this.coupon.error = true; }
            this.coupon.loading = false;
        },
        removeCoupon() { this.coupon = { code: '', applied: null, message: '', error: false, loading: false }; },
        choosePackage(slug) {
            this.packageSlug = slug;
            if (this.coupon.applied && this.coupon.applied.package !== slug) { const code = this.coupon.applied.code; this.coupon.applied = null; this.coupon.code = code; this.applyCoupon(); }
        },

        /* ---------------- submit ---------------- */
        mediaPayload() {
            const out = {};
            Object.entries(this.media).forEach(([k, list]) => { out[k] = list.filter((m) => m.id).map((m) => m.id); });
            return out;
        },
        captionsPayload() {
            const out = {};
            Object.values(this.media).flat().forEach((m) => { if (m.id && m.caption) out[m.id] = m.caption; });
            return out;
        },
        async submit() {
            const err = this.validateStep(10);
            if (err) { this.stepError = err; return; }
            this.submitting = true; this.stepError = '';
            const body = {
                template: this.templateSlug, package: this.packageSlug, occasion: this.occasion,
                customer_name: this.customer.name, customer_phone: this.customer.phone, customer_email: this.customer.email,
                desired_slug: this.slug, customer_note: this.note, content: this.content,
                media: this.mediaPayload(), captions: this.captionsPayload(),
                coupon_code: this.coupon.applied ? this.coupon.applied.code : '',
                payment_sender: this.payment.sender, payment_trx: this.payment.trx, payment_amount: this.payment.amount,
                confirm: this.confirm ? 1 : 0,
            };
            try {
                const r = await this.post(this.boot.urls.submit, body);
                const j = await r.json().catch(() => ({}));
                if (r.ok && j.redirect) {
                    try { localStorage.removeItem(STORE_KEY); } catch (e) {}
                    window.location.href = j.redirect; return;
                }
                if (r.status === 422 && j.errors) {
                    this.errors = Object.fromEntries(Object.entries(j.errors).map(([k, v]) => [k, v[0]]));
                    this.step = this.stepForErrors(Object.keys(this.errors));
                    this.$nextTick(() => (this.stepError = 'কিছু তথ্য ঠিক করতে হবে: ' + Object.values(this.errors)[0]));
                } else if (r.status === 419) {
                    this.stepError = 'পেজটি অনেকক্ষণ খোলা ছিল। রিফ্রেশ করে আবার জমা দিন (তথ্য হারাবে না)।';
                } else if (r.status === 429) {
                    this.stepError = 'অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার জমা দিন।';
                } else {
                    this.stepError = 'জমা দেওয়া যায়নি। একটু পরে আবার চেষ্টা করুন।';
                }
            } catch (e) { this.stepError = 'ইন্টারনেট সংযোগ দেখে আবার চেষ্টা করুন।'; }
            this.submitting = false;
        },
        stepForErrors(keys) {
            const has = (p) => keys.some((k) => k === p || k.startsWith(p));
            if (has('template')) return 2;
            if (has('content') || has('media')) return 3;
            if (has('customer_') || has('desired_slug')) return 4;
            if (has('occasion')) return 2;
            if (has('package')) return 6;
            if (has('coupon_code')) return 7;
            if (has('payment_')) return 9;
            return 10;
        },
        post(url, data, accept = 'application/json') {
            return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), Accept: accept }, body: JSON.stringify(data), credentials: 'same-origin' });
        },

        /* ---------------- persistence ---------------- */
        save() {
            clearTimeout(this._saveT);
            this._saveT = setTimeout(() => {
                const media = {};
                Object.entries(this.media).forEach(([k, l]) => (media[k] = l.filter((m) => m.id).map(({ id, thumb, caption }) => ({ id, thumb, caption }))));
                const data = { v: 1, at: Date.now(), step: this.step, categorySlug: this.categorySlug, templateSlug: this.templateSlug, occasion: this.occasion, content: this.content, customer: this.customer, slug: this.slug, note: this.note, media, packageSlug: this.packageSlug, couponCode: this.coupon.applied?.code || this.coupon.code };
                try { localStorage.setItem(STORE_KEY, JSON.stringify(data)); } catch (e) {}
            }, 300);
        },
        restore() {
            let d = null;
            try { d = JSON.parse(localStorage.getItem(STORE_KEY) || 'null'); } catch (e) {}
            if (!d || Date.now() - (d.at || 0) > 6 * 864e5) return;
            if (d.categorySlug && this.boot.catalog.some((c) => c.slug === d.categorySlug)) this.categorySlug = d.categorySlug;
            if (d.templateSlug && this.findTemplate(d.templateSlug)) {
                this.content = d.content || {}; this.media = d.media || {};
                this.selectTemplate(d.templateSlug, false);
            }
            Object.assign(this.customer, d.customer || {});
            this.occasion = d.occasion || ''; this.slug = d.slug || ''; this.note = d.note || '';
            if (d.packageSlug) this.packageSlug = d.packageSlug;
            if (d.couponCode) this.coupon.code = d.couponCode;
            const maxStep = this.template ? 8 : this.category ? 2 : 1; // never resume onto payment steps
            this.step = Math.min(d.step || 1, maxStep);
            if (this.step >= 5 && this.step <= 8) this.step = Math.min(this.step, 6);
            this.restored = this.step > 1;
            if (this.slug) this.checkSlug();
        },
        restored: false,
        startOver() {
            try { localStorage.removeItem(STORE_KEY); } catch (e) {}
            window.location.href = window.location.pathname;
        },
    };
}
