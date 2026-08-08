{{--
  Public project switcher (local/debug when allow_public_query_override).
  Navigates to same path with ?project={code}.
--}}
@php
    $__allow = (bool) config('project.allow_public_query_override', false);
    $__projects = $publicProjects ?? collect();
    if (! $__projects instanceof \Illuminate\Support\Collection) {
        $__projects = collect($__projects);
    }
    $__current = $currentProject ?? null;
    $__show = $__allow && $__projects->count() > 1;
@endphp

@if ($__show)
    {{-- Inline style: component renders after </head>, @push('head') would be too late --}}
    <style>
        .project-switcher {
            position: fixed;
            left: 0.75rem;
            bottom: 0.75rem;
            z-index: 60;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.55rem;
            background: color-mix(in srgb, var(--color-page-soft, #f8f6ec) 92%, transparent);
            border: 1px solid var(--color-line, #ddd9c2);
            color: var(--color-ink-soft, #514f45);
            font-size: 0.75rem;
            line-height: 1.2;
            box-shadow: 0 1px 2px rgb(39 43 35 / 6%);
        }
        .project-switcher__label {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-size: 0.65rem;
            color: var(--color-muted, #817d6e);
        }
        .project-switcher__select {
            max-width: 11rem;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            padding: 0;
            cursor: pointer;
        }
        .project-switcher__select:focus {
            outline: 1px solid var(--color-primary-500, #6b8f3f);
            outline-offset: 2px;
        }
    </style>
    <div class="project-switcher" role="navigation" aria-label="Chọn dự án">
        <label class="project-switcher__label" for="project-switcher-select">Dự án</label>
        <select id="project-switcher-select"
                class="project-switcher__select"
                onchange="(function(sel){if(!sel.value)return;var u=new URL(window.location.href);u.searchParams.set('project',sel.value);window.location.assign(u.toString());})(this)">
            @foreach ($__projects as $__p)
                <option value="{{ $__p->code }}" @selected($__current && $__current->code === $__p->code)>
                    {{ $__p->name }} ({{ $__p->code }})
                </option>
            @endforeach
        </select>
    </div>
@endif
