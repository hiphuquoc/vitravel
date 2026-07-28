@props(['name', 'value', 'label', 'checked' => false])

{{-- Checkbox dạng pill (chọn quốc gia, hạng khách sạn...) --}}
<label class="cursor-pointer">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" class="peer sr-only" @checked($checked)>
    <span class="form-pill__label">
        {{ $label }}
    </span>
</label>
