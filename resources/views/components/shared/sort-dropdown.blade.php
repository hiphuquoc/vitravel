@props([
    'options' => ['Phổ biến nhất', 'Mới nhất', 'Thời lượng ngắn → dài', 'Đánh giá cao nhất'],
    'name' => 'sort',
    'selected' => null,
])

@php
    $items = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $items[] = [
                'value' => (string) ($opt['value'] ?? ''),
                'label' => (string) ($opt['label'] ?? $opt['value'] ?? ''),
            ];
        } else {
            $items[] = [
                'value' => (string) $opt,
                'label' => (string) $opt,
            ];
        }
    }
    $initial = $selected ?? ($items[0]['value'] ?? '');
@endphp

<div {{ $attributes->class(['listing-sort']) }}>
    <x-form.select
        :name="$name"
        :options="$items"
        :selected="$initial"
        :searchable="false"
        class="listing-sort__select"
    />
</div>
