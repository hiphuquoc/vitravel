'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Anchor, ArrowLeft, Save, Search } from 'lucide-react';
import toast from '@/lib/toast';
import { cruiseTypesApi, metaApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormCluster, FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import { ViewPublicButton } from '@/components/ui/ViewPublicButton';
import { publicPageUrl } from '@/lib/publicUrl';

type FormState = {
  name: string;
  sort: string;
  is_active: boolean;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  rating_aggregate_star: string;
  rating_aggregate_count: string;
  banner: ImageFieldState;
};

const empty: FormState = {
  name: '',
  sort: '0',
  is_active: true,
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  rating_aggregate_star: '',
  rating_aggregate_count: '',
  banner: emptyImageField(),
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

function CruiseTypeFormInner() {
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

  const languagesQuery = useQuery({
    queryKey: ['meta-languages'],
    queryFn: () => metaApi.languages(),
  });

  const detailQuery = useQuery({
    queryKey: ['cruise-type', id, locale],
    queryFn: () => cruiseTypesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      name: d.name || '',
      sort: String(d.sort || 0),
      is_active: !!d.is_active,
      seo_slug: d.seo?.slug || d.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      rating_aggregate_star:
        d.seo?.rating_aggregate_star != null ? String(d.seo.rating_aggregate_star) : '',
      rating_aggregate_count:
        d.seo?.rating_aggregate_count != null ? String(d.seo.rating_aggregate_count) : '',
      banner: emptyImageField(d.banner ?? null),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
    setSlugTouched(true);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const slug = form.seo_slug || slugify(form.name);
      const payload = {
        name: form.name,
        slug,
        sort: Number(form.sort) || 0,
        is_active: form.is_active,
        seo_slug: slug,
        seo_title: form.seo_title || form.name,
        seo_description: form.seo_description || null,
        rating_aggregate_star: form.rating_aggregate_star
          ? Number(form.rating_aggregate_star)
          : null,
        rating_aggregate_count: form.rating_aggregate_count
          ? Number(form.rating_aggregate_count)
          : null,
        banner_media_id: form.banner.media?.id ?? null,
        remove_banner: form.banner.remove,
        locale,
      };
      return isNew ? cruiseTypesApi.create(payload) : cruiseTypesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo loại du thuyền' : 'Đã lưu loại du thuyền');
      await qc.invalidateQueries({ queryKey: ['cruise-types'] });
      await qc.invalidateQueries({ queryKey: ['packages-meta'] });
      router.replace(`/cruises/types/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(key: K, value: FormState[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  return (
    <div>
      <PageHeader
        eyebrow="Du thuyền"
        title={isNew ? 'Thêm loại du thuyền' : 'Chỉnh sửa loại du thuyền'}
        id={isNew ? null : id}
        description={isNew ? 'Loại cruise làm SEO parent cho gói du thuyền.' : undefined}
        actions={
          <HeadActions
            primary={
              <ViewPublicButton
                href={publicPageUrl(
                  detailQuery.data?.seo?.slug_full,
                  locale,
                  languagesQuery.data?.default_code || 'vi',
                )}
              />
            }
            secondary={
              <HeadSecondary
                href="/cruises/types/"
                icon={ArrowLeft}
                title="Quay lại"
                subtitle="Về danh sách"
              />
            }
          />
        }
      />

      <LocaleSwitcher
        languages={languagesQuery.data?.items ?? []}
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
          description="Slug khớp packages.cruise_type — meta và schema rating."
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
              hint="Khớp packages.cruise_type và seo_slug"
            />
            <Input
              label="SEO title"
              value={form.seo_title}
              onChange={(e) => set('seo_title', e.target.value)}
            />
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
          icon={Anchor}
          title="Thông tin loại"
          description="Tên hiển thị và thứ tự listing."
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
            <Input
              label="Thứ tự"
              type="number"
              value={form.sort}
              onChange={(e) => set('sort', e.target.value)}
            />
          </FormCluster>
          <div className="ui-form-flags">
            <Switch label="Đang hoạt động" checked={form.is_active} onChange={(v) => set('is_active', v)} />
          </div>
        </FormSection>

        <div className="ui-form-footer">
          <Link href="/cruises/types/">
            <Button type="button" variant="secondary">
              Hủy
            </Button>
          </Link>
          <Button type="submit" loading={save.isPending}>
            <Save size={17} />
            Lưu loại du thuyền
          </Button>
        </div>
        </div>

        <aside className="ui-form-layout__aside">
          <div className="ui-media-card">
            <div className="ui-media-card__head">
              <h3 className="ui-media-card__title">Banner listing</h3>
              <p className="ui-media-card__desc">
                Banner first-view trang listing loại du thuyền (tỉ lệ rộng 21:9).
              </p>
            </div>
            <ImageField
              ariaLabel="Banner listing loại du thuyền"
              folder="cruise_types"
              aspectRatio="21 / 9"
              variant="lg"
              value={form.banner}
              onChange={(banner) => set('banner', banner)}
            />
          </div>
        </aside>
      </form>
    </div>
  );
}

export default function CruiseTypeFormPage() {
  return (
    <Suspense fallback={<div>Đang tải form…</div>}>
      <CruiseTypeFormInner />
    </Suspense>
  );
}
