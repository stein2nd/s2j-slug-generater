#!/usr/bin/env node
/**
 * Run makejson after Gutenberg production build when WP-CLI is available.
 * CI installs wp; local dist without wp uses committed languages/*.json.
 */
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');

const wpCheck = spawnSync('wp', ['--info'], {
	cwd: root,
	stdio: 'ignore',
});

if (wpCheck.error || wpCheck.status !== 0) {
	console.warn(
		'postbuild:production:gutenberg — skipping makejson (wp CLI not found; using committed languages/*.json).'
	);
	process.exit(0);
}

const run = spawnSync('npm', ['run', 'makejson'], {
	cwd: root,
	stdio: 'inherit',
	shell: true,
});

process.exit(run.status === null ? 1 : run.status);
