@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null, // chữ script accent phía trên tiêu đề (tuỳ chọn, dùng tiết chế)
    'align' => 'center', // center | left
])

<div {{ $attributes->merge(['class' => 'section-heading' . ($align === 'center' ? ' section-heading--center' : '')]) }}>
    @if ($eyebrow)
        <span class="section-eyebrow" aria-hidden="true">{{ $eyebrow }}</span>
    @endif
    <h2 class="section-title text-balance">{{ $title }}</h2>
    @if ($subtitle)
        <p class="section-subtitle {{ $align === 'center' ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">{{ $subtitle }}</p>
    @endif
    <span class="section-heading__rule" aria-hidden="true"></span>
</div>
