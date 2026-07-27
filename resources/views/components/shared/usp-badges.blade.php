@php $usps = view_data()->usps(); @endphp

{{-- 4 cam kết dịch vụ — lặp lại ở Home, About Us --}}
<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-4 lg:gap-8']) }}>
    @foreach ($usps as $usp)
        <div class="flex flex-col items-center gap-3 text-center">
            <span class="flex size-[4.5rem] items-center justify-center rounded-full bg-primary-100 text-primary-600">
                <x-icon :name="$usp['icon']" class="size-9" />
            </span>
            <h3 class="item-title text-lg leading-snug sm:text-xl">{{ $usp['title'] }}</h3>
            <p class="body-text max-w-[18rem]">{{ $usp['desc'] }}</p>
        </div>
    @endforeach
</div>
