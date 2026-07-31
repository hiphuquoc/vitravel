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
