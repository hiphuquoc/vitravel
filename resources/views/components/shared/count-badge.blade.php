@props([
    'count' => 0,
])

<span {{ $attributes->class(['count-badge']) }}>{{ $count }}</span>
