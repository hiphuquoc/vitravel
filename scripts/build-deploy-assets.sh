#!/usr/bin/env bash
# Build Vite trên máy dev (Node 20+) — KHÔNG chạy trên server production.
# Sau khi chạy: upload toàn bộ thư mục public/build/ lên server.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

need_major=20
node_ver="$(node -v 2>/dev/null | sed 's/^v//' | cut -d. -f1 || echo 0)"
if [[ "${node_ver:-0}" -lt "$need_major" ]]; then
  echo "Cần Node >= ${need_major}. Hiện tại: $(node -v 2>/dev/null || echo 'chưa cài')."
  echo "Ví dụ: nvm install 22 && nvm use 22"
  exit 1
fi

if [[ ! -d node_modules ]]; then
  npm ci
fi

npm run build

if [[ ! -f public/build/manifest.json ]]; then
  echo "Lỗi: không tạo được public/build/manifest.json"
  exit 1
fi

# Gói sẵn để upload FTP/SFTP (giải nén vào public/ trên server → có public/build/)
ARCHIVE="${ROOT}/storage/app/vite-build.tar.gz"
mkdir -p storage/app
tar -czf "$ARCHIVE" -C public build
echo ""
echo "OK — manifest: public/build/manifest.json"
echo "Upload lên server:"
echo "  • Cách 1: copy cả thư mục public/build/ → /www/wwwroot/vitravel.net/public/build/"
echo "  • Cách 2: upload $ARCHIVE, trên server: cd public && tar -xzf ../storage/app/vite-build.tar.gz"
echo ""
echo "Trên server: xóa public/hot nếu có. Không cần npm/node trên server."
