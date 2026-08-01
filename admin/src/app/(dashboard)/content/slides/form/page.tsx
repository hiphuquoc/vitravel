'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { homeSlidesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Select, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import type { LocaleOption } from '@/lib/locale';
import type { ValueLabel } from '@/lib/types';

type FormState = {
  title: string;
  title_accent: string;
  description: string;
  button_label: string;
  text_align: string;
  link_url: string;
  sort: string;
  is_active: boolean;
  image_alt: string;
  image: ImageFieldState;
  image_mobile: ImageFieldState;
};

const empty: FormState = {
  title: '',
  title_accent: '',
  description: '',
  button_label: '',
  text_align: 'left',
  link_url: '',
  sort: '0',
  is_active: true,
  image_alt: '',
  image: emptyImageField(),
  image_mobile: emptyImageField(),
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
    queryKey: ['home-slides-meta'],
    queryFn: () => homeSlidesApi.meta(),
  });
  const detailQuery = useQuery({
    queryKey: ['home-slide', id, locale],
    queryFn: () => homeSlidesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data as Record<string, unknown>;
    const next: FormState = {
      title: String(d.title || ''),
      title_accent: String(d.title_accent || ''),
      description: String(d.description || ''),
      button_label: String(d.button_label || ''),
      text_align: String(d.text_align || 'left'),
      link_url: String(d.link_url || ''),
      sort: String(d.sort || 0),
      is_active: !!d.is_active,
      image_alt: String(d.image_alt || ''),
      image: emptyImageField(d.image as never),
      image_mobile: emptyImageField(d.image_mobile as never),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        ...form,
        sort: Number(form.sort) || 0,
        image_media_id: form.image.media?.id ?? null,
        remove_image: form.image.remove,
        image_mobile_media_id: form.image_mobile.media?.id ?? null,
        remove_image_mobile: form.image_mobile.remove,
        locale,
      };
      return isNew ? homeSlidesApi.create(payload) : homeSlidesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: ['home-slides'] });
      router.replace(`/content/slides/form/?id=${(data as { id: number }).id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(k: K, v: FormState[K]) =>
    setForm((p) => ({ ...p, [k]: v }));
  const meta = metaQuery.data as {
    languages?: LocaleOption[];
    align_options?: ValueLabel[];
  } | undefined;

  return (
    <div>
      <PageHeader
        eyebrow="Nội dung"
        title={isNew ? 'Thêm slide' : 'Sửa slide'}
        id={isNew ? null : id}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href="/content/slides/"
                icon={ArrowLeft}
                title="Quay lại"
                subtitle="Danh sách"
              />
            }
          />
        }
      />
      <LocaleSwitcher
        languages={meta?.languages ?? []}
        value={locale}
        onChange={(c) => setLocale(c, { confirmIfDirty: true, isDirty })}
        translatedLocales={
          ((detailQuery.data as { translated_locales?: string[] } | undefined)
            ?.translated_locales) ?? (isNew ? [] : undefined)
        }
      />
      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-layout"
      >
        <div className="ui-form-layout__main ui-form-stack">
          <FormSection title="Nội dung">
            <Input label="Title" value={form.title} onChange={(e) => set('title', e.target.value)} />
            <Input
              label="Title accent"
              value={form.title_accent}
              onChange={(e) => set('title_accent', e.target.value)}
            />
            <Textarea
              label="Description"
              value={form.description}
              onChange={(e) => set('description', e.target.value)}
            />
            <Input
              label="Button"
              value={form.button_label}
              onChange={(e) => set('button_label', e.target.value)}
            />
            <Select
              label="Align"
              value={form.text_align}
              onChange={(v) => set('text_align', v)}
              options={(meta?.align_options ?? []).map((o) => ({
                value: o.value,
                label: o.label,
              }))}
            />
            <Input
              label="Link URL"
              value={form.link_url}
              onChange={(e) => set('link_url', e.target.value)}
            />
            <Input
              label="Sort"
              type="number"
              value={form.sort}
              onChange={(e) => set('sort', e.target.value)}
            />
            <Switch label="Active" checked={form.is_active} onChange={(v) => set('is_active', v)} />
            <ImageField
              label="Desktop"
              folder="home_slider"
              value={form.image}
              onChange={(v) => set('image', v)}
            />
            <ImageField
              label="Mobile"
              folder="home_slider"
              value={form.image_mobile}
              onChange={(v) => set('image_mobile', v)}
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

export default function SlideFormPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <FormInner />
    </Suspense>
  );
}
