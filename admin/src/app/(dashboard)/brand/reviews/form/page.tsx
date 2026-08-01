'use client';

import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { reviewsApi } from '@/lib/services';

export default function ReviewFormPage() {
  return (
    <ResourceFormPage
      eyebrow="Thương hiệu"
      listHref="/brand/reviews/"
      queryKey="reviews"
      titleNew="Thêm cảm nhận"
      titleEdit="Sửa cảm nhận"
      withLocale={false}
      empty={{
        author_name: '',
        author_country: '',
        author_country_code: '',
        rating: '5',
        question_title: '',
        content: '',
        sort: '0',
        status: 'published',
        is_featured: false,
        show_on_home: false,
      }}
      fields={[
        { key: 'author_name', label: 'Tên KH' },
        { key: 'author_country', label: 'Quốc gia' },
        { key: 'author_country_code', label: 'Country code' },
        { key: 'rating', label: 'Rating', type: 'number' },
        { key: 'question_title', label: 'Tiêu đề' },
        { key: 'content', label: 'Nội dung', type: 'textarea' },
        { key: 'status', label: 'Status' },
        { key: 'sort', label: 'Sort', type: 'number' },
        { key: 'is_featured', label: 'Featured', type: 'switch' },
        { key: 'show_on_home', label: 'Home', type: 'switch' },
      ]}
      getFn={(id) => reviewsApi.get(id)}
      createFn={(b) => reviewsApi.create(b)}
      updateFn={(id, b) => reviewsApi.update(id, b)}
      mapDetail={(d) => ({
        author_name: d.author_name || '',
        author_country: d.author_country || '',
        author_country_code: d.author_country_code || '',
        rating: String(d.rating || 5),
        question_title: d.question_title || '',
        content: d.content || '',
        sort: String(d.sort || 0),
        status: d.status || 'published',
        is_featured: !!d.is_featured,
        show_on_home: !!d.show_on_home,
      })}
      mapPayload={(form) => ({
        ...form,
        rating: Number(form.rating) || 5,
        sort: Number(form.sort) || 0,
      })}
    />
  );
}
