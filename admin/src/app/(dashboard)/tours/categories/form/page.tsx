'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, FolderTree, Save, Search } from 'lucide-react';
import toast from '@/lib/toast';
import { categoriesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Select, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormCluster, FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import { ViewPublicButton } from '@/components/ui/ViewPublicButton';
import { publicPageUrl } from '@/lib/publicUrl';

type FormState = {
  name: string;
  type: string;
  country_id: string;
  sort: string;
  is_active: boolean;
  description: string;
  seo_intro: string;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  rating_aggregate_star: string;
  rating_aggregate_count: string;
  cover: ImageFieldState;
};

const empty: FormState = {
  name: '',
  type: 'theme',
  country_id: '',
  sort: '0',
  is_active: true,
  description: '',
  seo_intro: '',
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  rating_aggregate_star: '',
  rating_aggregate_count: '',
  cover: emptyImageField(),
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

function CategoryFormInner() {
  const search = useSearchParams();
  const idParam = search.get('id');
  const id = idParam ? Number(idParam) : null;
  const isNew = !id;
  const router = useRouter();
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<FormState>(empty);
  const [slugTouched, setSlugTouched] = useState(false);
  const snapshotRef = useRef(JSON.stringify(empty));
  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  const metaQuery = useQuery({
    queryKey: ['tour-categories-meta', locale],
    queryFn: () => categoriesApi.meta(locale),
  });

  const detailQuery = useQuery({
    queryKey: ['tour-category', id, locale],
    queryFn: () => categoriesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      name: d.name || '',
      type: d.type || 'theme',
      country_id: d.country_id ? String(d.country_id) : '',
      sort: String(d.sort || 0),
      is_active: !!d.is_active,
      description: d.description || '',
      seo_intro: d.seo_intro || '',
      seo_slug: d.seo?.slug || d.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      rating_aggregate_star:
        d.seo?.rating_aggregate_star != null ? String(d.seo.rating_aggregate_star) : '',
      rating_aggregate_count:
        d.seo?.rating_aggregate_count != null ? String(d.seo.rating_aggregate_count) : '',
      cover: emptyImageField(d.cover ?? null),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
    setSlugTouched(true);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        name: form.name,
        slug: form.seo_slug || slugify(form.name),
        type: form.type,
        country_id: form.country_id ? Number(form.country_id) : null,
        sort: Number(form.sort) || 0,
        is_active: form.is_active,
        description: form.description || null,
        seo_intro: form.seo_intro || null,
        seo_slug: form.seo_slug || slugify(form.name),
        seo_title: form.seo_title || form.name,
        seo_description: form.seo_description || null,
        rating_aggregate_star: form.rating_aggregate_star
          ? Number(form.rating_aggregate_star)
          : null,
        rating_aggregate_count: form.rating_aggregate_count
          ? Number(form.rating_aggregate_count)
          : null,
        cover_media_id: form.cover.media?.id ?? null,
        remove_cover: form.cover.remove,
        locale,
      };
      return isNew ? categoriesApi.create(payload) : categoriesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo danh mục' : 'Đã lưu danh mục');
      await qc.invalidateQueries({ queryKey: ['tour-categories'] });
      router.replace(`/tours/categories/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(key: K, value: FormState[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  return (
    <div>
      <PageHeader
        eyebrow="Tour"
        title={isNew ? 'Thêm chủ đề' : 'Chỉnh sửa chủ đề'}
        id={isNew ? null : id}
        description={isNew ? 'Tạo chủ đề lọc cho listing tour.' : undefined}
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
                href="/tours/categories/"
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
        hint={`Đang chỉnh bản dịch: ${locale.toUpperCase()} — tab cam = đang chọn · xám = chưa có bản dịch.`}
      />

      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-layout"
      >
        <div className="ui-form-layout__main ui-form-stack">
        <FormSection
          variant="priority"
          icon={Search}
          title="SEO"
          description="Slug full, meta và schema rating."
        >
          <FormCluster>
            <Input
              label="Slug"
              value={form.seo_slug}
              onChange={(e) => {
                setSlugTouched(true);
                set('seo_slug', e.target.value);
              }}
              required
            />
            <Input label="SEO title" value={form.seo_title} onChange={(e) => set('seo_title', e.target.value)} />
          </FormCluster>
          <FormCluster cols={1}>
            <Textarea
              label="SEO description"
              value={form.seo_description}
              onChange={(e) => set('seo_description', e.target.value)}
            />
          </FormCluster>
          <FormCluster title="Schema rating">
            <Input
              label="Điểm đánh giá"
              type="number"
              step="0.1"
              min={0}
              max={5}
              value={form.rating_aggregate_star}
              onChange={(e) => set('rating_aggregate_star', e.target.value)}
            />
            <Input
              label="Lượt đánh giá"
              type="number"
              min={0}
              value={form.rating_aggregate_count}
              onChange={(e) => set('rating_aggregate_count', e.target.value)}
            />
          </FormCluster>
        </FormSection>

        <FormSection
          icon={FolderTree}
          title="Thông tin danh mục"
          description="Tên, loại và gắn quốc gia (select đơn)."
        >
          <FormCluster title="Định danh">
            <Input
              label="Tên"
              value={form.name}
              onChange={(e) => {
                const name = e.target.value;
                set('name', name);
                if (!slugTouched) set('seo_slug', slugify(name));
              }}
              required
            />
          </FormCluster>

          <FormCluster title="Phân loại">
            <Select
              label="Loại"
              value={form.type}
              onChange={(v) => set('type', v)}
              required
              options={(metaQuery.data?.type_options ?? []).map((t) => ({
                value: t.value,
                label: t.label,
              }))}
            />
            <Select
              label="Quốc gia"
              value={form.country_id}
              onChange={(v) => set('country_id', v)}
              placeholder="Không gắn quốc gia"
              searchable
              options={(metaQuery.data?.countries ?? []).map((c) => ({
                value: c.id,
                label: c.name || `#${c.id}`,
              }))}
            />
            <Input label="Thứ tự" type="number" value={form.sort} onChange={(e) => set('sort', e.target.value)} />
          </FormCluster>

          <div className="ui-form-flags">
            <Switch label="Đang hoạt động" checked={form.is_active} onChange={(v) => set('is_active', v)} />
          </div>

          <FormCluster cols={1}>
            <Textarea label="Mô tả" value={form.description} onChange={(e) => set('description', e.target.value)} />
            <Textarea label="SEO intro" value={form.seo_intro} onChange={(e) => set('seo_intro', e.target.value)} />
          </FormCluster>
        </FormSection>

        <div className="ui-form-footer">
          <Link href="/tours/categories/">
            <Button type="button" variant="secondary">
              Hủy
            </Button>
          </Link>
          <Button type="submit" loading={save.isPending}>
            <Save size={17} />
            Lưu danh mục
          </Button>
        </div>
        </div>

        <aside className="ui-form-layout__aside">
          <div className="ui-media-card">
            <div className="ui-media-card__head">
              <h3 className="ui-media-card__title">Ảnh đại diện</h3>
              <p className="ui-media-card__desc">Thumbnail listing danh mục tour.</p>
            </div>
            <ImageField
              ariaLabel="Ảnh đại diện danh mục"
              folder="tour_categories"
              aspectRatio="3 / 2"
              variant="card"
              value={form.cover}
              onChange={(cover) => set('cover', cover)}
            />
          </div>
        </aside>
      </form>
    </div>
  );
}

export default function CategoryFormPage() {
  return (
    <Suspense fallback={<div>Đang tải form…</div>}>
      <CategoryFormInner />
    </Suspense>
  );
}
