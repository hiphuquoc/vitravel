@php
    $rows = old('achievements');
    if ($rows === null) {
        $rows = ($member?->achievements ?? collect())->map(fn ($a) => [
            'content' => $a->content,
            'ordering' => $a->ordering,
        ])->values()->all();
    }
    if (! is_array($rows) || $rows === []) {
        $rows = [null];
    }
@endphp

<div class="adminFormSection adminFormSection--repeater repeater" data-repeater-container>
    <div class="adminFormSection_header">
        <div class="adminFormSection_header_info">
            <h2 class="adminFormSection_title">Thành tích nổi bật</h2>
            <p class="adminFormSection_description">Mỗi dòng một thành tích. Kéo thả để đổi thứ tự.</p>
        </div>
        <button type="button" class="adminFormSection_header_action" data-repeater-create>
            <span>Thêm thành tích</span>
        </button>
    </div>
    <div class="adminFormSection_body">
        <div data-repeater-list="achievements">
            @foreach ($rows as $index => $row)
                @php $row = is_array($row) ? $row : []; @endphp
                <div class="adminFormRepeater_item adminFormRepeater_item--block" data-repeater-item>
                    <div class="adminFormRepeater_item_drag" title="Kéo để sắp xếp">⋮⋮</div>
                    <div class="adminFormRepeater_item_content">
                        <input type="hidden" name="ordering" value="{{ $row['ordering'] ?? $index }}" class="adminFormRepeater_item_ordering">
                        @include('admin.components.formField', [
                            'label' => 'Nội dung',
                            'name' => 'content',
                            'type' => 'textarea',
                            'rows' => 2,
                            'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                            'value' => $row['content'] ?? '',
                        ])
                    </div>
                    <button type="button" class="adminFormRepeater_item_delete" data-repeater-delete>
                        <span>Xóa</span>
                    </button>
                </div>
            @endforeach
        </div>
        <button type="button" data-repeater-create style="display:none;"></button>
    </div>
</div>
