// Renders two phone-sized shots per template (the closed cover and the opened page) for the template cards.
// Usage: php artisan serve & node tools/previews/phones.cjs http://localhost:8000 slug1 slug2 …
const { chromium } = require('playwright');
const { execFileSync } = require('child_process');
const fs = require('fs');
(async () => {
  const [base, ...slugs] = process.argv.slice(2);
  const out = 'public/images/previews/phone';
  fs.mkdirSync(out, { recursive: true });
  const b = await chromium.launch();
  for (const slug of slugs) {
    const p = await b.newPage({ viewport: { width: 390, height: 800 }, deviceScaleFactor: 1.5 });
    await p.goto(`${base}/templates/${slug}/render`, { waitUntil: 'networkidle' });
    await p.addStyleTag({ content: '.t-preview-bar,.t-music{display:none!important} .t-particles{opacity:.6}' });
    await p.waitForTimeout(1600);
    const gate = p.locator('[data-gate-open]');
    const hasGate = await gate.count();
    const shot = async (name) => {
      const png = `/tmp/${slug}-${name}.png`;
      await p.screenshot({ path: png });
      execFileSync('python3', ['-c', `from PIL import Image; Image.open('${png}').convert('RGB').resize((390,800), Image.LANCZOS).save('${out}/${slug}-${name}.webp', quality=80, method=6)`]);
    };
    await shot('cover');
    if (hasGate) { await gate.click(); await p.waitForTimeout(3200); }
    await p.evaluate((g) => { document.querySelectorAll('.t-reveal').forEach((e) => e.classList.add('is-in')); window.scrollTo(0, g ? 0 : Math.round(window.innerHeight * 0.9)); }, hasGate);
    await p.waitForTimeout(1400);
    await shot('inside');
    console.log(slug, hasGate ? 'gate' : 'scroll');
    await p.close();
  }
  await b.close();
})();
