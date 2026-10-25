// Run after tests/fixtures/render.php and tests/fixtures/serve.py.
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const root = process.env.NANA_FIXTURE_DIR || '/tmp/nana-v2-preview';
const base = process.env.NANA_FIXTURE_URL || 'http://127.0.0.1:8081';
(async () => {
    const browser = await chromium.launch({ executablePath: process.env.NANA_CHROMIUM || '/usr/bin/chromium', headless: true, args: ['--no-sandbox'] });
    try {
        const page = await browser.newPage();
        const art = path.join(root, 'data/file/oc');
        fs.mkdirSync(art, { recursive: true });
        // Local geometric placeholder art, generated only for these fixtures.
        for (const id of [1, 3, 5, 11, 12, 13, 14, 15, 16, 17]) {
            const palettes = [['#d6dfdf','#384e5d','#c4aa91'],['#dcd5ca','#68736c','#d9bc9e'],['#d3d9df','#4c596b','#ddc8b0']];
            const [bg, coat, skin] = palettes[(id - 1) % 3];
            const svg = id < 10 ? `<svg xmlns="http://www.w3.org/2000/svg" width="220" height="290" viewBox="0 0 220 290"><path d="M30 290V110Q30 20 110 20T190 110v180" fill="${bg}"/><path d="M35 290q0-85 55-98h40q55 13 55 98" fill="${coat}"/><path d="M96 163v35l14 21 14-21v-35" fill="${skin}"/><ellipse cx="110" cy="120" rx="43" ry="58" fill="${skin}"/><path d="M63 123q-12-78 47-78t47 88l-15-39q-40 15-65-4l-12 40" fill="${coat}"/><path d="m91 204 19 18-12 58-21-65m52-11-19 18 12 58 21-65" fill="#f5f2e9"/><path d="M84 125h12m28 0h12" stroke="${coat}" stroke-width="2"/><path d="M102 155q8 4 16 0" fill="none" stroke="${coat}"/></svg>` : `<svg xmlns="http://www.w3.org/2000/svg" width="220" height="150"><circle cx="110" cy="60" r="40" fill="none" stroke="#93aab8"/><path d="M50 105 110 10l60 95Z" fill="none" stroke="#b1bcc1"/><text x="110" y="76" text-anchor="middle" font-family="Georgia,serif" font-size="35" fill="#315875">${['I','II','III','IV','V','VI','VII'][id-11]}</text><path d="M40 118h140" stroke="#93aab8"/></svg>`;
            await page.setViewportSize({ width: 230, height: id < 10 ? 300 : 160 });
            await page.setContent(`<html><body style="margin:0;display:grid;place-items:center">${svg}</body></html>`);
            await page.screenshot({ path: path.join(art, `portrait-${id}.png`), omitBackground: true });
        }
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        page.on('response', response => { if (response.status() >= 400 && !response.url().endsWith('favicon.ico')) errors.push(`HTTP ${response.status()} ${response.url()}`); });
        for (const [name, url] of [['overview', '/site/bbs/board.php?bo_table=oc'], ['profile', '/site/bbs/board.php?bo_table=oc&wr_id=1'], ['timeline', '/site/bbs/board.php?bo_table=timeline'], ['write', '/site/bbs/write.php?bo_table=oc']]) {
            for (const width of [1280, 768, 390, 320]) {
                await page.setViewportSize({ width, height: 960 });
                await page.goto(base + url);
                await page.locator('.nana-skin').waitFor();
                if (await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth)) throw Error(`${name} horizontal overflow at ${width}px`);
                if (!await page.locator('.nana-sidebar').isVisible() || !await page.locator('#host-menu').isVisible()) throw Error(`${name} menu missing at ${width}px`);
                if (!await page.locator('#host-bgm').isVisible()) throw Error('Host BGM controls hidden');
                if (await page.locator('.nana-shell').evaluate(el => getComputedStyle(el).backgroundColor) !== 'rgb(255, 255, 255)') throw Error('Reading panel is not white');
                if (await page.locator('header').count() !== 1) throw Error('Skin added a conflicting host header');
                if (width === 1280 || width === 390) await page.screenshot({ path: path.join(root, `${name}-${width}.png`), fullPage: true });
            }
        }
        await page.setViewportSize({ width: 1280, height: 960 });
        await page.goto(base + '/site/bbs/board.php?bo_table=oc');
        if (await page.locator('.nana-era').count() !== 7) throw Error('Linked timeline preview did not render');
        if (!await page.locator('.nana-add-event').isVisible() || await page.locator('.nana-era-edit').count() !== 7) throw Error('Timeline add/edit actions missing for admin');
        await page.locator('.nana-era').first().click();
        if (new URL(page.url()).searchParams.get('bo_table') !== 'timeline' || !new URL(page.url()).searchParams.has('wr_id')) throw Error('Timeline image did not open the selected event');
        await page.goto(base + '/site/bbs/board.php?bo_table=oc');
        await page.locator('.nana-era-edit').first().click();
        if (!new URL(page.url()).searchParams.has('w') || new URL(page.url()).searchParams.get('bo_table') !== 'timeline') throw Error('Timeline edit did not open native editor');
        await page.locator('#bo_w').waitFor();
        await page.goto(base + '/site/bbs/board.php?bo_table=oc');
        await page.locator('.nana-card').first().click();
        await page.locator('.nana-profile-heading').waitFor();
        if (await page.locator('.nana-detail').count() !== 3) throw Error('Profile sections did not render');
        await page.locator('.nana-actions > a').click();
        if (!new URL(page.url()).searchParams.has('bo_table') || new URL(page.url()).searchParams.has('wr_id')) throw Error('Return link lost board ID or retained post ID');
        await page.locator('.nana-introduction').waitFor();
        await page.goto(base + '/site/index.php');
        const frame = page.frameLocator('iframe[name="frm_main"]');
        await frame.locator('.nana-card').first().click();
        await frame.locator('.nana-profile-heading').waitFor();
        await frame.locator('.nana-back-row > a').click();
        await frame.locator('.nana-introduction').waitFor();
        if (!page.url().endsWith('/site/index.php') || !await page.locator('#frame-menu').isVisible() || await page.locator('#frame-bgm').getAttribute('data-state') !== 'playing') throw Error('Navigation escaped the host iframe');
        if (errors.length) throw Error(errors.join('\n'));
        console.log('PASS: four skin pages at 1280/768/390/320px, white panel, clickable timeline, native timeline add/edit, profile sections, return-to-list, host/sidebar/BGM-control visibility, iframe navigation, no browser errors');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exit(1); });
