{{-- Một mục flyout trên thanh menu chính (desktop) --}}
@props(['drawer'])

<div class="relative" @mouseenter="openMenu = '{{ $drawer['menu_key'] }}'; moreOpen = false" @mouseleave="openMenu = null">
    <button type="button"
        class="nav-link flex cursor-pointer items-center gap-1 whitespace-nowrap"
        :aria-expanded="openMenu === '{{ $drawer['menu_key'] }}'"
        @click="openMenu = openMenu === '{{ $drawer['menu_key'] }}' ? null : '{{ $drawer['menu_key'] }}'; moreOpen = false">
        {{ $drawer['label'] }}
        <x-icon name="chevron-down" class="header-nav-chevron size-3.5 shrink-0" />
    </button>
    <div x-cloak x-show="openMenu === '{{ $drawer['menu_key'] }}'" x-transition.opacity.duration.150ms
        @class([
            'nav-flyout absolute top-full left-0 z-50',
            'nav-flyout--wide' => ($drawer['flyout'] ?? 'wide') === 'wide',
            'nav-flyout--sm' => ($drawer['flyout'] ?? 'wide') !== 'wide',
        ])>
        <div class="nav-flyout__card">
            <div class="nav-flyout__scroll vt-scrollbar">
                <a href="{{ $drawer['hub_url'] }}" class="nav-panel-row nav-flyout__lead group">
                    <span class="nav-panel-item-row">
                        <span class="nav-panel-lead-mark" aria-hidden="true"></span>
                        <span class="nav-panel-item">{{ $drawer['lead_label'] }}</span>
                    </span>
                    @if (! empty($drawer['meta']))
                        <span class="nav-panel-meta">{{ $drawer['meta'] }}</span>
                    @endif
                </a>
                <div @class([
                    'nav-flyout__grid' => ($drawer['flyout'] ?? 'wide') === 'wide',
                    'nav-panel-group' => ($drawer['flyout'] ?? 'wide') !== 'wide',
                ])>
                    @foreach ($drawer['extras'] ?? [] as $extra)
                        <a href="{{ $extra['url'] }}" class="{{ ($drawer['flyout'] ?? 'wide') === 'wide' ? 'nav-panel-row' : 'nav-panel-link' }} group">
                            <span class="nav-panel-item-row">
                                <span @class(['nav-panel-item' => ($drawer['flyout'] ?? 'wide') === 'wide'])>{{ $extra['label'] }}</span>
                                <x-shared.count-badge :count="$extra['count'] ?? 0" />
                            </span>
                        </a>
                    @endforeach
                    @foreach ($drawer['entries'] ?? [] as $entry)
                        <a href="{{ $entry['url'] }}" @class([
                            'nav-panel-row group' => ($drawer['flyout'] ?? 'wide') === 'wide',
                            'nav-panel-link' => ($drawer['flyout'] ?? 'wide') !== 'wide',
                        ])>
                            <span class="nav-panel-item-row">
                                <span @class(['nav-panel-item' => ($drawer['flyout'] ?? 'wide') === 'wide'])>{{ $entry['name'] }}</span>
                                <x-shared.count-badge :count="$entry['count'] ?? 0" />
                            </span>
                            @if (! empty($entry['meta']))
                                <span class="nav-panel-meta">{{ $entry['meta'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
