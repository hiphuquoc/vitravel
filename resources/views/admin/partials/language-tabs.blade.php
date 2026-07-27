<div class="mb-6 flex flex-wrap items-center gap-2">
    @foreach ($languages as $lang)
        <a href="{{ request()->fullUrlWithQuery(['language' => $lang->code]) }}"
           class="admin-btn {{ ($locale ?? 'vi') === $lang->code ? 'admin-btn--primary' : 'admin-btn--secondary' }}">
            {{ $lang->flag }} {{ $lang->name_native ?? $lang->name }}
        </a>
    @endforeach
</div>
