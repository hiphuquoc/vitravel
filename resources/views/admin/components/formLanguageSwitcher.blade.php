@php
    $languages = config('language.list', []);
    if (empty($languages)) {
        try {
            $languages = \App\Models\Language::active()->map(fn ($l) => [
                'code' => $l->code,
                'name_native' => $l->name_native ?? $l->name,
            ])->all();
        } catch (\Throwable $e) {
            $languages = [];
        }
    }
@endphp
<div class="adminFormLanguageSwitcher">
    <div class="adminFormLanguageSwitcher_label">Ngôn ngữ:</div>
    <div class="adminFormLanguageSwitcher_list">
        @foreach ($languages as $lang)
            @php
                $code = $lang['code'] ?? ($lang['key'] ?? '');
                $label = $lang['name_native'] ?? ($lang['name_by_language'] ?? ($lang['name'] ?? strtoupper($code)));
                $selected = (! empty($language) && $language === $code) ? 'adminFormLanguageSwitcher_item--active' : '';
                $languageLink = route($routeName, array_merge($routeParams ?? [], [
                    'language' => $code,
                    'id' => $item->id ?? 0,
                ]));
            @endphp
            <a href="{{ $languageLink }}"
               class="adminFormLanguageSwitcher_item {{ $selected }}"
               title="{{ $label }}">
                <span class="adminFormLanguageSwitcher_item_code">{{ strtoupper($code) }}</span>
                <span class="adminFormLanguageSwitcher_item_name">{{ $label }}</span>
            </a>
        @endforeach
    </div>
</div>
