@php
    $offices = view_data()->offices();
    $columns = view_data()->footerColumns();
    $seoLinks = view_data()->footerSeoLinks();
    $contact = view_data()->companyContact();
@endphp

<footer>
    {{-- Tầng 1 — Contact strip nền be (teaser hỏi nhanh chồng lên) --}}
    <section class="footer-contact-strip cv-auto bg-strip" aria-label="Thông tin liên hệ">
        <div class="container-site section-band grid site-gap-lg lg:grid-cols-[1.2fr_1fr_auto]">
            <div>
                <div class="footer-contact__brand-row">
                    <span class="footer-contact__logo">
                        <x-icon name="compass" class="size-5.5" />
                    </span>
                    <span>
                        <span class="footer-contact__name">{{ $contact['name'] }}</span>
                        <span class="footer-contact__slogan">{{ $contact['slogan'] }}</span>
                    </span>
                </div>
                <ul class="footer-contact__channels">
                    <li class="footer-contact__channel">
                        <x-icon name="mail" class="size-4.5 shrink-0 text-primary-600" />
                        <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                    </li>
                    <li class="footer-contact__channel">
                        <x-icon name="phone" class="size-4.5 shrink-0 text-primary-600" />
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a>
                    </li>
                    <li class="footer-contact__channel">
                        <x-icon name="whatsapp" class="size-4.5 shrink-0 text-leaf-600" />
                        <span>WhatsApp: {{ $contact['whatsapp'] }}</span>
                    </li>
                </ul>
            </div>

            <div class="grid site-gap sm:grid-cols-3 lg:grid-cols-1">
                @foreach ($offices as $office)
                    <div class="footer-office">
                        <x-icon name="map-pin" class="mt-0.5 size-4.5 shrink-0 text-primary-600" />
                        <div>
                            <p class="footer-office__city">{{ $office['city'] }}</p>
                            <p class="footer-office__line">{{ $office['address'] }}</p>
                            <p class="footer-office__line">{{ $office['phone'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- QR WhatsApp --}}
            <div class="footer-contact__qr">
                <div class="footer-contact__qr-box">
                    <x-icon name="qr" class="size-16 text-ink/70" />
                </div>
                <p class="footer-contact__qr-copy body-text">Quét mã QR để trò chuyện WhatsApp với tư vấn viên</p>
            </div>
        </div>
    </section>

    {{-- Tầng 2 — Footer chính nền đen --}}
    <section class="cv-auto bg-footer text-stone-300">
        <div class="container-site section-band">
            <div class="grid site-gap-lg sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($columns as $col)
                    <nav aria-label="{{ $col['title'] }}">
                        <h3 class="footer-col__title">{{ $col['title'] }}</h3>
                        <ul class="footer-nav__list">
                            @foreach ($col['links'] as $link)
                                <li>
                                    <a href="{{ locale_route($link['route'][0], $link['route'][1] ?? []) }}"
                                        class="footer-nav__link">{{ $link['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>

            {{-- Hàng link SEO rời --}}
            <nav class="footer-seo" aria-label="Liên kết nhanh">
                <p class="footer-seo__inner">
                    @foreach ($seoLinks as $link)
                        <a href="{{ locale_route($link['route'][0], $link['route'][1] ?? []) }}">{{ $link['label'] }}</a>@if (!$loop->last)<span class="footer-seo__sep">|</span>@endif
                    @endforeach
                </p>
            </nav>

            <div class="footer-bottom">
                <p class="footer-copyright">{{ $contact['footer_copyright'] }}</p>
                <div class="footer-social">
                    @foreach ($contact['social'] as $social)
                        <a href="{{ $social['url'] }}" class="footer-social__link" aria-label="{{ $social['label'] }}"
                           target="_blank" rel="noopener noreferrer">
                            <x-icon :name="$social['icon']" class="size-4" />
                        </a>
                    @endforeach
                    @if (! empty($contact['show_dmca_badge']))
                        <span class="footer-badge">
                            <x-icon name="shield" class="size-3" /> DMCA
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>
</footer>
