@props([
    'label' => null,      // nhãn mô tả ảnh (tuỳ chọn)
    'icon' => 'photo',    // icon giữa khung
    'iconClass' => 'size-10',
])

{{-- Ảnh placeholder xám — thay bằng ảnh thật sau --}}
<div {{ $attributes->merge(['class' => 'img-ph']) }} role="img"
    aria-label="{{ $label ?? 'Ảnh minh hoạ (đang cập nhật)' }}">
    <div class="flex flex-col items-center gap-1.5 p-4 text-center">
        <x-icon :name="$icon" class="{{ $iconClass }} opacity-60" />
        @if ($label)
            <span class="max-w-40 text-[11px] leading-tight font-medium opacity-70">{{ $label }}</span>
        @endif
    </div>
</div>
