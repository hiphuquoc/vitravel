'use client';

import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { videosApi } from '@/lib/services';

export default function VideoFormPage() {
  return (
    <ResourceFormPage
      eyebrow="Thương hiệu"
      listHref="/brand/videos/"
      queryKey="videos"
      titleNew="Thêm video"
      titleEdit="Sửa video"
      empty={{
        title: '',
        description: '',
        youtube_id: '',
        video_url: '',
        duration: '',
        tag: '',
        sort: '0',
        status: 'draft',
        show_on_home: false,
        country_id: '',
      }}
      fields={[
        { key: 'title', label: 'Tiêu đề' },
        { key: 'description', label: 'Mô tả', type: 'textarea' },
        { key: 'youtube_id', label: 'YouTube ID / URL' },
        { key: 'video_url', label: 'Video URL' },
        { key: 'duration', label: 'Duration' },
        { key: 'tag', label: 'Tag' },
        { key: 'status', label: 'Status' },
        { key: 'sort', label: 'Sort', type: 'number' },
        { key: 'show_on_home', label: 'Hiện trang chủ', type: 'switch' },
      ]}
      getFn={(id, locale) => videosApi.get(id, locale)}
      createFn={(b) => videosApi.create(b)}
      updateFn={(id, b) => videosApi.update(id, b)}
      mapDetail={(d) => ({
        title: d.title || '',
        description: d.description || '',
        youtube_id: d.youtube_id || '',
        video_url: d.video_url || '',
        duration: d.duration || '',
        tag: d.tag || '',
        sort: String(d.sort || 0),
        status: d.status || 'draft',
        show_on_home: !!d.show_on_home,
        country_id: d.country_id ? String(d.country_id) : '',
      })}
      mapPayload={(form, locale) => ({
        ...form,
        sort: Number(form.sort) || 0,
        country_id: form.country_id ? Number(form.country_id) : null,
        locale,
      })}
    />
  );
}
