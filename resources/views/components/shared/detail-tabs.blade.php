@props([
    /** @var array<string, array{label: string, icon?: string}|string> */
    'tabs' => [],
    'enableScrollSpy' => true,
])

@php
    $normalized = [];
    foreach ($tabs as $id => $tab) {
        if (is_string($tab)) {
            $normalized[$id] = ['label' => $tab, 'icon' => null];
        } elseif (is_array($tab) && filled($tab['label'] ?? null)) {
            $normalized[$id] = [
                'label' => (string) $tab['label'],
                'icon' => filled($tab['icon'] ?? null) ? (string) $tab['icon'] : null,
            ];
        }
    }
@endphp

@if (count($normalized) > 1)
    <nav class="detail-tabs" aria-label="Điều hướng trong trang">
        <div class="container-site">
            <div class="detail-tabs__wrapper">
                <div class="detail-tabs__inner">
                    @foreach ($normalized as $id => $tab)
                        <a href="#{{ $id }}"
                            class="detail-tabs__link"
                            @if ($enableScrollSpy)
                                :class="active === '{{ $id }}' ? 'is-active' : ''"
                            @endif>
                            @if ($tab['icon'])
                                <x-icon :name="$tab['icon']" class="detail-tabs__icon size-4 shrink-0" />
                            @endif
                            <span>{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>
@endif
