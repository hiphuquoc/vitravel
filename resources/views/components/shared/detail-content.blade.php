@props([
    'html' => '',
    'title' => 'Nội dung chi tiết',
    'id' => 'noi-dung',
])

@php
    $html = trim((string) $html);
@endphp

@if ($html !== '')
    <section id="{{ $id }}" class="detail-section" aria-label="{{ $title }}">
        <h2 class="detail-section__title">{{ $title }}</h2>
        <div class="detail-content">
            <div class="detail-content__body prose-travel prose-travel--itinerary">
                {!! $html !!}
            </div>
        </div>
    </section>
@endif
