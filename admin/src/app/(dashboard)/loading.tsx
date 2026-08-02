import { PageLoader } from '@/components/ui/PageLoader';

/** Suspense fallback khi chuyển route trong dashboard. */
export default function DashboardLoading() {
  return <PageLoader label="Đang tải trang…" variant="page" />;
}
