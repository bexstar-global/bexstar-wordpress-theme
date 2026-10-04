import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
import {createRequire} from 'node:module';
import {resolve,dirname} from 'node:path';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const require=createRequire(resolve(process.env.BEXSTAR_TEST_NODE_MODULES || 'node_modules','../package.json'));
const Ajv=require('ajv/dist/2020');const formats=require('ajv-formats');const ajv=new Ajv();formats(ajv);
const root=resolve(dirname(fileURLToPath(import.meta.url)),'../..');
const validate=ajv.compile(JSON.parse(fs.readFileSync(resolve(root,'inc/tracking/api-response.schema.json'))));
const rendered=JSON.parse(execFileSync(process.env.BEXSTAR_TEST_PHP || 'php',['-n','-r',"define('BEXSTAR_TRACKING_TEST',true);require 'tests/tracking/render.php';"],{cwd:root,encoding:'utf8'}));
for(const output of rendered.outputs){
 assert(validate(output),JSON.stringify(validate.errors));
 for(const key of ['provider','provider_reference','provider_tracking_number','credentials']){
  const bad=structuredClone(output);bad.shipment[key]='private';assert(!validate(bad),'private fields must be rejected');
 }
}
console.log(`PASS: ${rendered.outputs.length} actual API outputs match provider-neutral schema; private fields rejected`);
