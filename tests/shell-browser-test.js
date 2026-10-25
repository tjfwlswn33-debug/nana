const {chromium} = require('playwright');
const base = process.env.NANA_SHELL_TEST_URL || 'http://127.0.0.1:8085';
(async () => {
 const browser = await chromium.launch({executablePath:process.env.NANA_CHROMIUM || '/usr/bin/chromium', headless:true, args:['--no-sandbox']});
 try {
  const page = await browser.newPage();
  const errors=[]; page.on('pageerror', e=>errors.push(e.message));
  // Start on a native board, without visiting index.php first.
  await page.goto(base+'/site/bbs/board.php?bo_table=oc');
  await page.waitForURL('**/site/index.php?nana_route=*');
  const frame = page.frameLocator('#main');
  await frame.locator('.nana-card').first().waitFor();
  await page.waitForFunction(()=>window.__bgmLoads===1);
  if(!await page.locator('#original-menu').isVisible() || await page.locator('#bgm_frame').count()!==1) throw Error('Original outer menu/music frame missing');
  const initial = page.url();
  await page.locator('#original-menu a').click();
  await frame.locator('.nana-card').first().waitFor();
  if(page.url()!==initial || await page.evaluate(()=>window.__bgmLoads)!==1) throw Error('Original top-target menu escaped shell');
  await frame.locator('.nana-era').first().click();
  await frame.locator('.nana-story').waitFor();
  await frame.locator('.nana-back-row a').click();
  await frame.locator('.nana-card').first().waitFor();
  await frame.getByRole('link', {name:'전체 타임라인 ↗', exact:true}).click();
  await frame.locator('.nana-event').first().waitFor();
  await frame.locator('.nana-event h3 a').first().click();
  await frame.locator('.nana-story').waitFor();
  await frame.locator('.nana-back-row a').click();
  await frame.locator('.nana-event').first().waitFor();
  await frame.locator('.nana-add-event').click();
  if(await frame.locator('#nana_entry_type').inputValue()!=='nana_timeline') throw Error('Same-board event creation lost type inside shell');
  if(page.url()!==initial || await page.evaluate(()=>window.__bgmLoads)!==1) throw Error('Outer music frame restarted during board navigation');
  await page.setViewportSize({width:390,height:844});
  if(!await page.locator('#original-menu').isVisible() || !await page.locator('#bgm_frame').isVisible()) throw Error('Mobile shell bypassed menu/BGM');
  // main.php itself must also recover the outer shell when opened directly.
  await page.goto(base+'/site/main.php');
  await page.waitForURL('**/site/index.php?nana_route=*');
  await page.frameLocator('#main').locator('#original-head').waitFor();
  if(errors.length) throw Error(errors.join('\n'));
  console.log('PASS: actual integration index/main, direct-entry recovery, native same-board navigation, outer menu/BGM frame retained once, mobile shell');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
