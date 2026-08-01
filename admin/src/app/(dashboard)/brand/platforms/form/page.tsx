'use client';

import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { reviewPlatformsApi } from '@/lib/services';

export default function PlatformFormPage() {
  return (
    <ResourceFormPage
      eyebrow="Thương hiệu"
      listHref="/brand/platforms/"
      queryKey="review-platforms"
      titleNew="Thêm nền tảng"
      titleEdit="Sửa nền tảng"
      withLocale={false}
      empty={{
        code: '',
        name: '',
        rating: '',
        review_count: '',
        url: '',
        quote: '',
        link_label: '',
        sort: '0',
        is_active: true,
        show_on_home: false,
      }}
      fields={[
        { key: 'code', label: 'Code' },
        { key: 'name', label: 'Name' },
        { key: 'rating', label: 'Rating', type: 'number' },
        { key: 'review_count', label: 'Review count', type: 'number' },
        { key: 'url', label: 'URL' },
        { key: 'quote', label: 'Quote', type: 'textarea' },
        { key: 'link_label', label: 'Link label' },
        { key: 'sort', label: 'Sort', type: 'number' },
        { key: 'is_active', label: 'Active', type: 'switch' },
        { key: 'show_on_home', label: 'Home', type: 'switch' },
      ]}
      getFn={(id) => reviewPlatformsApi.get(id)}
      createFn={(b) => reviewPlatformsApi.create(b)}
      updateFn={(id, b) => reviewPlatformsApi.update(id, b)}
      mapDetail={(d) => ({
        code: d.code || '',
        name: d.name || '',
        rating: d.rating != null ? String(d.rating) : '',
        review_count: d.review_count != null ? String(d.review_count) : '',
        url: d.url || '',
        quote: d.quote || '',
        link_label: d.link_label || '',
        sort: String(d.sort || 0),
        is_active: !!d.is_active,
        show_on_home: !!d.show_on_home,
      })}
      mapPayload={(form) => ({
        ...form,
        rating: form.rating ? Number(form.rating) : null,
        review_count: form.review_count ? Number(form.review_count) : null,
        sort: Number(form.sort) || 0,
      })}
    />
  );
}
