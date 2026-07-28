@extends('layouts.app')

@section('title', 'Về chúng tôi — ViTravel, đại lý du lịch bản địa Đông Nam Á')
@section('meta_description', 'Câu chuyện, sứ mệnh và đội ngũ ViTravel — đại lý lữ hành quốc tế bản địa với hơn 10 năm thiết kế hành trình riêng tại Việt Nam và Đông Nam Á.')

@section('content')
    <x-layout.page-header title="Về chúng tôi"
        subtitle="Hành trình chân thật, được thiết kế bởi những người bản địa yêu nghề"
        :breadcrumbs="[['label' => 'Về chúng tôi']]" banner-label="Ảnh banner: đội ngũ ViTravel" />

    {{-- Giới thiệu công ty (dùng chung với Home) — ẩn CTA vì đã ở trang Về chúng tôi --}}
    <x-shared.company-intro :show-cta="false" />

    {{-- Đội ngũ (dùng chung) --}}
    <x-shared.team-grid :team="$team" class="pt-0" />

    {{-- Sứ mệnh / Tầm nhìn — 2 block biển gỗ --}}
    <section class="cv-auto section-band" aria-label="Sứ mệnh và tầm nhìn">
        <div class="container-site grid site-gap lg:grid-cols-2">
            <div class="card signpost-card">
                <x-ph class="signpost-card__media" label="Ảnh: biển gỗ khắc chữ Sứ mệnh" icon="compass" icon-class="size-9" />
                <div class="signpost-card__body">
                    <h2 class="signpost-card__title">Sứ mệnh của chúng tôi</h2>
                    <p class="body-text">
                        Mang đến những hành trình chân thật giúp du khách chạm vào đời sống, văn hoá và con người bản địa —
                        đồng thời tạo sinh kế bền vững cho cộng đồng tại mỗi điểm đến chúng tôi đi qua.
                    </p>
                </div>
            </div>
            <div class="card signpost-card">
                <x-ph class="signpost-card__media" label="Ảnh: biển gỗ khắc chữ Tầm nhìn" icon="eye" icon-class="size-9" />
                <div class="signpost-card__body">
                    <h2 class="signpost-card__title">Tầm nhìn của chúng tôi</h2>
                    <p class="body-text">
                        Trở thành đại lý du lịch bản địa được tin cậy nhất Đông Nam Á — nơi mỗi du khách rời đi
                        với cảm giác "hài lòng hơn cả mong đợi" và một phần trái tim ở lại với điểm đến.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Sơ đồ vòng tròn 4 giá trị cốt lõi --}}
    <section class="cv-auto section-band" aria-label="Giá trị cốt lõi">
        <div class="container-site">
            <x-shared.section-heading title="Cam kết với giá trị cốt lõi" />
            <div class="values-diagram">
                {{-- 2 giá trị trái --}}
                <div class="values-diagram__side values-diagram__side--left">
                    @foreach (array_slice($values, 0, 2) as $v)
                        <div>
                            <h3 class="values-diagram__item-title">
                                <span class="values-diagram__item-icon" aria-hidden="true"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text values-diagram__item-desc">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Vòng tròn trung tâm --}}
                <div class="values-diagram__hub-wrap">
                    <div class="values-diagram__hub">
                        <span class="values-diagram__hub-ring" aria-hidden="true"></span>
                        <span class="values-diagram__hub-ring values-diagram__hub-ring--inner" aria-hidden="true"></span>
                        <div class="values-diagram__hub-core">
                            <x-icon name="sparkles" class="size-7" />
                            <p class="values-diagram__hub-label">Cam kết với giá trị cốt lõi</p>
                        </div>
                        @foreach ($values as $v)
                            @php
                                $pos = [
                                    0 => 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2',
                                    1 => 'top-1/2 left-0 -translate-x-1/2 -translate-y-1/2',
                                    2 => 'top-1/2 right-0 translate-x-1/2 -translate-y-1/2',
                                    3 => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2',
                                ][$loop->index];
                            @endphp
                            <span class="values-diagram__orbit-label {{ $pos }}">{{ $v['name'] }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- 2 giá trị phải --}}
                <div class="values-diagram__side">
                    @foreach (array_slice($values, 2, 2) as $v)
                        <div>
                            <h3 class="values-diagram__item-title">
                                <span class="values-diagram__item-icon" aria-hidden="true"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text values-diagram__item-desc">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Chính sách bán hàng --}}
    <section class="cv-auto section-band" aria-label="Chính sách bán hàng">
        <div class="container-site">
            <div class="card grid overflow-hidden lg:grid-cols-[1fr_minmax(0,20rem)]">
                <div class="site-pad">
                    <h2 class="section-title">Chính sách bán hàng minh bạch</h2>
                    <p class="body-text about-policy__lead">
                        Mọi báo giá của ViTravel đều liệt kê rõ từng hạng mục — không phụ phí ẩn, không "giá từ" gây hiểu lầm.
                        Trẻ em dưới 4 tuổi được miễn phí dịch vụ mặt đất; trẻ 4–10 tuổi giảm 25% giá tour khi ngủ chung giường với bố mẹ.
                        Chính sách huỷ/hoàn tiền được ghi rõ trong hợp đồng: miễn phí trước 30 ngày, minh bạch theo từng mốc thời gian.
                    </p>
                    <a href="{{ route('contact') }}" class="btn-ghost about-policy__cta">Hỏi thêm về chính sách <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <x-ph class="min-h-52 lg:min-h-full" label="Ảnh: gia đình đi du lịch" icon="users" icon-class="size-10" />
            </div>
        </div>
    </section>

    {{-- Vì sao chọn chúng tôi --}}
    <section class="cv-auto section-band" aria-label="Vì sao chọn chúng tôi">
        <div class="container-site grid items-center site-gap-lg lg:grid-cols-[minmax(0,24rem)_1fr]">
            <x-ph class="about-mockup" label="Ảnh: điện thoại hiển thị website ViTravel" icon="photo" icon-class="size-12" />
            <div>
                <h2 class="section-title">Vì sao chọn ViTravel?</h2>
                <ol class="reason-list">
                    @foreach ($reasons as $r)
                        <li class="reason-list__item">
                            <span class="reason-list__index">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="reason-list__title">{{ $r['title'] }}</h3>
                                <p class="body-text reason-list__desc">{{ $r['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <a href="{{ route('customize') }}" class="btn-primary reason-list__cta">
                    <x-icon name="route" class="size-5 shrink-0" />
                    Bắt đầu hành trình của bạn
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- USP + trust dùng chung --}}
    <section class="container-site section-band" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>
    <x-shared.review-platforms class="pt-0" />
    <x-shared.testimonial-carousel class="pt-0" />

    {{-- Người đại diện tại nước ngoài --}}
    <section class="cv-auto section-band" aria-label="Người đại diện tại nước ngoài">
        <div class="container-site">
            <x-shared.section-heading title="Người đại diện của chúng tôi tại nước ngoài"
                subtitle="Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện ViTravel tại châu Âu và châu Úc." />
            <div class="grid site-gap sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($referencePersons as $p)
                    <article class="card ref-person-card">
                        <x-ph class="ref-person-card__avatar" icon="user" icon-class="size-10" :label="null" />
                        <h3 class="ref-person-card__name">{{ $p['name'] }}</h3>
                        <p class="ref-person-card__country">{{ $p['country'] }}</p>
                        <ul class="ref-person-card__contacts">
                            <li class="ref-person-card__contact"><x-icon name="mail" class="size-4 text-primary-600" /> {{ $p['email'] }}</li>
                            <li class="ref-person-card__contact"><x-icon name="phone" class="size-4 text-primary-600" /> {{ $p['phone'] }}</li>
                            <li class="ref-person-card__contact"><x-icon name="skype" class="size-4 text-primary-600" /> {{ $p['skype'] }}</li>
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-shared.video-showcase :home-only="true" :limit="4" />
@endsection
