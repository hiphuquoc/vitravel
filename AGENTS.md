# Agent notes — ViTravel / multi-project travel CMS

## Seed & demo content (critical)

All bootstrap/demo content goes through **`PROJECT_SEED`** → `project/seed_{name}.php`.

- Config: `config/project.php`, `.env` key `PROJECT_SEED`
- Schema: `project/README.md`
- Loader: `App\Support\ProjectSeed`
- UI fallback: `App\Support\SampleData` (thin; no large hardcoded catalogs)

**Do not** hardcode catalog/marketing data in seeders or revive fat arrays in `SampleData` when adding features. Extend the active seed file and wire seeders to `ProjectSeed::get()`. **Services catalogue:** data in `project/seed_services.php` (merged into `seed_vitravel.php`); seeder `ServiceCatalogSeeder`.

Cursor rule (always on): `.cursor/rules/project-seed.mdc`

## Google Cloud Storage (critical)

Canonical env block: `GCS_PROJECT_ID`, `GCS_BUCKET`, `GCS_KEY_FILE`, `GCS_PUBLIC_URL` + `MEDIA_DISK=gcs`. 
Key file: `storage/app/gcs-credentials.json` (never hardcode SA in config). 
Full spec: `docs/gcs-standard.md` · rule: `.cursor/rules/gcs-config.mdc`

## Company / site identity

Runtime brand + contact + social + footer comes from **`company_profiles`** (seed key `company` in `project/seed_company.php`), not from hardcoding in Blade.

- Seed: `HomeFeaturedSeeder::seedCompanyIdentity()`
- Admin: **Cài đặt → Thông tin dự án** (`/settings/site`)
- Reader: `CompanyProfile::contact()` / `view_data()->companyContact()`
- `config/company.php` = env fallback only when DB empty

## Admin Console (Next.js) — phase 1

Headless admin lives in **separate repo** `admin.vitravel.dev` (Next.js 15 + SCSS tokens + TanStack Query).

- Production: static export → Laravel `public/he-thong` → `/he-thong/`
- API: `/api/v1/admin/*` — Bearer tokens (`admin_api_tokens`)
- Docs: `docs/10-admin-console-api.md`
- Build: `cd admin.vitravel.dev && npm ci && npm run build` (syncs into this project's `public/he-thong`)
- Menu Tour: Gói Tour, Danh mục Tour, Chủ đề Tour (`TravelStyle`)
- Legacy Blade admin đã retire — SPA fallback / redirect trong `routes/admin.php`
