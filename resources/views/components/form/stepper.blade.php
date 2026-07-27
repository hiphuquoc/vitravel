@props([
    'label',
    'sub' => null,
    'name',
    'initial' => 0,
])

{{-- Stepper số lượng khách: [−] số [+] --}}
<div x-data="stepper({{ $initial }})" class="flex items-center justify-between gap-4 rounded-xl border border-line bg-white px-4 py-3">
    <span>
        <span class="block text-sm font-semibold">{{ $label }}</span>
        @if ($sub)
            <span class="block text-xs text-muted">{{ $sub }}</span>
        @endif
    </span>
    <span class="flex items-center gap-1">
        <button type="button" @click="dec"
            class="flex size-8 items-center justify-center rounded-full border border-line transition hover:border-primary-400 hover:text-primary-600"
            aria-label="Giảm {{ $label }}">
            <x-icon name="minus" class="size-3.5" />
        </button>
        <input type="number" name="{{ $name }}" x-model.number="value" min="0" readonly
            class="w-10 border-0 bg-transparent text-center text-sm font-bold focus:ring-0 focus:outline-none"
            aria-label="Số {{ $label }}">
        <button type="button" @click="inc"
            class="flex size-8 items-center justify-center rounded-full border border-line transition hover:border-primary-400 hover:text-primary-600"
            aria-label="Tăng {{ $label }}">
            <x-icon name="plus" class="size-3.5" />
        </button>
    </span>
</div>
