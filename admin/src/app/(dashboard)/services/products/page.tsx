'use client';

import { Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import { servicesApi } from '@/lib/services';
import { Badge } from '@/components/ui/Page';
import { ResourceListPage } from '@/components/admin/ResourceListPage';
import { serviceClusterLabel } from '@/lib/nav';

function Inner() {
  const search = useSearchParams();
  const cluster = search.get('cluster') || '';
  const label = serviceClusterLabel(cluster);

  return (
    <ResourceListPage
      eyebrow={label}
      title={`Sản phẩm — ${label}`}
      description="Sản phẩm thuộc cụm dịch vụ đang chọn."
      queryKey={`services-${cluster || 'all'}`}
      createHref={
        cluster ? `/services/products/form/?cluster=${cluster}` : '/services/products/form/'
      }
      editHref={(id) =>
        cluster
          ? `/services/products/form/?id=${id}&cluster=${cluster}`
          : `/services/products/form/?id=${id}`
      }
      createLabel="Thêm sản phẩm"
      unitLabel="sản phẩm"
      extraQuery={cluster ? { cluster } : undefined}
      listFn={(q) => servicesApi.list(q)}
      removeFn={(id) => servicesApi.remove(id)}
      titleOf={(r) => String(r.title || r.code || `#${r.id}`)}
      slugOf={(r) => (r.seo as { slug_full?: string } | undefined)?.slug_full}
      thumbOf={(r) => {
        const c = r.cover as { url_thumb?: string; url?: string } | null | undefined;
        return c?.url_thumb || c?.url || null;
      }}
      statusOptions={[
        { value: 'draft', label: 'Nháp' },
        { value: 'published', label: 'Xuất bản' },
        { value: 'archived', label: 'Lưu trữ' },
      ]}
      badgeOf={(r) => <Badge>{String(r.status || '')}</Badge>}
    />
  );
}

export default function ServicesProductsPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <Inner />
    </Suspense>
  );
}
