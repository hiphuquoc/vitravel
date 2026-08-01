'use client';

import clsx from 'clsx';
import { Languages } from 'lucide-react';
import type { LocaleOption } from '@/lib/locale';

export function LocaleSwitcher({
  languages,
  value,
  onChange,
  disabled,
  hint,
  translatedLocales,
}: {
  languages: LocaleOption[];
  value: string;
  onChange: (code: string) => void;
  disabled?: boolean;
  hint?: string;
  /** Locale đã có bản dịch — thiếu sẽ hiện xám (vẫn chọn được để tạo) */
  translatedLocales?: string[] | null;
}) {
  if (!languages.length) return null;

  const readySet =
    translatedLocales == null ? null : new Set(translatedLocales.map((c) => c.toLowerCase()));

  return (
    <div className={clsx('ui-locale-switcher', disabled && 'ui-locale-switcher--disabled')}>
      <div className="ui-locale-switcher__label">
        <Languages size={15} strokeWidth={2.2} aria-hidden />
        <span>Ngôn ngữ</span>
      </div>
      <div className="ui-locale-switcher__list" role="tablist" aria-label="Chọn ngôn ngữ chỉnh sửa">
        {languages.map((lang) => {
          const active = lang.code === value;
          const ready = readySet == null ? true : readySet.has(lang.code.toLowerCase());
          const title = ready
            ? lang.name_native || lang.name
            : `${lang.name_native || lang.name} — chưa có bản dịch`;

          return (
            <button
              key={lang.code}
              type="button"
              role="tab"
              aria-selected={active}
              className={clsx(
                'ui-locale-switcher__item',
                active && 'ui-locale-switcher__item--active',
                !active && !ready && 'ui-locale-switcher__item--missing',
              )}
              disabled={disabled}
              title={title}
              onClick={() => onChange(lang.code)}
            >
              <span className="ui-locale-switcher__code">{lang.code.toUpperCase()}</span>
              <span className="ui-locale-switcher__name">{lang.name_native || lang.name}</span>
              {!ready && !active ? (
                <span className="ui-locale-switcher__dot" aria-hidden />
              ) : null}
            </button>
          );
        })}
      </div>
      {hint ? <p className="ui-locale-switcher__hint">{hint}</p> : null}
    </div>
  );
}
