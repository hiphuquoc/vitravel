@php
    $rows = $oldData ?? null;
    if ($rows === null) {
        $rows = ($days ?? collect())->map(function ($day) use ($locale) {
            $translation = $day->translation($locale);

            return [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'meals_included' => $day->meals_included,
                'transport_icons' => is_array($day->transport_icons) ? implode(', ', $day->transport_icons) : '',
                'title' => $translation?->title,
                'content' => $translation?->content,
                'overnight_at' => $translation?->overnight_at,
            ];
        })->values()->all();
    }
    if (! is_array($rows) || $rows === []) {
        $rows = [null];
    }
@endphp

<div class="adminFormSection adminFormSection--repeater repeater" data-repeater-container>
    <div class="adminFormSection_header">
        <div class="adminFormSection_header_icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="adminFormSection_header_info">
            <h2 class="adminFormSection_title">Lịch trình từng ngày</h2>
            <p class="adminFormSection_description">Hiển thị accordion trên trang chi tiết. Kéo thả để đổi thứ tự ngày.</p>
        </div>
        <button type="button" class="adminFormSection_header_action" data-repeater-create>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            <span>Thêm ngày</span>
        </button>
    </div>
    <div class="adminFormSection_body">
        <div data-repeater-list="itinerary">
            @foreach ($rows as $index => $row)
                @php $row = is_array($row) ? $row : []; @endphp
                <div class="adminFormRepeater_item adminFormRepeater_item--block" data-repeater-item>
                    <div class="adminFormRepeater_item_drag" title="Kéo để sắp xếp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/>
                            <circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/>
                        </svg>
                    </div>
                    <div class="adminFormRepeater_item_content">
                        <input type="hidden" name="ordering" value="{{ $row['ordering'] ?? $index }}" class="adminFormRepeater_item_ordering">
                        @if (! empty($row['id']))
                            <input type="hidden" name="id" value="{{ $row['id'] }}">
                        @endif
                        <div class="adminFormGrid adminFormGrid--2cols">
                            @include('admin.components.formField', [
                                'label' => 'Ngày',
                                'name' => 'day_number',
                                'type' => 'number',
                                'min' => 1,
                                'value' => $row['day_number'] ?? ($index + 1),
                                'class' => 'adminFormRepeater_item_field',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Bữa ăn',
                                'name' => 'meals_included',
                                'value' => $row['meals_included'] ?? '',
                                'helpText' => 'VD: Sáng; Trưa; Tối',
                                'class' => 'adminFormRepeater_item_field',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề ngày',
                                'name' => 'title',
                                'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                                'value' => $row['title'] ?? '',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Nội dung chi tiết',
                                'name' => 'content',
                                'type' => 'textarea',
                                'rows' => 3,
                                'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                                'value' => $row['content'] ?? '',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Nghỉ đêm tại',
                                'name' => 'overnight_at',
                                'value' => $row['overnight_at'] ?? '',
                                'class' => 'adminFormRepeater_item_field',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Phương tiện (icon)',
                                'name' => 'transport_icons',
                                'value' => $row['transport_icons'] ?? '',
                                'helpText' => 'Cách nhau bởi dấu phẩy: plane, car, boat, cruise, trekking, walking, bike',
                                'class' => 'adminFormRepeater_item_field',
                            ])
                        </div>
                    </div>
                    <button type="button" class="adminFormRepeater_item_delete" data-repeater-delete>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        <span>Xóa</span>
                    </button>
                </div>
            @endforeach
        </div>
        <button type="button" data-repeater-create style="display:none;"></button>
    </div>
</div>
