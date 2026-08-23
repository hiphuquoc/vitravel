# Stay crawler trên VPS aaPanel (vitravel.net)

> Hướng dẫn **cài môi trường + lệnh kiểm tra** để crawler Booking.com (`scripts/stay-crawl`) chạy ổn định trên VPS aaPanel.  
> Deploy Laravel/admin chung: [`13-deploy-aapanel-vps.md`](13-deploy-aapanel-vps.md).  
> Luồng sản phẩm / schema: [`16-accommodation-stays.md`](16-accommodation-stays.md).

**Path site (vitravel.net):**

```text
/www/wwwroot/vitravel.net/          ← Laravel (document root = public/)
User PHP-FPM / CLI crawl:           www   (mặc định aaPanel)
```

Crawler = **PHP** spawn **Node** → **Puppeteer** → **Chrome headless**. Thiếu Node/Chrome/lib/`proc_open`/proxy → skeleton HTML, timeout, hoặc worker chết giữa chừng.

Laravel đọc `.env` → `config/stay.php` → `StayCrawlBrowser` **truyền** `STAY_CRAWL_CHROME` / `STAY_CRAWL_USER_DATA_DIR` sang process Node (không cần export thủ công trong PHP-FPM).

---

## 0. Checklist nhanh

| Bước | Việc |
|------|------|
| 1 | PHP 8.3: `proc_open`, `putenv`, `shell_exec` **không** bị `disable_functions` |
| 2 | Node.js **20+** trên PATH của user `www` |
| 3 | `cd scripts/stay-crawl && sudo -u www npm ci` (Chrome cache thuộc **www**, không phải user SSH khác) |
| 4 | Lib hệ thống cho Chrome (fonts, `nss`, `atk`, …) |
| 5 | `.env`: `STAY_CRAWL_HEADLESS=true` + `STAY_CRAWL_CHROME=…` (binary **www đọc được**) |
| 6 | Quyền ghi: `storage/`, profile Chrome |
| 7 | Smoke test CLI một URL hotel |
| 8 | RAM ≥ **2 GB trống** khi crawl |

---

## 1. PHP (aaPanel) — cho phép spawn Node

### 1.1 Extension / disable_functions

**App Store → PHP 8.3 → Disable functions** — bỏ (hoặc không liệt kê):

- `proc_open`
- `putenv`
- `shell_exec` / `exec` (spawn Chrome step / CLI dự phòng; listing chính dùng Laravel queue)
- `pcntl_*` (tuỳ chọn)

## 1.2 Cấu hình Nginx & PHP-FPM Timeout (Phòng ngừa ngắt kết nối 60s)

Mặc định Nginx và PHP-FPM trên nhiều VPS thường đặt `fastcgi_read_timeout` là **60s**. Dù mã nguồn crawler đã được tối ưu bất đồng bộ hoàn toàn (khởi tạo job và poll trạng thái chỉ mất **< 0.5s**), việc cấu hình timeout dài ở tầng Nginx/PHP là lớp phòng vệ dự phòng quan trọng cho các tác vụ xuất/nhập dữ liệu nặng.

### Nginx (aaPanel: WebSite → Site Settings → Config):
Thêm các dòng sau vào khối `location ~ [^/]\.php(/|$)` hoặc `location ~ \.php$`:

```nginx
    fastcgi_read_timeout 600s;
    fastcgi_send_timeout 600s;
    fastcgi_connect_timeout 60s;
```

Nếu chạy qua reverse proxy (Node.js/Next.js/Cloudflare):
```nginx
    proxy_read_timeout 600s;
    proxy_send_timeout 600s;
```

Sau đó reload Nginx:
```bash
sudo nginx -t && sudo nginx -s reload
# hoặc trên aaPanel: App Store → Nginx → Reload
```

### PHP-FPM (aaPanel: App Store → PHP 8.3 → Settings):
- **Configuration (php.ini)**:
  ```ini
  max_execution_time = 300
  max_input_time = 300
  ```
- **FPM Profile (php-fpm.conf / www.conf)**:
  ```ini
  request_terminate_timeout = 600
  ```
- Sau đó **Restart PHP-FPM**.

---

## 1.3 open_basedir (aaPanel — hay gặp)

PHP-FPM thường chỉ cho phép:

```text
/www/wwwroot/vitravel.net/:/tmp/
```

Khi đó `is_executable('/usr/bin/node')` **lỗi** dù CLI chạy OK → admin báo không gọi được `/stay-crawls/status`.

**Cách 1 (khuyến nghị — đã hỗ trợ trong code):** đặt trong `.env` rồi `config:cache`:

```env
STAY_CRAWL_NODE=/usr/bin/node
STAY_CRAWL_CHROME=/home/www/.cache/puppeteer/chrome/linux-…/chrome-linux64/chrome
```

Code **tin** `STAY_CRAWL_NODE` (không gọi `is_executable` ngoài open_basedir). Process `proc_open` vẫn chạy `/usr/bin/node` bình thường.

**Cách 2:** nới open_basedir trong aaPanel → site → PHP → `open_basedir`, thêm:

```text
/www/wwwroot/vitravel.net/:/tmp/:/usr/bin/node:/usr/bin:/home/www/.cache/puppeteer/
```

(Hoặc tắt open_basedir trên site này nếu chấp nhận được.)

---

## 2. Node.js 20+

```bash
sudo -u www bash -lc 'node -v && which node'
```

Nếu thiếu:

```env
STAY_CRAWL_NODE=/www/server/nodejs/v20.xx.x/bin/node
```

(hoặc symlink `/usr/bin/node`).

---

## 3. Cài Puppeteer + Chrome (khuyến nghị — user `www`)

**Quan trọng:** đừng cài Chrome bằng user SSH `phupv` rồi trỏ `STAY_CRAWL_CHROME=/home/phupv/.cache/...` nếu PHP chạy user `www` — `www` thường **không đọc** `/home/phupv/`.

Nếu `sudo -u www npm ci` báo `EACCES` trên `node_modules` — thư mục đang thuộc **root** (hoặc user SSH). Sửa quyền trước:

```bash
cd /www/wwwroot/vitravel.net

# Xóa cache npm hỏng / node_modules thuộc root rồi giao cho www
rm -rf scripts/stay-crawl/node_modules
mkdir -p /home/www/.npm /www/.cache/puppeteer
chown -R www:www storage bootstrap/cache scripts/stay-crawl /home/www/.npm /www/.cache
chmod -R ug+rwX storage bootstrap/cache scripts/stay-crawl

cd /www/wwwroot/vitravel.net/scripts/stay-crawl
sudo -u www npm ci
```

Chrome for Testing thường nằm:

```text
/www/.cache/puppeteer/chrome/linux-*/chrome-linux64/chrome
# hoặc /home/www/.cache/puppeteer/...  (HOME của www)
```

Kiểm tra path thật sau `npm ci`:

```bash
sudo -u www bash -lc 'ls -la /www/.cache/puppeteer/chrome 2>/dev/null; ls -la /home/www/.cache/puppeteer/chrome 2>/dev/null; find /www /home/www -path "*puppeteer/chrome/*/chrome-linux64/chrome" 2>/dev/null | head'
cd /www/wwwroot/vitravel.net/scripts/stay-crawl
sudo -u www bash -lc 'node -e "const c=require(\"./chrome.cjs\"); console.log(c.getChromePath()||\"NO_CHROME\");"'
```

Ghi path in ra vào `.env` (ví dụ):

```env
STAY_CRAWL_CHROME=/home/www/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome
```

### 3.1 Nếu đã có Chrome dưới `/home/phupv/.cache/...`

Chỉ chạy khi **file nguồn tồn tại**:

```bash
ls /home/phupv/.cache/puppeteer/chrome/   # phải thấy linux-…

# Nếu thư mục linux-142… không có → bỏ qua copy; dùng Chrome do `sudo -u www npm ci` vừa tải.
```

Copy (tạo đích trước — tránh `mkdir failed: No such file`):

```bash
SRC_VER=$(ls /home/phupv/.cache/puppeteer/chrome | head -1)   # vd. linux-142.0.7444.175
test -d "/home/phupv/.cache/puppeteer/chrome/$SRC_VER/chrome-linux64" || { echo "Không có Chrome nguồn"; exit 1; }

mkdir -p /www/.cache/puppeteer/chrome
rsync -a "/home/phupv/.cache/puppeteer/chrome/$SRC_VER" /www/.cache/puppeteer/chrome/
chown -R www:www /www/.cache/puppeteer

# .env:
# STAY_CRAWL_CHROME=/www/.cache/puppeteer/chrome/${SRC_VER}/chrome-linux64/chrome
```

**Cách B:** nới quyền home (kém an toàn) — chỉ khi nguồn tồn tại:

```bash
chmod o+x /home/phupv /home/phupv/.cache /home/phupv/.cache/puppeteer
chmod -R o+rX /home/phupv/.cache/puppeteer/chrome
sudo -u www test -x /home/phupv/.cache/puppeteer/chrome/*/chrome-linux64/chrome && echo OK
```

**Cách C:** cài `google-chrome-stable` → `STAY_CRAWL_CHROME=/usr/bin/google-chrome-stable`.

---

## 4. Thư viện hệ thống + (tuỳ chọn) Google Chrome

```bash
apt-get update
apt-get install -y \
  ca-certificates fonts-liberation fonts-noto-color-emoji \
  libasound2 libatk-bridge2.0-0 libatk1.0-0 libcups2 libdbus-1-3 \
  libdrm2 libgbm1 libgtk-3-0 libnspr4 libnss3 libx11-xcb1 \
  libxcomposite1 libxdamage1 libxrandr2 libxkbcommon0 libxshmfence1 \
  xdg-utils
```

(Trên Ubuntu mới có thể là `libasound2t64`.)

Tuỳ chọn Chrome hệ thống:

```bash
curl -fsSL https://dl.google.com/linux/linux_signing_key.pub | gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" \
  > /etc/apt/sources.list.d/google-chrome.list
apt-get update && apt-get install -y google-chrome-stable
```

Script crawler đã bật `--no-sandbox` / `--disable-dev-shm-usage`.

Smoke launch:

```bash
sudo -u www bash -lc '
  CHROME=/www/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome
  "$CHROME" --headless --no-sandbox --disable-gpu --dump-dom https://example.com | head -c 200
'
```

---

## 5. Biến `.env` mẫu (`/www/wwwroot/vitravel.net/.env`)

```env
# ── Stay crawler (VPS vitravel.net) ──
STAY_CRAWL_DRIVER=browser
STAY_CRAWL_HEADLESS=true
STAY_CRAWL_SLOW_MO=0
STAY_CRAWL_BROWSER_TIMEOUT=240
STAY_CRAWL_LIST_BROWSER_EXTRA_SEC=240

# Node (bỏ trống nếu /usr/bin/node OK). aaPanel thường cần path đầy đủ:
# STAY_CRAWL_NODE=/www/server/nodejs/v20.20.2/bin/node

# Chrome — PHẢI là path mà user www thực thi được
STAY_CRAWL_CHROME=/www/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome
# hoặc: STAY_CRAWL_CHROME=/usr/bin/google-chrome-stable

# Profile (mặc định storage/app/stay-crawl-chrome-profile)
# STAY_CRAWL_USER_DATA_DIR=/www/wwwroot/vitravel.net/storage/app/stay-crawl-chrome-profile

# Proxy residential (IP datacenter hay bị Booking chặn hydrate)
STAY_CRAWL_PROXY=false
STAY_CRAWL_PROXY_HOST=
STAY_CRAWL_PROXY_PORT=
STAY_CRAWL_PROXY_USER=
STAY_CRAWL_PROXY_PASS=
```

| Biến | Vai trò |
|------|---------|
| `STAY_CRAWL_CHROME` | Binary Chrome (Puppeteer `executablePath`) |
| `STAY_CRAWL_NODE` | Binary Node nếu không có trên PATH của `www` |
| `STAY_CRAWL_HEADLESS` | `true` trên VPS |
| `STAY_CRAWL_BROWSER_TIMEOUT` | Timeout 1 phiên Chrome (giây) |
| `STAY_CRAWL_LIST_BROWSER_EXTRA_SEC` | Cộng thêm khi crawl listing (scroll + «Tải thêm») |
| `STAY_CRAWL_USER_DATA_DIR` | Profile Chrome persistent |
| `STAY_CRAWL_PROXY_*` | Proxy; form admin bật `--proxy` chỉ chạy khi có `HOST` |

Áp dụng:

```bash
cd /www/wwwroot/vitravel.net
sudo -u www mkdir -p storage/app/stay-crawl-chrome-profile storage/app/tmp storage/logs
chown -R www:www storage
php artisan config:clear
php artisan config:cache
```

**GCS:** [`gcs-standard.md`](gcs-standard.md) — crawl upload ảnh qua `MEDIA_DISK=gcs`.

---

## 6. Proxy (ổn định trên VPS)

IP datacenter thường bị Booking hạn chế. Điền `STAY_CRAWL_PROXY_*`, bật proxy trên admin / CLI `--proxy`.  
Log: `pack.debug.network.hint` = `proxy_or_ip` → cần proxy.

---

## 7. Smoke test

```bash
cd /www/wwwroot/vitravel.net

sudo -u www bash -lc 'cd scripts/stay-crawl && node -e "
  process.env.STAY_CRAWL_CHROME=process.env.STAY_CRAWL_CHROME||\"\";
  console.log(require(\"./chrome.cjs\").getChromePath()||\"NO_CHROME\");
"'

# Export tạm từ .env rồi test:
export STAY_CRAWL_CHROME=/www/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome
sudo -u www -E bash -lc 'cd /www/wwwroot/vitravel.net/scripts/stay-crawl && node -e "console.log(require(\"./chrome.cjs\").getChromePath())"'

sudo -u www php artisan stay:crawl ingest \
  "https://www.booking.com/hotel/vn/EXAMPLE.html" \
  --project=vitravel \
  --category=ID_DANH_MUC
```

Listing / queue (sau khi admin «Cào danh mục»):

```bash
# Bắt buộc: worker Laravel queue (Supervisor — xem docs/deploy/supervisor-laravel-worker.ini.example)
sudo -u www php artisan queue:work database --sleep=3 --tries=3 --timeout=1200

# Theo dõi jobs
sudo -u www php artisan queue:failed
# (tuỳ chọn) CLI cũ theo jobId:
# sudo -u www php artisan stay-crawl:work {jobId}
```

---

## 8. Deploy lặp lại

```bash
cd /www/wwwroot/vitravel.net
git pull --ff-only
composer install --no-dev --optimize-autoloader
php artisan migrate --force
cd scripts/stay-crawl && sudo -u www npm ci && cd ../..
php artisan optimize:clear && php artisan config:cache
php artisan queue:restart
chown -R www:www storage bootstrap/cache
```

---

## 9. Xử lý sự cố & Tiến trình bị kẹt / treo

### A. Dừng tiến trình kẹt (Kill hanging crawler/chrome processes)
Nếu crawler hoặc Chrome bị kẹt do timeout, chiếm dụng CPU/RAM hoặc giữ lock:

```bash
# 1. Diệt toàn bộ tiến trình Chrome & Stay crawl đang chạy ngầm
pkill -9 -f "chrome-linux64/chrome" 2>/dev/null
pkill -9 -f "stay:crawl" 2>/dev/null
pkill -9 -f "stay_crawl" 2>/dev/null
pkill -9 -f "stay-crawl" 2>/dev/null

# 2. Xóa các file tmp crawler bị rác
rm -rf storage/app/tmp/stay_crawl_* 2>/dev/null
```

### B. Câu lệnh chẩn đoán & kiểm tra nhanh môi trường (Diagnostics)
Chạy khối lệnh sau để rà soát toàn bộ trạng thái crawler:

```bash
# Kiểm tra các tiến trình đang chạy
ps aux | grep -E 'stay:crawl|chrome|puppeteer|artisan' | grep -v grep

# Kiểm tra Node và đường dẫn Chrome
which node nodejs google-chrome google-chrome-stable chromium-browser 2>/dev/null
node -v 2>/dev/null
ls -la $(grep 'STAY_CRAWL_CHROME' .env | cut -d '=' -f2) 2>/dev/null

# Xem 30 dòng log lỗi gần nhất của crawler / Laravel
tail -n 30 storage/logs/laravel.log
```

---

## 10. Lỗi thường gặp

| Triệu chứng | Việc kiểm |
|-------------|-----------|
| Nút crawler / lỗi `open_basedir` + `is_executable(/usr/bin/node)` | Đặt `STAY_CRAWL_NODE=/usr/bin/node` (bắt buộc trên aaPanel). Deploy bản Laravel có fix tin `.env`. Tuỳ chọn: nới `open_basedir` thêm `/usr/bin` + `/home/www/.cache/puppeteer/` |
| Nút «Bắt đầu crawler» xám / không chạy | Admin hiện lý do dưới form. Thường `browser_ready=false` vì PHP không thấy Node → `STAY_CRAWL_NODE=…` + `config:cache`. Kiểm tra API: `GET /api/v1/admin/stay-crawls/status` |
| `EACCES` `npm ci` / `node_modules` | `rm -rf scripts/stay-crawl/node_modules` rồi `chown -R www:www scripts/stay-crawl` + `mkdir -p /home/www/.npm && chown www:www /home/www/.npm` |
| `rsync` No such file `/home/phupv/...` | Chrome chưa tải dưới user đó — bỏ copy; dùng `sudo -u www npm ci` |
| `chown …/www/.cache/puppeteer` No such file | `mkdir -p /www/.cache/puppeteer` trước |
| `NO_CHROME` / Failed to launch | `STAY_CRAWL_CHROME` + `sudo -u www test -x …` |
| Thiếu `.so` | apt libs mục 4 |
| Skeleton / GraphQL 403 | Proxy residential |
| Queue không chạy / job treo | Supervisor `queue:work`, `QUEUE_CONNECTION=database`, `--timeout=1200`; `php artisan queue:failed` |
| Worker không spawn (CLI cũ) | `shell_exec` / `nohup`; log `storage/logs/stay-crawl-work-*.log` |

---

## 11. Liên kết

| Tài liệu | Nội dung |
|----------|----------|
| [`13-deploy-aapanel-vps.md`](13-deploy-aapanel-vps.md) | Nginx, cron, Supervisor |
| [`16-accommodation-stays.md`](16-accommodation-stays.md) | Pipeline crawl / API |
| [`gcs-standard.md`](gcs-standard.md) | Media GCS |


## 6. Luồng Crawler Danh mục Bất đồng bộ & Stream Real-time

1. **Background Listing Worker**: "php artisan stay-crawl:list {jobId}" chạy tiến trình Chrome nền độc lập, hỗ trợ cào danh mục lớn lên tới 90 rounds mà không phụ thuộc timeout HTTP.
2. **Stream Sidecar**: Tiến độ cuộn trang và các URL mới tìm thấy được ghi nhận liên tục vào "storage/app/tmp/stay_list_stream_{jobId}.json" và lưu vào database tức thời.
3. **Queue Xử lý Song song**: Các item mới được dispatch ngay vào hàng đợi "php artisan queue:work" để enrich hình ảnh, tiện ích và phòng song song.
