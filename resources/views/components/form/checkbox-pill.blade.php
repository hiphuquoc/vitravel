@props(['name', 'value', 'label', 'checked' => false])

{{-- Checkbox dạng pill (chọn quốc gia, hạng khách sạn...) --}}
<label class="cursor-pointer">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" class="peer sr-only" @checked($checked)>
    <span class="inline-flex items-center gap-1.5 rounded-[10px] border border-line bg-white px-4 py-2 text-base font-semibold text-ink-soft transition peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-600 hover:border-primary-300">
        {{ $label }}
    </span>
</label>
