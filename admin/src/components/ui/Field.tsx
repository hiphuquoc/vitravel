'use client';

import clsx from 'clsx';
import type { InputHTMLAttributes, TextareaHTMLAttributes } from 'react';
import { Field } from '@/components/ui/FieldShell';

export { Field } from '@/components/ui/FieldShell';
export { Select } from '@/components/ui/Select';
export type { SelectOption } from '@/components/ui/Select';
export { MultiSelect } from '@/components/ui/MultiSelect';

type InputProps = InputHTMLAttributes<HTMLInputElement> & {
  label?: string;
  hint?: string;
  error?: string;
};

export function Input({ label, hint, error, className, id, required, ...rest }: InputProps) {
  const inputId = id || (typeof rest.name === 'string' ? rest.name : undefined);
  return (
    <Field label={label} hint={hint} error={error} htmlFor={inputId} required={required}>
      <div className={clsx('ui-field__box', error && 'ui-field__box--error')}>
        <input
          id={inputId}
          className={clsx('ui-field__control', className)}
          required={required}
          {...rest}
        />
      </div>
    </Field>
  );
}

type MoneyInputProps = Omit<InputProps, 'type' | 'value' | 'onChange'> & {
  value: string;
  onValueChange: (rawDigits: string) => void;
};

/** Hiển thị 28,000,000 — lưu chuỗi số thuần (không dấu). */
export function MoneyInput({
  label,
  hint,
  error,
  className,
  id,
  required,
  value,
  onValueChange,
  ...rest
}: MoneyInputProps) {
  const inputId = id || (typeof rest.name === 'string' ? rest.name : undefined);
  const digits = String(value ?? '').replace(/\D/g, '');
  const display = digits ? Number(digits).toLocaleString('en-US') : '';

  return (
    <Field label={label} hint={hint} error={error} htmlFor={inputId} required={required}>
      <div className={clsx('ui-field__box', error && 'ui-field__box--error')}>
        <input
          id={inputId}
          inputMode="numeric"
          className={clsx('ui-field__control', className)}
          required={required}
          {...rest}
          value={display}
          onChange={(e) => onValueChange(e.target.value.replace(/\D/g, ''))}
        />
      </div>
    </Field>
  );
}

type TextareaProps = TextareaHTMLAttributes<HTMLTextAreaElement> & {
  label?: string;
  hint?: string;
  error?: string;
};

export function Textarea({ label, hint, error, className, id, required, ...rest }: TextareaProps) {
  const inputId = id || (typeof rest.name === 'string' ? rest.name : undefined);
  return (
    <Field label={label} hint={hint} error={error} htmlFor={inputId} required={required}>
      <div className={clsx('ui-field__box', error && 'ui-field__box--error')}>
        <textarea
          id={inputId}
          className={clsx('ui-field__control', 'ui-field__control--textarea', className)}
          required={required}
          {...rest}
        />
      </div>
    </Field>
  );
}

type SwitchProps = {
  label: string;
  checked: boolean;
  onChange: (checked: boolean) => void;
  name?: string;
};

export function Switch({ label, checked, onChange, name }: SwitchProps) {
  return (
    <label className="ui-switch">
      <input
        type="checkbox"
        name={name}
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      <span className="ui-switch__track" />
      <span className="ui-switch__label">{label}</span>
    </label>
  );
}
