# Agent notes — ViTravel / multi-project travel CMS

## Multi-project runtime (critical)

Một codebase phục vụ nhiều domain/dữ liệu qua **`projects` + `project_id`**. Không clone deploy theo từng địa phương.

- **Hướng dẫn đọc lại:** [`docs/11-multi-project-architecture.md`](docs/11-multi-project-architecture.md) (seed, đổi dự án public/admin, domain local)
- Context: `App\Support\ProjectContext` · Trait: `BelongsToProject`
- Public: `ResolveProjectFromHost` — `?project=` / cookie `vt_project` (khi override) → Host → `PROJECT_DEFAULT_CODE` → first active
- Domain Cát Bà Hub: local **`hicatba.dev`** · prod **`hicatba.com`** (project `code` = `hicatba`)
- Domain phuquy.net: local **`phuquy.dev`** · prod **`phuquy.net`** (project `code` = `phuquy`)
- Domain Đảo Phú Quốc: local **`phuquocangiang.dev`** · prod **`phuquocangiang.com`** (project `code` = `phuquoc`)
- Domain culaocham.net: local **`culaocham.dev`** · prod **`culaocham.net`** (project `code` = `culaocham`)
- Admin API: `X-Project-Code` / `X-Project-Id` → `ResolveAdminProject`
- CLI: `php artisan project:seed hicatba --domain=hicatba.dev` · `project:seed phuquy --domain=phuquy.dev --domain=phuquy.net` · `project:seed culaocham --domain=culaocham.dev --domain=culaocham.net` · `project:ensure …` · `project:domain …`

## Seed & demo content (critical)

Bootstrap/demo content nằm trong **`project/seed_{name}.php`**, gắn vào row `projects`.

- **Không** dùng `PROJECT_SEED` / `COMPANY_*` trong `.env`
- `php artisan migrate:fresh --seed` → seed **tất cả** `seed_*.php`
- Một profile: `php artisan project:seed hicatba --domain=hicatba.dev --domain=hicatba.com`
- Phú Quý: `php artisan project:seed phuquy --domain=phuquy.dev --domain=phuquy.net`
- Phú Quốc: `php artisan project:seed phuquoc --domain=phuquocangiang.dev --domain=phuquocangiang.com --name="Đảo Phú Quốc"`
- Cù Lao Chàm: `php artisan project:seed culaocham --domain=culaocham.dev --domain=culaocham.net`
- Loader: `App\Support\ProjectSeed` (`useProfile` / `ProjectContext`)
- Schema: `project/README.md` · UI fallback: `App\Support\SampleData`
- Listing chrome (hub / country / chủ đề tour / cruise / service): `ListingChrome` + seed keys `subtitle` / `seo_body` trên `tour_categories` (xem README § Listing chrome)

**Do not** hardcode catalog/marketing data in seeders hoặc revive fat arrays in `SampleData`. Extend seed file và wire `ProjectSeed::get()`. Services + company nằm trong cùng file seed.

## Google Cloud Storage (critical)

Canonical env block: `GCS_PROJECT_ID`, `GCS_BUCKET`, `GCS_KEY_FILE`, `GCS_PUBLIC_URL` + `MEDIA_DISK=gcs`.
Key file: `storage/app/gcs-credentials.json` (never hardcode SA in config).
Full spec: `docs/gcs-standard.md`
Media path runtime: `projects/{code}/…` khi có `ProjectContext`.

## Company / site identity

Runtime brand + contact + social + footer từ **`company_profiles`** (scoped theo project), seed key `company`.

- Seed: `HomeFeaturedSeeder::seedCompanyIdentity()`
- Admin: **Cài đặt → Thông tin dự án** (`/settings/site`) — nhớ gửi `X-Project-Code`
- Reader: `CompanyProfile::contact()` / `view_data()->companyContact()`
- `config/company.php` = fallback **rỗng** khi DB trống (không còn `COMPANY_*` env)

## Admin Console (Next.js)

Headless admin: repo **`admin.vitravel.dev`** (Next.js).

- Production: host riêng `admin.vitravel.dev` / `.net` (static `out/`, không còn `/he-thong` trên domain public)
- API: `/api/v1/admin/*` trên Laravel — Bearer + **`X-Project-Code`** + CORS (`ADMIN_APP_URL`, `CORS_ALLOWED_ORIGINS`)
- Docs: `docs/10-admin-console-api.md`, `docs/11-multi-project-architecture.md`, `docs/13-deploy-aapanel-vps.md`
- Build: `cd admin.vitravel.dev && npm ci && npm run build`
- Legacy `/he-thong/*` trên Laravel redirect → `ADMIN_APP_URL` (`routes/admin.php`)
- **Deploy VPS aaPanel:** [`docs/13-deploy-aapanel-vps.md`](docs/13-deploy-aapanel-vps.md) (Nginx public + admin / `.env` / Supervisor)
- **Stay crawler trên VPS:** [`docs/17-stay-crawl-vps-aapanel.md`](docs/17-stay-crawl-vps-aapanel.md) — path `/www/wwwroot/vitravel.net`, Node + `scripts/stay-crawl` (`sudo -u www npm ci`), `STAY_CRAWL_CHROME` / `STAY_CRAWL_HEADLESS` (Chrome phải nằm chỗ user `www` đọc được, không dùng `/home/<ssh>/.cache` nếu FPM là `www`)
