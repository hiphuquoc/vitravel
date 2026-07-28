@php
    $contact = view_data()->companyContact();
    $waPhone = preg_replace('/\D/', '', $contact['whatsapp']);
    $zaloPhone = preg_replace('/\D/', '', $contact['phone'] ?: $contact['whatsapp']);
@endphp

{{-- Nút nổi Zalo + WhatsApp --}}
<div class="fixed right-4 bottom-4 z-40 flex flex-col items-end gap-2.5 sm:right-6 sm:bottom-6">
    <a href="https://zalo.me/{{ $zaloPhone }}" target="_blank" rel="noopener"
        class="btn-zalo w-auto self-end shadow-(--shadow-float)"
        aria-label="Trò chuyện Zalo">
        <x-icon name="zalo" class="size-5" />
        <span class="hidden sm:inline">Zalo</span>
    </a>
    <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"
        class="btn-whatsapp w-auto self-end shadow-(--shadow-float)"
        aria-label="Trò chuyện WhatsApp">
        <x-icon name="whatsapp" class="size-5" />
        <span class="hidden sm:inline">WhatsApp</span>
    </a>
</div>
