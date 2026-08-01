'use client';

import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { blogCategoriesApi } from '@/lib/services';

export default function BlogCategoryFormPage() {
  return (
    <ResourceFormPage
      eyebrow="Nội dung"
      listHref="/content/blog-categories/"
      queryKey="blog-categories"
      titleNew="Thêm chuyên mục"
      titleEdit="Sửa chuyên mục"
      empty={{
        name: '',
        slug: '',
        seo_intro: '',
        sort: '0',
        is_active: true,
        seo_slug: '',
        seo_title: '',
        seo_description: '',
        country_id: '',
      }}
      fields={[
        { key: 'name', label: 'Tên' },
        { key: 'slug', label: 'Slug' },
        { key: 'seo_intro', label: 'Intro SEO', type: 'textarea' },
        { key: 'sort', label: 'Sort', type: 'number' },
        { key: 'is_active', label: 'Active', type: 'switch' },
        { key: 'seo_slug', label: 'SEO slug' },
        { key: 'seo_title', label: 'SEO title' },
        { key: 'seo_description', label: 'SEO description', type: 'textarea' },
      ]}
      getFn={(id, locale) => blogCategoriesApi.get(id, locale)}
      createFn={(body) => blogCategoriesApi.create(body)}
      updateFn={(id, body) => blogCategoriesApi.update(id, body)}
      mapDetail={(d) => ({
        name: d.name || '',
        slug: d.slug || '',
        seo_intro: d.seo_intro || '',
        sort: String(d.sort || 0),
        is_active: !!d.is_active,
        seo_slug: (d.seo as { slug?: string } | undefined)?.slug || '',
        seo_title: (d.seo as { title?: string } | undefined)?.title || '',
        seo_description: (d.seo as { description?: string } | undefined)?.description || '',
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
