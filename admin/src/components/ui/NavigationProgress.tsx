'use client';

import { Suspense, type ReactNode } from 'react';
import clsx from 'clsx';
import { NavigationLoadingProvider, useNavigationLoading } from '@/lib/navigation-loading';

function ProgressBar() {
  const { isNavigating } = useNavigationLoading();

  return (
    <div
      className={clsx('ui-nav-progress', isNavigating && 'ui-nav-progress--active')}
      aria-hidden={!isNavigating}
      role="presentation"
    >
      <span className="ui-nav-progress__bar" />
    </div>
  );
}

function NavigationLoadingRoot({ children }: { children: ReactNode }) {
  return (
    <NavigationLoadingProvider>
      {children}
      <ProgressBar />
    </NavigationLoadingProvider>
  );
}

/**
 * Provider + thanh progress chuyển trang.
 * Mount bọc children trong Providers (Suspense vì useSearchParams).
 */
export function NavigationProgress({ children }: { children: ReactNode }) {
  return (
    <Suspense fallback={children}>
      <NavigationLoadingRoot>{children}</NavigationLoadingRoot>
    </Suspense>
  );
}
