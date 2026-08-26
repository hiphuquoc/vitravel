{{--
  Select form thương hiệu — UX theo adminCustomSelect (liendoan.dev), skin ViTravel.
  Usage:
    <x-form.select
        name="destination"
        label="Điểm đến"
        icon="map-pin"
        :options="[['value' => 'vn', 'label' => 'Việt Nam'], ...]"
        :selected="$selected"
        placeholder="Chọn điểm đến"
        :searchable="true"
    />
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'icon' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'searchable' => true,
    'multiple' => false,
])

@php
    $fieldId = $id ?: ('vt-sel-'.str_replace(['[', ']'], ['-', ''], $name).'-'.substr(md5($name.(string) $label), 0, 6));
    $items = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $items[] = [
                'value' => (string) ($opt['value'] ?? ''),
                'label' => (string) ($opt['label'] ?? $opt['value'] ?? ''),
            ];
        } else {
            $items[] = [
                'value' => (string) $key,
                'label' => (string) $opt,
            ];
        }
    }
    $placeholderText = $placeholder ?? '- Lựa chọn -';
    $initial = $selected;
    if ($initial === null) {
        $initial = $items[0]['value'] ?? '';
    }
    $initialLabel = $placeholderText;
    $matched = false;
    foreach ($items as $item) {
        if ((string) $item['value'] === (string) $initial) {
            $initialLabel = $item['label'];
            $matched = true;
            break;
        }
    }
    if (! $matched && $initial === '' && isset($items[0]) && $items[0]['value'] === '') {
        $initialLabel = $items[0]['label'];
    }
@endphp

<div
    {{ $attributes->class(['vt-form-select', 'vt-form-select--required' => $required]) }}
    x-data="formSelect(@js([
        'name' => (string) $name,
        'value' => (string) $initial,
        'label' => (string) $initialLabel,
        'options' => $items,
        'placeholder' => $placeholderText,
        'searchable' => (bool) $searchable,
        'required' => (bool) $required,
    ]))"
    @keydown.escape.window="open && close()"
    @click.outside="close()"
>
    @if (filled($label))
        <label class="vt-form-select__label field-label" :id="$id('label')" for="{{ $fieldId }}_trigger">
            <span>{{ $label }}</span>
            @if ($required)<span class="vt-form-select__req" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <div
        class="vt-select"
        :class="open && 'vt-select--open'"
        data-field-name="{{ $name }}"
    >
        <input
            type="hidden"
            name="{{ $name }}"
            :value="value"
            @if ($required) required @endif
            x-ref="hidden"
        >

        <div
            @class([
                'vt-select__display',
                'vt-select__display--has-icon' => filled($icon),
            ])
            @click="onDisplayClick($event)"
            role="combobox"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-controls="$id('listbox')"
            @if (filled($label)) :aria-labelledby="$id('label')" @endif
        >
            @if (filled($icon))
                <span class="vt-select__leading" aria-hidden="true">
                    <x-icon :name="$icon" class="size-4.5" />
                </span>
            @endif

            <div class="vt-select__value-wrap">
                <div
                    class="vt-select__selected"
                    x-show="!open || !searchable"
                >
                    <span
                        class="vt-select__selected-text"
                        :class="!hasValue && 'vt-select__selected-text--placeholder'"
                        x-text="displayLabel"
                    ></span>
                </div>

                <input
                    type="text"
                    class="vt-select__search"
                    id="{{ $fieldId }}_search"
                    x-ref="search"
                    x-show="open && searchable"
                    x-cloak
                    x-model="query"
                    @input="onSearch()"
                    @keydown.arrow-down.prevent="move(1)"
                    @keydown.arrow-up.prevent="move(-1)"
                    @keydown.enter.prevent="chooseHighlighted()"
                    @click.stop
                    :placeholder="placeholder"
                    autocomplete="off"
                    aria-autocomplete="list"
                    :aria-controls="$id('listbox')"
                >
            </div>

            <button
                type="button"
                id="{{ $fieldId }}_trigger"
                class="vt-select__dropdown"
                tabindex="-1"
                aria-label="Mở danh sách"
                @click.stop="toggle()"
            >
                <x-icon name="chevron-down" class="size-4" />
            </button>
        </div>

        <div
            class="vt-select__options"
            role="listbox"
            :id="$id('listbox')"
            :aria-activedescendant="highlight >= 0 ? $id('opt-'+highlight) : null"
        >
            <template x-for="(opt, idx) in filtered" :key="String(opt.value) + '-' + idx">
                <div
                    class="vt-select__option"
                    role="option"
                    :id="$id('opt-'+idx)"
                    :class="{
                        'vt-select__option--selected': String(value) === String(opt.value),
                        'vt-select__option--highlight': highlight === idx,
                    }"
                    :aria-selected="String(value) === String(opt.value)"
                    @click.stop="select(opt)"
                    @mouseenter="highlight = idx"
                >
                    <span class="vt-select__option-label" x-text="opt.label"></span>
                    <span class="vt-select__option-check" aria-hidden="true" x-show="String(value) === String(opt.value)">
                        <x-icon name="check" class="vt-select__option-check-icon" />
                    </span>
                </div>
            </template>
            <div class="vt-select__empty" x-show="filtered.length === 0" x-cloak>
                Không có kết quả
            </div>
        </div>
    </div>
</div>
