import { Loader2 } from 'lucide-react';
import clsx from 'clsx';

type PageLoaderProps = {
  label?: string;
  /** `page` = vùng content; `screen` = full viewport (auth boot). */
  variant?: 'page' | 'screen' | 'inline';
  className?: string;
};

/** Loading dùng chung — route Suspense / auth boot / khối nội dung. */
export function PageLoader({
  label = 'Đang tải…',
  variant = 'page',
  className,
}: PageLoaderProps) {
  return (
    <div
      className={clsx(
        'ui-page-loader',
        `ui-page-loader--${variant}`,
        className,
      )}
      role="status"
      aria-live="polite"
      aria-busy="true"
    >
      <div className="ui-page-loader__card">
        <span className="ui-page-loader__spinner" aria-hidden>
          <Loader2 size={22} strokeWidth={2.35} className="ui-spin" />
        </span>
        <div className="ui-page-loader__copy">
          <p className="ui-page-loader__title">{label}</p>
          <p className="ui-page-loader__hint">Vui lòng chờ trong giây lát</p>
        </div>
        <div className="ui-page-loader__shimmer" aria-hidden>
          <span />
          <span />
          <span />
        </div>
      </div>
    </div>
  );
}

export default PageLoader;
