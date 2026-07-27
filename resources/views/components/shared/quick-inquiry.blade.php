{{-- "Hỏi nhanh về tour" — form lead lặp lại cuối hầu hết các trang, ngay trước footer --}}
<section class="cv-auto py-14" aria-label="Hỏi nhanh về tour">
    <div class="container-site">
        <div class="card overflow-hidden">
            <div class="grid lg:grid-cols-2">
                {{-- Trái: lời dẫn + minh hoạ --}}
                <div class="flex flex-col justify-center bg-leaf-100/60 p-8 sm:p-10 lg:p-12">
                    <h2 class="section-title">Hỏi nhanh về tour</h2>
                    <p class="section-subtitle max-w-md">
                        Bạn chưa chắc nên đi đâu, đi mùa nào, ngân sách bao nhiêu? Để lại lời nhắn —
                        chuyên gia bản địa của chúng tôi sẽ phản hồi trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>, hoàn toàn miễn phí.
                    </p>
                    <div class="mt-8 flex items-end gap-5 sm:mt-10 sm:gap-6">
                        <x-ph class="h-32 w-40 rounded-xl" label="Minh hoạ: xe đạp chở hoa" icon="bike" icon-class="size-8" />
                        <x-ph class="h-24 w-32 rounded-xl" label="Minh hoạ: gánh hàng rong" icon="walking" icon-class="size-7" />
                    </div>
                </div>

                {{-- Phải: form --}}
                @if (session('success') === 'quick_inquiry')
                    <div class="flex h-full flex-col items-center justify-center p-8 py-10 text-center sm:p-10 lg:p-12">
                        <span class="flex size-14 items-center justify-center rounded-full bg-leaf-100 text-leaf-600">
                            <x-icon name="check" class="size-7" />
                        </span>
                        <h3 class="mt-4 font-display text-xl font-bold sm:text-2xl">Đã nhận được lời nhắn của bạn!</h3>
                        <p class="body-text mt-3 max-w-sm">Tư vấn viên sẽ liên hệ qua email hoặc điện thoại trong vòng 24 giờ làm việc. Hãy kiểm tra hộp thư nhé.</p>
                    </div>
                @else
                    <form action="{{ route('leads.quick-inquiry') }}" method="POST" class="p-8 sm:p-10 lg:p-12">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2 sm:gap-5">
                            <div>
                                <label for="qi-name" class="field-label field-required">Họ và tên</label>
                                <input id="qi-name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}" class="field-input" placeholder="Nguyễn Văn A">
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="qi-email" class="field-label field-required">Email</label>
                                <input id="qi-email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}" class="field-input" placeholder="ban@email.com">
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="qi-phone" class="field-label field-required">Số điện thoại</label>
                                <input id="qi-phone" name="phone" type="tel" required autocomplete="tel" value="{{ old('phone') }}" class="field-input" placeholder="+84 ...">
                                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="qi-address" class="field-label">Địa chỉ</label>
                                <input id="qi-address" name="address" type="text" autocomplete="street-address" value="{{ old('address') }}" class="field-input" placeholder="Thành phố bạn đang sống">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="qi-message" class="field-label">Bạn cần chúng tôi tư vấn điều gì?</label>
                                <textarea id="qi-message" name="message" rows="4" class="field-input resize-none"
                                    placeholder="VD: Gia đình 4 người, muốn đi miền Bắc 8 ngày vào tháng 10...">{{ old('message') }}</textarea>
                            </div>
                            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                            <div class="sm:col-span-2 pt-1">
                                <button type="submit" class="btn-primary w-full sm:w-auto">
                                    <x-icon name="mail" class="size-4" /> Gửi lời nhắn
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
