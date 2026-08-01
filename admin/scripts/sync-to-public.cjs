#!/usr/bin/env node
/**
 * Copy Next.js static export (out/) → Laravel public/he-thong/
 */
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const from = path.join(root, 'out');
const to = path.resolve(root, '../public/he-thong');

function rmrf(dir) {
  if (!fs.existsSync(dir)) return;
  fs.rmSync(dir, { recursive: true, force: true });
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const s = path.join(src, entry.name);
    const d = path.join(dest, entry.name);
    if (entry.isDirectory()) copyDir(s, d);
    else fs.copyFileSync(s, d);
  }
}

if (!fs.existsSync(from)) {
  console.error('Missing admin/out — run `next build` first.');
  process.exit(1);
}

rmrf(to);
copyDir(from, to);
console.log(`Synced ${from} → ${to}`);
