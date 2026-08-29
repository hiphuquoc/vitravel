@props([
    'inclusions' => [],
    'exclusions' => [],
    'notes' => [],
    'sectionId' => 'bao-gom',
    'embedded' => false,
])

@php
    $blocks = [];
    if (count($inclusions)) {
        $blocks[] = [
            'key' => 'in',
            'title' => 'Bao gồm',
            'items' => $inclusions,
            'icon' => 'check',
            'markIcon' => 'check',
        ];
    }
    if (count($exclusions)) {
        $blocks[] = [
            'key' => 'out',
            'title' => 'Không bao gồm',
            'items' => $exclusions,
            'icon' => 'x-mark',
            'markIcon' => 'x-mark',
        ];
    }
    if (count($notes)) {
        $blocks[] = [
            'key' => 'note',
            'title' => 'Lưu ý',
            'items' => $notes,
            'icon' => 'flag',
            'markIcon' => null,
        ];
    }
@endphp

@foreach ($blocks as $index => $block)
    @php $blockId = (! $embedded && $index === 0) ? $sectionId : null; @endphp

    @if ($embedded)
        <div @class(['detail-cover-block', 'detail-cover-block--'.$block['key']])>
    @else
        <section
            @if ($blockId) id="{{ $blockId }}" @endif
            @class(['detail-section', 'detail-cover-block', 'detail-cover-block--'.$block['key']])
            aria-labelledby="detail-cover-title-{{ $block['key'] }}"
        >
    @endif
        <h2
            id="detail-cover-title-{{ $block['key'] }}"
            class="detail-section__title detail-cover-block__title"
        >
            <span class="detail-cover-block__title-mark" aria-hidden="true">
                <x-icon :name="$block['icon']" class="size-4" />
            </span>
            <span>{{ $block['title'] }}</span>
        </h2>

        <ul class="detail-cover-block__list">
            @foreach ($block['items'] as $item)
                <li class="detail-cover-block__row">
                    <span class="detail-cover-block__mark" aria-hidden="true">
                        @if ($block['markIcon'])
                            <x-icon :name="$block['markIcon']" class="size-3.5" />
                        @else
                            <span class="detail-cover-block__dot"></span>
                        @endif
                    </span>
                    <span class="detail-cover-block__text">{{ $item }}</span>
                </li>
            @endforeach
        </ul>
    @if ($embedded)
        </div>
    @else
        </section>
    @endif
@endforeach
