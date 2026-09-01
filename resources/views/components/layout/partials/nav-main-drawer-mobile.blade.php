{{-- Một mục accordion menu di động --}}
@props(['drawer'])

<div class="mobile-nav-drawer__section" x-ref="sec-{{ $drawer['menu_key'] }}">
    <button type="button" class="mobile-nav-drawer__trigger"
        :aria-expanded="mobileSub === '{{ $drawer['menu_key'] }}'"
        @click="toggleMobileSub('{{ $drawer['menu_key'] }}')">
        <span class="mobile-nav-drawer__trigger-icon" aria-hidden="true">
            <x-icon :name="$drawer['icon'] ?? 'briefcase'" class="size-4" />
        </span>
        <span class="mobile-nav-drawer__trigger-label">{{ $drawer['label'] }}</span>
        <x-icon name="chevron-down" class="mobile-nav-drawer__chevron size-4" ::class="mobileSub === '{{ $drawer['menu_key'] }}' && 'is-open'" />
    </button>
    <div class="mobile-nav-drawer__sub" x-show="mobileSub === '{{ $drawer['menu_key'] }}'" x-collapse>
        <ul class="mobile-nav-drawer__tree">
            <li>
                <a href="{{ $drawer['hub_url'] }}" class="mobile-nav-drawer__tree-link mobile-nav-drawer__tree-link--lead" @click="closeMobileNav()">
                    <span class="mobile-nav-drawer__tree-link-title item-title">{{ $drawer['lead_label'] }}</span>
                    @if (! empty($drawer['meta']))
                        <span class="mobile-nav-drawer__tree-link-meta">{{ $drawer['meta'] }}</span>
                    @endif
                </a>
            </li>
            @foreach ($drawer['extras'] ?? [] as $extra)
                <li>
                    <a href="{{ $extra['url'] }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                        <span class="mobile-nav-drawer__tree-link-row">
                            <span class="mobile-nav-drawer__tree-link-title">{{ $extra['label'] }}</span>
                            <x-shared.count-badge :count="$extra['count'] ?? 0" />
                        </span>
                    </a>
                </li>
            @endforeach
            @foreach ($drawer['entries'] ?? [] as $entry)
                <li @class(['mobile-nav-drawer__tree-item--last' => $loop->last && empty($drawer['extras'] ?? [])])>
                    <a href="{{ $entry['url'] }}" class="mobile-nav-drawer__tree-link" @click="closeMobileNav()">
                        <span class="mobile-nav-drawer__tree-link-row">
                            <span class="mobile-nav-drawer__tree-link-title">{{ $entry['name'] }}</span>
                            <x-shared.count-badge :count="$entry['count'] ?? 0" />
                        </span>
                        @if (! empty($entry['meta']))
                            <span class="mobile-nav-drawer__tree-link-meta">{{ $entry['meta'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
