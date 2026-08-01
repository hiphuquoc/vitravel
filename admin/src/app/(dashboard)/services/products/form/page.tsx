'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { servicesApi } from '@/lib/services';
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
  service_category_id: string;
  country_id: string;
  code: string;
  title: string;
  status: string;
  price_from: string;
  currency: string;
  sort: string;
  is_featured: boolean;
  is_hot_deal: boolean;
  location_label: string;
  summary: string;
  content: string;
  highlights: string;
  inclusions: string;
  exclusions: string;
  notes: string;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  cover: ImageFieldState;
};

const empty: FormState = {
  cluster: 'experiences',
  service_category_id: '',
  country_id: '',
  code: '',
  title: '',
  status: 'draft',
  price_from: '',
  currency: 'VND',
  sort: '0',
  is_featured: false,
  is_hot_deal: false,
  location_label: '',
  summary: '',
  content: '',
  highlights: '',
  inclusions: '',
  exclusions: '',
  notes: '',
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  cover: emptyImageField(),
};

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
    queryKey: ['services-meta', locale, form.cluster],
    queryFn: () => servicesApi.meta(locale, form.cluster),
  });
  const detailQuery = useQuery({
    queryKey: ['service', id, locale],
    queryFn: () => servicesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      cluster: d.cluster || 'experiences',
      service_category_id: d.service_category_id ? String(d.service_category_id) : '',
      country_id: d.country_id ? String(d.country_id) : '',
      code: d.code || '',
      title: d.title || '',
      status: d.status || 'draft',
      price_from: d.price_from != null ? String(d.price_from) : '',
      currency: d.currency || 'VND',
      sort: String(d.sort || 0),
      is_featured: !!d.is_featured,
      is_hot_deal: !!d.is_hot_deal,
      location_label: d.location_label || '',
      summary: d.summary || '',
      content: d.content || '',
      highlights: d.highlights || '',
      inclusions: d.inclusions || '',
      exclusions: d.exclusions || '',
      notes: d.notes || '',
      seo_slug: d.seo?.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      cover: emptyImageField(d.cover),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        ...form,
        service_category_id: form.service_category_id ? Number(form.service_category_id) : null,
        country_id: form.country_id ? Number(form.country_id) : null,
        price_from: form.price_from ? Number(form.price_from) : null,
        sort: Number(form.sort) || 0,
        cover_media_id: form.cover.media?.id ?? null,
        remove_cover: form.cover.remove,
        locale,
      };
      return isNew ? servicesApi.create(payload) : servicesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: ['services'] });
      router.replace(`/services/products/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(k: K, v: FormState[K]) =>
    setForm((p) => ({ ...p, [k]: v }));

  return (
    <div>
      <PageHeader
        eyebrow="Sản phẩm"
        title={isNew ? 'Thêm dịch vụ' : 'Sửa dịch vụ'}
        id={isNew ? null : id}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href="/services/products/"
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
        onChange={(c) => setLocale(c, { confirmIfDirty: true, isDirty })}
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
            <Select
              label="Danh mục"
              value={form.service_category_id}
              onChange={(v) => set('service_category_id', v)}
              placeholder="Chọn"
              options={(metaQuery.data?.categories ?? []).map((c) => ({
                value: String(c.id),
                label: c.name || `#${c.id}`,
              }))}
            />
            <Select
              label="Quốc gia"
              value={form.country_id}
              onChange={(v) => set('country_id', v)}
              placeholder="—"
              options={(metaQuery.data?.countries ?? []).map((c) => ({
                value: String(c.id),
                label: c.name || `#${c.id}`,
              }))}
            />
            <Input label="Mã" value={form.code} onChange={(e) => set('code', e.target.value)} />
            <Input label="Tiêu đề" value={form.title} onChange={(e) => set('title', e.target.value)} />
            <Select
              label="Trạng thái"
              value={form.status}
              onChange={(v) => set('status', v)}
              options={(metaQuery.data?.statuses ?? []).map((s) => ({
                value: s.value,
                label: s.label,
              }))}
            />
            <Input
              label="Giá từ"
              value={form.price_from}
              onChange={(e) => set('price_from', e.target.value)}
            />
            <Input
              label="Currency"
              value={form.currency}
              onChange={(e) => set('currency', e.target.value)}
            />
            <Textarea
              label="Tóm tắt"
              value={form.summary}
              onChange={(e) => set('summary', e.target.value)}
            />
            <Textarea
              label="Nội dung"
              value={form.content}
              onChange={(e) => set('content', e.target.value)}
            />
            <Textarea
              label="Highlights (mỗi dòng)"
              value={form.highlights}
              onChange={(e) => set('highlights', e.target.value)}
            />
            <Textarea
              label="Inclusions"
              value={form.inclusions}
              onChange={(e) => set('inclusions', e.target.value)}
            />
            <Textarea
              label="Exclusions"
              value={form.exclusions}
              onChange={(e) => set('exclusions', e.target.value)}
            />
            <Switch
              label="Featured"
              checked={form.is_featured}
              onChange={(v) => set('is_featured', v)}
            />
            <Switch
              label="Hot deal"
              checked={form.is_hot_deal}
              onChange={(v) => set('is_hot_deal', v)}
            />
            <Input
              label="SEO slug"
              value={form.seo_slug}
              onChange={(e) => set('seo_slug', e.target.value)}
            />
            <ImageField
              label="Cover"
              folder="services"
              value={form.cover}
              onChange={(v) => set('cover', v)}
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

export default function ServiceProductFormPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <FormInner />
    </Suspense>
  );
}
