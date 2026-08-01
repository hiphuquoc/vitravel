'use client';

import { countriesApi } from '@/lib/services';
import { Badge } from '@/components/ui/Page';
import { ResourceListPage } from '@/components/admin/ResourceListPage';

export default function DestinationsPage() {
  return (
    <ResourceListPage
      eyebrow="Sản phẩm"
      title="Danh mục Tour"
      description="Quốc gia / điểm đến — SEO parent cho gói tour."
      queryKey="countries"
      createHref="/tours/destinations/form/"
      editHref={(id) => `/tours/destinations/form/?id=${id}`}
      createLabel="Thêm điểm đến"
      unitLabel="quốc gia"
      listFn={(q) => countriesApi.list(q)}
      removeFn={(id) => countriesApi.remove(id)}
      titleOf={(r) => String(r.name || r.code || `#${r.id}`)}
      slugOf={(r) => (r.seo as { slug_full?: string } | undefined)?.slug_full}
      thumbOf={(r) => {
        const b = r.banner as { url_thumb?: string; url?: string } | null | undefined;
        return b?.url_thumb || b?.url || null;
      }}
      badgeOf={(r) => (
        <Badge tone={r.is_active ? 'success' : 'neutral'}>
          {r.is_active ? 'Đang bật' : 'Tắt'}
        </Badge>
      )}
    />
  );
}
