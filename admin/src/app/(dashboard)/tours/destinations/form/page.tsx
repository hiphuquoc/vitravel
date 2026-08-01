'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { countriesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Select, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import { ViewPublicButton } from '@/components/ui/ViewPublicButton';
import { publicPageUrl } from '@/lib/publicUrl';

type FormState = {
  code: string;
  name: string;
  slug: string;
  tagline: string;
  intro_text: string;
  long_form_content: string;
  sort: string;
  home_grid_size: string;
  is_active: boolean;
  show_in_menu: boolean;
  show_in_customize_form: boolean;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  seo_keywords: string;
  seo_parent_id: string;
  rating_aggregate_star: string;
  rating_aggregate_count: string;
  banner: ImageFieldState;
  listing_banner: ImageFieldState;
};

const empty: FormState = {
  code: '',
  name: '',
  slug: '',
  tagline: '',
  intro_text: '',
  long_form_content: '',
  sort: '0',
  home_grid_size: 'medium',
  is_active: true,
  show_in_menu: true,
  show_in_customize_form: true,
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  seo_keywords: '',
  seo_parent_id: '',
  rating_aggregate_star: '',
  rating_aggregate_count: '',
  banner: emptyImageField(),
  listing_banner: emptyImageField(),
};

function slugify(value: string): string {
  return value
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
    queryKey: ['countries-meta', locale],
    queryFn: () => countriesApi.meta(locale),
  });
  const detailQuery = useQuery({
    queryKey: ['country', id, locale],
    queryFn: () => countriesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      code: d.code || '',
      name: d.name || '',
      slug: d.slug || '',
      tagline: d.tagline || '',
      intro_text: d.intro_text || '',
      long_form_content: d.long_form_content || '',
      sort: String(d.sort || 0),
      home_grid_size: d.home_grid_size || 'medium',
      is_active: !!d.is_active,
      show_in_menu: !!d.show_in_menu,
      show_in_customize_form: !!d.show_in_customize_form,
      seo_slug: d.seo?.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      seo_keywords: d.seo?.keywords || '',
      seo_parent_id: d.seo?.parent_id ? String(d.seo.parent_id) : '',
      rating_aggregate_star:
        d.seo?.rating_aggregate_star != null ? String(d.seo.rating_aggregate_star) : '',
      rating_aggregate_count:
        d.seo?.rating_aggregate_count != null ? String(d.seo.rating_aggregate_count) : '',
      banner: emptyImageField(d.banner),
      listing_banner: emptyImageField(d.listing_banner),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        code: form.code,
        name: form.name,
        slug: form.slug || slugify(form.name),
        tagline: form.tagline || null,
        intro_text: form.intro_text || null,
        long_form_content: form.long_form_content || null,
        sort: Number(form.sort) || 0,
        home_grid_size: form.home_grid_size,
        is_active: form.is_active,
        show_in_menu: form.show_in_menu,
        show_in_customize_form: form.show_in_customize_form,
        seo_slug: form.seo_slug || form.slug || slugify(form.name),
        seo_title: form.seo_title || form.name,
        seo_description: form.seo_description || null,
        seo_keywords: form.seo_keywords || null,
        seo_parent_id: form.seo_parent_id ? Number(form.seo_parent_id) : null,
        rating_aggregate_star: form.rating_aggregate_star
          ? Number(form.rating_aggregate_star)
          : null,
        rating_aggregate_count: form.rating_aggregate_count
          ? Number(form.rating_aggregate_count)
          : null,
        banner_media_id: form.banner.media?.id ?? null,
        remove_banner: form.banner.remove,
        listing_banner_media_id: form.listing_banner.media?.id ?? null,
        remove_listing_banner: form.listing_banner.remove,
        locale,
      };
      return isNew ? countriesApi.create(payload) : countriesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: ['countries'] });
      router.replace(`/tours/destinations/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(key: K, value: FormState[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  return (
    <div>
      <PageHeader
        eyebrow="Sản phẩm"
        title={isNew ? 'Thêm điểm đến' : 'Chỉnh sửa điểm đến'}
        id={isNew ? null : id}
        actions={
          <HeadActions
            primary={
              <ViewPublicButton
                href={publicPageUrl(
                  detailQuery.data?.seo?.slug_full,
                  locale,
                  metaQuery.data?.default_locale || 'vi',
                )}
              />
            }
            secondary={
              <HeadSecondary
                href="/tours/destinations/"
                icon={ArrowLeft}
                title="Quay lại"
                subtitle="Về danh sách"
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
            <Input label="Mã" value={form.code} onChange={(e) => set('code', e.target.value)} />
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
            <Input
              label="Tagline"
              value={form.tagline}
              onChange={(e) => set('tagline', e.target.value)}
            />
            <Textarea
              label="Intro"
              value={form.intro_text}
              onChange={(e) => set('intro_text', e.target.value)}
            />
            <Textarea
              label="Nội dung dài"
              value={form.long_form_content}
              onChange={(e) => set('long_form_content', e.target.value)}
            />
            <Input
              label="Sort"
              type="number"
              value={form.sort}
              onChange={(e) => set('sort', e.target.value)}
            />
            <Select
              label="Home grid"
              value={form.home_grid_size}
              onChange={(v) => set('home_grid_size', v)}
              options={(metaQuery.data?.home_grid_sizes ?? []).map((o) => ({
                value: o.value,
                label: o.label,
              }))}
            />
            <Switch
              label="Active"
              checked={form.is_active}
              onChange={(v) => set('is_active', v)}
            />
            <Switch
              label="Hiện menu"
              checked={form.show_in_menu}
              onChange={(v) => set('show_in_menu', v)}
            />
            <Switch
              label="Form customize"
              checked={form.show_in_customize_form}
              onChange={(v) => set('show_in_customize_form', v)}
            />
          </FormSection>
          <FormSection title="SEO">
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
            <Input
              label="Keywords"
              value={form.seo_keywords}
              onChange={(e) => set('seo_keywords', e.target.value)}
            />
            <Select
              label="SEO parent"
              value={form.seo_parent_id}
              onChange={(v) => set('seo_parent_id', v)}
              placeholder="Hub mặc định"
              options={(metaQuery.data?.seo_parents ?? []).map((p) => ({
                value: String(p.id),
                label: p.label,
              }))}
            />
          </FormSection>
          <FormSection title="Media">
            <ImageField
              label="Banner"
              folder="countries"
              value={form.banner}
              onChange={(v) => set('banner', v)}
            />
            <ImageField
              label="Listing banner"
              folder="countries"
              value={form.listing_banner}
              onChange={(v) => set('listing_banner', v)}
            />
          </FormSection>
        </div>
        <div className="ui-form-layout__side">
          <Button type="submit" disabled={save.isPending}>
            <Save size={16} />
            {save.isPending ? 'Đang lưu…' : 'Lưu'}
          </Button>
        </div>
      </form>
    </div>
  );
}

export default function DestinationFormPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <FormInner />
    </Suspense>
  );
}
