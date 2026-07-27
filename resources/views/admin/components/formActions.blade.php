{{--
    Component: Admin Form Actions
    Usage: @include('admin.components.formActions', [
        'backRoute' => 'admin.packages.tours',
        'viewUrl' => '/tours/...',
    ])
--}}
<div class="adminFormActions">
    <div class="adminFormActions_primary">
        <button type="submit" form="formAction" id="adminFormSubmitBtn" class="adminFormActions_button adminFormActions_button--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            <span>Lưu thay đổi</span>
        </button>
    </div>

    <div class="adminFormActions_secondary">
        @if (! empty($backRoute))
            <a href="{{ route($backRoute) }}" class="adminFormActions_button adminFormActions_button--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Quay lại</span>
            </a>
        @endif

        @if (! empty($viewUrl))
            <a href="{{ $viewUrl }}" target="_blank" class="adminFormActions_button adminFormActions_button--view">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span>Xem trang</span>
            </a>
        @endif
    </div>
</div>

<div class="adminFormActions_mobile">
    <div class="adminFormActions_mobile_container">
        @if (! empty($viewUrl))
            <a href="{{ $viewUrl }}" target="_blank" class="adminFormActions_mobile_button adminFormActions_mobile_button--view" title="Xem trang">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </a>
        @endif

        <div class="adminFormActions_mobile_right">
            @if (! empty($backRoute))
                <a href="{{ route($backRoute) }}" class="adminFormActions_mobile_button adminFormActions_mobile_button--back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>Quay lại</span>
                </a>
            @endif

            <button type="submit" form="formAction" id="adminFormSubmitBtnMobile" class="adminFormActions_mobile_button adminFormActions_mobile_button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>Lưu</span>
            </button>
        </div>
    </div>
</div>
