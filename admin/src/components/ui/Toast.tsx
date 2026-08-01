'use client';

import clsx from 'clsx';
import {
  AlertTriangle,
  Check,
  Info,
  Loader2,
  X,
} from 'lucide-react';
import toast, { Toaster, resolveValue, type Toast as HotToast } from 'react-hot-toast';

function iconFor(type: HotToast['type']) {
  switch (type) {
    case 'success':
      return <Check size={15} strokeWidth={2.6} />;
    case 'error':
      return <AlertTriangle size={15} strokeWidth={2.4} />;
    case 'loading':
      return <Loader2 size={15} strokeWidth={2.4} className="ui-toast__spin" />;
    default:
      return <Info size={15} strokeWidth={2.4} />;
  }
}

function toneFor(type: HotToast['type']) {
  if (type === 'success' || type === 'error' || type === 'loading') return type;
  return 'info';
}

function ToastCard({ t }: { t: HotToast }) {
  const tone = toneFor(t.type);
  const message = resolveValue(t.message, t);

  return (
    <div
      className={clsx(
        'ui-toast',
        `ui-toast--${tone}`,
        t.visible ? 'ui-toast--in' : 'ui-toast--out',
      )}
      role={tone === 'error' ? 'alert' : 'status'}
      aria-live={tone === 'error' ? 'assertive' : 'polite'}
    >
      <span className="ui-toast__spine" aria-hidden />
      <span className="ui-toast__icon" aria-hidden>
        {iconFor(t.type)}
      </span>
      <div className="ui-toast__body">
        <p className="ui-toast__message">{message}</p>
      </div>
      {t.type !== 'loading' ? (
        <button
          type="button"
          className="ui-toast__close"
          onClick={() => toast.dismiss(t.id)}
          aria-label="Đóng thông báo"
        >
          <X size={14} strokeWidth={2.4} />
        </button>
      ) : null}
    </div>
  );
}

/** Toaster brand — mount 1 lần trong Providers */
export function AppToaster() {
  return (
    <Toaster
      position="top-right"
      gutter={12}
      containerClassName="ui-toast-viewport"
      containerStyle={{ top: 18, right: 18 }}
      toastOptions={{
        duration: 4000,
        success: { duration: 3600 },
        error: { duration: 5200 },
        loading: { duration: Infinity },
      }}
    >
      {(t) => <ToastCard t={t} />}
    </Toaster>
  );
}
