const fs=require('fs');
const path=require('path');
const {execFileSync}=require('child_process');
const {createRequire}=require('module');
const testRequire=createRequire(path.resolve(process.env.BEXSTAR_TEST_NODE_MODULES || 'node_modules','../package.json'));
const {chromium}=testRequire('playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.BEXSTAR_TEST_CHROMIUM || undefined,headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
 const root=path.resolve(__dirname,'../..');
 const rendered=JSON.parse(execFileSync(process.env.BEXSTAR_TEST_PHP || 'php',['-n','-r',"define('BEXSTAR_TRACKING_TEST',true);require 'tests/tracking/render.php';"],{cwd:root,encoding:'utf8'}));
 const html=rendered.html;
 const css=fs.readFileSync(root+'/assets/css/site.css','utf8')+'\n'+fs.readFileSync(root+'/assets/css/tracking.css','utf8');
 const js=fs.readFileSync(root+'/assets/js/tracking.js','utf8');
 const sample=rendered.outputs[3];
 let mode='success',calls=0;
 const page=await browser.newPage(); const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.route('https://example.test/**',async route=>{
  const request=route.request();
  if(request.url().includes('/wp-json/')){
   calls++;const input=JSON.parse(request.postData());
   if(mode==='notfound')return route.fulfill({status:404,contentType:'application/json',body:JSON.stringify({success:false,error:{code:'TRACKING_NOT_FOUND',message:'PRIVATE RAW PROVIDER ERROR'}})});
   if(mode==='unavailable')return route.fulfill({status:503,contentType:'application/json',body:JSON.stringify({success:false,error:{code:'PROVIDER_AUTH_ERROR',message:'PRIVATE SECRET'}})});
   const result=structuredClone(sample);result.reference=input.reference;result.shipment.tracking_number=input.reference;
   result.events[0].description='<img src=x onerror=alert(1)> Cargo received';
   if(mode==='empty')result.events=[];
   if(mode==='partial')result.meta.partial=true;
   return route.fulfill({contentType:'application/json',body:JSON.stringify(result)});
  }
  return route.fulfill({contentType:'text/html',body:`<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}</style></head><body><main class="bex-main"><div class="bex-section bex-tracking-wrap"><p class="bex-eyebrow">TRACKING</p><h1>Track your shipment.</h1><p class="bex-lead">Enter your BEXSTAR or shipment reference to view the latest available tracking updates.</p>${html}</div></main><script>${js}</script></body></html>`});
 });
 for(const width of [1920,1536,1440,768,390,320]){
  await page.setViewportSize({width,height:1000});await page.goto('https://example.test/track/');
  await page.locator('#bex-tracking-number').fill('154554');await page.getByRole('button',{name:'TRACK SHIPMENT'}).click();
  await page.locator('.bex-tracking-results').waitFor({state:'visible'});
  const result=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth,headings:document.querySelectorAll('h1').length,injected:document.querySelector('.bex-tracking-timeline img')!==null,fields:[...document.querySelectorAll('input,button')].every(e=>e.getBoundingClientRect().right<=innerWidth)}));
  if(result.overflow||result.headings!==1||result.injected||!result.fields)throw Error(JSON.stringify({width,...result}));
  if(process.env.BEXSTAR_TEST_SCREENSHOTS && (width===1440||width===320)) { fs.mkdirSync(process.env.BEXSTAR_TEST_SCREENSHOTS,{recursive:true});await page.screenshot({path:path.join(process.env.BEXSTAR_TEST_SCREENSHOTS,`tracking-api-${width}.png`),fullPage:true}); }
  console.log('PASS viewport '+width+' no overflow, accessible labels, safe text rendering');
 }
 await page.locator('#bex-tracking-number').focus();await page.keyboard.press('Tab');
 if(await page.locator('button:focus').count()!==1)throw Error('Keyboard focus order');
 await page.keyboard.press('Enter');await page.locator('.bex-tracking-results').waitFor({state:'visible'});
 for(const state of ['notfound','unavailable','empty','partial']){
  mode=state;await page.getByRole('button',{name:'TRACK SHIPMENT'}).click();
  if(state==='notfound'||state==='unavailable'){
   await page.locator('.bex-tracking-error').waitFor({state:'visible'});
   if((await page.locator('.bex-tracking-error').textContent()).includes('PRIVATE'))throw Error('raw error exposed');
  }else{await page.locator('.bex-tracking-'+state).waitFor({state:'visible'});}
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS keyboard submit, no-result/unavailable/empty/partial states; '+calls+' mocked requests; no live calls');
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
