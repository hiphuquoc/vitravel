'use client';

import { serviceCategoriesApi } from '@/lib/services';
import { Badge } from '@/components/ui/Page';
import { ResourceListPage } from '@/components/admin/ResourceListPage';

export default function ServiceCategoriesPage() {
  return (
    <ResourceListPage
      eyebrow="Sản phẩm"
      title="Danh mục dịch vụ"
      queryKey="service-categories"
      createHref="/services/categories/form/"
      editHref={(id) => `/services/categories/form/?id=${id}`}
      createLabel="Thêm danh mục"
      unitLabel="danh mục"
      listFn={(q) => serviceCategoriesApi.list(q)}
      removeFn={(id) => serviceCategoriesApi.remove(id)}
      titleOf={(r) => String(r.name || `#${r.id}`)}
      slugOf={(r) => (r.seo as { slug_full?: string } | undefined)?.slug_full}
      thumbOf={(r) => {
        const b = r.banner as { url_thumb?: string; url?: string } | null | undefined;
        return b?.url_thumb || b?.url || null;
      }}
      badgeOf={(r) => (
        <>
          <Badge>{String(r.cluster_label || r.cluster || '')}</Badge>
          <Badge tone={r.is_active ? 'success' : 'neutral'}>
            {r.is_active ? 'Đang bật' : 'Tắt'}
          </Badge>
        </>
      )}
    />
  );
}
