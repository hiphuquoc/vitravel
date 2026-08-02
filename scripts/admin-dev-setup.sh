#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
php artisan migrate --force
cd "$ROOT/admin"
if [[ ! -f .env.local ]]; then
  cp .env.local.example .env.local
fi
npm install
npm run build
echo ""
echo "Admin sẵn sàng tại: https://vitravel.dev/he-thong/"
echo "(hoặc http://vitravel.dev/he-thong/ trên host local)"
