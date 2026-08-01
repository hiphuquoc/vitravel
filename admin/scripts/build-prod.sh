#!/usr/bin/env bash
# Build production (static → public/he-thong)
set -euo pipefail
cd "$(dirname "$0")/.."
export ADMIN_BUILD=1
# Same-origin API trên domain (không hardcode localhost)
export NEXT_PUBLIC_API_BASE="${NEXT_PUBLIC_API_BASE_PROD:-/api/v1/admin}"
npx next build
node scripts/sync-to-public.cjs
echo "Synced to public/he-thong — open https://vitravel.dev/he-thong/"
echo "Live HMR: unset ADMIN_DEV_URL only when viewing this static build; for design use npm run dev"
