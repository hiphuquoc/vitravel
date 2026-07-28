@php
    $offices = view_data()->offices();
    $columns = view_data()->footerColumns();
    $seoLinks = view_data()->footerSeoLinks();
    $contact = view_data()->companyContact();
@endphp

<footer>
    {{-- Tầng 1 — Contact strip nền be (teaser hỏi nhanh chồng lên) --}}
    <section class="footer-contact-strip cv-auto bg-strip" aria-label="Thông tin liên hệ">
        <div class="container-site grid gap-8 py-12 lg:grid-cols-[1.2fr_1fr_auto] lg:gap-12 lg:py-14">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-full bg-leaf-500 text-white">
                        <x-icon name="compass" class="size-5.5" />
                    </span>
                    <span>
                        <span class="block font-display text-2xl font-bold leading-none sm:text-[1.75rem]">ViTravel</span>
                        <span class="mt-1 block text-sm font-medium text-ink-soft italic">{{ $contact['slogan'] }}</span>
                    </span>
                </div>
                <ul class="mt-6 space-y-3 text-base">
                    <li class="flex items-center gap-2.5">
                        <x-icon name="mail" class="size-4.5 shrink-0 text-primary-600" />
                        <a href="mailto:{{ $contact['email'] }}" class="font-medium hover:text-primary-600">{{ $contact['email'] }}</a>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <x-icon name="phone" class="size-4.5 shrink-0 text-primary-600" />
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="font-medium hover:text-primary-600">{{ $contact['phone'] }}</a>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <x-icon name="whatsapp" class="size-4.5 shrink-0 text-leaf-600" />
                        <span class="font-medium">WhatsApp: {{ $contact['whatsapp'] }}</span>
                    </li>
                </ul>
            </div>

            <div class="grid gap-5 sm:grid-cols-3 lg:grid-cols-1 lg:gap-5">
                @foreach ($offices as $office)
                    <div class="flex gap-2.5 text-base leading-7">
                        <x-icon name="map-pin" class="mt-0.5 size-4.5 shrink-0 text-primary-600" />
                        <div>
                            <p class="font-semibold text-ink">{{ $office['city'] }}</p>
                            <p class="text-ink-soft">{{ $office['address'] }}</p>
                            <p class="text-ink-soft">{{ $office['phone'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- QR WhatsApp --}}
            <div class="flex items-center gap-4 lg:flex-col lg:items-center lg:text-center">
                <div class="flex size-28 items-center justify-center rounded-xl border-2 border-ink/15 bg-white">
                    <x-icon name="qr" class="size-16 text-ink/70" />
                </div>
                <p class="body-text max-w-40 font-medium">Quét mã QR để trò chuyện WhatsApp với tư vấn viên</p>
            </div>
        </div>
    </section>

    {{-- Tầng 2 — Footer chính nền đen --}}
    <section class="cv-auto bg-footer text-stone-300">
        <div class="container-site py-12 lg:py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
                @foreach ($columns as $col)
                    <nav aria-label="{{ $col['title'] }}">
                        <h3 class="footer-col__title">{{ $col['title'] }}</h3>
                        <ul class="space-y-3">
                            @foreach ($col['links'] as $link)
                                <li>
                                    <a href="{{ route($link['route'][0], $link['route'][1] ?? []) }}"
                                        class="text-base leading-7 transition hover:text-primary-300">{{ $link['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>

            {{-- Hàng link SEO rời --}}
            <nav class="mt-10 border-t border-white/10 pt-7 text-center" aria-label="Liên kết nhanh">
                <p class="text-base leading-7 text-stone-400">
                    @foreach ($seoLinks as $link)
                        <a href="{{ route($link['route'][0], $link['route'][1] ?? []) }}"
                            class="transition hover:text-primary-300">{{ $link['label'] }}</a>@if (!$loop->last)<span class="mx-2.5 text-stone-600">|</span>@endif
                    @endforeach
                </p>
            </nav>

            <div class="mt-8 flex flex-col items-center justify-between gap-5 border-t border-white/10 pt-7 sm:flex-row">
                <p class="text-sm leading-6 text-stone-400">© {{ date('Y') }} ViTravel. Giấy phép lữ hành quốc tế số 01-2234/TCDL-GP-LHQT.</p>
                <div class="flex items-center gap-3">
                    @foreach (['facebook' => 'Facebook', 'play' => 'YouTube', 'photo' => 'Instagram', 'share' => 'TikTok'] as $icon => $label)
                        <a href="#" class="flex size-9 cursor-pointer items-center justify-center rounded-full bg-white/10 transition hover:bg-primary-500 hover:text-white"
                            aria-label="{{ $label }}">
                            <x-icon :name="$icon" class="size-4" />
                        </a>
                    @endforeach
                    <span class="ml-1 inline-flex items-center gap-1 rounded bg-white/10 px-2 py-1 text-[10px] font-bold tracking-wider">
                        <x-icon name="shield" class="size-3" /> DMCA
                    </span>
                </div>
            </div>
        </div>
    </section>
</footer>
