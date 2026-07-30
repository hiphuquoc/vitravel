@php
    $rows = old('degrees');
    if ($rows === null) {
        $rows = ($member?->degrees ?? collect())->map(fn ($d) => [
            'title' => $d->title,
            'school' => $d->school,
            'items' => $d->items->pluck('content')->implode("\n"),
            'ordering' => $d->ordering,
        ])->values()->all();
    }
    if (! is_array($rows) || $rows === []) {
        $rows = [null];
    }
@endphp

<div class="adminFormSection adminFormSection--repeater repeater" data-repeater-container>
    <div class="adminFormSection_header">
        <div class="adminFormSection_header_info">
            <h2 class="adminFormSection_title">Bằng cấp &amp; Chứng chỉ</h2>
            <p class="adminFormSection_description">Mỗi dòng chi tiết là một bullet phụ.</p>
        </div>
        <button type="button" class="adminFormSection_header_action" data-repeater-create>
            <span>Thêm bằng cấp</span>
        </button>
    </div>
    <div class="adminFormSection_body">
        <div data-repeater-list="degrees">
            @foreach ($rows as $index => $row)
                @php $row = is_array($row) ? $row : []; @endphp
                <div class="adminFormRepeater_item adminFormRepeater_item--block" data-repeater-item>
                    <div class="adminFormRepeater_item_drag" title="Kéo để sắp xếp">⋮⋮</div>
                    <div class="adminFormRepeater_item_content">
                        <input type="hidden" name="ordering" value="{{ $row['ordering'] ?? $index }}" class="adminFormRepeater_item_ordering">
                        <div class="adminFormGrid adminFormGrid--2cols">
                            @include('admin.components.formField', [
                                'label' => 'Tên bằng / chứng chỉ',
                                'name' => 'title',
                                'class' => 'adminFormRepeater_item_field',
                                'value' => $row['title'] ?? '',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Trường / Tổ chức',
                                'name' => 'school',
                                'class' => 'adminFormRepeater_item_field',
                                'value' => $row['school'] ?? '',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Chi tiết (mỗi dòng một mục)',
                                'name' => 'items',
                                'type' => 'textarea',
                                'rows' => 2,
                                'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                                'value' => $row['items'] ?? '',
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
