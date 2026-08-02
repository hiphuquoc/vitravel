'use client';

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from 'react';
import { usePathname, useSearchParams } from 'next/navigation';
import { getBasePath } from '@/lib/api';

type NavigationLoadingContextValue = {
  /** Đang chuyển trang (sau debounce ngắn). */
  isNavigating: boolean;
  /** Bắt đầu indicator ngay khi click / router.push. */
  start: () => void;
  /** Kết thúc (pathname đổi hoặc huỷ). */
  done: () => void;
};

const NavigationLoadingContext = createContext<NavigationLoadingContextValue | null>(null);

function normalizePath(pathname: string, search = ''): string {
  const base = getBasePath().replace(/\/$/, '') || '';
  let path = pathname || '/';
  if (base && path.startsWith(base)) {
    path = path.slice(base.length) || '/';
  }
  if (!path.startsWith('/')) path = `/${path}`;
  const qs = search.startsWith('?') ? search : search ? `?${search}` : '';
  return `${path}${qs}`;
}

function isModifiedClick(e: MouseEvent) {
  return e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0;
}

export function NavigationLoadingProvider({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [active, setActive] = useState(false);
  const showTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const safetyTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const pendingRef = useRef(false);
  const routeKey = `${pathname}?${searchParams.toString()}`;

  const clearTimers = useCallback(() => {
    if (showTimer.current) {
      clearTimeout(showTimer.current);
      showTimer.current = null;
    }
    if (safetyTimer.current) {
      clearTimeout(safetyTimer.current);
      safetyTimer.current = null;
    }
  }, []);

  const done = useCallback(() => {
    pendingRef.current = false;
    clearTimers();
    setActive(false);
  }, [clearTimers]);

  const start = useCallback(() => {
    if (pendingRef.current) return;
    pendingRef.current = true;
    clearTimers();
    // Tránh nhấp nháy khi chuyển trang nhanh
    showTimer.current = setTimeout(() => {
      if (pendingRef.current) setActive(true);
    }, 80);
    // An toàn nếu navigation bị huỷ / soft-nav không đổi URL
    safetyTimer.current = setTimeout(() => {
      if (pendingRef.current) done();
    }, 12_000);
  }, [clearTimers, done]);

  // Kết thúc khi URL App Router đổi
  useEffect(() => {
    if (pendingRef.current || active) {
      // Đợi một nhịp để thanh progress kịp animate tới 100%
      const t = setTimeout(() => done(), 180);
      return () => clearTimeout(t);
    }
    return undefined;
    // eslint-disable-next-line react-hooks/exhaustive-deps -- chỉ khi route đổi
  }, [routeKey]);

  // Bắt click Link nội bộ — phản hồi ngay trước khi RSC load
  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (isModifiedClick(e) || e.defaultPrevented) return;
      const el = e.target as Element | null;
      const anchor = el?.closest?.('a');
      if (!anchor || !(anchor instanceof HTMLAnchorElement)) return;
      if (anchor.target && anchor.target !== '_self') return;
      if (anchor.hasAttribute('download')) return;
      const hrefAttr = anchor.getAttribute('href');
      if (!hrefAttr || hrefAttr.startsWith('#') || hrefAttr.startsWith('mailto:') || hrefAttr.startsWith('tel:')) {
        return;
      }

      let url: URL;
      try {
        url = new URL(hrefAttr, window.location.href);
      } catch {
        return;
      }
      if (url.origin !== window.location.origin) return;

      const next = normalizePath(url.pathname, url.search);
      const current = normalizePath(window.location.pathname, window.location.search);
      if (next === current) return;

      start();
    };

    document.addEventListener('click', onClick, true);
    return () => document.removeEventListener('click', onClick, true);
  }, [start]);

  useEffect(() => () => clearTimers(), [clearTimers]);

  useEffect(() => {
    document.documentElement.classList.toggle('is-navigating', active);
    return () => document.documentElement.classList.remove('is-navigating');
  }, [active]);

  const value = useMemo(
    () => ({ isNavigating: active, start, done }),
    [active, start, done],
  );

  return (
    <NavigationLoadingContext.Provider value={value}>{children}</NavigationLoadingContext.Provider>
  );
}

export function useNavigationLoading() {
  const ctx = useContext(NavigationLoadingContext);
  if (!ctx) {
    return {
      isNavigating: false,
      start: () => undefined,
      done: () => undefined,
    } satisfies NavigationLoadingContextValue;
  }
  return ctx;
}
