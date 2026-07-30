@php
    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
    $zaloPhone = preg_replace('/\D/', '', $contact['zalo'] ?? ($contact['phone'] ?? ''));
@endphp

{{-- Nút nổi Zalo + WhatsApp — số từ config/company.php (+ admin ghi đè) --}}
<div class="site-fab fixed z-40 flex flex-col items-end site-gap-sm">
    {{-- Tạm ẩn Zalo fixed --}}
    {{--
    @if ($zaloPhone !== '')
        <a href="https://zalo.me/{{ $zaloPhone }}" target="_blank" rel="noopener"
            class="btn-zalo w-auto self-end shadow-(--shadow-float)"
            aria-label="Trò chuyện Zalo">
            <x-icon name="zalo" class="size-5" />
            <span class="hidden sm:inline">Zalo</span>
        </a>
    @endif
    --}}
    @if ($waPhone !== '')
        <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"
            class="btn-whatsapp w-auto self-end shadow-(--shadow-float)"
            aria-label="Trò chuyện WhatsApp">
            <x-icon name="whatsapp" class="size-5" />
            <span class="hidden sm:inline">WhatsApp</span>
        </a>
    @endif
</div>
