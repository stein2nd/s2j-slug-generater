/**
 * Copy WP-CLI make-json hash file to the handle-based name
 * WordPress looks up first: {domain}-{locale}-{handle}.json
 */
import { copyFileSync, readdirSync } from 'node:fs';
import { join } from 'node:path';

const dir = 'languages';
const dest = join(dir, 's2j-slug-generater-ja-s2j-slug-generater-gutenberg.json');
const hashed = readdirSync(dir).find((name) =>
  /^s2j-slug-generater-ja-[a-f0-9]{32}\.json$/.test(name)
);

if (!hashed) {
  console.error('No make-json hash file found in languages/. Run wp i18n make-json first.');
  process.exit(1);
}

copyFileSync(join(dir, hashed), dest);
console.log(`Copied ${hashed} -> ${dest}`);
