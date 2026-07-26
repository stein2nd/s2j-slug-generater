#!/usr/bin/env node
/**
 * Keep plugin version in sync with package.json (source of truth).
 *
 * Usage:
 *   node scripts/sync-version.mjs         — write PHP header/constant and block.json
 *   node scripts/sync-version.mjs --check — exit 1 if any target differs
 */

import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const checkOnly = process.argv.includes('--check');

const { version } = JSON.parse(
	readFileSync(join(root, 'package.json'), 'utf8')
);

const phpPath = join(root, 's2j-slug-generater.php');
const blockPath = join(root, 'src/gutenberg/block.json');

function phpVersions(content) {
	const header = content.match(/^\s*\*\s*Version:\s*(.+)$/m)?.[1]?.trim();
	const constant = content.match(
		/define\('S2J_SLUG_GENERATER_VERSION',\s*'([^']+)'\)/
	)?.[1];
	return { header, constant };
}

function applyPhpVersion(content, nextVersion) {
	let updated = content.replace(
		/^(\s*\*\s*Version:\s*).+$/m,
		`$1${nextVersion}`
	);
	updated = updated.replace(
		/define\('S2J_SLUG_GENERATER_VERSION',\s*'[^']+'\)/,
		`define('S2J_SLUG_GENERATER_VERSION', '${nextVersion}')`
	);
	return updated;
}

function blockVersion(content) {
	return JSON.parse(content).version;
}

function applyBlockVersion(content, nextVersion) {
	return content.replace(
		/("version":\s*")[^"]+(")/,
		`$1${nextVersion}$2`
	);
}

const phpContent = readFileSync(phpPath, 'utf8');
const blockContent = readFileSync(blockPath, 'utf8');
const php = phpVersions(phpContent);
const block = blockVersion(blockContent);

const mismatches = [];
if (php.header !== version) {
	mismatches.push(
		`s2j-slug-generater.php header: ${php.header ?? '(missing)'} (expected ${version})`
	);
}
if (php.constant !== version) {
	mismatches.push(
		`S2J_SLUG_GENERATER_VERSION: ${php.constant ?? '(missing)'} (expected ${version})`
	);
}
if (block !== version) {
	mismatches.push(
		`src/gutenberg/block.json version: ${block ?? '(missing)'} (expected ${version})`
	);
}

if (checkOnly) {
	if (mismatches.length > 0) {
		console.error('Version is out of sync with package.json:\n');
		for (const line of mismatches) {
			console.error(`  - ${line}`);
		}
		console.error('\nRun: npm run version:sync');
		process.exit(1);
	}
	console.log(`Version ${version} is in sync.`);
	process.exit(0);
}

if (mismatches.length === 0) {
	console.log(`Version ${version} already in sync.`);
	process.exit(0);
}

writeFileSync(phpPath, applyPhpVersion(phpContent, version));
writeFileSync(blockPath, applyBlockVersion(blockContent, version));
console.log(`Synced version ${version} to PHP plugin file and block.json.`);
