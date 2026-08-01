# ViTravel Admin Console

Headless **Next.js 15** admin — App Router, SCSS tokens, TanStack Query.  
Production: static export → `public/he-thong` tại **`/he-thong/`**.

## Chạy / cập nhật UI

### Live HMR (khuyến nghị khi design)

```bash
# Laravel .env
ADMIN_DEV_URL=http://localhost:3100
CORS_ALLOWED_ORIGINS=http://localhost:3100,http://127.0.0.1:3100
php artisan config:clear

# Next
cd admin
npm run dev   # → http://localhost:3100/he-thong/
```

- API (dev): `NEXT_PUBLIC_API_BASE=https://vitravel.dev/api/v1/admin` trong `admin/.env.local` (CORS, không phụ thuộc Next rewrite).
- Khi `ADMIN_DEV_URL` bật, `https://vitravel.dev/he-thong/*` redirect sang localhost.
- Đăng nhập: `admin@vitravel.dev` / `111111`

### Production static

```bash
# Comment/xoá ADMIN_DEV_URL trong .env
cd admin && npm run build   # → ../public/he-thong
# API same-origin: /api/v1/admin
```

## Phase 1 routes

| Area | Path |
|---|---|
| Đăng nhập | `/he-thong/login/` |
| Dashboard | `/he-thong/` |
| Gói Tour | `/he-thong/tours/packages/` |
| Danh mục | `/he-thong/tours/categories/` |
| Chủ đề | `/he-thong/tours/themes/` |

Blade admin cũ đã ngưng — mọi `/he-thong/*` đi console mới (hoặc redirect live).

## Design system (admin)

Nguồn: public `docs/04-design-system.md` + token trong `admin/src/styles/tokens/_tokens.scss`.

| Quy tắc | Chi tiết |
|---|---|
| Base | `html` 106.25%; body **`0.95rem`** / lh 1.75 |
| Font | Sans **Be Vietnam Pro**; display **Fraunces** |
| Sidebar nav | `0.925rem` + `letter-spacing: 0.02em` |
| Form section | Một card/section; **không** box lồng box |
| Form cluster | Gạch brand ngắn (`1.75rem`) + dashed separator giữa cụm |
| Field label | ~`0.95rem`, ink đậm |
| Page header | Band tint + mark brand + title/desc + action dock |
| Select | Giá trị đã chọn = base; multi-chip = sm; check = vòng primary |

## API

Xem `docs/10-admin-console-api.md`. Token: bảng `admin_api_tokens` (Bearer).

```bash
php artisan migrate --force
# hoặc php scripts/ensure-admin-tokens-table.php
php artisan admin:reset-password 111111
```
