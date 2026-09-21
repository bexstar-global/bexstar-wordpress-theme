import assert from 'node:assert/strict';
import { readFileSync, readdirSync, existsSync } from 'node:fs';
import { resolve, relative, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const read = path => readFileSync(resolve(root,path),'utf8');
function walk(path='') {
  return readdirSync(resolve(root,path),{withFileTypes:true}).flatMap(e => {
    const name = path ? path+'/'+e.name : e.name;
    if (e.name === '.git' || e.name === 'node_modules' || e.name === '.test-output') return [];
    return e.isDirectory() ? walk(name) : [name];
  });
}
const files = walk();
let checks = 0;
function ok(value, label) { assert.ok(value,label); checks++; }
const required=['style.css','theme.json','functions.php','parts/header.html','parts/footer.html',
  ...['index','front-page','page','single','archive','search','404'].map(s=>'templates/'+s+'.html')];
for(const name of required) ok(existsSync(resolve(root,name)), 'Missing '+name);
ok(!files.some(f=>f==='bexstar/style.css'), 'Nested theme root');
ok(/Theme Name: BEXSTAR/.test(read('style.css')), 'Theme header');
const theme=JSON.parse(read('theme.json'));
ok(theme.version===3, 'theme.json v3');
ok(theme.settings.color.palette.find(p=>p.slug==='yellow').color==='#FFC400', 'Brand yellow');
ok(theme.settings.color.palette.find(p=>p.slug==='navy').color==='#071E33', 'Brand navy');
for(const name of files.filter(f=>f.endsWith('.json'))) {JSON.parse(read(name)); checks++;}
const manifest=JSON.parse(read('assets/media-manifest.json'));
const ids=new Set();
const patterns=files.filter(f=>f.startsWith('patterns/') && f.endsWith('.php'));
for(const name of [...patterns,...files.filter(f=>f.endsWith('.html'))]) {
  const source=read(name);
  const stack=[];
  for(const match of source.matchAll(/<!--\s*(\/?)wp:([\w/-]+)(?:\s+(\{[\s\S]*?\}))?\s*(\/?)-->/g)) {
    const [,close,block,attrs,self]=match;
    if(attrs) {JSON.parse(attrs);checks++;}
    if(close) assert.equal(stack.pop(),block,'Unbalanced block in '+name);
    else if(!self) stack.push(block);
    if(block==='pattern' && !close) {
      const slug=JSON.parse(attrs).slug.split('/')[1];
      ok(existsSync(resolve(root,'patterns/'+slug+'.php')), 'Missing pattern '+slug);
    }
    if(block==='bexstar/media' && attrs) ok(manifest[JSON.parse(attrs).slot], 'Unknown media slot');
  }
  ok(stack.length===0,'Unclosed blocks '+name);
  for(const id of source.matchAll(/id="([^"]+)"/g)) ids.add(id[1]);
}
const home=read('templates/front-page.html');
ok((home.match(/wp:pattern/g)||[]).length===14,'Fourteen homepage sections');
const homeText=[...home.matchAll(/"slug":"bexstar\/([^"]+)"/g)].map(m=>read('patterns/'+m[1]+'.php')).join('\n');
ok((homeText.match(/<h1\b/g)||[]).length===1,'Exactly one homepage H1');
for(const m of homeText.matchAll(/bexstar_section_url\( '[^']+', '([^']+)' \)/g)) ok(ids.has(m[1]),'Missing fallback anchor '+m[1]);
for(const [slot,m] of Object.entries(manifest)) {
  ok(m.width>0&&m.height>0,'Dimensions '+slot);
  ok(!m.path.includes('..')&&m.path.startsWith('assets/images/'),'Safe asset path');
  ok(existsSync(resolve(root,dirname(m.path))), 'Missing media directory '+m.path);
}
for(const name of files.filter(f=>/\.(php|js|css|html|json)$/.test(f))) {
  ok(!/https?:\/\/(?:www\.)?(?:dsv\.com|bexgl\.com)/i.test(read(name)), 'Forbidden runtime domain '+name);
  ok(!/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|gh[pousr]_[A-Za-z0-9]{20,}/.test(read(name)), 'Possible credential '+name);
}
const navigation=JSON.parse(read('inc/navigation.json'));
ok(navigation.length===7,'Seven primary groups');
ok(!/fetch\(|XMLHttpRequest|localStorage|document.cookie/.test(read('assets/js/site.js')), 'Unexpected remote/data behavior');
console.log(JSON.stringify({result:'PASS',checks,files:files.length,patterns:patterns.length,mediaSlots:Object.keys(manifest).length,
  limitation:'Static structure only; PHP syntax, Gutenberg serialization and WordPress activation need runtime checks.'},null,2));
