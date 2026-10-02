import fs from 'node:fs';
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import { resolve } from 'node:path';
const require = createRequire(resolve(process.env.BEXSTAR_TEST_NODE_MODULES || 'node_modules', '../package.json'));
const Ajv = require('ajv/dist/2020');
const formats = require('ajv-formats');
const ajv = new Ajv({allErrors:true}); formats(ajv);
const validate = ajv.compile(JSON.parse(fs.readFileSync(new URL('../../inc/tracking/response.schema.json',import.meta.url))));
const fixture = JSON.parse(fs.readFileSync(new URL('./fixtures/normalized.json',import.meta.url)));
assert(validate(fixture),JSON.stringify(validate.errors));
for(const field of ['provider','provider_tracking_number','credentials']) {
 const value=structuredClone(fixture);value.shipment[field]='must-not-leak';assert(!validate(value),field);
}
const invalid=structuredClone(fixture);invalid.shipment.current_status='made_up';assert(!validate(invalid));
const timestamp=structuredClone(fixture);timestamp.shipment.last_updated='yesterday';assert(!validate(timestamp));
console.log('PASS: normalized schema, null fields, unknown status, invalid timestamp/status, private-field rejection');
for(const number of ['','x'.repeat(129),'reference\u0000bad']) {
 const value=structuredClone(fixture);value.shipment.tracking_number=number;assert(!validate(value),number);
}
console.log('PASS: lookup schema rejects unsafe references; canonical allocation tested separately');

for (const number of ['BEXSTAR1002037','BEXSTAR0924US-10','BEXMX0920US-1','154554','111111123','order/Abc_1']) { const v=structuredClone(fixture); v.shipment.tracking_number=number; assert(validate(v)); }
