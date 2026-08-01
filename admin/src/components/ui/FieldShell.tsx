'use client';

import clsx from 'clsx';
import type { ReactNode } from 'react';

type FieldShellProps = {
  label?: string;
  hint?: string;
  error?: string;
  htmlFor?: string;
  required?: boolean;
  children: ReactNode;
  className?: string;
};

function renderLabel(label: string, required?: boolean): ReactNode {
  const match = label.match(/^(.*?)\s*\*\s*$/);
  const text = (match ? match[1] : label).trimEnd();
  const showReq = Boolean(required || match);

  return (
    <>
      {text}
      {showReq ? (
        <span className="ui-field__req" aria-hidden>
          *
        </span>
      ) : null}
    </>
  );
}

export function Field({
  label,
  hint,
  error,
  htmlFor,
  required,
  children,
  className,
}: FieldShellProps) {
  return (
    <div className={clsx('ui-field', error && 'ui-field--invalid', className)}>
      {label ? (
        <label className="ui-field__label" htmlFor={htmlFor}>
          {renderLabel(label, required)}
        </label>
      ) : null}
      {children}
      {error ? <span className="ui-field__error">{error}</span> : null}
      {!error && hint ? <span className="ui-field__hint">{hint}</span> : null}
    </div>
  );
}
