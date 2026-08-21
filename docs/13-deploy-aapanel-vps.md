# Deploy multi-project lên VPS aaPanel

> Mục tiêu: **một** Laravel (nhiều domain public) + **Admin Next.js trên host riêng** (`admin.vitravel.net` / `.dev`).  
> Chi tiết tenancy / seed: [`11-multi-project-architecture.md`](11-multi-project-architecture.md).

---

## 0. Kiến trúc trên server

```text
                    DNS
   vitravel.net ──┐
   hicatba.com  ──┼──► Nginx ──► /www/wwwroot/vitravel/public
   phuquy.net   ──┘         ├── /api/v1/admin  ← Laravel API (CORS)
                            └── /*             ← Public Blade

   admin.vitravel.net ──► Nginx ──► /www/wwwroot/admin.vitravel/out
                                    (Next static export, root /)

MySQL: một database · nhiều project_id
```

| Thành phần | Repo / path | Ghi chú |
|------------|-------------|---------|
| App Laravel | `vitravel` | PHP 8.3+, document root = `public/` |
| Admin Console | `admin.vitravel` | Host riêng; build → `out/` (Nginx root) |
| Seed | `project/seed_*.php` | Không clone code theo từng địa phương |

**Không** embed admin dưới `/he-thong` trên domain public nữa. URL cũ `/he-thong/*` trên Laravel **redirect** sang `ADMIN_APP_URL`.

**Không** tạo site Laravel riêng cho mỗi domain public — mọi Host public trỏ **cùng** `public/`, Laravel chọn project theo `project_domains`.

---

## 1. Chuẩn bị VPS (aaPanel)

### 1.1 Phần mềm

| Thành phần | Phiên bản gợi ý |
|------------|-----------------|
| aaPanel | mới nhất ổn định |
| Nginx | kèm aaPanel |
| PHP | **8.3** (extension: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `gd`/`imagick`, `intl`) |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x (CLI) |
| Node.js | **20+** (build Vite / admin; **bắt buộc** nếu chạy stay crawler Booking trên VPS) |
| Redis | tuỳ chọn (cache/queue); mặc định app dùng `database` |

Trong aaPanel: **App Store → PHP 8.3 → Install extensions** như trên. Bật `disable_functions` đủ lỏng cho `proc_open` / `putenv` / `shell_exec` nếu Composer/artisan báo thiếu (crawler spawn Node + worker `stay-crawl:work` cũng cần các hàm này).

**Stay crawler (Booking.com / Chrome):** sau khi Laravel lên, làm thêm Node + `scripts/stay-crawl` + lib Chrome + `STAY_CRAWL_*` theo [`17-stay-crawl-vps-aapanel.md`](17-stay-crawl-vps-aapanel.md) (path mẫu: `/www/wwwroot/vitravel.net`).

Tóm tắt lệnh (user `www`):

```bash
cd /www/wwwroot/vitravel.net/scripts/stay-crawl && sudo -u www npm ci
# .env: STAY_CRAWL_HEADLESS=true
#       STAY_CRAWL_CHROME=/www/.cache/puppeteer/chrome/linux-…/chrome-linux64/chrome
php artisan config:cache
```

### 1.2 Thư mục đề xuất

```text
/www/wwwroot/vitravel.net/          ← Laravel prod (tên folder aaPanel; hoặc vitravel/)
/www/wwwroot/vitravel.net/public/   ← Nginx root (các domain public)
/www/wwwroot/admin.vitravel/        ← Next admin (git clone)
/www/wwwroot/admin.vitravel/out/    ← Nginx root admin.vitravel.net (sau npm run build)
```

Mẫu Nginx/Supervisor trong repo dùng `/www/wwwroot/vitravel/` — trên server thật **đổi cho khớp** folder (vd. `vitravel.net`).

Sau cutover có thể xóa bản cũ: `rm -rf /www/wwwroot/vitravel.net/public/he-thong`.

**Stay crawler (Puppeteer/Chrome):** cài bằng user `www`, set `STAY_CRAWL_CHROME` trỏ binary `www` đọc được — chi tiết lệnh + `.env`: [`17-stay-crawl-vps-aapanel.md`](17-stay-crawl-vps-aapanel.md).

---

## 2. DNS

Trỏ **A record** (hoặc CNAME) về IP VPS:

| Host | Project `code` (sau khi gắn DB) |
|------|----------------------------------|
| `vitravel.net`, `www.vitravel.net` | `vitravel` |
| `hicatba.com`, `www.hicatba.com` | `hicatba` |
| `phuquy.net`, `www.phuquy.net` | `phuquy` |
| `admin.vitravel.net` | *(Admin Console — không map project_domains)* |

Staging có thể thêm `*.dev` / subdomain `staging.*` — nhớ `project:domain … --add=` tương ứng.

---

## 3. Tạo site trên aaPanel

### Cách A — Một site, nhiều domain (khuyến nghị)

1. **Website → Add site**
   - Domain chính: `vitravel.net` (hoặc domain “chủ” của bạn)
   - Root: `/www/wwwroot/vitravel/public`
   - PHP: **8.3**
   - Database: tạo MySQL DB + user (ghi lại vào `.env`)
2. **Domain → Add domain** (alias): `www.vitravel.net`, `hicatba.com`, `www.hicatba.com`, `phuquy.net`, `www.phuquy.net`, …
3. SSL: Let's Encrypt cho **tất cả** domain trên site (aaPanel hỗ trợ multi-domain certificate).

### Site Admin (bắt buộc — host riêng)

1. **Website → Add site**
   - Domain: `admin.vitravel.net` (+ `www` nếu cần)
   - Root: `/www/wwwroot/admin.vitravel/out` (static export)
   - PHP: **không cần** (pure static) — hoặc tắt PHP
2. SSL Let's Encrypt cho `admin.vitravel.net`
3. Config mẫu: [`deploy/nginx-admin.conf.example`](deploy/nginx-admin.conf.example)

### Cách B — Nhiều site aaPanel, cùng root public

Mỗi domain một “Website” nhưng **Root Directory giống nhau** (`…/vitravel/public`). Dễ quản lý SSL riêng; cấu hình PHP/Nginx phải giữ đồng bộ.

> Document root **phải** là `public/`, không phải thư mục gốc Laravel.

---

## 4. Config mẫu Nginx (aaPanel)

Trong site → **Config** (hoặc *Custom configuration*), giữ `root` / `index` của aaPanel, bổ sung block Laravel:

File mẫu đầy đủ: [`deploy/nginx-site.conf.example`](deploy/nginx-site.conf.example).

Điểm quan trọng:

```nginx
root /www/wwwroot/vitravel/public;
index index.php index.html;

# Không còn location /he-thong — admin trên host riêng

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    # … fastcgi_pass tới PHP 8.3 socket aaPanel …
    include fastcgi.conf;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}

location ~* /(\.env|composer\.(json|lock)|artisan|storage/logs) {
    deny all;
}
```

Admin site riêng: [`deploy/nginx-admin.conf.example`](deploy/nginx-admin.conf.example) — root = `…/admin.vitravel/out`.

Sau khi sửa: **Reload Nginx**.

---

## 5. Clone & cài đặt Laravel

```bash
cd /www/wwwroot
# Lần đầu
git clone <URL_REPO_LARAVEL> vitravel
cd vitravel

cp .env.example .env
# hoặc copy từ docs/deploy/env.production.example

composer install --no-dev --optimize-autoloader
php artisan key:generate

# Quyền ghi (user www của aaPanel thường là www)
chown -R www:www storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

Nếu `admin` cũng clone trên server:

```bash
cd /www/wwwroot
git clone <URL_REPO_ADMIN> admin.vitravel
```

---

## 6. `.env` production (mẫu)

File đầy đủ: [`deploy/env.production.example`](deploy/env.production.example).

Tối thiểu:

```env
APP_NAME=ViTravel
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vitravel.net

# Admin Console (host riêng)
ADMIN_APP_URL=https://admin.vitravel.net
CORS_ALLOWED_ORIGINS=https://admin.vitravel.net,https://admin.vitravel.dev

APP_LOCALE=vi
APP_DEFAULT_LOCALE=vi
APP_CURRENCY_DEFAULT=VND
APP_CACHE_HTML=true

# Multi-project — production: tắt ?project=
PROJECT_DEFAULT_CODE=vitravel
PROJECT_PUBLIC_QUERY_OVERRIDE=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vitravel
DB_USERNAME=vitravel
DB_PASSWORD=********

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Media: local hoặc GCS (xem docs/gcs-standard.md)
MEDIA_DISK=public
# MEDIA_DISK=gcs
# GCS_PROJECT_ID=…
# GCS_BUCKET=…
# GCS_KEY_FILE=storage/app/gcs-credentials.json
# GCS_PUBLIC_URL=https://storage.googleapis.com/your-bucket

LOG_CHANNEL=stack
LOG_LEVEL=error
```

```bash
php artisan config:clear
php artisan config:cache
```

---

## 7. Database & domain dự án

### 7.1 Migrate (server có data mới)

```bash
cd /www/wwwroot/vitravel
php artisan migrate --force
```

### 7.2 Seed lần đầu (cẩn thận)

`migrate:fresh --seed` **xóa toàn bộ DB** — chỉ dùng server trống / staging:

```bash
php artisan migrate:fresh --seed --force
```

Production thường: migrate + gắn domain, **không** fresh:

```bash
php artisan migrate --force

# Gắn domain production (giữ data đã có)
php artisan project:ensure vitravel \
  --domain=vitravel.net --domain=www.vitravel.net --name="ViTravel"

php artisan project:ensure hicatba \
  --domain=hicatba.com --domain=www.hicatba.com --name="Cát Bà Hub"

php artisan project:ensure phuquy \
  --domain=phuquy.net --domain=www.phuquy.net --name="phuquy.net"

# Đặt primary trên prod (tuỳ chọn)
php artisan project:domain vitravel --primary=vitravel.net
php artisan project:domain hicatba --primary=hicatba.com
php artisan project:domain phuquy --primary=phuquy.net

php artisan project:domain hicatba --list
```

Seed / cập nhật nội dung **một** profile (có thể ghi đè nội dung seed — biết rủi ro trước khi chạy):

```bash
php artisan project:seed hicatba --domain=hicatba.com --domain=www.hicatba.com
php artisan project:seed phuquy --domain=phuquy.net --domain=www.phuquy.net
php artisan project:seed vitravel --domain=vitravel.net
```

Kiểm tra:

```bash
php artisan tinker --execute="
foreach (App\Models\Project::with('domains')->get() as \$p) {
  echo \$p->code.' → '.\$p->domains->pluck('domain')->join(', ').PHP_EOL;
}
"
```

---

## 8. Build front (Vite) & Admin Console

### 8.1 Assets public (Laravel Vite)

Trên máy build hoặc trên VPS (Node 20+):

```bash
cd /www/wwwroot/vitravel
npm ci
npm run build
# Kết quả: public/build/ — commit sẵn hoặc build trên CI/server
```

### 8.2 Admin Console (host `admin.vitravel.net`)

```bash
cd /www/wwwroot/admin.vitravel
npm ci
# Absolute API + CORS trên Laravel (.env: CORS_ALLOWED_ORIGINS, ADMIN_APP_URL)
export NEXT_PUBLIC_API_BASE=https://vitravel.net/api/v1/admin
export NEXT_PUBLIC_SITE_ORIGIN=https://vitravel.net
# Không set NEXT_PUBLIC_BASE_PATH — admin root = /
npm run build
# → out/  (trỏ Nginx root site admin vào đây)
```

Mở: `https://admin.vitravel.net/` — chọn dự án trên topbar (API gửi `X-Project-Code`).

URL cũ `https://vitravel.net/he-thong/…` redirect sang `ADMIN_APP_URL`. Sau ổn định: `rm -rf public/he-thong`.

Đăng nhập seed mặc định (đổi mật khẩu ngay trên prod):

| Project | Email gợi ý (theo seed) | Password seed |
|---------|-------------------------|---------------|
| vitravel | `admin@vitravel.dev` | `111111` |
| hicatba | `admin@hicatba.dev` | `111111` |
| phuquy | `admin@phuquy.dev` / `admin@phuquy.net` | `111111` |

---

## 9. Cron & Queue (bắt buộc vận hành ổn)

### 9.1 Scheduler (aaPanel Cron)

**Website / Plan Task → Shell** — chạy mỗi phút:

```bash
cd /www/wwwroot/vitravel && php artisan schedule:run >> /dev/null 2>&1
```

Hoặc crontab user `www`:

```cron
* * * * * cd /www/wwwroot/vitravel && php artisan schedule:run >> /dev/null 2>&1
```

### 9.2 Queue worker (Supervisor)

Mẫu: [`deploy/supervisor-laravel-worker.ini.example`](deploy/supervisor-laravel-worker.ini.example).

```ini
[program:vitravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/vitravel/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/vitravel/storage/logs/worker.log
```

aaPanel: **Supervisor** (plugin) → Add → paste; hoặc cài `supervisor` hệ thống rồi `supervisorctl reread && supervisorctl update`.

```bash
php artisan queue:restart   # sau mỗi lần deploy
```

---

## 10. Quy trình deploy lặp lại (git pull)

```bash
cd /www/wwwroot/vitravel
git pull --ff-only

composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Nếu đổi front:
npm ci && npm run build

# Nếu đổi scripts/stay-crawl (package-lock) — xem docs/17-stay-crawl-vps-aapanel.md:
# cd scripts/stay-crawl && sudo -u www npm ci && cd ../..
# Nhớ STAY_CRAWL_CHROME trỏ binary mà user www thực thi được (không dùng /home/<ssh-user>/.cache nếu www không đọc được).

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart
chown -R www:www storage bootstrap/cache
```

Cài lần đầu / kiểm tra crawler Booking trên VPS: [`17-stay-crawl-vps-aapanel.md`](17-stay-crawl-vps-aapanel.md) (`/www/wwwroot/vitravel.net`, Puppeteer, `STAY_CRAWL_CHROME`).

Admin có thay đổi UI:

```bash
cd /www/wwwroot/admin.vitravel
git pull --ff-only
npm ci
NEXT_PUBLIC_API_BASE=https://vitravel.net/api/v1/admin npm run build
# Nginx root đã trỏ out/ — không cần copy sang Laravel public/
```

Script gợi ý (tự tạo trên server): `deploy/deploy.sh.example`.

---

## 11. Checklist sau deploy

- [ ] `https://vitravel.net` → đúng brand ViTravel  
- [ ] `https://hicatba.com` → Cát Bà Hub  
- [ ] `https://phuquy.net` → phuquy.net  
- [ ] `https://admin.vitravel.net/` đăng nhập được, dropdown **Dự án** đổi data  
- [ ] `https://vitravel.net/he-thong/` redirect sang admin host  
- [ ] CORS: admin gọi được `/api/v1/admin` (`CORS_ALLOWED_ORIGINS`)  
- [ ] Upload ảnh / media (local disk hoặc GCS)  
- [ ] Form lead / tour riêng gửi được (queue nếu mail async)  
- [ ] SSL xanh trên mọi domain (kể cả admin)  
- [ ] `APP_DEBUG=false`, `PROJECT_PUBLIC_QUERY_OVERRIDE=false`  
- [ ] Đổi mật khẩu admin seed  

---

## 12. Troubleshooting

| Hiện tượng | Việc kiểm |
|------------|-----------|
| Mọi domain đều hiện 1 project | `project:domain {code} --list` — thiếu Host trong `project_domains` |
| Admin API 401 / sai data | Header `X-Project-Code`; chọn đúng dự án trên topbar |
| Admin CORS / network error | `CORS_ALLOWED_ORIGINS` gồm origin admin; `NEXT_PUBLIC_API_BASE` absolute |
| `admin.vitravel.net` 404 | Chưa `npm run build` hoặc Nginx root chưa trỏ `out/` |
| `/he-thong/` không redirect | Thiếu `ADMIN_APP_URL` / `config:cache`; hoặc còn static cũ trong `public/he-thong` |
| 500 sau deploy | `storage/logs/laravel.log`; quyền `storage` / `bootstrap/cache` |
| CSS/JS cũ | `npm run build` + hard refresh; kiểm `public/build/manifest.json` |
| Redirect loop | `php artisan seo:fix-redirects` (nếu có); SSL / `APP_URL` |
| `?project=` vẫn đổi được trên prod | Đặt `PROJECT_PUBLIC_QUERY_OVERRIDE=false` + `config:cache` |

---

## 13. Tài liệu liên quan

| File | Nội dung |
|------|----------|
| [`11-multi-project-architecture.md`](11-multi-project-architecture.md) | Seed, domain, admin switcher |
| [`12-admin-users-rbac.md`](12-admin-users-rbac.md) | User / quyền theo project |
| [`10-admin-console-api.md`](10-admin-console-api.md) | API `/api/v1/admin` |
| [`gcs-standard.md`](gcs-standard.md) | Media GCS |
| [`project/README.md`](../project/README.md) | Schema seed |

### File config mẫu trong thư mục này

| File | Mục đích |
|------|----------|
| [`deploy/env.production.example`](deploy/env.production.example) | `.env` production (`ADMIN_APP_URL`, CORS) |
| [`deploy/nginx-site.conf.example`](deploy/nginx-site.conf.example) | Nginx Laravel (public domains) |
| [`deploy/nginx-admin.conf.example`](deploy/nginx-admin.conf.example) | Nginx Admin Console |
| [`deploy/supervisor-laravel-worker.ini.example`](deploy/supervisor-laravel-worker.ini.example) | Queue worker |
| [`deploy/deploy.sh.example`](deploy/deploy.sh.example) | Script pull + migrate + optimize |
