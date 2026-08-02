'use client';

import type { ReactNode } from 'react';
import Link from 'next/link';
import { usePathname, useSearchParams } from 'next/navigation';
import { ChevronRight, LogOut, Menu } from 'lucide-react';
import { Suspense, useState } from 'react';
import clsx from 'clsx';
import { useAuth } from '@/lib/auth-context';
import { NAV_GROUPS, isNavActive } from '@/lib/nav';
import { ThemeSwitcher } from '@/components/ui/ThemeSwitcher';
import { PageLoader } from '@/components/ui/PageLoader';
import { useAppRouter } from '@/hooks/useAppRouter';

function buildCrumbs(pathname: string, hasId: boolean, searchParams: URLSearchParams) {
  const crumbs: { label: string; href?: string }[] = [{ label: 'Admin', href: '/' }];

  for (const group of NAV_GROUPS) {
    for (const item of group.items) {
      if (isNavActive(pathname, item, searchParams)) {
        crumbs.push({ label: group.title });
        crumbs.push({ label: item.label, href: item.href });
        if (pathname.includes('/form')) {
          crumbs.push({ label: hasId ? 'Chỉnh sửa' : 'Thêm mới' });
        }
        return crumbs;
      }
    }
  }

  crumbs.push({ label: 'Bảng điều khiển' });
  return crumbs;
}

function ShellInner({ children }: { children: ReactNode }) {
  const { user, logout } = useAuth();
  const pathname = usePathname();
  const search = useSearchParams();
  const router = useAppRouter();
  const [open, setOpen] = useState(false);

  const crumbs = buildCrumbs(pathname, !!search.get('id'), search);
  const pageTitle = crumbs[crumbs.length - 1]?.label || 'Admin';

  const handleLogout = async () => {
    await logout();
    router.replace('/login');
  };

  return (
    <div className="shell">
      <div
        className={clsx('sidebar-backdrop', open && 'sidebar-backdrop--open')}
        onClick={() => setOpen(false)}
      />

      <aside className={clsx('sidebar', open && 'sidebar--open')}>
        <div className="sidebar__brand">
          <div className="sidebar__mark">V</div>
          <div>
            <div className="sidebar__wordmark">ViTravel</div>
            <div className="sidebar__tagline">Admin Console</div>
          </div>
        </div>

        <nav className="sidebar__nav">
          {NAV_GROUPS.map((group) => (
            <div key={group.key} className="sidebar__group">
              <div className="sidebar__group-title">{group.title}</div>
              {group.items.map((item) => {
                const Icon = item.icon;
                const active = isNavActive(pathname, item, search);
                return (
                  <Link
                    key={`${item.href}-${item.matchQuery ? JSON.stringify(item.matchQuery) : ''}`}
                    href={item.href}
                    className={clsx('sidebar__link', active && 'sidebar__link--active')}
                    onClick={() => setOpen(false)}
                  >
                    <Icon />
                    {item.label}
                  </Link>
                );
              })}
            </div>
          ))}
        </nav>

        {user ? (
          <div className="sidebar__user">
            <div className="sidebar__avatar">{user.name.slice(0, 1).toUpperCase()}</div>
            <div style={{ minWidth: 0, flex: 1 }}>
              <div className="sidebar__user-name">{user.name}</div>
              <div className="sidebar__user-email">{user.email}</div>
            </div>
            <button type="button" onClick={handleLogout} aria-label="Đăng xuất" title="Đăng xuất">
              <LogOut size={18} color="rgba(255,255,255,0.7)" />
            </button>
          </div>
        ) : null}
      </aside>

      <div className="shell__main">
        <header className="topbar">
          <div className="topbar__left">
            <button
              type="button"
              className="topbar__menu"
              onClick={() => setOpen(true)}
              aria-label="Mở menu"
            >
              <Menu size={18} />
            </button>

            <div className="topbar__trail">
              <nav className="breadcrumb" aria-label="Breadcrumb">
                {crumbs.map((c, i) => {
                  const last = i === crumbs.length - 1;
                  return (
                    <span key={`${c.label}-${i}`} className="breadcrumb__item">
                      {i > 0 ? (
                        <ChevronRight size={14} className="breadcrumb__sep" aria-hidden />
                      ) : null}
                      {c.href && !last ? (
                        <Link href={c.href} className="breadcrumb__link">
                          {c.label}
                        </Link>
                      ) : (
                        <span
                          className={clsx(
                            'breadcrumb__current',
                            last && 'breadcrumb__current--strong',
                          )}
                        >
                          {c.label}
                        </span>
                      )}
                    </span>
                  );
                })}
              </nav>
              <div className="topbar__page-title">{pageTitle}</div>
            </div>
          </div>

          <div className="topbar__right">
            <ThemeSwitcher />
          </div>
        </header>
        <main className="shell__content">{children}</main>
      </div>
    </div>
  );
}

export function AdminShell({ children }: { children: ReactNode }) {
  return (
    <Suspense fallback={<PageLoader label="Đang tải giao diện…" variant="screen" />}>
      <ShellInner>{children}</ShellInner>
    </Suspense>
  );
}
