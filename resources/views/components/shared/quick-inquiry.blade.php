{{-- Hỏi nhanh — teaser thu gọn, form letter mở trong modal --}}
@php
    $companyBrand = site_brand();
    $qi = view_data()->quickInquiry();
    $qiTitle = $qi['title'] ?? 'Gửi lời nhắn cho chúng tôi';
    $qiBody = $qi['body'] ?? $qi['subtitle'] ?? 'Bạn chưa chắc nên đi đâu, đi mùa nào, ngân sách bao nhiêu? Để lại lời nhắn — chuyên gia bản địa của chúng tôi sẽ phản hồi trong vòng <strong class="font-semibold text-ink">24 giờ làm việc</strong>, hoàn toàn miễn phí.';
    $qiErrors = $errors ?? session('errors');
    $qiHasErrors = $qiErrors ? $qiErrors->hasAny(['name', 'email', 'phone', 'address', 'message']) : false;
    $qiSuccess = session('success') === 'quick_inquiry';
@endphp
@unless ($qi['hidden'] ?? false)
<section
    class="qi-band cv-auto"
    aria-label="{{ $qiTitle }}"
    x-data="quickInquiry({ openOnLoad: @js($qiHasErrors) })"
    @keydown.escape.window="closeModal()"
>
    <div class="container-site">
        @if ($qiSuccess)
            <div class="qi-teaser qi-teaser--success" role="status">
                <span class="qi-teaser__accent" aria-hidden="true"></span>
                <div class="qi-teaser__copy">
                    <p class="qi-teaser__eyebrow">Đã gửi thành công</p>
                    <h2 class="qi-teaser__title">Cảm ơn bạn đã viết thư!</h2>
                    <p class="qi-teaser__lead">Tư vấn viên {{ $companyBrand }} sẽ liên hệ qua email hoặc điện thoại trong vòng 24 giờ làm việc.</p>
                </div>
            </div>
        @else
            <button type="button" class="qi-teaser" @click="openModal()" aria-haspopup="dialog">
                <span class="qi-teaser__accent" aria-hidden="true"></span>
                <span class="qi-teaser__action">
                    <span class="btn-primary shrink-0">
                        <x-icon name="mail" class="size-4" />
                        Viết lời nhắn
                    </span>
                </span>
                <div class="qi-teaser__copy">
                    <p class="qi-teaser__eyebrow">Một lời nhắn gửi chúng tôi</p>
                    <h2 class="qi-teaser__title">{{ $qiTitle }}</h2>
                    <p class="qi-teaser__lead">{!! $qiBody !!}</p>
                </div>
            </button>
        @endif
    </div>

    {{-- Modal box vừa nội dung — nền đen mờ xung quanh --}}
    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            class="qi-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="qi-modal-title"
            @keydown.escape.window="closeModal()"
        >
            <div
                class="qi-modal__backdrop"
                x-show="open"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeModal()"
            ></div>

            <div
                class="qi-modal__panel"
                x-show="open"
                x-transition:enter="transition ease-out duration-280"
                x-transition:enter-start="opacity-0 scale-[0.97] translate-y-3"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-[0.98] translate-y-2"
                @click.stop
            >
                <button type="button" class="qi-modal__close" @click="closeModal()" aria-label="Đóng">
                    <x-icon name="close" class="size-5" />
                </button>

                <article class="vt-letter vt-letter--modal">
                            <header class="vt-letter__head">
                                <div class="vt-letter__mast">
                                    <span class="vt-letter__seal" aria-hidden="true">
                                        <x-icon name="compass" class="size-5" />
                                    </span>
                                    <span class="vt-letter__wordmark">{{ $companyBrand }}</span>
                                </div>
                                <p class="vt-letter__eyebrow">Một lời nhắn gửi chúng tôi</p>
                                <h2 id="qi-modal-title" class="vt-letter__title">{{ $qiTitle }}</h2>
                                <p class="vt-letter__lead">{!! $qiBody !!}</p>
                            </header>

                            <form action="{{ route('leads.quick-inquiry') }}" method="POST" class="vt-letter__form">
                                @csrf
                                <p class="vt-letter__salutation">Kính gửi đội ngũ {{ $companyBrand }},</p>

                                <div class="vt-letter__grid">
                                    <div class="vt-letter__field">
                                        <label for="qi-name" class="vt-letter__label">Tôi là <span class="text-primary-600">*</span></label>
                                        <input id="qi-name" name="name" type="text" required autocomplete="name" x-ref="firstInput"
                                            value="{{ old('name') }}" class="vt-letter__line" placeholder="Họ và tên của bạn">
                                        @if(($errors ?? null)?->has('name')) @php $message = $errors->first('name'); @endphp<p class="vt-letter__error">{{ $message }}</p>@endif
                                    </div>
                                    <div class="vt-letter__field">
                                        <label for="qi-email" class="vt-letter__label">Email <span class="text-primary-600">*</span></label>
                                        <input id="qi-email" name="email" type="email" required autocomplete="email"
                                            value="{{ old('email') }}" class="vt-letter__line" placeholder="ban@email.com">
                                        @if(($errors ?? null)?->has('email')) @php $message = $errors->first('email'); @endphp<p class="vt-letter__error">{{ $message }}</p>@endif
                                    </div>
                                    <div class="vt-letter__field">
                                        <label for="qi-phone" class="vt-letter__label">Liên hệ qua <span class="text-primary-600">*</span></label>
                                        <input id="qi-phone" name="phone" type="tel" required autocomplete="tel"
                                            value="{{ old('phone') }}" class="vt-letter__line" placeholder="+84 ...">
                                        @if(($errors ?? null)?->has('phone')) @php $message = $errors->first('phone'); @endphp<p class="vt-letter__error">{{ $message }}</p>@endif
                                    </div>
                                    <div class="vt-letter__field">
                                        <label for="qi-address" class="vt-letter__label">Đang ở</label>
                                        <input id="qi-address" name="address" type="text" autocomplete="street-address"
                                            value="{{ old('address') }}" class="vt-letter__line" placeholder="Thành phố, quốc gia">
                                    </div>
                                </div>

                                <div class="vt-letter__field vt-letter__field--wide">
                                    <label for="qi-message" class="vt-letter__label">Tôi muốn hỏi</label>
                                    <textarea id="qi-message" name="message" rows="3" class="vt-letter__area resize-none"
                                        placeholder="Chia sẻ ý tưởng hành trình, mùa đi, số người, ngân sách dự kiến…">{{ old('message') }}</textarea>
                                </div>

                                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                                <div class="vt-letter__sign">
                                    <button type="submit" class="btn-primary">
                                        <x-icon name="mail" class="size-4" />
                                        Gửi lời nhắn
                                    </button>
                                </div>
                            </form>
                        </article>
            </div>
        </div>
    </template>
</section>
@endunless
