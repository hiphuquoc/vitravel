# Cấu hình dữ liệu dự án (`project/seed.php`)

**Một file duy nhất** chứa toàn bộ nội dung demo dùng để seed DB + fallback UI (`SampleData` → `ProjectSeed`).

Khi copy mã nguồn cho dự án du lịch / đảo / khu vực khác: **chỉ cần sửa `project/seed.php`** (và tuỳ chọn `.env` / `config/company.php`), rồi chạy seed một lần.

## Clone dự án mới

```bash
composer install
cp .env.example .env   # DB, APP_URL, COMPANY_*
php artisan key:generate
# Sửa project/seed.php cho đúng khu vực / brand / tour
php artisan migrate --seed
npm run build
```

Pipeline seed (idempotent): taxonomy → cruise types → content → tour categories → home/reviews → **SeoHierarchySeeder cuối** (cây SEO hub→con + dọn redirect hỏng).

## Các nhóm key chính trong `seed.php`

| Key | Dùng cho |
|-----|----------|
| `meta.brand` / `meta.admin` / `meta.country_codes` | Brand, tài khoản admin seed, mã quốc gia |
| `content_tag_map` | Map nhãn tag bài viết → `content_type_tags.code` |
| `travel_styles`, `content_tags`, `review_platforms` | `TaxonomySeeder` |
| `cruise_types`, `home_slides` | Cruise + hero |
| `countries`, `country_translations`, `tour_categories` | Điểm đến & danh mục |
| `tours`, `cruises`, `articles`, `blog_categories`, `team`, … | Catalog |
| `about_page`, `home_sections`, `hero_pills`, `footer_*` | Marketing / layout |
| `travel_style_labels`, `duration_buckets`, `listing_faqs` | Filter + FAQ |

```php
use App\Support\ProjectSeed;

ProjectSeed::get('tours');
ProjectSeed::meta()['admin'];
```

## Lưu ý

- Production: dùng admin (`/he-thong`); file này chủ yếu bootstrap lần đầu.
- Thiếu URL tour sau khi thêm data: `php artisan db:seed --class=SeoHierarchySeeder`
- `ERR_TOO_MANY_REDIRECTS`: `php artisan seo:fix-redirects --purge-all`
