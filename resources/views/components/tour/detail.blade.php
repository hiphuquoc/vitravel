@props([
    'item',              // tour hoặc cruise
    'type' => 'tour',    // 'tour' | 'cruise'
    'related' => [],
    'breadcrumbs' => [],
])

@php
    $isCruise = $type === 'cruise';
    $sectionIds = ['tong-quan', 'diem-nhan', 'lich-trinh', 'bao-gom', 'danh-gia', 'faq'];
    if ($isCruise && !empty($item['cabinTypes'])) {
        array_splice($sectionIds, 2, 0, 'hang-cabin');
    }
    $tabs = [
        'tong-quan' => 'Tổng quan',
        'diem-nhan' => 'Điểm nhấn',
        ...($isCruise && !empty($item['cabinTypes']) ? ['hang-cabin' => 'Hạng cabin'] : []),
        'lich-trinh' => 'Lịch trình',
        'bao-gom' => 'Bao gồm',
        'danh-gia' => 'Đánh giá',
        'faq' => 'FAQ',
    ];
    $reviews = array_slice(view_data()->testimonials(), 0, 3);
@endphp

{{-- Gallery đầu trang: ảnh lớn + dải thumbnail --}}
<section class="container-site pt-6" aria-label="Thư viện ảnh">
    <div class="grid gap-3 lg:grid-cols-[2.2fr_1fr]">
        <x-ph class="h-72 w-full rounded-2xl sm:h-96" :label="'Ảnh chính: ' . $item['title']" icon-class="size-14" />
        <div class="grid grid-cols-4 gap-3 lg:grid-cols-2 lg:grid-rows-2">
            @for ($i = 1; $i <= 4; $i++)
                <div class="relative overflow-hidden rounded-xl">
                    <div class="relative aspect-[4/3] lg:aspect-auto lg:h-full lg:min-h-[110px]">
                        <x-ph class="absolute inset-0" icon-class="size-6" :label="null" />
                        @if ($i === 4 && $item['galleryCount'] > 4)
                            <button type="button" class="absolute inset-0 flex items-center justify-center bg-ink/50 text-sm font-bold text-white">
                                +{{ $item['galleryCount'] - 4 }} ảnh
                            </button>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Card tiêu đề + breadcrumb --}}
    <div class="card mt-5 px-6 py-5 sm:px-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl">{{ $item['title'] }}</h1>
                <x-layout.breadcrumb :items="$breadcrumbs" class="mt-3" />
            </div>
            <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />
        </div>
    </div>
</section>

<div x-data="scrollSpy(@js($sectionIds))">
    {{-- Tab anchor sticky --}}
    <nav class="sticky top-16 z-30 mt-6 border-y border-line bg-page-soft/95 backdrop-blur lg:top-[72px]" aria-label="Điều hướng trong trang">
        <div class="container-site flex gap-1 overflow-x-auto">
            @foreach ($tabs as $id => $label)
                <a href="#{{ $id }}"
                    class="border-b-2 px-4 py-3 text-sm font-semibold whitespace-nowrap transition"
                    :class="active === '{{ $id }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-ink-soft hover:text-ink'">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    <div class="container-site grid items-start gap-8 py-8 lg:grid-cols-[1fr_340px]">
        {{-- ── Cột nội dung chính ── --}}
        <div class="min-w-0 space-y-10">

            {{-- Tổng quan --}}
            <section id="tong-quan" class="scroll-mt-32" aria-label="Tổng quan">
                <div class="card p-6 sm:p-7">
                    <dl class="grid gap-x-8 gap-y-4 text-base sm:grid-cols-2">
                        <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="tag" class="size-4 text-primary-600" /> Mã {{ $isCruise ? 'du thuyền' : 'tour' }}:</dt><dd class="text-ink-soft">{{ $item['tourCode'] }}</dd></div>
                        <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="calendar" class="size-4 text-primary-600" /> Thời lượng:</dt><dd class="text-ink-soft">{{ $item['duration'] }}</dd></div>
                        <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="map-pin" class="size-4 text-primary-600" /> Khởi hành:</dt><dd class="text-ink-soft">{{ $item['start'] }}</dd></div>
                        <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="flag" class="size-4 text-primary-600" /> Kết thúc:</dt><dd class="text-ink-soft">{{ $item['end'] }}</dd></div>
                        @if ($isCruise)
                            <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="cruise" class="size-4 text-primary-600" /> Cảng đi:</dt><dd class="text-ink-soft">{{ $item['departurePort'] }}</dd></div>
                            <div class="flex gap-2.5"><dt class="flex items-center gap-1.5 font-semibold"><x-icon name="sparkles" class="size-4 text-primary-600" /> Hạng tàu:</dt><dd class="text-ink-soft">{{ $item['boatClass'] }}</dd></div>
                        @endif
                        <div class="flex gap-2.5 sm:col-span-2">
                            <dt class="flex shrink-0 items-center gap-1.5 font-semibold"><x-icon name="map-pin" class="size-4 text-primary-600" /> Điểm tham quan:</dt>
                            <dd class="text-ink-soft">{{ implode(' – ', $item['places']) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Bản đồ tuyến --}}
                <div class="card group mt-5 cursor-zoom-in overflow-hidden" role="button" tabindex="0" aria-label="Phóng to bản đồ hành trình">
                    <div class="relative">
                        <x-ph class="h-56 w-full sm:h-64" label="Bản đồ tuyến hành trình" icon="map-pin" icon-class="size-10" />
                        <span class="absolute right-4 bottom-4 rounded-full bg-white/95 px-4 py-1.5 text-xs font-semibold shadow transition group-hover:bg-primary-500 group-hover:text-white">
                            Nhấn để xem bản đồ lớn
                        </span>
                    </div>
                </div>
            </section>

            {{-- Điểm nhấn --}}
            <section id="diem-nhan" class="scroll-mt-32" aria-label="Điểm nhấn hành trình">
                <h2 class="section-title mb-4">Điểm nhấn hành trình</h2>
                <p class="prose-travel">{{ $item['highlightsIntro'] }}</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($item['highlights'] as $h)
                        <li class="flex gap-3 text-base leading-7">
                            <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-leaf-100 text-leaf-600">
                                <x-icon name="check" class="size-3" />
                            </span>
                            {{ $h }}
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Hạng cabin (chỉ du thuyền) --}}
            @if ($isCruise && !empty($item['cabinTypes']))
                <section id="hang-cabin" class="scroll-mt-32" aria-label="Hạng cabin">
                    <h2 class="section-title mb-5">Hạng cabin</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($item['cabinTypes'] as $cabin)
                            <article class="card overflow-hidden transition hover:shadow-(--shadow-card-hover)">
                                <div class="relative aspect-[3/2]">
                                    <x-ph class="absolute inset-0" :label="'Cabin ' . $cabin['name']" icon-class="size-8" />
                                </div>
                                <div class="p-4">
                                    <h3 class="text-sm font-bold">{{ $cabin['name'] }}</h3>
                                    <p class="mt-1 flex items-center gap-1.5 text-xs text-muted">
                                        <x-icon name="users" class="size-3.5" /> Tối đa {{ $cabin['capacity'] }} khách · {{ $cabin['note'] }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Lịch trình --}}
            <section id="lich-trinh" class="scroll-mt-32" aria-label="Lịch trình chi tiết"
                x-data="{ opened: [1], all: false,
                    toggle(d) { this.opened.includes(d) ? this.opened = this.opened.filter(x => x !== d) : this.opened.push(d) },
                    toggleAll() { this.all = !this.all; this.opened = this.all ? @js(array_column($item['itinerary'], 'day')) : [] } }">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 class="section-title">Lịch trình từng ngày</h2>
                    <button type="button" @click="toggleAll"
                        class="btn-ghost shrink-0" x-text="all ? 'Thu gọn tất cả' : 'Mở rộng tất cả'"></button>
                </div>
                <ol class="space-y-3">
                    @foreach ($item['itinerary'] as $day)
                        <li class="card overflow-hidden">
                            <h3>
                                <button type="button" @click="toggle({{ $day['day'] }})"
                                    class="flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-page-soft"
                                    :aria-expanded="opened.includes({{ $day['day'] }})">
                                    <span class="flex size-10 shrink-0 flex-col items-center justify-center rounded-xl bg-primary-500 leading-none text-white">
                                        <span class="text-[9px] font-semibold uppercase">Ngày</span>
                                        <span class="text-sm font-bold">{{ $day['day'] }}</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-base font-bold">{{ $day['title'] }}</span>
                                        <span class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
                                            <span class="inline-flex items-center gap-1"><x-icon name="clock" class="size-3.5" /> Bữa ăn: {{ $day['meals'] }}</span>
                                            <span class="inline-flex items-center gap-1.5" aria-label="Phương tiện di chuyển">
                                                @foreach ($day['transport'] as $t)
                                                    <x-icon :name="$t" class="size-4 text-leaf-600" />
                                                @endforeach
                                            </span>
                                        </span>
                                    </span>
                                    <x-icon name="chevron-down" class="size-4 shrink-0 transition"
                                        ::class="opened.includes({{ $day['day'] }}) && 'rotate-180 text-primary-600'" />
                                </button>
                            </h3>
                            <div x-show="opened.includes({{ $day['day'] }})" x-collapse x-cloak>
                                <div class="grid gap-4 border-t border-line px-5 py-4 sm:grid-cols-[1fr_180px]">
                                    <div>
                                        <p class="body-text">{{ $day['content'] }}</p>
                                        @if ($day['overnight'])
                                            <p class="mt-2.5 inline-flex items-center gap-1.5 rounded-full bg-page px-3 py-1 text-xs font-semibold">
                                                <x-icon name="map-pin" class="size-3.5 text-primary-600" /> Nghỉ đêm: {{ $day['overnight'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <x-ph class="h-28 rounded-xl sm:h-full" icon-class="size-6" :label="null" />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Bao gồm / Không bao gồm / Lưu ý --}}
            <section id="bao-gom" class="scroll-mt-32" aria-label="Giá bao gồm và không bao gồm">
                <h2 class="section-title mb-5">Giá đã bao gồm những gì?</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="card p-6">
                        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-leaf-700">
                            <x-icon name="check" class="size-4.5" /> Bao gồm
                        </h3>
                        <ul class="body-text space-y-2.5">
                            @foreach ($item['inclusions'] as $inc)
                                <li class="flex gap-2.5"><x-icon name="check" class="mt-1 size-3.5 shrink-0 text-leaf-600" /> {{ $inc }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card p-6">
                        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-primary-700">
                            <x-icon name="x-mark" class="size-4.5" /> Không bao gồm
                        </h3>
                        <ul class="body-text space-y-2.5">
                            @foreach ($item['exclusions'] as $exc)
                                <li class="flex gap-2.5"><x-icon name="x-mark" class="mt-1 size-3.5 shrink-0 text-primary-500" /> {{ $exc }}</li>
                            @endforeach
                        </ul>
                        @if (!empty($item['notes']))
                            <h3 class="mt-6 mb-3 text-base font-bold">Lưu ý</h3>
                            <ul class="space-y-2 text-base leading-7 text-muted">
                                @foreach ($item['notes'] as $note)
                                    <li class="flex gap-2">• {{ $note }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Đánh giá khách hàng (Q&A) --}}
            <section id="danh-gia" class="scroll-mt-32" aria-label="Đánh giá của khách hàng">
                <h2 class="section-title mb-5">Khách hàng nói gì về hành trình này</h2>
                <div class="space-y-4">
                    @foreach ($reviews as $r)
                        <article class="card p-6">
                            <div class="flex items-center gap-3">
                                <x-ph class="size-11 rounded-full" icon="user" icon-class="size-5" :label="null" />
                                <div>
                                    <p class="text-base font-bold">{{ $r['name'] }} <span class="font-normal text-muted">{{ $r['flag'] }}</span></p>
                                    <x-shared.rating :rating="$r['rating']" class="mt-0.5" />
                                </div>
                                <span class="ml-auto text-xs text-muted">{{ $r['trip'] }}</span>
                            </div>
                            <p class="body-text mt-3">{{ $r['quote'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- FAQ cuối trang --}}
            <div id="faq" class="scroll-mt-32">
                <x-shared.faq :faqs="$item['faqs']" title="Câu hỏi thường gặp về {{ $isCruise ? 'du thuyền' : 'tour' }} này" />
            </div>
        </div>

        {{-- ── Sidebar đặt tour sticky ── --}}
        <aside class="top-32 space-y-5 lg:sticky" aria-label="Đặt tour">
            <div class="card overflow-hidden">
                <div class="bg-primary-500 px-6 py-4 text-white">
                    <p class="kicker opacity-90">Giá trọn gói theo yêu cầu</p>
                    <p class="mt-1 font-display text-2xl font-bold">Nhận báo giá trong 24h</p>
                </div>
                <div class="p-6">
                    @if ($item['badge'])
                        <p class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-accent-50 px-3 py-1 text-xs font-bold text-accent-700">
                            <x-icon name="sparkles" class="size-3.5" /> {{ $item['badge'] }}
                        </p>
                    @endif
                    <x-shared.rating :rating="$item['rating']" :count="$item['reviewCount']" />
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('customize') }}" class="btn-primary w-full">
                            <x-icon name="sparkles" class="size-4" /> Yêu cầu báo giá
                        </a>
                        <a href="https://wa.me/84912345678" target="_blank" rel="noopener"
                            class="btn-whatsapp w-full">
                            <x-icon name="whatsapp" class="size-4.5" /> Chat WhatsApp
                        </a>
                    </div>
                    <ul class="mt-6 space-y-2.5 border-t border-line pt-5 text-sm leading-6 text-ink-soft">
                        <li class="flex gap-2"><x-icon name="expert" class="mt-0.5 size-4 shrink-0 text-leaf-600" /> Chuyên gia bản địa thiết kế riêng</li>
                        <li class="flex gap-2"><x-icon name="refund" class="mt-0.5 size-4 shrink-0 text-leaf-600" /> Cam kết hoàn tiền minh bạch</li>
                        <li class="flex gap-2"><x-icon name="value" class="mt-0.5 size-4 shrink-0 text-leaf-600" /> Giá trị vượt trội, không phí ẩn</li>
                        <li class="flex gap-2"><x-icon name="support" class="mt-0.5 size-4 shrink-0 text-leaf-600" /> Hỗ trợ 24/7 suốt hành trình</li>
                    </ul>
                    <p class="mt-5 flex items-center justify-center gap-2 rounded-xl bg-page px-4 py-2.5 text-[11px] font-semibold text-muted">
                        <x-icon name="shield" class="size-4" /> Được đề xuất trên TripAdvisor
                    </p>
                </div>
            </div>
        </aside>
    </div>
</div>

{{-- Tour/du thuyền tương tự --}}
@if (count($related))
    <section class="cv-auto container-site py-10" aria-label="Hành trình tương tự">
        <x-shared.section-heading title="Hành trình tương tự bạn có thể thích" />
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($related as $r)
                <x-tour.card-compact :item="$r"
                    :href="$isCruise
                        ? route('cruises.show', ['type' => $r['typeSlug'], 'slug' => $r['slug']])
                        : route('tours.show', ['country' => $r['countrySlug'], 'slug' => $r['slug']])" />
            @endforeach
        </div>
    </section>
@endif

<x-shared.testimonial-carousel />
<x-shared.review-platforms class="pt-0" />

{{-- JSON-LD sản phẩm du lịch --}}
@php
    $tripJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristTrip',
        'name' => $item['title'],
        'description' => $item['highlightsIntro'],
        'touristType' => array_values(array_intersect_key(view_data()->travelStyles(), array_flip($item['styles']))),
        'itinerary' => ['@type' => 'ItemList', 'numberOfItems' => count($item['itinerary'])],
        'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => $item['rating'], 'reviewCount' => $item['reviewCount']],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($tripJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
