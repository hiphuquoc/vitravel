# Deploy production (không build Vite trên server)

Laravel dùng **Vite**. Ở môi trường production, Blade gọi `@vite(...)` và cần file:

`public/build/manifest.json`

Thư mục `public/build/` **không có trong git** (`.gitignore`). Nếu chỉ `git pull` / upload code mà không kèm `build`, sẽ lỗi:

`ViteManifestNotFoundException`

## Quy trình đúng

### 1. Trên máy dev (một lần mỗi khi đổi CSS/JS/admin SCSS)

Yêu cầu **Node.js 20+** (Vite 8 / laravel-vite-plugin 3).

```bash
cd /path/to/vitravel.dev
chmod +x scripts/build-deploy-assets.sh
./scripts/build-deploy-assets.sh
```

Hoặc thủ công:

```bash
npm ci
npm run build
```

Kiểm tra có file `public/build/manifest.json`.

### 2. Đưa `public/build/` lên server

Copy **toàn bộ** thư mục `public/build/` (manifest + các file `.js`, `.css`, font…) lên:

`/www/wwwroot/vitravel.net/public/build/`

Ví dụ rsync:

```bash
rsync -avz --delete public/build/ user@server:/www/wwwroot/vitravel.net/public/build/
```

Hoặc dùng gói `storage/app/vite-build.tar.gz` (tạo bởi script), trên server:

```bash
cd /www/wwwroot/vitravel.net/public
tar -xzf ../storage/app/vite-build.tar.gz
```

### 3. Trên server — không cần Node/npm

- **Xóa** `public/hot` nếu tồn tại (file này chỉ dùng khi `npm run dev`).
- `.env`: `APP_ENV=production`, `APP_DEBUG=false`.
- `composer install --no-dev --optimize-autoloader`
- `php artisan migrate --force` (nếu cần)
- `php artisan storage:link`
- `php artisan config:cache` và `php artisan route:cache` (tùy chọn)

Document root của site phải trỏ vào thư mục `public/` (chuẩn Laravel).

### 4. Admin CSS

Admin cũng build qua Vite (`resources/sources/admin/style.scss`). Cùng một lần `npm run build` — không cần build riêng trên server.

## Deploy bằng git mà không muốn upload tay

**Cách A (khuyến nghị):** CI (GitHub Actions, GitLab CI) chạy `npm run build` rồi deploy artifact `public/build/` cùng code.

**Cách B:** Bỏ ignore và commit `public/build/` (repo nặng hơn, nhưng server chỉ `git pull`):

1. Xóa hoặc comment dòng `/public/build` trong `.gitignore`
2. Chạy `npm run build` trên máy dev
3. `git add public/build && git commit`

## Checklist nhanh khi lỗi manifest

| Kiểm tra | |
|----------|--|
| `public/build/manifest.json` có trên server? | Upload lại từ máy dev sau `npm run build` |
| Có file `public/hot`? | Xóa |
| Đang chạy `npm run dev` trên server? | Không dùng trên production |
| Quyền đọc thư mục `public/build`? | `www-data` / user PHP-FPM đọc được |
