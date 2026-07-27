{{--
   Region Switcher — Language (Hitour UX: deferred-apply).

   Props:
   - $variant: 'desktop' | 'mobile' (mặc định 'desktop')
--}}
@props(['variant' => 'desktop'])
@php
    $isMobile = $variant === 'mobile';

    $__currentLocale = current_locale();
    try {
        $__activeLangs = \App\Models\Language::active();
    } catch (\Throwable $e) {
        $__activeLangs = collect();
    }

    $__configFlags = collect(config('language', []))->mapWithKeys(function ($lang, $key) {
        $code = $lang['key'] ?? $key;

        return [$code => $lang['flag'] ?? null];
    });

    if ($__activeLangs->isEmpty()) {
        $__activeLangs = collect(config('language', []))->map(fn ($lang, $key) => (object) [
            'code' => $lang['key'] ?? $key,
            'name' => $lang['name'] ?? strtoupper($key),
            'name_native' => $lang['name_by_language'] ?? ($lang['name'] ?? strtoupper($key)),
            'flag' => $lang['flag'] ?? null,
        ]);
    } else {
        // DB thiếu flag → fallback config (Hitour: /images/flags/{code}.svg)
        $__activeLangs = $__activeLangs->map(function ($lang) use ($__configFlags) {
            $code = $lang->code ?? '';
            if (empty($lang->flag)) {
                $lang->flag = $__configFlags[$code]
                    ?? (file_exists(public_path("images/flags/{$code}.svg"))
                        ? "/images/flags/{$code}.svg"
                        : null);
            }

            return $lang;
        });
    }

    $__languageUxMap = [
        'vi' => ['native' => 'Tiếng Việt', 'english' => 'Vietnamese', 'note' => 'Phù hợp cho khách nội địa'],
        'en' => ['native' => 'English', 'english' => 'English', 'note' => 'Recommended for international guests'],
        'ko' => ['native' => '한국어', 'english' => 'Korean', 'note' => 'Dành cho khách Hàn Quốc'],
        'ja' => ['native' => '日本語', 'english' => 'Japanese', 'note' => 'Dành cho khách Nhật Bản'],
        'zh' => ['native' => '中文', 'english' => 'Chinese', 'note' => 'Dành cho khách Trung Quốc'],
        'fr' => ['native' => 'Français', 'english' => 'French', 'note' => 'Dành cho khách Pháp ngữ'],
        'de' => ['native' => 'Deutsch', 'english' => 'German', 'note' => 'Dành cho khách Đức'],
        'ru' => ['native' => 'Русский', 'english' => 'Russian', 'note' => 'Dành cho khách Nga'],
        'th' => ['native' => 'ไทย', 'english' => 'Thai', 'note' => 'Dành cho khách Thái Lan'],
    ];

    $__currentLang = $__activeLangs->firstWhere('code', $__currentLocale) ?? $__activeLangs->first();
    $__currentLangCode = strtolower($__currentLang->code ?? $__currentLocale ?? 'vi');
    $__isEmojiFlag = is_string($__currentLang->flag ?? null)
        && ! str_starts_with((string) $__currentLang->flag, 'http')
        && ! str_starts_with((string) $__currentLang->flag, '/');

    $__ui = [
        'choose' => $__currentLocale === 'en' ? 'Choose language' : 'Chọn ngôn ngữ',
        'language' => $__currentLocale === 'en' ? 'Language' : 'Ngôn ngữ',
        'display' => $__currentLocale === 'en' ? 'Display content' : 'Hiển thị nội dung',
        'cancel' => $__currentLocale === 'en' ? 'Cancel' : 'Hủy',
        'apply' => $__currentLocale === 'en' ? 'Apply' : 'Áp dụng',
        'applying' => $__currentLocale === 'en' ? 'Applying…' : 'Đang áp dụng…',
        'close' => $__currentLocale === 'en' ? 'Close' : 'Đóng',
        'title' => $__currentLocale === 'en' ? 'Language' : 'Ngôn ngữ',
    ];

    $__rootClass = 'regionSwitcher'.($isMobile ? ' regionSwitcher--mobile' : '');
    $__triggerClass = 'regionSwitcher_trigger'.($isMobile ? ' regionSwitcher_trigger--mobile' : '');
@endphp

<div class="{{ $__rootClass }}"
     data-region-switcher
     data-variant="{{ $variant }}"
     data-current-locale="{{ $__currentLocale }}">

    <button type="button"
            class="{{ $__triggerClass }}"
            aria-haspopup="true" aria-expanded="false"
            aria-label="{{ $__ui['choose'] }}">
        <span class="regionSwitcher_label">
            @if (! empty($__currentLang->flag))
                @if ($__isEmojiFlag)
                    <span class="regionSwitcher_flag regionSwitcher_flag--emoji" aria-hidden="true">{{ $__currentLang->flag }}</span>
                @else
                    <img class="regionSwitcher_flag"
                         src="{{ str_starts_with((string) $__currentLang->flag, 'http') ? $__currentLang->flag : asset(ltrim($__currentLang->flag, '/')) }}"
                         width="24" height="24"
                         decoding="async"
                         alt="{{ $__currentLang->name_native }}" />
                @endif
            @endif
            <span class="regionSwitcher_lang">{{ strtoupper($__currentLang->code ?? $__currentLocale) }}</span>
        </span>
        @unless ($isMobile)
            <x-icon name="chevron-down" class="regionSwitcher_chevron size-3" />
        @endunless
    </button>

    <div class="regionSwitcher_menu" role="dialog" aria-label="{{ $__ui['choose'] }}">
        @if ($isMobile)
            <div class="regionSwitcher_mobileHeader">
                <div class="regionSwitcher_mobileHeader_title">
                    <x-icon name="globe" class="size-4" />
                    <span>{{ $__ui['title'] }}</span>
                </div>
                <button type="button" class="regionSwitcher_mobileHeader_close" data-region-cancel aria-label="{{ $__ui['close'] }}">
                    <x-icon name="close" class="size-4" />
                </button>
            </div>
        @endif

        <div class="regionSwitcher_grid regionSwitcher_grid--langOnly">
            <section class="regionSwitcher_col regionSwitcher_col--lang" aria-label="{{ $__ui['language'] }}">
                <header class="regionSwitcher_col_header">
                    <x-icon name="globe" class="size-3.5" />
                    <span class="regionSwitcher_col_header_title">{{ $__ui['language'] }}</span>
                    <span class="regionSwitcher_col_header_sub">{{ $__ui['display'] }}</span>
                </header>
                <div class="regionSwitcher_col_list regionSwitcher_col_list--1col" role="group" data-region-col="lang">
                    @foreach ($__activeLangs as $__lang)
                        @php
                            $__isCurrent = ($__lang->code ?? '') === $__currentLocale;
                            $__href = locale_switch_url($__lang->code);
                            $__langCode = strtolower($__lang->code);
                            $__langUx = $__languageUxMap[$__langCode] ?? [
                                'native' => $__lang->name_native ?: strtoupper($__lang->code),
                                'english' => $__lang->name ?: strtoupper($__lang->code),
                            ];
                            $__langEmoji = is_string($__lang->flag ?? null)
                                && ! str_starts_with((string) $__lang->flag, 'http')
                                && ! str_starts_with((string) $__lang->flag, '/');
                        @endphp
                        <button type="button"
                                role="menuitemradio"
                                aria-checked="{{ $__isCurrent ? 'true' : 'false' }}"
                                title="{{ $__lang->name_native }}"
                                class="regionSwitcher_item regionSwitcher_item--lang{{ $__isCurrent ? ' is-selected is-current' : '' }}"
                                data-lang-code="{{ $__lang->code }}"
                                data-lang-href="{{ $__href }}">
                            @if (! empty($__lang->flag))
                                @if ($__langEmoji)
                                    <span class="regionSwitcher_item_flag" aria-hidden="true">{{ $__lang->flag }}</span>
                                @else
                                    <img class="regionSwitcher_item_flag regionSwitcher_item_flag--img"
                                         src="{{ str_starts_with((string) $__lang->flag, 'http') ? $__lang->flag : asset(ltrim($__lang->flag, '/')) }}"
                                         width="24" height="24"
                                         decoding="async"
                                         alt="{{ $__lang->name_native }}" />
                                @endif
                            @else
                                <span class="regionSwitcher_item_flag">{{ strtoupper(substr($__lang->code, 0, 2)) }}</span>
                            @endif
                            <span class="regionSwitcher_item_text">
                                <span class="regionSwitcher_item_title">{{ $__langUx['native'] }}</span>
                                <span class="regionSwitcher_item_sub">{{ $__langUx['english'] }} · {{ strtoupper($__lang->code) }}</span>
                            </span>
                            <span class="regionSwitcher_item_check" aria-hidden="true">
                                <x-icon name="check" class="size-2.5" />
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        </div>

        <footer class="regionSwitcher_footer">
            <button type="button" class="regionSwitcher_btn regionSwitcher_btn--ghost" data-region-cancel>
                {{ $__ui['cancel'] }}
            </button>
            <button type="button" class="regionSwitcher_btn regionSwitcher_btn--primary" data-region-apply>
                <span class="regionSwitcher_btn_label">{{ $__ui['apply'] }}</span>
                <x-icon name="arrow-right" class="regionSwitcher_btn_icon size-3.5" />
                <span class="regionSwitcher_btn_spinner" aria-hidden="true"></span>
            </button>
        </footer>

        <div class="regionSwitcher_loading" aria-hidden="true">
            <div class="regionSwitcher_loading_spinner"></div>
            <div class="regionSwitcher_loading_text">{{ $__ui['applying'] }}</div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.__regionSwitcherInit) return;
    window.__regionSwitcherInit = true;

    function lockBodyScroll(yes) {
        try { document.body.classList.toggle('is-regionSwitcherFullscreen', !!yes); } catch (e) {}
    }

    function closeAllOthers(except) {
        document.querySelectorAll('[data-region-switcher].open').forEach(function (el) {
            if (el === except) return;
            el.classList.remove('open');
            var t = el.querySelector('.regionSwitcher_trigger');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    }

    function anyOpenMobile() {
        return !!document.querySelector('[data-region-switcher][data-variant="mobile"].open');
    }

    function bindOne(root) {
        if (!root || root.dataset.bound === '1') return;
        root.dataset.bound = '1';

        var trigger = root.querySelector('.regionSwitcher_trigger');
        var menu = root.querySelector('.regionSwitcher_menu');
        if (!trigger || !menu) return;

        var applyBtn = menu.querySelector('[data-region-apply]');
        var cancelBtns = menu.querySelectorAll('[data-region-cancel]');
        var langCol = menu.querySelector('[data-region-col="lang"]');
        var currentLang = root.getAttribute('data-current-locale') || '';
        var isMobile = root.getAttribute('data-variant') === 'mobile';

        function getSelected(col) {
            return col ? col.querySelector('.regionSwitcher_item.is-selected') : null;
        }

        function pendingState() {
            var ls = getSelected(langCol);
            var langCode = ls ? (ls.getAttribute('data-lang-code') || '') : currentLang;
            return {
                langCode: langCode,
                langHref: ls ? (ls.getAttribute('data-lang-href') || '') : '',
                langChanged: langCode && langCode !== currentLang,
            };
        }

        function refreshApply() {
            var s = pendingState();
            applyBtn.removeAttribute('disabled');
            applyBtn.classList.toggle('is-ready', !!s.langChanged);
        }

        function resetSelection() {
            menu.querySelectorAll('.regionSwitcher_item.is-selected').forEach(function (el) {
                if (!el.classList.contains('is-current')) el.classList.remove('is-selected');
            });
            menu.querySelectorAll('.regionSwitcher_item.is-current').forEach(function (el) {
                el.classList.add('is-selected');
            });
            refreshApply();
        }

        function closeMenu() {
            if (!root.classList.contains('open')) return;
            root.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
            resetSelection();
            if (!anyOpenMobile()) lockBodyScroll(false);
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = !root.classList.contains('open');
            closeAllOthers(root);
            root.classList.toggle('open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                resetSelection();
                if (isMobile) lockBodyScroll(true);
            } else if (isMobile && !anyOpenMobile()) {
                lockBodyScroll(false);
            }
        });

        document.addEventListener('click', function (e) {
            if (isMobile) return;
            if (!root.contains(e.target)) closeMenu();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && root.classList.contains('open')) {
                closeMenu();
                try { trigger.focus(); } catch (err) {}
            }
        });

        if (langCol) {
            langCol.querySelectorAll('.regionSwitcher_item').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    langCol.querySelectorAll('.is-selected').forEach(function (el) {
                        el.classList.remove('is-selected');
                    });
                    btn.classList.add('is-selected');
                    refreshApply();
                });
            });
        }

        applyBtn.addEventListener('click', function () {
            var s = pendingState();
            if (!s.langChanged) {
                closeMenu();
                return;
            }
            root.classList.add('is-switching');
            applyBtn.classList.add('is-loading');
            applyBtn.setAttribute('disabled', 'disabled');
            cancelBtns.forEach(function (c) { c.setAttribute('disabled', 'disabled'); });
            try {
                window.location.assign(s.langHref);
            } catch (err) {
                window.location.href = s.langHref || window.location.href;
            }
        });

        cancelBtns.forEach(function (btn) {
            btn.addEventListener('click', function () { closeMenu(); });
        });

        resetSelection();
    }

    function init() {
        document.querySelectorAll('[data-region-switcher]').forEach(bindOne);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
@endonce
