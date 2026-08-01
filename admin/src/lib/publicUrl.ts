/**
 * Build public site URL from SEO slug_full (+ locale prefix when non-default).
 * Prefer NEXT_PUBLIC_SITE_ORIGIN (dev: same as ADMIN_API_ORIGIN).
 */
export function publicPageUrl(
  slugFull: string | null | undefined,
  locale = 'vi',
  defaultLocale = 'vi',
): string | null {
  if (!slugFull || !String(slugFull).trim()) return null;

  const path = `/${String(slugFull).replace(/^\/+/, '')}`;
  const localized =
    locale && locale !== defaultLocale ? `/${locale}${path === '/' ? '' : path}` : path;

  const origin = (process.env.NEXT_PUBLIC_SITE_ORIGIN || '').replace(/\/$/, '');
  if (origin) return `${origin}${localized}`;

  if (typeof window !== 'undefined') {
    return `${window.location.origin}${localized}`;
  }

  return localized;
}
