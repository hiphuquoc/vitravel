'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Compass, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { metaApi, themesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormCluster, FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';

type FormState = {
  name: string;
  code: string;
  slug: string;
  sort: string;
  is_active: boolean;
  description: string;
};

const empty: FormState = {
  name: '',
  code: '',
  slug: '',
  sort: '0',
  is_active: true,
  description: '',
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

function ThemeFormInner() {
  const search = useSearchParams();
  const idParam = search.get('id');
  const id = idParam ? Number(idParam) : null;
  const isNew = !id;
  const router = useRouter();
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<FormState>(empty);
  const [slugTouched, setSlugTouched] = useState(false);
  const [codeTouched, setCodeTouched] = useState(false);
  const snapshotRef = useRef(JSON.stringify(empty));
  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  const languagesQuery = useQuery({
    queryKey: ['meta-languages'],
    queryFn: () => metaApi.languages(),
  });

  const detailQuery = useQuery({
    queryKey: ['travel-style', id, locale],
    queryFn: () => themesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      name: d.name || '',
      code: d.code || '',
      slug: d.slug || '',
      sort: String(d.sort || 0),
      is_active: !!d.is_active,
      description: d.description || '',
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
    setSlugTouched(true);
    setCodeTouched(true);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        name: form.name,
        code: form.code || slugify(form.name),
        slug: form.slug || slugify(form.name),
        sort: Number(form.sort) || 0,
        is_active: form.is_active,
        description: form.description || null,
        locale,
      };
      return isNew ? themesApi.create(payload) : themesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo chủ đề' : 'Đã lưu chủ đề');
      await qc.invalidateQueries({ queryKey: ['travel-styles'] });
      router.replace(`/tours/themes/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(key: K, value: FormState[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  return (
    <div>
      <PageHeader
        eyebrow="Cài đặt"
        title={isNew ? 'Thêm phong cách' : 'Chỉnh sửa phong cách'}
        id={isNew ? null : id}
        description={isNew ? 'Tạo travel style mới cho filter tour/cruise.' : undefined}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href="/tours/themes/"
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
        className="ui-form-stack"
      >
        <FormSection
          icon={Compass}
          title="Thông tin chủ đề"
          description="Mã ổn định dùng trong filter / API."
        >
          <FormCluster title="Định danh">
            <Input
              label="Tên"
              value={form.name}
              onChange={(e) => {
                const name = e.target.value;
                set('name', name);
                if (!slugTouched) set('slug', slugify(name));
                if (!codeTouched) set('code', slugify(name));
              }}
              required
            />
            <Input
              label="Mã (code)"
              value={form.code}
              onChange={(e) => {
                setCodeTouched(true);
                set('code', e.target.value);
              }}
              required
              hint="Mã ổn định dùng trong API / filter"
            />
            <Input
              label="Slug"
              value={form.slug}
              onChange={(e) => {
                setSlugTouched(true);
                set('slug', e.target.value);
              }}
            />
            <Input label="Thứ tự" type="number" value={form.sort} onChange={(e) => set('sort', e.target.value)} />
          </FormCluster>

          <div className="ui-form-flags">
            <Switch label="Đang hoạt động" checked={form.is_active} onChange={(v) => set('is_active', v)} />
          </div>

          <FormCluster cols={1}>
            <Textarea label="Mô tả" value={form.description} onChange={(e) => set('description', e.target.value)} />
          </FormCluster>
        </FormSection>

        <div className="ui-form-footer">
          <Link href="/tours/themes/">
            <Button type="button" variant="secondary">
              Hủy
            </Button>
          </Link>
          <Button type="submit" loading={save.isPending}>
            <Save size={17} />
            Lưu chủ đề
          </Button>
        </div>
      </form>
    </div>
  );
}

export default function ThemeFormPage() {
  return (
    <Suspense fallback={<div>Đang tải form…</div>}>
      <ThemeFormInner />
    </Suspense>
  );
}
