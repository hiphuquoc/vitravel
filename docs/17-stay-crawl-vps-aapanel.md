# Stay crawler trên VPS aaPanel (vitravel.net)

> Hướng dẫn **cài môi trường + lệnh kiểm tra** để crawler Booking.com (`scripts/stay-crawl`) chạy ổn định trên VPS aaPanel.  
> Deploy Laravel/admin chung: [`13-deploy-aapanel-vps.md`](13-deploy-aapanel-vps.md).  
> Luồng sản phẩm / schema: [`16-accommodation-stays.md`](16-accommodation-stays.md).

**Giả định path** (đổi nếu site bạn khác):

```text
/www/wwwroot/vitravel/          ← Laravel (vitravel.net + multi-domain)
User chạy PHP-FPM / CLI:        www   (mặc định aaPanel)
```

Crawler = **PHP** spawn **Node** → **Puppeteer** → **Chrome headless**. Thiếu Node/Chrome/lib/`proc_open`/proxy → skeleton HTML, timeout, hoặc worker chết giữa chừng.

---

## 0. Checklist nhanh

| Bước | Việc |
|------|------|
| 1 | PHP 8.3: `proc_open`, `putenv`, `shell_exec` **không** bị `disable_functions` |
| 2 | Node.js **20+** trên PATH của user `www` |
| 3 | `cd scripts/stay-crawl && npm ci` (cài Puppeteer + tải Chrome) |
| 4 | Lib hệ thống cho Chrome (fonts, `nss`, `atk`, …) |
| 5 | `.env` VPS: headless + timeout + (khuyến nghị) proxy residential |
| 6 | Quyền ghi: `storage/`, profile Chrome, cache Puppeteer |
| 7 | Smoke test CLI một URL hotel |
| 8 | RAM ≥ **2 GB trống** khi crawl (gallery + nhiều phòng) |

---

## 1. PHP (aaPanel) — cho phép spawn Node

### 1.1 Extension / disable_functions

**App Store → PHP 8.3 → Disable functions** — bỏ (hoặc không liệt kê) các hàm crawler cần:

- `proc_open`
- `putenv`
- `shell_exec` / `exec` (spawn worker `stay-crawl:work` qua `nohup`)
- `pcntl_*` (tuỳ chọn; worker CLI bắt signal dừng)

Không đủ thì admin bấm crawl sẽ báo lỗi kiểu *không tìm thấy node* / *Crawler Chrome không trả JSON* / worker không spawn.

### 1.2 Timeout & memory (CLI + FPM)

Crawl một chỗ nghỉ (basic + gallery + từng phòng) có thể **vài–nhiều phút**.

- PHP-FPM: `max_execution_time` ≥ **300** (hoặc tách: chỉ tăng pool dùng cho admin API nếu tách được).
- CLI (worker): mặc định thường không giới hạn — ổn.
- `memory_limit` ≥ **256M** (khuyến nghị **512M** khi map HTML lớn).

```bash
# Kiểm tra CLI
sudo -u www php -r 'echo "proc_open=".(function_exists("proc_open")?"ok":"MISSING").PHP_EOL;'
sudo -u www which php
sudo -u www php -v
```

---

## 2. Node.js 20+

aaPanel → **App Store → Node.js** (hoặc nvm / NodeSource). Cần binary mà user **`www`** gọi được:

```bash
sudo -u www bash -lc 'node -v && which node'
# Kỳ vọng: v20.x hoặc cao hơn
```

Nếu `www` không thấy `node` (PATH khác root):

```bash
# Ví dụ path aaPanel / nvm — chỉnh đúng máy bạn
NODE_BIN=$(command -v node || true)
# Ghi vào .env Laravel:
# STAY_CRAWL_NODE=/www/server/nodejs/v20.x.x/bin/node
```

Hoặc symlink vào `/usr/bin/node` (StayCrawlBrowser cũng dò `/usr/bin/node`, `/usr/local/bin/node`, `/opt/nodejs/bin/node`).

---

## 3. Cài Puppeteer + Chrome (bắt buộc)

Chạy **đúng user `www`** để cache Chrome nằm dưới home của user FPM (không cài bằng root rồi để `www` không đọc được).

```bash
cd /www/wwwroot/vitravel

# Quyền thư mục app (một lần / sau git pull)
chown -R www:www storage bootstrap/cache scripts/stay-crawl
chmod -R ug+rwX storage bootstrap/cache

cd /www/wwwroot/vitravel/scripts/stay-crawl
sudo -u www npm ci
```

`npm ci` tải **Chrome for Testing** vào cache Puppeteer, thường:

```text
/www/.cache/puppeteer/chrome/.../chrome-linux64/chrome
# hoặc /home/www/.cache/puppeteer/...  (tuỳ HOME của www)
```

Kiểm tra:

```bash
sudo -u www bash -lc '
  node -e "const c=require(\"./chrome.cjs\"); console.log(c.getChromePath()||\"NO_CHROME\");"
'
# Chạy trong scripts/stay-crawl
```

Nếu `NO_CHROME`: cài Chrome hệ thống (mục 4) hoặc set `STAY_CRAWL_CHROME`.

---

## 4. Thư viện hệ thống + (tuỳ chọn) Google Chrome

Puppeteer Chrome cần shared libraries. Trên Ubuntu/Debian (aaPanel thường dựa Ubuntu):

```bash
# Cập nhật + deps tối thiểu cho Chrome headless
apt-get update
apt-get install -y \
  ca-certificates fonts-liberation fonts-noto-color-emoji \
  libasound2 libatk-bridge2.0-0 libatk1.0-0 libcups2 libdbus-1-3 \
  libdrm2 libgbm1 libgtk-3-0 libnspr4 libnss3 libx11-xcb1 \
  libxcomposite1 libxdamage1 libxrandr2 libxkbcommon0 libxshmfence1 \
  xdg-utils
```

Tên gói có thể lệch theo phiên bản (ví dụ `libasound2t64`). Nếu Chrome crash lúc launch, xem log lỗi `.so` thiếu rồi `apt-cache search` tương ứng.

**Tuỳ chọn — Chrome ổn định hệ thống** (song song / fallback):

```bash
# Google Chrome stable (không dùng snap — crawler bỏ qua binary /snap/)
curl -fsSL https://dl.google.com/linux/linux_signing_key.pub | gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" \
  > /etc/apt/sources.list.d/google-chrome.list
apt-get update && apt-get install -y google-chrome-stable

which google-chrome-stable
# /usr/bin/google-chrome-stable
```

Trong `.env` (nếu muốn cố định):

```env
STAY_CRAWL_CHROME=/usr/bin/google-chrome-stable
```

> Lưu ý: biến `STAY_CRAWL_CHROME` được **Node** đọc từ môi trường process. Khi crawl từ PHP-FPM, Process kế thừa env FPM — thường **không** có biến trong `.env` Laravel. Cách chắc:
>
> 1. Dựa vào Chrome Puppeteer cache / `/usr/bin/google-chrome*` (không cần env), **hoặc**
> 2. Export trong pool PHP-FPM / `env` aaPanel, **hoặc**
> 3. Chạy worker bằng CLI `sudo -u www` (CLI load `.env` Laravel; vẫn cần export nếu Node không nhận — ưu tiên path hệ thống).

Script đã bật `--no-sandbox` / `--disable-dev-shm-usage` (phù hợp VPS Docker/LXC/`/dev/shm` nhỏ).

---

## 5. Cấu hình `.env` trên VPS

Trong `/www/wwwroot/vitravel/.env` (sau đó `php artisan config:cache`):

```env
# ── Stay crawler (VPS) ──
STAY_CRAWL_DRIVER=browser
STAY_CRAWL_HEADLESS=true
STAY_CRAWL_SLOW_MO=0
STAY_CRAWL_BROWSER_TIMEOUT=240
# STAY_CRAWL_NODE=/usr/bin/node
# STAY_CRAWL_CHROME=/usr/bin/google-chrome-stable

# Proxy residential — mạnh khuyến nghị trên IP datacenter VPS
STAY_CRAWL_PROXY=false
STAY_CRAWL_PROXY_HOST=
STAY_CRAWL_PROXY_PORT=
STAY_CRAWL_PROXY_USER=
STAY_CRAWL_PROXY_PASS=

# Worker listing (tuỳ chỉnh)
# STAY_CRAWL_WORKER_SLEEP_MS=400
# STAY_CRAWL_WORKER_STALE_SEC=900
# STAY_CRAWL_LIST_MAX_PAGES=80
```

| Biến | VPS nên đặt |
|------|-------------|
| `STAY_CRAWL_HEADLESS` | `true` (không có màn hình) |
| `STAY_CRAWL_BROWSER_TIMEOUT` | ≥ `240` (giây; gallery/phòng chậm) |
| `STAY_CRAWL_PROXY_*` | Điền host/port/user/pass proxy **residential** nếu Booking trả skeleton / GraphQL 403–429 |
| `STAY_CRAWL_PROXY` | `false` mặc định; bật trên form admin / `--proxy` khi đã có `STAY_CRAWL_PROXY_HOST` |

Profile Chrome mặc định: `storage/app/stay-crawl-chrome-profile` (user `www` phải ghi được).

```bash
cd /www/wwwroot/vitravel
sudo -u www mkdir -p storage/app/stay-crawl-chrome-profile storage/app/tmp storage/logs
chown -R www:www storage/app/stay-crawl-chrome-profile
php artisan config:clear
php artisan config:cache
```

**GCS / media:** crawl tải ảnh qua phiên Chrome rồi upload disk `MEDIA_DISK` — cấu hình GCS theo [`gcs-standard.md`](gcs-standard.md) (cùng `.env` site).

---

## 6. Proxy (ổn định trên VPS)

IP datacenter thường bị Booking hạn chế hydrate (gallery / phòng / GraphQL). Máy nhà thường ổn hơn vì IP “residential”.

1. Thuê proxy HTTP(S) residential (host:port + auth).
2. Điền `STAY_CRAWL_PROXY_*` trong `.env`.
3. Trên admin **Lưu trú → Crawler** bật proxy, hoặc CLI `--proxy`.
4. Log gợi ý: `pack.debug.network.hint` = `proxy_or_ip` → cần proxy; `fingerprint_or_lazy` → Chrome/Puppeteer/DOM.

Không có `STAY_CRAWL_PROXY_HOST` thì công tắc proxy trên form **không chạy**.

---

## 7. Smoke test (nên chạy trước khi dùng admin)

```bash
cd /www/wwwroot/vitravel

# 1) Node + Chrome path
sudo -u www bash -lc 'cd scripts/stay-crawl && node -e "console.log(require(\"./chrome.cjs\").getChromePath())"'

# 2) Một hotel (đổi URL / project / category cho đúng DB)
sudo -u www php artisan stay:crawl ingest \
  "https://www.booking.com/hotel/vn/EXAMPLE.html" \
  --project=vitravel \
  --category=ID_DANH_MUC_LUU_TRU

# Có proxy:
# … --proxy
```

Thành công: job/item có HTML + pack (tiện ích / phòng / ảnh), không chỉ skeleton.

Worker listing (sau khi admin tạo job list hoặc CLI `stay:crawl list`):

```bash
sudo -u www php artisan stay-crawl:work {jobId}
# hoặc --proxy

# Log
tail -f storage/logs/stay-crawl-work-{jobId}.log

# Pause / resume
touch storage/app/stay-crawl-pause-{jobId}
rm -f storage/app/stay-crawl-pause-{jobId}
# Nếu heartbeat chết: chạy lại stay-crawl:work {jobId}
```

Admin list/multi-item sẽ **tự `nohup` spawn** `stay-crawl:work` — cần `shell_exec` + quyền ghi `storage/logs/`.

---

## 8. Deploy lặp lại (sau `git pull`)

Thêm vào quy trình deploy Laravel (cùng [`13-deploy-aapanel-vps.md`](13-deploy-aapanel-vps.md) §10):

```bash
cd /www/wwwroot/vitravel
git pull --ff-only
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Khi package.json / lock crawler đổi:
cd scripts/stay-crawl && sudo -u www npm ci && cd ../..

php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```

Không cần rebuild Vite chỉ vì crawler.

---

## 9. Tài nguyên & vận hành ổn định

| Khuyến nghị | Lý do |
|-------------|--------|
| ≥ 2 GB RAM trống khi crawl | Chrome + Node + PHP cùng lúc |
| Không chạy 2 worker crawl song song trên cùng profile | `SingletonLock` / tranh profile |
| `STAY_CRAWL_WORKER_STALE_SEC` ≥ 900 | Một bước gallery có thể > vài phút |
| Dọn `storage/app/tmp/stay_crawl_img_*` nếu đầy đĩa | Ảnh tạm trước khi lên GCS |
| Xóa lock kẹt: file trong `storage/app/stay-crawl-chrome-profile/Singleton*` | Chrome chết bất thường |

Queue Supervisor (`queue:work`) **không** thay worker crawl — crawl dùng `stay-crawl:work` / spawn riêng. Vẫn cần Supervisor cho queue AI/mail khác.

---

## 10. Lỗi thường gặp

| Triệu chứng | Việc kiểm |
|-------------|-----------|
| *không tìm thấy node* | `STAY_CRAWL_NODE` + `sudo -u www which node` |
| *Crawler Chrome không trả JSON* / thiếu `.so` | `npm ci` lại; cài lib mục 4; thử `google-chrome-stable --headless --dump-dom https://example.com` |
| Skeleton / thiếu phòng–gallery | Proxy residential; xem `pack.debug.network.hint` |
| Worker admin không chạy nền | `shell_exec` / `nohup`; đọc `storage/logs/stay-crawl-work-*.log` |
| Permission denied profile/cache | `chown www:www` `storage` + `scripts/stay-crawl` + `~www/.cache/puppeteer` |
| Ảnh không lên site | GCS credentials + `MEDIA_DISK=gcs`; log importer |
| Timeout giữa gallery | Tăng `STAY_CRAWL_BROWSER_TIMEOUT`; giảm tải song song; proxy ổn định hơn |

---

## 11. Liên kết nhanh

| Tài liệu | Nội dung |
|----------|----------|
| [`13-deploy-aapanel-vps.md`](13-deploy-aapanel-vps.md) | Nginx, `.env` site, cron, Supervisor queue |
| [`16-accommodation-stays.md`](16-accommodation-stays.md) | Pipeline crawl, API admin, CLI đầy đủ |
| [`gcs-standard.md`](gcs-standard.md) | Disk media khi import ảnh |
| Admin | **Lưu trú → Crawler Booking.com** trên `admin.vitravel.net` + header `X-Project-Code` |
