'use client';

import { useQuery } from '@tanstack/react-query';
import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { blogCategoriesApi } from '@/lib/services';
import { ACTIVE_STATUS_OPTIONS, activeStatusValue, parseActiveStatus } from '@/components/ui/SeoBox';
import { Select } from '@/components/ui/Field';

export default function BlogCategoryFormPage() {
  const metaQuery = useQuery({
    queryKey: ['blog-categories-meta'],
    queryFn: () => blogCategoriesApi.meta(),
  });

  return (
    <ResourceFormPage
      eyebrow="Nội dung"
      listHref="/content/blog-categories/"
      queryKey="blog-categories"
      titleNew="Thêm chuyên mục"
      titleEdit="Sửa chuyên mục"
      seoParents={(metaQuery.data?.seo_parents as import('@/components/ui/SeoBox').SeoParentOption[]) ?? []}
      empty={{
        name: '',
        seo_intro: '',
        sort: '0',
        is_active: true,
        seo_slug: '',
        seo_title: '',
        seo_description: '',
        seo_parent_id: '',
        country_id: '',
      }}
      fields={[
        { key: 'name', label: 'Tên' },
        { key: 'seo_intro', label: 'Intro SEO', type: 'textarea' },
        { key: 'sort', label: 'Sort', type: 'number' },
        {
          key: 'is_active',
          label: 'Trạng thái',
          type: 'custom',
          render: (v, set) => (
            <Select
              label="Trạng thái"
              value={activeStatusValue(!!v)}
              onChange={(next) => set(parseActiveStatus(next))}
              options={[...ACTIVE_STATUS_OPTIONS]}
            />
          ),
        },
      ]}
      getFn={(id, locale) => blogCategoriesApi.get(id, locale)}
      createFn={(body) => blogCategoriesApi.create(body)}
      updateFn={(id, body) => blogCategoriesApi.update(id, body)}
      mapDetail={(d) => {
        const seo = d.seo as
          | { slug?: string; title?: string; description?: string; parent_id?: number }
          | undefined;
        return {
          name: d.name || '',
          seo_intro: d.seo_intro || '',
          sort: String(d.sort || 0),
          is_active: !!d.is_active,
          seo_slug: seo?.slug || String(d.slug || ''),
          seo_title: seo?.title || '',
          seo_description: seo?.description || '',
          seo_parent_id: seo?.parent_id ? String(seo.parent_id) : '',
          country_id: d.country_id ? String(d.country_id) : '',
        };
      }}
      mapPayload={(form, locale) => {
        const slug = String(form.seo_slug || form.name || '');
        return {
          ...form,
          slug,
          seo_slug: slug,
          sort: Number(form.sort) || 0,
          country_id: form.country_id ? Number(form.country_id) : null,
          seo_parent_id: form.seo_parent_id ? Number(form.seo_parent_id) : null,
          locale,
        };
      }}
    />
  );
}
