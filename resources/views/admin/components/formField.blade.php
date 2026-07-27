@php
    $fieldId = $name ?? 'field_'.uniqid();
    $fieldType = $type ?? 'text';
    $fieldValue = $value ?? old($name);
    $isRequired = $required ?? false;
    $hasCharCount = $charCount ?? false;
    $maxLength = $maxLength ?? null;
@endphp

<div class="adminFormField {{ $isRequired ? 'adminFormField--required' : '' }} {{ $class ?? '' }}">
    <div class="adminFormField_labelWrapper">
        <label class="adminFormField_label" for="{{ $fieldId }}">
            @if (! empty($tooltip))
                <span class="adminFormField_tooltip" data-tooltip="{{ $tooltip }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </span>
            @endif
            <span>{{ $label ?? '' }}</span>
            @if ($isRequired)
                <span class="adminFormField_required">*</span>
            @endif
        </label>
        @if ($hasCharCount)
            <div class="adminFormField_charCount" data-field="{{ $fieldId }}">
                <span class="adminFormField_charCount_current" data-charactor="{{ $fieldId }}">{{ mb_strlen((string) ($fieldValue ?? '')) }}</span>
                @if ($maxLength)
                    <span class="adminFormField_charCount_separator">/</span>
                    <span class="adminFormField_charCount_max">{{ $maxLength }}</span>
                @endif
            </div>
        @endif
    </div>

    @if ($fieldType === 'textarea')
        <textarea
            class="adminFormField_input adminFormField_input--textarea"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            @if ($isRequired) required @endif
            @if ($maxLength) maxlength="{{ $maxLength }}" @endif
            @if (! empty($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if (! empty($rows)) rows="{{ $rows }}" @endif
            @if (! empty($readonly) && $readonly) readonly @endif
        >{{ $fieldValue }}</textarea>
    @elseif ($fieldType === 'select')
        <select
            class="adminFormField_input adminFormField_input--select"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            @if ($isRequired) required @endif
            @if (! empty($readonly) && $readonly) readonly @endif
        >
            @if (! empty($placeholder))
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach (($options ?? []) as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($fieldValue == $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($fieldType === 'checkbox')
        <div class="adminFormField_checkbox">
            <input
                type="checkbox"
                class="adminFormField_checkbox_input"
                id="{{ $fieldId }}"
                name="{{ $name }}"
                value="1"
                @checked((bool) $fieldValue)
            />
            <label class="adminFormField_checkbox_label" for="{{ $fieldId }}">
                {{ $checkboxLabel ?? $label ?? '' }}
            </label>
        </div>
    @else
        <input
            type="{{ $fieldType }}"
            class="adminFormField_input"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $fieldValue }}"
            @if ($isRequired) required @endif
            @if ($maxLength) maxlength="{{ $maxLength }}" @endif
            @if (! empty($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if (! empty($readonly) && $readonly) readonly @endif
            @if (! empty($step)) step="{{ $step }}" @endif
            @if (! empty($min)) min="{{ $min }}" @endif
            @if (! empty($max)) max="{{ $max }}" @endif
        />
    @endif

    @if ($errors->has($name))
        <div class="adminFormField_error">{{ $errors->first($name) }}</div>
    @elseif (! empty($helpText))
        <div class="adminFormField_help">{{ $helpText }}</div>
    @endif
</div>
