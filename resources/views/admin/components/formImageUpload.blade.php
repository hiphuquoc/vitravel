{{--
    Component: Admin Form Image Upload (liendoan pattern — drag & drop + preview)
    Usage:
    @include('admin.components.formImageUpload', [
        'name' => 'image',
        'label' => 'Ảnh đại diện',
        'currentImage' => $url,   // or currentUrl
        'removeName' => 'remove_image',
        'aspectRatio' => '800/533',
        'required' => false,
        'tooltip' => '...',
        'hint' => 'JPG, PNG, WebP — tối đa 5MB.',
    ])
--}}
@php
    $fieldId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($name ?? 'image')).'_'.uniqid();
    $previewId = $fieldId.'_preview';
    $uploadAreaId = $fieldId.'_area';
    $aspectRatio = $aspectRatio ?? '800/533';
    $currentImage = $currentImage ?? $currentUrl ?? null;
    $hasCurrentImage = filled($currentImage);
    $removeName = $removeName ?? 'remove_image';
    $maxKb = (int) config('media.max_upload_kb', 5120);
@endphp

<div class="adminFormImageUpload">
    <label class="adminFormImageUpload_label" for="{{ $fieldId }}">
        @if (! empty($tooltip))
            <span class="adminFormImageUpload_tooltip" data-tooltip="{{ $tooltip }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </span>
        @endif
        <span>{{ $label ?? 'Ảnh đại diện' }}</span>
        @if (! empty($required))
            <span class="adminFormImageUpload_required">*</span>
        @endif
    </label>

    <div class="adminFormImageUpload_area {{ $hasCurrentImage ? 'adminFormImageUpload_area--hasImage' : '' }}" id="{{ $uploadAreaId }}">
        <input
            type="file"
            class="adminFormImageUpload_input"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            accept="image/jpeg,image/png,image/webp,image/gif"
            data-max-kb="{{ $maxKb }}"
            data-remove-name="{{ $removeName }}"
            @if (! empty($required) && ! $hasCurrentImage) required @endif
            onchange="handleImageUpload(this, '{{ $previewId }}', '{{ $uploadAreaId }}')"
        />

        @if ($hasCurrentImage)
            <div class="adminFormImageUpload_current" id="{{ $previewId }}_current">
                <div class="adminFormImageUpload_current_image" style="aspect-ratio: {{ $aspectRatio }};">
                    <img src="{{ $currentImage }}" alt="Ảnh hiện tại" loading="lazy" />
                    <div class="adminFormImageUpload_current_overlay">
                        <button type="button" class="adminFormImageUpload_current_action adminFormImageUpload_current_action--change"
                            onclick="showImageUploadInput('{{ $fieldId }}', '{{ $previewId }}', '{{ $uploadAreaId }}')" title="Thay đổi ảnh">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            <span>Thay đổi</span>
                        </button>
                        <button type="button" class="adminFormImageUpload_current_action adminFormImageUpload_current_action--remove"
                            onclick="removeCurrentImage('{{ $fieldId }}', '{{ $previewId }}', '{{ $uploadAreaId }}')" title="Xóa ảnh">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                            <span>Xóa</span>
                        </button>
                    </div>
                </div>
                @if (! empty($imageInfo))
                    <div class="adminFormImageUpload_info">
                        <span>{{ $imageInfo['extension'] ?? '' }}</span>
                        <span>{{ ($imageInfo['width'] ?? '').' × '.($imageInfo['height'] ?? '') }} px</span>
                        <span>{{ $imageInfo['size'] ?? '' }} KB</span>
                    </div>
                @endif
            </div>
        @endif

        <div class="adminFormImageUpload_dropzone" id="{{ $previewId }}_dropzone" style="{{ $hasCurrentImage ? 'display: none;' : '' }}">
            <div class="adminFormImageUpload_dropzone_content">
                <svg class="adminFormImageUpload_dropzone_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <p class="adminFormImageUpload_dropzone_text">
                    <span class="adminFormImageUpload_dropzone_text_primary">Kéo thả ảnh vào đây</span>
                    <span class="adminFormImageUpload_dropzone_text_secondary">hoặc click để chọn</span>
                </p>
            </div>
        </div>

        <div class="adminFormImageUpload_preview" id="{{ $previewId }}" style="aspect-ratio: {{ $aspectRatio }}; display: none;">
            <img src="" alt="Preview" />
            <button type="button" class="adminFormImageUpload_preview_remove"
                onclick="removeImagePreview('{{ $previewId }}', '{{ $uploadAreaId }}', '{{ $fieldId }}')" title="Xóa ảnh">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    @if (! empty($hint))
        <p class="adminFormField_hint" style="margin-top:0.5rem;">{{ $hint }}</p>
    @endif
</div>

@once
@push('scriptCustom')
<script>
function handleImageUpload(input, previewId, uploadAreaId) {
    const preview = document.getElementById(previewId);
    const uploadArea = document.getElementById(uploadAreaId);
    const dropzone = document.getElementById(previewId + '_dropzone');
    const currentImage = document.getElementById(previewId + '_current');

    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    if (!file.type.startsWith('image/')) {
        alert('Vui lòng chọn file ảnh (JPG, PNG, WebP, GIF).');
        input.value = '';
        return;
    }

    const maxKb = parseInt(input.dataset.maxKb || '5120', 10);
    if (file.size > maxKb * 1024) {
        alert('Ảnh vượt quá ' + maxKb + 'KB. Vui lòng chọn ảnh nhỏ hơn.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        if (dropzone) dropzone.style.display = 'none';
        if (currentImage) currentImage.style.display = 'none';
        preview.querySelector('img').src = e.target.result;
        preview.style.display = 'block';
        uploadArea.classList.add('adminFormImageUpload_area--hasImage');

        const form = input.closest('form');
        const removeName = input.dataset.removeName || 'remove_image';
        if (form) {
            form.querySelectorAll('input[type="hidden"][name="' + removeName + '"]').forEach(el => el.remove());
        }
    };
    reader.readAsDataURL(file);
}

function showImageUploadInput(inputId, previewId, uploadAreaId) {
    const input = document.getElementById(inputId);
    const dropzone = document.getElementById(previewId + '_dropzone');
    const currentImage = document.getElementById(previewId + '_current');
    const uploadArea = document.getElementById(uploadAreaId);

    if (currentImage) currentImage.style.display = 'none';
    if (dropzone) dropzone.style.display = 'block';
    uploadArea.classList.remove('adminFormImageUpload_area--hasImage');
    setTimeout(() => input.click(), 80);
}

function removeCurrentImage(inputId, previewId, uploadAreaId) {
    const input = document.getElementById(inputId);
    const dropzone = document.getElementById(previewId + '_dropzone');
    const currentImage = document.getElementById(previewId + '_current');
    const uploadArea = document.getElementById(uploadAreaId);
    const removeName = input.dataset.removeName || 'remove_image';

    if (currentImage) currentImage.style.display = 'none';
    input.value = '';
    input.removeAttribute('required');
    if (dropzone) dropzone.style.display = 'block';
    uploadArea.classList.remove('adminFormImageUpload_area--hasImage');

    const form = input.closest('form');
    if (form) {
        form.querySelectorAll('input[type="hidden"][name="' + removeName + '"]').forEach(el => el.remove());
        const removeInput = document.createElement('input');
        removeInput.type = 'hidden';
        removeInput.name = removeName;
        removeInput.value = '1';
        form.appendChild(removeInput);
    }
}

function removeImagePreview(previewId, uploadAreaId, inputId) {
    const preview = document.getElementById(previewId);
    const uploadArea = document.getElementById(uploadAreaId);
    const input = document.getElementById(inputId);
    const dropzone = document.getElementById(previewId + '_dropzone');
    const currentImage = document.getElementById(previewId + '_current');

    preview.querySelector('img').src = '';
    preview.style.display = 'none';
    uploadArea.classList.remove('adminFormImageUpload_area--hasImage');
    input.value = '';
    if (dropzone) dropzone.style.display = 'block';

    if (currentImage) {
        currentImage.style.display = 'block';
        uploadArea.classList.add('adminFormImageUpload_area--hasImage');
        if (dropzone) dropzone.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.adminFormImageUpload_area').forEach(function (uploadArea) {
        if (uploadArea.dataset.dndBound) return;
        uploadArea.dataset.dndBound = '1';

        const input = uploadArea.querySelector('input[type="file"]');
        const dropzone = uploadArea.querySelector('.adminFormImageUpload_dropzone');
        if (!input || !dropzone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
            uploadArea.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            uploadArea.addEventListener(eventName, function () {
                if (dropzone.style.display !== 'none') {
                    uploadArea.classList.add('adminFormImageUpload_area--dragover');
                }
            }, false);
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            uploadArea.addEventListener(eventName, function () {
                uploadArea.classList.remove('adminFormImageUpload_area--dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', function (e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, false);

        dropzone.addEventListener('click', function () {
            input.click();
        });
    });
});
</script>
@endpush
@endonce
