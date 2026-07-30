@extends('layouts.app')

@section('title', 'Thiết kế tour riêng — nhận lịch trình & báo giá trong 24 giờ | ViTravel')
@section('meta_description', 'Cho chúng tôi biết mong muốn của bạn — chuyên gia bản địa ViTravel sẽ thiết kế lịch trình riêng và gửi báo giá chi tiết trong vòng 24 giờ làm việc, hoàn toàn miễn phí.')
@section('hide-inquiry', '1')

@section('content')
    <x-layout.page-header title="Thiết kế tour riêng"
        subtitle="Kể cho chúng tôi nghe chuyến đi trong mơ của bạn — phần còn lại để chuyên gia lo"
        :breadcrumbs="[['label' => 'Thiết kế tour riêng']]" banner-label="Ảnh banner: hành trình theo yêu cầu" />

    @if (session('success') === 'custom_tour')
        <div class="container-site max-w-4xl section-band">
            <div class="card form-success">
                <span class="form-success__icon">
                    <x-icon name="check" class="size-8" />
                </span>
                <h2 class="form-success__title">Yêu cầu đã được gửi!</h2>
                <p class="body-text max-w-md">
                    Cảm ơn bạn đã tin tưởng ViTravel. Chuyên gia phụ trách tuyến của bạn sẽ nghiên cứu yêu cầu và gửi
                    lịch trình chi tiết kèm báo giá qua email trong vòng 24 giờ làm việc.
                </p>
                <a href="{{ locale_route('guide.index') }}" class="btn-outline site-mt">Đọc cẩm nang trong lúc chờ</a>
            </div>
        </div>
    @else
        <form action="{{ route('leads.custom-tour') }}" method="POST" class="container-site max-w-4xl customize-form section-band">
            @csrf
            <fieldset class="card form-section">
                <legend class="sr-only">Thông tin chuyến đi của bạn</legend>
                <h2 class="form-section__title">
                    <span class="form-section__index">1</span>
                    Thông tin chuyến đi của bạn
                </h2>

                <p class="field-label field-required">Số lượng khách</p>
                <div class="form-grid form-grid--3">
                    <x-form.stepper label="Người lớn" sub="Trên 10 tuổi" name="adults" :initial="old('adults', 2)" />
                    <x-form.stepper label="Trẻ em" sub="4 – 10 tuổi" name="children" :initial="old('children', 0)" />
                    <x-form.stepper label="Em bé" sub="0 – 3 tuổi" name="infants" :initial="old('infants', 0)" />
                </div>
                @error('adults')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="form-section__block form-grid form-grid--2">
                    <div>
                        <label for="cz-days" class="field-label field-required">Bạn có bao nhiêu ngày cho chuyến đi?</label>
                        <input id="cz-days" name="duration_text" type="text" required value="{{ old('duration_text') }}" class="field-input" placeholder="Ví dụ: 10 ngày">
                        @error('duration_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-arrival" class="field-label field-required">Dự kiến ngày đến</label>
                        <input id="cz-arrival" name="arrival_date" type="date" required value="{{ old('arrival_date') }}" class="field-input">
                        @error('arrival_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <p class="field-label field-required form-section__block">Bạn muốn ghé thăm quốc gia nào?</p>
                <div class="form-pills">
                    @foreach (['VIỆT NAM', 'THÁI LAN', 'CAMPUCHIA', 'LÀO', 'BALI (INDONESIA)'] as $c)
                        <x-form.checkbox-pill name="countries[]" :value="$c" :label="$c" :checked="in_array($c, old('countries', []))" />
                    @endforeach
                </div>
                @error('countries')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <p class="field-label field-required form-section__block">Bạn thích loại lưu trú nào?</p>
                <div class="form-pills">
                    @foreach (['Tiêu chuẩn (khách sạn 3*)', 'Cao cấp (khách sạn 4*)', 'Sang trọng (khách sạn 5*)', 'Nhờ tư vấn giúp tôi'] as $a)
                        <x-form.checkbox-pill name="accommodation[]" :value="$a" :label="$a" :checked="in_array($a, old('accommodation', []))" />
                    @endforeach
                </div>
                @error('accommodation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <p class="field-label form-section__block">Ngân sách dự kiến (chưa gồm vé máy bay quốc tế)</p>
                <div class="form-budget-row">
                    <div class="form-budget-row__amount">
                        <span class="form-budget-row__currency" aria-hidden="true">₫</span>
                        <input type="number" name="budget_amount" min="0" step="1000000" value="{{ old('budget_amount') }}" class="field-input form-budget-row__input" placeholder="30.000.000" aria-label="Số tiền ngân sách">
                    </div>
                    <x-form.select
                        name="budget_unit"
                        :options="[
                            ['value' => 'Mỗi người', 'label' => 'Mỗi người'],
                            ['value' => 'Cả nhóm', 'label' => 'Cả nhóm'],
                        ]"
                        :selected="old('budget_unit', 'Mỗi người')"
                        :searchable="false"
                        class="form-budget-row__unit"
                    />
                </div>
            </fieldset>

            <fieldset class="card form-section">
                <legend class="sr-only">Thông tin cá nhân của bạn</legend>
                <h2 class="form-section__title">
                    <span class="form-section__index">2</span>
                    Thông tin cá nhân của bạn
                </h2>

                <p class="field-label field-required">Danh xưng</p>
                <div class="form-radio-row">
                    @foreach (['Ông', 'Bà'] as $g)
                        <label class="flex cursor-pointer items-center gap-2 font-medium">
                            <input type="radio" name="gender" value="{{ $g }}" class="size-4 border-line text-primary-500 focus:ring-primary-400" @checked(old('gender', 'Ông') === $g)>
                            {{ $g }}
                        </label>
                    @endforeach
                </div>
                @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="form-section__block form-grid form-grid--2">
                    <div>
                        <label for="cz-firstname" class="field-label field-required">Tên</label>
                        <input id="cz-firstname" name="first_name" type="text" required autocomplete="given-name" value="{{ old('first_name') }}" class="field-input">
                        @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-lastname" class="field-label field-required">Họ</label>
                        <input id="cz-lastname" name="last_name" type="text" required autocomplete="family-name" value="{{ old('last_name') }}" class="field-input">
                        @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-email" class="field-label field-required">Email</label>
                        <input id="cz-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}" class="field-input">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-phone" class="field-label field-required">Số điện thoại</label>
                        <input id="cz-phone" name="phone" type="tel" required autocomplete="tel" value="{{ old('phone') }}" class="field-input">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-form.select
                            name="nationality"
                            id="cz-nationality"
                            label="Quốc tịch"
                            icon="globe"
                            placeholder="— Chọn quốc tịch —"
                            :required="true"
                            :options="collect(['Việt Nam', 'Pháp', 'Ý', 'Úc', 'Mỹ', 'Anh', 'Đức', 'Khác'])->map(fn ($n) => ['value' => $n, 'label' => $n])->all()"
                            :selected="old('nationality', '')"
                        />
                        @error('nationality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-city" class="field-label field-required">Thành phố</label>
                        <input id="cz-city" name="city" type="text" required value="{{ old('city') }}" class="field-input">
                        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="card form-section">
                <legend class="sr-only">Yêu cầu đặc biệt khác</legend>
                <h2 class="form-section__title">
                    <span class="form-section__index">3</span>
                    Yêu cầu đặc biệt khác
                </h2>
                <textarea name="additional_notes" rows="5" class="field-input resize-none"
                    placeholder="Chia sẻ thêm về những gì bạn mong muốn...">{{ old('additional_notes') }}</textarea>
            </fieldset>

            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="text-center">
                <p class="form-submit-note">
                    <x-icon name="clock" class="size-4 text-leaf-600" />
                    Một tư vấn viên sẽ liên hệ với bạn trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>. Hãy kiểm tra email nhé!
                </p>
                <button type="submit" class="btn-primary">
                    <x-icon name="sparkles" class="size-4" /> Gửi yêu cầu
                </button>
            </div>
        </form>
    @endif
@endsection
