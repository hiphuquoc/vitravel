<div class="adminFormLanguageSwitcher">
    <div class="adminFormLanguageSwitcher_label">Ngôn ngữ:</div>
    <div class="adminFormLanguageSwitcher_list">
        @foreach (config('language') as $lang)
            @php
                $selected = (! empty($language) && $language === $lang['key']) ? 'adminFormLanguageSwitcher_item--active' : '';
                $languageLink = route($routeName, array_merge($routeParams ?? [], [
                    'language' => $lang['key'],
                    'id' => $item->id ?? 0,
                ]));
            @endphp
            <a href="{{ $languageLink }}"
               class="adminFormLanguageSwitcher_item {{ $selected }}"
               title="{{ $lang['name_by_language'] }}">
                <span class="adminFormLanguageSwitcher_item_code">{{ strtoupper($lang['key']) }}</span>
                <span class="adminFormLanguageSwitcher_item_name">{{ $lang['name_by_language'] }}</span>
            </a>
        @endforeach
    </div>
</div>
