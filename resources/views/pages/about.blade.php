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
    <section class="cv-auto py-14" aria-label="Sứ mệnh và tầm nhìn">
        <div class="container-site grid gap-6 lg:grid-cols-2">
            <div class="card grid overflow-hidden sm:grid-cols-[200px_1fr]">
                <x-ph class="min-h-44" label="Ảnh: biển gỗ khắc chữ Sứ mệnh" icon="compass" icon-class="size-9" />
                <div class="p-7">
                    <h2 class="font-display text-xl font-bold">Sứ mệnh của chúng tôi</h2>
                    <p class="body-text mt-3">
                        Mang đến những hành trình chân thật giúp du khách chạm vào đời sống, văn hoá và con người bản địa —
                        đồng thời tạo sinh kế bền vững cho cộng đồng tại mỗi điểm đến chúng tôi đi qua.
                    </p>
                </div>
            </div>
            <div class="card grid overflow-hidden sm:grid-cols-[200px_1fr]">
                <x-ph class="min-h-44" label="Ảnh: biển gỗ khắc chữ Tầm nhìn" icon="eye" icon-class="size-9" />
                <div class="p-7">
                    <h2 class="font-display text-xl font-bold">Tầm nhìn của chúng tôi</h2>
                    <p class="body-text mt-3">
                        Trở thành đại lý du lịch bản địa được tin cậy nhất Đông Nam Á — nơi mỗi du khách rời đi
                        với cảm giác "hài lòng hơn cả mong đợi" và một phần trái tim ở lại với điểm đến.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Sơ đồ vòng tròn 4 giá trị cốt lõi --}}
    <section class="cv-auto py-14" aria-label="Giá trị cốt lõi">
        <div class="container-site">
            <x-shared.section-heading title="Cam kết với giá trị cốt lõi" />
            <div class="grid items-center gap-10 lg:grid-cols-[1fr_auto_1fr]">
                {{-- 2 giá trị trái --}}
                <div class="order-2 space-y-8 lg:order-1 lg:text-right">
                    @foreach (array_slice($values, 0, 2) as $v)
                        <div>
                            <h3 class="flex items-center gap-2 text-base font-bold lg:flex-row-reverse">
                                <span class="flex size-7 items-center justify-center rounded-full bg-leaf-500 text-white"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text mt-1.5">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Vòng tròn trung tâm --}}
                <div class="order-1 mx-auto lg:order-2">
                    <div class="relative flex size-64 items-center justify-center sm:size-72">
                        <span class="absolute inset-0 rounded-full border-2 border-dashed border-primary-300" aria-hidden="true"></span>
                        <span class="absolute inset-6 rounded-full border border-line" aria-hidden="true"></span>
                        <div class="flex size-40 flex-col items-center justify-center rounded-full bg-primary-500 p-4 text-center text-white shadow-(--shadow-float) sm:size-44">
                            <x-icon name="sparkles" class="size-7" />
                            <p class="mt-2 text-sm leading-tight font-bold">Cam kết với giá trị cốt lõi</p>
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
                            <span class="absolute {{ $pos }} rounded-full bg-white px-3.5 py-1.5 text-xs font-bold shadow-(--shadow-card) ring-1 ring-line">
                                {{ $v['name'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- 2 giá trị phải --}}
                <div class="order-3 space-y-8">
                    @foreach (array_slice($values, 2, 2) as $v)
                        <div>
                            <h3 class="flex items-center gap-2 text-base font-bold">
                                <span class="flex size-7 items-center justify-center rounded-full bg-leaf-500 text-white"><x-icon name="check" class="size-4" /></span>
                                {{ $v['name'] }}
                            </h3>
                            <p class="body-text mt-1.5">{{ $v['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Chính sách bán hàng --}}
    <section class="cv-auto py-14" aria-label="Chính sách bán hàng">
        <div class="container-site">
            <div class="card grid overflow-hidden lg:grid-cols-[1fr_320px]">
                <div class="p-8 sm:p-10">
                    <h2 class="section-title">Chính sách bán hàng minh bạch</h2>
                    <p class="body-text mt-4">
                        Mọi báo giá của ViTravel đều liệt kê rõ từng hạng mục — không phụ phí ẩn, không "giá từ" gây hiểu lầm.
                        Trẻ em dưới 4 tuổi được miễn phí dịch vụ mặt đất; trẻ 4–10 tuổi giảm 25% giá tour khi ngủ chung giường với bố mẹ.
                        Chính sách huỷ/hoàn tiền được ghi rõ trong hợp đồng: miễn phí trước 30 ngày, minh bạch theo từng mốc thời gian.
                    </p>
                    <a href="{{ route('contact') }}" class="btn-ghost mt-5">Hỏi thêm về chính sách <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <x-ph class="min-h-52 lg:min-h-full" label="Ảnh: gia đình đi du lịch" icon="users" icon-class="size-10" />
            </div>
        </div>
    </section>

    {{-- Vì sao chọn chúng tôi --}}
    <section class="cv-auto py-14" aria-label="Vì sao chọn chúng tôi">
        <div class="container-site grid items-center gap-10 lg:grid-cols-[380px_1fr]">
            <x-ph class="mx-auto h-80 w-full max-w-sm rounded-3xl" label="Ảnh: điện thoại hiển thị website ViTravel" icon="photo" icon-class="size-12" />
            <div>
                <h2 class="section-title">Vì sao chọn ViTravel?</h2>
                <ol class="mt-6 space-y-5">
                    @foreach ($reasons as $r)
                        <li class="flex gap-4">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-500 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="text-base font-bold">{{ $r['title'] }}</h3>
                                <p class="body-text mt-1">{{ $r['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <a href="{{ route('customize') }}" class="btn-primary mt-7">
                    <x-icon name="route" class="size-5 shrink-0" />
                    Bắt đầu hành trình của bạn
                    <x-icon name="arrow-right" class="size-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- USP + trust dùng chung --}}
    <section class="container-site py-14" aria-label="Cam kết dịch vụ">
        <x-shared.usp-badges />
    </section>
    <x-shared.review-platforms class="pt-4" />
    <x-shared.testimonial-carousel class="pt-0" />

    {{-- Người đại diện tại nước ngoài --}}
    <section class="cv-auto py-14" aria-label="Người đại diện tại nước ngoài">
        <div class="container-site">
            <x-shared.section-heading title="Người đại diện của chúng tôi tại nước ngoài"
                subtitle="Bạn có thể trao đổi trực tiếp bằng ngôn ngữ của mình với đại diện ViTravel tại châu Âu và châu Úc." />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($referencePersons as $p)
                    <article class="card flex flex-col items-center p-7 text-center transition hover:shadow-(--shadow-card-hover)">
                        <x-ph class="size-24 rounded-full" icon="user" icon-class="size-10" :label="null" />
                        <h3 class="mt-4 font-bold">{{ $p['name'] }}</h3>
                        <p class="text-xs font-semibold text-muted">{{ $p['country'] }}</p>
                        <ul class="mt-4 space-y-2.5 text-base text-ink-soft">
                            <li class="flex items-center justify-center gap-2"><x-icon name="mail" class="size-4 text-primary-600" /> {{ $p['email'] }}</li>
                            <li class="flex items-center justify-center gap-2"><x-icon name="phone" class="size-4 text-primary-600" /> {{ $p['phone'] }}</li>
                            <li class="flex items-center justify-center gap-2"><x-icon name="skype" class="size-4 text-primary-600" /> {{ $p['skype'] }}</li>
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-shared.video-showcase :home-only="true" :limit="4" />
@endsection
