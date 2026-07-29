@php
    if (! isset($languages) || (is_countable($languages) && count($languages) === 0)) {
        try {
            $languages = \App\Models\Language::active();
        } catch (\Throwable $e) {
            $languages = collect(config('language.list', []))->map(fn ($l) => (object) $l);
        }
    }
    $currentCode = $locale ?? request('language') ?? 'vi';
@endphp
<div class="mb-6 flex flex-wrap items-center gap-2">
    @foreach ($languages as $lang)
        @php
            $code = is_array($lang) ? ($lang['code'] ?? '') : ($lang->code ?? '');
            $name = is_array($lang) ? ($lang['name_native'] ?? $lang['name'] ?? $code) : ($lang->name_native ?? $lang->name ?? $code);
            $flag = is_array($lang) ? ($lang['flag'] ?? null) : ($lang->flag ?? null);
            $isImg = is_string($flag) && (str_starts_with($flag, '/') || str_starts_with($flag, 'http'));
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['language' => $code]) }}"
           class="admin-btn {{ $currentCode === $code ? 'admin-btn--primary' : 'admin-btn--secondary' }}">
            @if ($isImg)
                <img src="{{ str_starts_with($flag, 'http') ? $flag : asset(ltrim($flag, '/')) }}"
                     alt="" width="16" height="16" class="inline-block rounded-sm mr-1" />
            @elseif ($flag)
                <span class="mr-1">{{ $flag }}</span>
            @endif
            {{ $name }}
        </a>
    @endforeach
</div>
