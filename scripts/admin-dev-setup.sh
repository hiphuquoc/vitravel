#!/usr/bin/env bash
# Gợi ý env local cho admin + Laravel CORS
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ADMIN="$(cd "$ROOT/../admin.vitravel.dev" 2>/dev/null && pwd || true)"

echo "Admin chạy host riêng — không còn /he-thong trên Laravel."
echo ""
echo "1) Laravel .env:"
echo "   ADMIN_APP_URL=https://admin.vitravel.dev   # hoặc http://localhost:3100"
echo "   CORS_ALLOWED_ORIGINS=http://localhost:3100,https://admin.vitravel.dev"
echo ""
echo "2) Admin:"
if [[ -n "${ADMIN}" ]]; then
  echo "   cd $ADMIN && cp -n .env.local.example .env.local && npm run dev"
else
  echo "   cd ../admin.vitravel.dev && npm run dev"
fi
echo "   → http://localhost:3100/"
echo ""
echo "3) Hosts (tuỳ chọn): 127.0.0.1 admin.vitravel.dev"
