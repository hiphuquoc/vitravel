@php $usps = view_data()->usps(); @endphp

{{-- 4 cam kết — open layout; chip vuông + gradient/viền nhẹ --}}
<div {{ $attributes->merge(['class' => 'usp-strip']) }}>
    @foreach ($usps as $usp)
        <article class="usp-item">
            <span class="usp-item__icon" aria-hidden="true">
                <x-icon :name="$usp['icon']" class="usp-item__glyph !h-11 !w-11 !max-h-none !max-w-none" />
            </span>
            <div class="usp-item__body card-inner">
                <h3 class="item-title usp-item__title">{{ $usp['title'] }}</h3>
                <p class="body-text usp-item__desc">{{ $usp['desc'] }}</p>
            </div>
        </article>
    @endforeach
</div>
