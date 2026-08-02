'use client';

import { useQuery } from '@tanstack/react-query';
import { ResourceFormPage } from '@/components/admin/ResourceFormPage';
import { teamMembersApi } from '@/lib/services';
import type { SeoParentOption } from '@/components/ui/SeoBox';

export default function TeamFormPage() {
  const metaQuery = useQuery({
    queryKey: ['team-members-meta'],
    queryFn: () => teamMembersApi.meta(),
  });

  return (
    <ResourceFormPage
      eyebrow="Thương hiệu"
      listHref="/brand/team/"
      queryKey="team-members"
      titleNew="Thêm thành viên"
      titleEdit="Sửa thành viên"
      seoParents={(metaQuery.data?.seo_parents as SeoParentOption[] | undefined) ?? []}
      empty={{
        name: '',
        role: '',
        department: '',
        short_bio: '',
        bio_html: '',
        phone: '',
        email: '',
        area: '',
        years_experience: '',
        languages: '',
        sort: '0',
        is_active: true,
        show_on_home: false,
        is_verified: false,
        seo_slug: '',
        seo_title: '',
        seo_description: '',
        seo_parent_id: '',
      }}
      fields={[
        { key: 'name', label: 'Họ tên' },
        { key: 'role', label: 'Vai trò' },
        { key: 'department', label: 'Phòng ban' },
        { key: 'short_bio', label: 'Bio ngắn', type: 'textarea' },
        { key: 'bio_html', label: 'Bio HTML', type: 'textarea' },
        { key: 'phone', label: 'Phone' },
        { key: 'email', label: 'Email' },
        { key: 'area', label: 'Khu vực' },
        { key: 'years_experience', label: 'Số năm KN', type: 'number' },
        { key: 'languages', label: 'Ngôn ngữ (mỗi dòng)', type: 'textarea' },
        { key: 'sort', label: 'Sort', type: 'number' },
        { key: 'is_active', label: 'Active', type: 'switch' },
        { key: 'show_on_home', label: 'Hiện trang chủ', type: 'switch' },
      ]}
      getFn={(id, locale) => teamMembersApi.get(id, locale)}
      createFn={(b) => teamMembersApi.create(b)}
      updateFn={(id, b) => teamMembersApi.update(id, b)}
      mapDetail={(d) => {
        const seo = d.seo as
          | { slug?: string; title?: string; description?: string; parent_id?: number }
          | undefined;
        return {
          name: d.name || '',
          role: d.role || '',
          department: d.department || '',
          short_bio: d.short_bio || '',
          bio_html: d.bio_html || '',
          phone: d.phone || '',
          email: d.email || '',
          area: d.area || '',
          years_experience: d.years_experience != null ? String(d.years_experience) : '',
          languages: d.languages || '',
          sort: String(d.sort || 0),
          is_active: !!d.is_active,
          show_on_home: !!d.show_on_home,
          is_verified: !!d.is_verified,
          seo_slug: seo?.slug || '',
          seo_title: seo?.title || '',
          seo_description: seo?.description || '',
          seo_parent_id: seo?.parent_id ? String(seo.parent_id) : '',
        };
      }}
      mapPayload={(form, locale) => ({
        ...form,
        sort: Number(form.sort) || 0,
        years_experience: form.years_experience ? Number(form.years_experience) : null,
        seo_parent_id: form.seo_parent_id ? Number(form.seo_parent_id) : null,
        locale,
      })}
    />
  );
}
