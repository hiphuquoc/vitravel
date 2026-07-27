@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null, // chữ script accent phía trên tiêu đề (tuỳ chọn, dùng tiết chế)
    'align' => 'center', // center | left
])

<div {{ $attributes->merge(['class' => 'mb-9 ' . ($align === 'center' ? 'text-center' : '')]) }}>
    @if ($eyebrow)
        <span class="section-eyebrow" aria-hidden="true">{{ $eyebrow }}</span>
    @endif
    <h2 class="section-title text-balance">{{ $title }}</h2>
    @if ($subtitle)
        <p class="section-subtitle {{ $align === 'center' ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">{{ $subtitle }}</p>
    @endif
    <span class="mt-3.5 block h-1 w-14 rounded-full bg-primary-500 {{ $align === 'center' ? 'mx-auto' : '' }}"></span>
</div>
