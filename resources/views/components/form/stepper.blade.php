@props([
    'label',
    'sub' => null,
    'name',
    'initial' => 0,
])

{{-- Stepper số lượng khách: [−] số [+] --}}
<div x-data="stepper({{ $initial }})" class="form-stepper">
    <span>
        <span class="form-stepper__label block">{{ $label }}</span>
        @if ($sub)
            <span class="form-stepper__sub block">{{ $sub }}</span>
        @endif
    </span>
    <span class="flex items-center gap-1">
        <button type="button" @click="dec"
            class="form-stepper__btn"
            aria-label="Giảm {{ $label }}">
            <x-icon name="minus" class="size-3.5" />
        </button>
        <input type="number" name="{{ $name }}" x-model.number="value" min="0" readonly
            class="form-stepper__value focus:ring-0 focus:outline-none"
            aria-label="Số {{ $label }}">
        <button type="button" @click="inc"
            class="form-stepper__btn"
            aria-label="Tăng {{ $label }}">
            <x-icon name="plus" class="size-3.5" />
        </button>
    </span>
</div>
