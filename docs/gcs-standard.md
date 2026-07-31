# Google Cloud Storage — chuẩn cấu hình đa dự án

Chuẩn lấy từ **ViTravel** (`vitravel.dev`). Mọi dự án (baseos, hitour, liendoan, superdong, …) nên đồng bộ cùng tên biến / đường dẫn key / cách build public URL.

## Biến `.env` bắt buộc (khi dùng GCS)

```env
MEDIA_DISK=gcs

GCS_PROJECT_ID=your-gcp-project-id
GCS_BUCKET=your-bucket
GCS_KEY_FILE=storage/app/gcs-credentials.json
GCS_PUBLIC_URL=https://storage.googleapis.com/your-bucket

# Tuỳ chọn
# GCS_PATH_PREFIX=
MEDIA_MAX_UPLOAD_KB=5120
MEDIA_MAX_VIDEO_UPLOAD_KB=1048576
MEDIA_UPLOAD_FOLDER={project}/images
```

| Biến | Ý nghĩa |
|------|---------|
| `MEDIA_DISK` | `public` (local) hoặc `gcs` |
| `GCS_PROJECT_ID` | GCP project id |
| `GCS_BUCKET` | Tên bucket |
| `GCS_KEY_FILE` | Path tới JSON service account — **relative `base_path`** hoặc absolute. Chuẩn: `storage/app/gcs-credentials.json` |
| `GCS_PUBLIC_URL` | Base URL public (không slash cuối). Dùng để ghép URL media |
| `GCS_PATH_PREFIX` | Prefix object trong bucket (hiếm khi cần) |

## File credentials

- Đặt tại: `{project}/storage/app/gcs-credentials.json`
- **Commit & push** file này để các môi trường / clone nhận cùng credentials qua git (`git pull`)
- `storage/app/.gitignore` phải có exception `!gcs-credentials.json` (vì thư mục `storage/app` mặc định ignore `*`)
- Root `.gitignore`: **không** liệt kê `/storage/app/gcs-credentials.json`
- Nếu root ignore cả `/storage` (một số dự án cũ): thêm un-ignore:
  ```
  !/storage/
  !/storage/app/
  !/storage/app/gcs-credentials.json
  ```
- **Không** hardcode `private_key` trong `config/filesystems.php`

## Wire trong Laravel (ViTravel)

| Config | Đọc |
|--------|-----|
| `config/filesystems.php` → disk `gcs` | `GCS_*` → `key_file_path`, `bucket`, `storage_api_uri` = `GCS_PUBLIC_URL` |
| `config/services.php` → `gcs` | Cùng `GCS_*`; `public_url` cho MediaService |
| `config/media.php` → `disk` | `MEDIA_DISK` |

`MediaService::urlForDiskPath('gcs', $path)`:

```text
rtrim(GCS_PUBLIC_URL, '/') + '/' + ltrim($path, '/')
```

Fallback nếu thiếu `GCS_PUBLIC_URL`: `https://storage.googleapis.com/{GCS_BUCKET}/{path}`

## Migrate từ cấu hình cũ

| Cũ (bỏ dần) | Mới |
|--------------|-----|
| `GOOGLE_CLOUD_PROJECT_ID` | `GCS_PROJECT_ID` |
| `GOOGLE_CLOUD_STORAGE_BUCKET` | `GCS_BUCKET` |
| `GOOGLE_CLOUD_KEY_FILE` / hardcoded `key_file` array | `GCS_KEY_FILE=storage/app/gcs-credentials.json` |
| `GOOGLE_CLOUD_STORAGE_API_URI` | `GCS_PUBLIC_URL` |
| `GCS_PUBLIC_BASE_URL` (superdong) | `GCS_PUBLIC_URL` |
| `GOOGLE_CLOUD_URL` (orphan) | `GCS_PUBLIC_URL` |

Config vẫn **fallback** đọc `GOOGLE_CLOUD_*` một thời gian để không gãy deploy cũ — nhưng `.env` mới chỉ ghi `GCS_*`.

## Checklist port sang dự án khác

1. Copy block `.env` GCS ở trên (đổi bucket / project id / folder prefix).
2. Đặt JSON vào `storage/app/gcs-credentials.json`.
3. `config/filesystems.php` disk `gcs` + `config/services.php` `gcs` theo ViTravel (không nhúng private key).
4. Media layer đọc `config('services.gcs.public_url')` khi build URL.
5. Xóa / comment biến `GOOGLE_CLOUD_*` và `key_file` hardcoded sau khi verify upload + URL public.

## Tham chiếu code

- `config/filesystems.php`, `config/services.php`, `config/media.php`
- `app/Services/MediaService.php`
- Cursor rule: `.cursor/rules/gcs-config.mdc`
