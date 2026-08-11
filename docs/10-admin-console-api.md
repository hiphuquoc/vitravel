# Admin Console API — v1

Base path: `/api/v1/admin`

Auth: `Authorization: Bearer <token>` (except login).  
Tokens lưu bảng **`admin_api_tokens`** (không dùng Sanctum morph / `personal_access_tokens`).

**Multi-project:** hầu hết endpoint nội dung cần context dự án — header `X-Project-Code: {code}` hoặc `X-Project-Id: {id}` (middleware `ResolveAdminProject`). Khi hệ thống chỉ có 1 project (hoặc khớp `PROJECT_DEFAULT_CODE`), API soft-resolve. Chi tiết: [`docs/11-multi-project-architecture.md`](11-multi-project-architecture.md).

Frontend: repo `admin.vitravel.dev` — host riêng (`admin.vitravel.dev` / `.net`), static `out/` (không còn `/he-thong` trên domain public).

## Auth

| Method | Path | Body | Notes |
|---|---|---|---|
| POST | `/auth/login` | `email`, `password`, `device_name?` | Returns `token` + `user` + `projects[]` (kèm `role`, `permissions[]`) |
| GET | `/auth/me` | — | Current user + `is_super_admin` + `projects[]`; nếu có `X-Project-Code` → thêm `permissions[]` |
| PUT | `/auth/me` | `name`, `email`, `current_password?`, `password?`, `password_confirmation?` | Cập nhật hồ sơ / đổi mật khẩu |
| POST | `/auth/logout` | — | Revokes current token |

## Users & RBAC

Chi tiết: [`docs/12-admin-users-rbac.md`](12-admin-users-rbac.md). Catalog: `config/admin_permissions.php`.

| Method | Path | Notes |
|---|---|---|
| GET | `/users/meta` | system/project roles, permission groups, projects gán được |
| GET | `/users` | `search`, `status`, `role`, `page` |
| POST | `/users` | Tạo + `projects[]` |
| GET/PUT/DELETE | `/users/{id}` | Chi tiết / cập nhật / xóa |

Mọi route trong group `ResolveAdminProject` chạy thêm `AuthorizeAdminPermission` (map HTTP → `module.action`).

## Projects

| Method | Path | Notes |
|---|---|---|
| GET | `/projects` | Danh sách project user được vào (admin = tất cả active) — **không** cần `X-Project-*` |
| GET | `/projects/{id}` | Chi tiết + domains |

Sau khi chọn project trên UI, gửi `X-Project-Code` cho mọi CRUD packages/media/…

| Method | Path | Notes |
|---|---|---|
| GET | `/packages?type=tour&search=&country_id=&status=&page=` | Paginated list |
| GET | `/packages/meta` | Countries, travel styles, statuses |
| GET | `/packages/{id}` | Detail |
| POST | `/packages` | Create (`type=tour`) |
| PUT | `/packages/{id}` | Update |
| DELETE | `/packages/{id}` | Soft delete |

## Tour categories (Danh mục Tour)

| Method | Path | Notes |
|---|---|---|
| GET | `/tour-categories` | Filters: `search`, `type`, `country_id` |
| GET | `/tour-categories/meta` | Countries + type_options |
| GET/POST/PUT/DELETE | `/tour-categories[/{id}]` | CRUD |

## Travel styles (Chủ đề Tour)

| Method | Path | Notes |
|---|---|---|
| GET | `/travel-styles` | Filters: `search`, `is_active` |
| GET/POST/PUT/DELETE | `/travel-styles[/{id}]` | CRUD |

## Envelope

```json
{ "success": true, "message": "OK", "data": {} }
{ "success": false, "error": { "code": "VALIDATION_ERROR", "message": "…", "details": {} } }
```

## AI

Chi tiết đầy đủ: [`docs/14-ai-system-prompts.md`](14-ai-system-prompts.md).

| Method | Path | Permission | Notes |
|---|---|---|---|
| GET | `/ai/status` | `ai.use` | Provider configured? |
| POST | `/ai/translate-page` | `ai.use` | Dịch JSON fields form |
| POST | `/ai/enrich-detail-program` | `ai.use` | Xây dựng chương trình tour/cruise/service |
| GET | `/ai/prompts` | `ai.manage` | Catalog prompt hệ thống |
| GET/PUT | `/ai/prompts/{key}` | `ai.manage` | Xem / cập nhật |
| POST | `/ai/prompts/sync` | `ai.manage` | Sync file seed → DB (`force?`) |
| GET | `/ai/usage` | `ai.manage` | Nhật ký gọi AI |