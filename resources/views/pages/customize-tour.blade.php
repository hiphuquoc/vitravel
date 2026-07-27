@extends('layouts.app')

@section('title', 'Thiết kế tour riêng — nhận lịch trình & báo giá trong 24 giờ | ViTravel')
@section('meta_description', 'Cho chúng tôi biết mong muốn của bạn — chuyên gia bản địa ViTravel sẽ thiết kế lịch trình riêng và gửi báo giá chi tiết trong vòng 24 giờ làm việc, hoàn toàn miễn phí.')
@section('hide-inquiry', '1')

@section('content')
    <x-layout.page-header title="Thiết kế tour riêng"
        subtitle="Kể cho chúng tôi nghe chuyến đi trong mơ của bạn — phần còn lại để chuyên gia lo"
        :breadcrumbs="[['label' => 'Thiết kế tour riêng']]" banner-label="Ảnh banner: hành trình theo yêu cầu" />

    @if (session('success') === 'custom_tour')
        <div class="container-site max-w-4xl py-12">
            <div class="card flex flex-col items-center p-14 text-center">
                <span class="flex size-16 items-center justify-center rounded-full bg-leaf-100 text-leaf-600">
                    <x-icon name="check" class="size-8" />
                </span>
                <h2 class="mt-5 font-display text-2xl font-bold">Yêu cầu đã được gửi!</h2>
                <p class="body-text mt-3 max-w-md">
                    Cảm ơn bạn đã tin tưởng ViTravel. Chuyên gia phụ trách tuyến của bạn sẽ nghiên cứu yêu cầu và gửi
                    lịch trình chi tiết kèm báo giá qua email trong vòng 24 giờ làm việc.
                </p>
                <a href="{{ route('guide.index') }}" class="btn-outline mt-6">Đọc cẩm nang trong lúc chờ</a>
            </div>
        </div>
    @else
        <form action="{{ route('leads.custom-tour') }}" method="POST" class="container-site max-w-4xl space-y-8 py-12">
            @csrf
            {{-- Khối 1: Thông tin chuyến đi --}}
            <fieldset class="card p-7 sm:p-8">
                <legend class="sr-only">Thông tin chuyến đi của bạn</legend>
                <h2 class="mb-6 flex items-center gap-3 font-display text-xl font-bold">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-500 text-sm text-white">1</span>
                    Thông tin chuyến đi của bạn
                </h2>

                <p class="field-label field-required">Số lượng khách</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-form.stepper label="Người lớn" sub="Trên 10 tuổi" name="adults" :initial="old('adults', 2)" />
                    <x-form.stepper label="Trẻ em" sub="4 – 10 tuổi" name="children" :initial="old('children', 0)" />
                    <x-form.stepper label="Em bé" sub="0 – 3 tuổi" name="infants" :initial="old('infants', 0)" />
                </div>
                @error('adults')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
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

                <p class="field-label field-required mt-6">Bạn muốn ghé thăm quốc gia nào?</p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach (['VIỆT NAM', 'THÁI LAN', 'CAMPUCHIA', 'LÀO', 'BALI (INDONESIA)'] as $c)
                        <x-form.checkbox-pill name="countries[]" :value="$c" :label="$c" :checked="in_array($c, old('countries', []))" />
                    @endforeach
                </div>
                @error('countries')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <p class="field-label field-required mt-6">Bạn thích loại lưu trú nào?</p>
                <div class="flex flex-wrap gap-2.5">
                    @foreach (['Tiêu chuẩn (khách sạn 3*)', 'Cao cấp (khách sạn 4*)', 'Sang trọng (khách sạn 5*)', 'Nhờ tư vấn giúp tôi'] as $a)
                        <x-form.checkbox-pill name="accommodation[]" :value="$a" :label="$a" :checked="in_array($a, old('accommodation', []))" />
                    @endforeach
                </div>
                @error('accommodation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <p class="field-label mt-6">Ngân sách dự kiến (chưa gồm vé máy bay quốc tế)</p>
                <div class="flex max-w-md gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-4 flex items-center text-sm font-bold text-muted">₫</span>
                        <input type="number" name="budget_amount" min="0" step="1000000" value="{{ old('budget_amount') }}" class="field-input pl-9" placeholder="30.000.000" aria-label="Số tiền ngân sách">
                    </div>
                    <select name="budget_unit" class="field-input w-40 appearance-none" aria-label="Đơn vị ngân sách">
                        <option value="Mỗi người" @selected(old('budget_unit') === 'Mỗi người')>Mỗi người</option>
                        <option value="Cả nhóm" @selected(old('budget_unit') === 'Cả nhóm')>Cả nhóm</option>
                    </select>
                </div>
            </fieldset>

            {{-- Khối 2: Thông tin cá nhân --}}
            <fieldset class="card p-7 sm:p-8">
                <legend class="sr-only">Thông tin cá nhân của bạn</legend>
                <h2 class="mb-6 flex items-center gap-3 font-display text-xl font-bold">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-500 text-sm text-white">2</span>
                    Thông tin cá nhân của bạn
                </h2>

                <p class="field-label field-required">Danh xưng</p>
                <div class="flex gap-5">
                    @foreach (['Ông', 'Bà'] as $g)
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                            <input type="radio" name="gender" value="{{ $g }}" class="size-4 border-line text-primary-500 focus:ring-primary-400" @checked(old('gender', 'Ông') === $g)>
                            {{ $g }}
                        </label>
                    @endforeach
                </div>
                @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
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
                        <label for="cz-nationality" class="field-label field-required">Quốc tịch</label>
                        <select id="cz-nationality" name="nationality" required class="field-input appearance-none">
                            <option value="">— Chọn quốc tịch —</option>
                            @foreach (['Việt Nam', 'Pháp', 'Ý', 'Úc', 'Mỹ', 'Anh', 'Đức', 'Khác'] as $n)
                                <option value="{{ $n }}" @selected(old('nationality') === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                        @error('nationality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cz-city" class="field-label field-required">Thành phố</label>
                        <input id="cz-city" name="city" type="text" required value="{{ old('city') }}" class="field-input">
                        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            {{-- Khối 3: Yêu cầu đặc biệt --}}
            <fieldset class="card p-7 sm:p-8">
                <legend class="sr-only">Yêu cầu đặc biệt khác</legend>
                <h2 class="mb-6 flex items-center gap-3 font-display text-xl font-bold">
                    <span class="flex size-8 items-center justify-center rounded-full bg-primary-500 text-sm text-white">3</span>
                    Yêu cầu đặc biệt khác
                </h2>
                <textarea name="additional_notes" rows="5" class="field-input resize-none"
                    placeholder="Chia sẻ thêm về những gì bạn mong muốn...">{{ old('additional_notes') }}</textarea>
            </fieldset>

            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div class="text-center">
                <p class="body-text mb-4 flex items-center justify-center gap-2">
                    <x-icon name="clock" class="size-4 text-leaf-600" />
                    Một tư vấn viên sẽ liên hệ với bạn trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>. Hãy kiểm tra email nhé!
                </p>
                <button type="submit" class="btn-primary px-12">
                    <x-icon name="sparkles" class="size-4" /> Gửi yêu cầu
                </button>
            </div>
        </form>
    @endif
@endsection
