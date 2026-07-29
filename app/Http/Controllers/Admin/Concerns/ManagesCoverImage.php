<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ManagesCoverImage
{
    protected function mediaService(): MediaService
    {
        return app(MediaService::class);
    }

    /** Giới hạn upload thực tế (KB) = min(config, PHP upload_max_filesize). */
    protected function effectiveUploadMaxKb(): int
    {
        $configKb = (int) config('media.max_upload_kb', 5120);
        $phpKb = $this->phpIniSizeToKb((string) ini_get('upload_max_filesize'));

        return max(100, min($configKb, $phpKb > 0 ? $phpKb : $configKb));
    }

    protected function phpIniSizeToKb(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => ((float) $value) / 1024,
        };
    }

    protected function coverImageRules(string $fileField = 'image', string $removeField = 'remove_image'): array
    {
        $maxKb = $this->effectiveUploadMaxKb();

        return [
            $fileField => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
            $removeField => 'nullable|boolean',
        ];
    }

    /**
     * Bắt lỗi PHP từ chối file trước khi Laravel validate (INI size…).
     */
    protected function assertUploadedFileOk(Request $request, string $fileField = 'image'): void
    {
        $file = $_FILES[$fileField] ?? null;
        if (! is_array($file)) {
            return;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE || $error === UPLOAD_ERR_OK) {
            return;
        }

        $maxLabel = ini_get('upload_max_filesize') ?: '?';
        $message = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Ảnh vượt quá giới hạn upload của máy chủ ({$maxLabel}). Vui lòng chọn ảnh nhỏ hơn.",
            UPLOAD_ERR_PARTIAL => 'Ảnh chỉ tải lên một phần. Vui lòng thử lại.',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm trên máy chủ.',
            UPLOAD_ERR_CANT_WRITE => 'Không ghi được file ảnh lên đĩa (kiểm tra quyền storage).',
            UPLOAD_ERR_EXTENSION => 'Một extension PHP đã chặn upload ảnh.',
            default => 'Upload ảnh thất bại (mã lỗi '.$error.').',
        };

        throw ValidationException::withMessages([$fileField => $message]);
    }

    protected function syncCoverAttachment(
        Model $model,
        Request $request,
        ?string $folder = null,
        string $fileField = 'image',
    ): void {
        $this->mediaService()->syncCoverAttachment(
            $model,
            $request,
            $fileField,
            'remove_image',
            $folder,
        );
    }

    protected function syncDirectCover(
        Model $model,
        string $column,
        Request $request,
        ?string $folder = null,
        string $fileField = 'image',
        string $removeField = 'remove_image',
    ): void {
        $this->mediaService()->syncDirectMediaColumn(
            $model,
            $column,
            $request,
            $fileField,
            $removeField,
            $folder,
        );
    }
}
