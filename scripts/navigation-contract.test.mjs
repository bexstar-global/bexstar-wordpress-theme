import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import {readFileSync} from 'node:fs';
const source = readFileSync(new URL('../assets/js/site.js',import.meta.url),'utf8');

/* Event/DOM contract tests for the production script, not a rendered browser substitute. */
function fixture({mobile=false,hover=true}={}) {
  const timers=new Map();let time=0,seq=0,document;
  class Element {
    constructor(kind,parent=null) {this.kind=kind;this.parent=parent;this.listeners={};this.attrs={};this.hidden=false;this.open=false;}
    addEventListener(name,fn){(this.listeners[name]??=[]).push(fn);}
    fire(name,event={}){for(const fn of this.listeners[name]??[])fn({target:this,...event});}
    contains(el){for(let n=el;n;n=n.parent)if(n===this)return true;return false;}
    focus(){document.activeElement=this;}
    setAttribute(k,v){this.attrs[k]=v;}
    getAttribute(k){return this.attrs[k];}
    closest(selector){
      for(let n=this;n;n=n.parent){
        if(selector==='details[open]'&&n.kind==='details'&&n.open)return n;
        if(selector==='a'&&n.kind==='a')return n;
      }
      return null;
    }
  }
  const header=new Element('header'),nav=new Element('nav',header),toggle=new Element('button',header);
  const menus=[new Element('details',nav),new Element('details',nav)];
  for(const m of menus){m.summary=new Element('summary',m);m.link=new Element('a',m);m.querySelector=()=>m.summary;}
  const outside=new Element('outside');
  header.querySelector=s=>s==='.bex-menu-toggle'?toggle:nav;
  header.querySelectorAll=s=>s==='.bex-mega'?menus:menus.filter(m=>m.open);
  document={activeElement:outside,hidden:false,listeners:{},
    querySelectorAll:s=>s==='[data-bex-header]'?[header]:[],
    addEventListener(n,fn){(this.listeners[n]??=[]).push(fn);},
    fire(n,e){for(const fn of this.listeners[n]??[])fn(e);}
  };
  function mq(matches){return {matches,listeners:[],addEventListener(n,fn){this.listeners.push(fn);},change(v){this.matches=v;for(const fn of this.listeners)fn();}};}
  const mobileQuery=mq(mobile),hoverQuery=mq(hover),reduced=mq(false);
  const window={matchMedia:s=>s.includes('max-width')?mobileQuery:s.includes('pointer')?hoverQuery:reduced,
    setTimeout(fn,ms){const id=++seq;timers.set(id,{fn,at:time+ms});return id;},
    clearTimeout(id){timers.delete(id);}
  };
  function tick(ms){time+=ms;for(const [id,t]of [...timers])if(t.at<=time){timers.delete(id);t.fn();}}
  vm.runInNewContext(source,{document,window});
  return {header,nav,toggle,menus,outside,document,mobileQuery,hoverQuery,tick};
}

test('desktop starts with navigation visible and mobile trigger hidden',()=>{
 const x=fixture();assert.equal(x.nav.hidden,false);assert.equal(x.toggle.hidden,true);
});
test('click opens immediately, clicking another switches, clicking again closes',()=>{
 const x=fixture();let prevented=0;
 const click=m=>m.summary.fire('click',{preventDefault(){prevented++;}});
 click(x.menus[0]);assert.equal(x.menus[0].open,true);
 click(x.menus[1]);assert.equal(x.menus[0].open,false);assert.equal(x.menus[1].open,true);
 click(x.menus[1]);assert.equal(x.menus[1].open,false);assert.equal(prevented,3);
});
test('click closes a hover-open menu without a delayed reopen',()=>{
 const x=fixture();x.menus[0].fire('pointerenter');x.tick(160);
 x.menus[0].summary.fire('click',{preventDefault(){}});x.tick(500);
 assert.equal(x.menus[0].open,false);
});
test('mobile click works without hover and cancels a pending desktop hover',()=>{
 const x=fixture({mobile:true});x.toggle.fire('click');
 x.menus[0].summary.fire('click',{preventDefault(){}});assert.equal(x.menus[0].open,true);
 const y=fixture();y.menus[0].fire('pointerenter');
 y.menus[1].summary.fire('click',{preventDefault(){}});y.tick(200);
 assert.equal(y.menus[0].open,false);assert.equal(y.menus[1].open,true);
});
test('hover intent delays opening; leaving cancels accidental flyover',()=>{
 const x=fixture();x.menus[0].fire('pointerenter');x.tick(100);assert.equal(x.menus[0].open,false);
 x.menus[0].fire('pointerleave');x.tick(300);assert.equal(x.menus[0].open,false);
 x.menus[0].fire('pointerenter');x.tick(160);assert.equal(x.menus[0].open,true);
});
test('opening another menu closes first; pointerleave closes after delay',()=>{
 const x=fixture();x.menus[0].fire('pointerenter');x.tick(160);x.menus[1].fire('pointerenter');x.tick(160);
 assert.equal(x.menus[0].open,false);assert.equal(x.menus[1].open,true);
 x.menus[1].fire('pointerleave');x.tick(219);assert.equal(x.menus[1].open,true);x.tick(1);assert.equal(x.menus[1].open,false);
});
test('keyboard focus is not hidden by hover on a different menu',()=>{
 const x=fixture();x.menus[0].open=true;x.menus[0].link.focus();x.menus[1].fire('pointerenter');x.tick(160);
 assert.equal(x.menus[0].open,true);assert.equal(x.menus[1].open,false);
});
test('Escape closes menu, cancels delayed opens, restores trigger focus',()=>{
 const x=fixture();x.menus[0].open=true;x.menus[0].link.focus();x.menus[1].fire('pointerenter');
 x.header.fire('keydown',{key:'Escape'});x.tick(200);
 assert.equal(x.menus[0].open,false);assert.equal(x.menus[1].open,false);assert.equal(x.document.activeElement,x.menus[0].summary);
});
test('click outside and focus departure close disclosures',()=>{
 const x=fixture();x.menus[0].open=true;x.document.fire('click',{target:x.outside});assert.equal(x.menus[0].open,false);
 x.menus[1].open=true;x.outside.focus();x.header.fire('focusout');x.tick(0);assert.equal(x.menus[1].open,false);
});
test('mobile toggle updates hidden and expanded state; hover does not open',()=>{
 const x=fixture({mobile:true});assert.equal(x.nav.hidden,true);assert.equal(x.toggle.hidden,false);
 x.menus[0].fire('pointerenter');x.tick(200);assert.equal(x.menus[0].open,false);
 x.toggle.fire('click');assert.equal(x.nav.hidden,false);assert.equal(x.toggle.getAttribute('aria-expanded'),'true');
 x.header.fire('keydown',{key:'Escape'});assert.equal(x.nav.hidden,true);assert.equal(x.document.activeElement,x.toggle);
});
test('resize to mobile moves focus away from hidden navigation',()=>{
 const x=fixture();x.menus[0].link.focus();x.mobileQuery.change(true);
 assert.equal(x.nav.hidden,true);assert.equal(x.document.activeElement,x.toggle);
});
test('touch pointer cannot hover-open on desktop; native toggle stays exclusive',()=>{
 const x=fixture({hover:false});x.menus[0].fire('pointerenter');x.tick(200);assert.equal(x.menus[0].open,false);
 x.menus[0].open=true;x.menus[1].open=true;x.menus[1].fire('toggle');assert.equal(x.menus[0].open,false);
});
