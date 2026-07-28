@php $usps = view_data()->usps(); @endphp

{{-- 4 cam kết dịch vụ — lặp lại ở Home, About Us --}}
<div {{ $attributes->merge(['class' => 'grid grid-cols-2 site-gap lg:grid-cols-4']) }}>
    @foreach ($usps as $usp)
        <div class="usp-item">
            <span class="usp-item__icon">
                <x-icon :name="$usp['icon']" />
            </span>
            <h3 class="item-title usp-item__title leading-snug">{{ $usp['title'] }}</h3>
            <p class="body-text max-w-[18rem]">{{ $usp['desc'] }}</p>
        </div>
    @endforeach
</div>
