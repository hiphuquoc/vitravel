@props(['current' => 1, 'total' => 3])

{{-- Phân trang demo (tĩnh) — thay bằng paginator thật khi nối dữ liệu --}}
<nav {{ $attributes->merge(['class' => 'flex items-center justify-center gap-1.5']) }} aria-label="Phân trang">
    <a href="#" class="flex size-9 items-center justify-center rounded-full border border-line bg-white text-muted transition hover:border-primary-300 hover:text-primary-600"
        aria-label="Trang trước">
        <x-icon name="chevron-left" class="size-4" />
    </a>
    @for ($i = 1; $i <= $total; $i++)
        <a href="#"
            class="{{ $i === $current
                ? 'bg-primary-500 text-white'
                : 'border border-line bg-white text-ink-soft hover:border-primary-300 hover:text-primary-600' }} flex size-9 items-center justify-center rounded-full text-sm font-semibold transition"
            @if ($i === $current) aria-current="page" @endif>{{ $i }}</a>
    @endfor
    <a href="#" class="flex size-9 items-center justify-center rounded-full border border-line bg-white text-muted transition hover:border-primary-300 hover:text-primary-600"
        aria-label="Trang sau">
        <x-icon name="chevron-right" class="size-4" />
    </a>
</nav>
