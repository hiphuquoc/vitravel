@php
    $rows = $oldData ?? null;
    if ($rows === null) {
        $rows = ($faqs ?? collect())->map(function ($faq) use ($locale) {
            $translation = $faq->translation($locale);

            return [
                'id' => $faq->id,
                'question' => $translation?->question,
                'answer' => $translation?->answer,
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
                <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="adminFormSection_header_info">
            <h2 class="adminFormSection_title">Câu hỏi thường gặp</h2>
            <p class="adminFormSection_description">Hiển thị ở cuối trang chi tiết tour. Kéo thả để đổi thứ tự.</p>
        </div>
        <button type="button" class="adminFormSection_header_action" data-repeater-create>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            <span>Thêm FAQ</span>
        </button>
    </div>
    <div class="adminFormSection_body">
        <div data-repeater-list="faqs">
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
                        @include('admin.components.formField', [
                            'label' => 'Câu hỏi',
                            'name' => 'question',
                            'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                            'value' => $row['question'] ?? '',
                        ])
                        @include('admin.components.formField', [
                            'label' => 'Trả lời',
                            'name' => 'answer',
                            'type' => 'textarea',
                            'rows' => 3,
                            'class' => 'adminFormGrid__full adminFormRepeater_item_field',
                            'value' => $row['answer'] ?? '',
                        ])
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
