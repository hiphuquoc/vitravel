'use client';

import { videosApi } from '@/lib/services';
import { Badge } from '@/components/ui/Page';
import { ResourceListPage } from '@/components/admin/ResourceListPage';

export default function VideosPage() {
  return (
    <ResourceListPage
      eyebrow="Thương hiệu"
      title="Video"
      queryKey="videos"
      createHref="/brand/videos/form/"
      editHref={(id) => `/brand/videos/form/?id=${id}`}
      createLabel="Thêm video"
      listFn={(q) => videosApi.list(q)}
      removeFn={(id) => videosApi.remove(id)}
      titleOf={(r) => String(r.title || r.youtube_id || `#${r.id}`)}
      thumbOf={(r) => {
        const t = r.thumbnail as { url_thumb?: string; url?: string } | null | undefined;
        return t?.url_thumb || t?.url || null;
      }}
      statusOptions={[
        { value: 'draft', label: 'Draft' },
        { value: 'published', label: 'Published' },
      ]}
      badgeOf={(r) => <Badge>{String(r.status || '')}</Badge>}
    />
  );
}
