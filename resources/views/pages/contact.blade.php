@extends('layouts.app')

@section('title', 'Liên hệ ViTravel — chúng tôi luôn sẵn sàng lắng nghe')
@section('meta_description', 'Liên hệ đội ngũ ViTravel qua email, điện thoại, WhatsApp hoặc gửi lời nhắn trực tiếp — phản hồi trong vòng 24 giờ làm việc.')
@section('hide-inquiry', '1')

@section('content')
    <div class="container-site pt-8 pb-14">
        <x-layout.breadcrumb :items="[['label' => 'Liên hệ']]" />

        <div class="mt-6 max-w-2xl">
            <h1 class="font-display text-4xl font-bold tracking-tight sm:text-5xl">ViTravel</h1>
            <p class="body-text mt-3">
                Bạn muốn giữ liên lạc với chúng tôi? Dưới đây là tất cả các cách để tìm thấy ViTravel —
                dù bạn cần tư vấn hành trình, hỗ trợ trong chuyến đi hay chỉ đơn giản muốn trò chuyện về Đông Nam Á.
            </p>
            <ul class="mt-5 space-y-2.5 text-base">
                <li class="flex items-center gap-2.5">
                    <x-icon name="mail" class="size-4.5 text-primary-600" />
                    <span><span class="font-semibold">Email:</span> <a href="mailto:hello@vitravel.example" class="text-primary-600 hover:underline">hello@vitravel.example</a></span>
                </li>
                <li class="flex items-center gap-2.5">
                    <x-icon name="phone" class="size-4.5 text-primary-600" />
                    <span><span class="font-semibold">Điện thoại & WhatsApp:</span> +84 24 3999 8888 · +84 912 345 678</span>
                </li>
            </ul>
        </div>

        <div class="mt-10 grid items-start gap-8 lg:grid-cols-[1fr_380px]">
            {{-- Form liên hệ --}}
            @if (session('success') === 'contact')
                <div class="card flex flex-col items-center p-7 py-12 text-center sm:p-8">
                    <span class="flex size-14 items-center justify-center rounded-full bg-leaf-100 text-leaf-600">
                        <x-icon name="check" class="size-7" />
                    </span>
                    <h3 class="mt-4 font-display text-xl font-bold">Lời nhắn đã được gửi!</h3>
                    <p class="body-text mt-2 max-w-sm">Chúng tôi sẽ phản hồi qua email trong vòng 24 giờ làm việc.</p>
                </div>
            @else
                <form action="{{ route('leads.contact') }}" method="POST" class="card p-7 sm:p-8">
                    @csrf
                    <h2 class="section-title mb-6">Gửi lời nhắn cho chúng tôi</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="ct-name" class="field-label field-required">Họ và tên</label>
                            <input id="ct-name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}" class="field-input">
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="ct-email" class="field-label field-required">Email</label>
                            <input id="ct-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}" class="field-input">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="ct-phone" class="field-label field-required">Số điện thoại</label>
                            <input id="ct-phone" name="phone" type="tel" required autocomplete="tel" value="{{ old('phone') }}" class="field-input">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="ct-address" class="field-label field-required">Địa chỉ</label>
                            <input id="ct-address" name="address" type="text" required autocomplete="street-address" value="{{ old('address') }}" class="field-input">
                            @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="ct-message" class="field-label field-required">Lời nhắn</label>
                            <textarea id="ct-message" name="message" rows="5" required class="field-input resize-none"
                                placeholder="Chúng tôi có thể giúp gì cho bạn?">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <div><button type="submit" class="btn-primary"><x-icon name="mail" class="size-4" /> Gửi lời nhắn</button></div>
                    </div>
                </form>
            @endif

            {{-- Văn phòng --}}
            <div class="space-y-5">
                @foreach ($offices as $office)
                    <article class="card overflow-hidden">
                        <x-ph class="h-36 w-full" :label="'Bản đồ văn phòng ' . $office['city']" icon="map-pin" icon-class="size-8" />
                        <div class="p-5">
                            <h3 class="font-bold">{{ $office['city'] }}</h3>
                            <p class="body-text mt-1.5 flex items-start gap-2">
                                <x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-primary-600" /> {{ $office['address'] }}
                            </p>
                            <p class="body-text mt-1 flex items-center gap-2">
                                <x-icon name="phone" class="size-4 shrink-0 text-primary-600" /> {{ $office['phone'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection
