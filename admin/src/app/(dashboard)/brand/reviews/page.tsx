'use client';

import { reviewsApi } from '@/lib/services';
import { Badge } from '@/components/ui/Page';
import { ResourceListPage } from '@/components/admin/ResourceListPage';

export default function ReviewsPage() {
  return (
    <ResourceListPage
      eyebrow="Thương hiệu"
      title="Cảm nhận KH"
      queryKey="reviews"
      createHref="/brand/reviews/form/"
      editHref={(id) => `/brand/reviews/form/?id=${id}`}
      createLabel="Thêm cảm nhận"
      listFn={(q) => reviewsApi.list(q)}
      removeFn={(id) => reviewsApi.remove(id)}
      titleOf={(r) => String(r.author_name || `#${r.id}`)}
      thumbOf={(r) => {
        const a = r.avatar as { url_thumb?: string; url?: string } | null | undefined;
        return a?.url_thumb || a?.url || null;
      }}
      statusOptions={[
        { value: 'published', label: 'Published' },
        { value: 'draft', label: 'Draft' },
        { value: 'hidden', label: 'Hidden' },
      ]}
      badgeOf={(r) => <Badge>{String(r.status || '')}</Badge>}
    />
  );
}
