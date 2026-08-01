'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { serviceCategoriesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Select, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';

type FormState = {
  cluster: string;
  name: string;
  slug: string;
  intro: string;
  sort: string;
  is_active: boolean;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  seo_keywords: string;
  seo_parent_id: string;
  banner: ImageFieldState;
};

const empty: FormState = {
  cluster: 'experiences',
  name: '',
  slug: '',
  intro: '',
  sort: '0',
  is_active: true,
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  seo_keywords: '',
  seo_parent_id: '',
  banner: emptyImageField(),
};

function slugify(v: string) {
  return v
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
}

function FormInner() {
  const search = useSearchParams();
  const id = search.get('id') ? Number(search.get('id')) : null;
  const isNew = !id;
  const router = useRouter();
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<FormState>(empty);
  const snapshotRef = useRef(JSON.stringify(empty));
  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  const metaQuery = useQuery({
    queryKey: ['service-categories-meta', locale, form.cluster],
    queryFn: () => serviceCategoriesApi.meta(locale, form.cluster),
  });
  const detailQuery = useQuery({
    queryKey: ['service-category', id, locale],
    queryFn: () => serviceCategoriesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      cluster: d.cluster || 'experiences',
      name: d.name || '',
      slug: d.slug || '',
      intro: d.intro || '',
      sort: String(d.sort || 0),
      is_active: !!d.is_active,
      seo_slug: d.seo?.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      seo_keywords: d.seo?.keywords || '',
      seo_parent_id: d.seo?.parent_id ? String(d.seo.parent_id) : '',
      banner: emptyImageField(d.banner),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        cluster: form.cluster,
        name: form.name,
        slug: form.slug || slugify(form.name),
        intro: form.intro || null,
        sort: Number(form.sort) || 0,
        is_active: form.is_active,
        seo_slug: form.seo_slug || form.slug || slugify(form.name),
        seo_title: form.seo_title || form.name,
        seo_description: form.seo_description || null,
        seo_keywords: form.seo_keywords || null,
        seo_parent_id: form.seo_parent_id ? Number(form.seo_parent_id) : null,
        banner_media_id: form.banner.media?.id ?? null,
        remove_banner: form.banner.remove,
        locale,
      };
      return isNew
        ? serviceCategoriesApi.create(payload)
        : serviceCategoriesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: ['service-categories'] });
      router.replace(`/services/categories/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(k: K, v: FormState[K]) =>
    setForm((p) => ({ ...p, [k]: v }));

  return (
    <div>
      <PageHeader
        eyebrow="Sản phẩm"
        title={isNew ? 'Thêm danh mục DV' : 'Sửa danh mục DV'}
        id={isNew ? null : id}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href="/services/categories/"
                icon={ArrowLeft}
                title="Quay lại"
                subtitle="Danh sách"
              />
            }
          />
        }
      />
      <LocaleSwitcher
        languages={metaQuery.data?.languages ?? []}
        value={locale}
        onChange={(code) => setLocale(code, { confirmIfDirty: true, isDirty })}
        translatedLocales={detailQuery.data?.translated_locales ?? (isNew ? [] : undefined)}
      />
      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-layout"
      >
        <div className="ui-form-layout__main ui-form-stack">
          <FormSection title="Thông tin">
            <Select
              label="Cluster"
              value={form.cluster}
              onChange={(v) => set('cluster', v)}
              options={(metaQuery.data?.clusters ?? []).map((c) => ({
                value: c.value,
                label: c.label,
              }))}
            />
            <Input
              label="Tên"
              value={form.name}
              onChange={(e) => {
                set('name', e.target.value);
                if (isNew) {
                  set('slug', slugify(e.target.value));
                  set('seo_slug', slugify(e.target.value));
                }
              }}
            />
            <Input label="Slug" value={form.slug} onChange={(e) => set('slug', e.target.value)} />
            <Textarea
              label="Intro"
              value={form.intro}
              onChange={(e) => set('intro', e.target.value)}
            />
            <Input
              label="Sort"
              type="number"
              value={form.sort}
              onChange={(e) => set('sort', e.target.value)}
            />
            <Switch label="Active" checked={form.is_active} onChange={(v) => set('is_active', v)} />
            <Input
              label="SEO slug"
              value={form.seo_slug}
              onChange={(e) => set('seo_slug', e.target.value)}
            />
            <Input
              label="SEO title"
              value={form.seo_title}
              onChange={(e) => set('seo_title', e.target.value)}
            />
            <Textarea
              label="SEO description"
              value={form.seo_description}
              onChange={(e) => set('seo_description', e.target.value)}
            />
            <ImageField
              label="Banner"
              folder="service_categories"
              value={form.banner}
              onChange={(v) => set('banner', v)}
            />
          </FormSection>
        </div>
        <div className="ui-form-layout__side">
          <Button type="submit" disabled={save.isPending}>
            <Save size={16} />
            Lưu
          </Button>
        </div>
      </form>
    </div>
  );
}

export default function ServiceCategoryFormPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <FormInner />
    </Suspense>
  );
}
