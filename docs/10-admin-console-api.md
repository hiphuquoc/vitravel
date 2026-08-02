# Admin Console API — v1

Base path: `/api/v1/admin`

Auth: `Authorization: Bearer <token>` (except login).  
Tokens lưu bảng **`admin_api_tokens`** (không dùng Sanctum morph / `personal_access_tokens`).

Frontend: `admin/` — live HMR xem `admin/README.md`.

## Auth

| Method | Path | Body | Notes |
|---|---|---|---|
| POST | `/auth/login` | `email`, `password`, `device_name?` | Returns `token` + `user` |
| GET | `/auth/me` | — | Current admin |
| POST | `/auth/logout` | — | Revokes current token |

## Packages (Gói Tour)

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
