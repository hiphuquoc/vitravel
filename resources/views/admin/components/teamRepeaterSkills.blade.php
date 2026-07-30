@php
    $rows = old('skills');
    if ($rows === null) {
        $rows = ($member?->skills ?? collect())->map(fn ($s) => [
            'skill' => $s->skill,
            'percent' => $s->percent,
            'ordering' => $s->ordering,
        ])->values()->all();
    }
    if (! is_array($rows) || $rows === []) {
        $rows = [null];
    }
@endphp

<div class="adminFormSection adminFormSection--repeater repeater" data-repeater-container>
    <div class="adminFormSection_header">
        <div class="adminFormSection_header_info">
            <h2 class="adminFormSection_title">Kỹ năng chuyên môn</h2>
            <p class="adminFormSection_description">Tên kỹ năng và mức độ 0–100%.</p>
        </div>
        <button type="button" class="adminFormSection_header_action" data-repeater-create>
            <span>Thêm kỹ năng</span>
        </button>
    </div>
    <div class="adminFormSection_body">
        <div data-repeater-list="skills">
            @foreach ($rows as $index => $row)
                @php $row = is_array($row) ? $row : []; @endphp
                <div class="adminFormRepeater_item adminFormRepeater_item--block" data-repeater-item>
                    <div class="adminFormRepeater_item_drag" title="Kéo để sắp xếp">⋮⋮</div>
                    <div class="adminFormRepeater_item_content">
                        <input type="hidden" name="ordering" value="{{ $row['ordering'] ?? $index }}" class="adminFormRepeater_item_ordering">
                        <div class="adminFormGrid adminFormGrid--2cols">
                            @include('admin.components.formField', [
                                'label' => 'Kỹ năng',
                                'name' => 'skill',
                                'class' => 'adminFormRepeater_item_field',
                                'value' => $row['skill'] ?? '',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mức độ (%)',
                                'name' => 'percent',
                                'type' => 'number',
                                'min' => 0,
                                'max' => 100,
                                'class' => 'adminFormRepeater_item_field',
                                'value' => $row['percent'] ?? 80,
                            ])
                        </div>
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
