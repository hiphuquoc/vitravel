'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { articlesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Select, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import type { LocaleOption } from '@/lib/locale';

type FormState = {
  title: string;
  excerpt: string;
  content: string;
  author_name: string;
  status: string;
  blog_category_id: string;
  country_id: string;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  cover: ImageFieldState;
};

const empty: FormState = {
  title: '',
  excerpt: '',
  content: '',
  author_name: '',
  status: 'draft',
  blog_category_id: '',
  country_id: '',
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
    queryKey: ['articles-meta', locale],
    queryFn: () => articlesApi.meta(locale),
  });
  const detailQuery = useQuery({
    queryKey: ['article', id, locale],
    queryFn: () => articlesApi.get(id!, locale),
    enabled: !!id,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data as Record<string, unknown>;
    const seo = d.seo as Record<string, string | null> | undefined;
    const next: FormState = {
      title: String(d.title || ''),
      excerpt: String(d.excerpt || ''),
      content: String(d.content || ''),
      author_name: String(d.author_name || ''),
      status: String(d.status || 'draft'),
      blog_category_id: d.blog_category_id ? String(d.blog_category_id) : '',
      country_id: d.country_id ? String(d.country_id) : '',
      seo_slug: String(seo?.slug || ''),
      seo_title: String(seo?.title || ''),
      seo_description: String(seo?.description || ''),
      cover: emptyImageField(d.cover as never),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        ...form,
        blog_category_id: form.blog_category_id ? Number(form.blog_category_id) : null,
        country_id: form.country_id ? Number(form.country_id) : null,
        cover_media_id: form.cover.media?.id ?? null,
        remove_cover: form.cover.remove,
        locale,
      };
      return isNew ? articlesApi.create(payload) : articlesApi.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: ['articles'] });
      router.replace(`/content/articles/form/?id=${(data as { id: number }).id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const set = <K extends keyof FormState>(k: K, v: FormState[K]) =>
    setForm((p) => ({ ...p, [k]: v }));
  const meta = metaQuery.data as {
    languages?: LocaleOption[];
    categories?: { id: number; name: string | null }[];
    countries?: { id: number; name: string | null }[];
    statuses?: { value: string; label: string }[];
  };

  return (
    <div>
      <PageHeader
        eyebrow="Nội dung"
        title={isNew ? 'Thêm bài viết' : 'Sửa bài viết'}
        id={isNew ? null : id}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href="/content/articles/"
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
          <FormSection title="Bài viết">
            <Input label="Tiêu đề" value={form.title} onChange={(e) => set('title', e.target.value)} />
            <Select
              label="Chuyên mục"
              value={form.blog_category_id}
              onChange={(v) => set('blog_category_id', v)}
              placeholder="—"
              options={(meta?.categories ?? []).map((c) => ({
                value: String(c.id),
                label: c.name || `#${c.id}`,
              }))}
            />
            <Select
              label="Trạng thái"
              value={form.status}
              onChange={(v) => set('status', v)}
              options={(meta?.statuses ?? []).map((s) => ({ value: s.value, label: s.label }))}
            />
            <Input
              label="Tác giả"
              value={form.author_name}
              onChange={(e) => set('author_name', e.target.value)}
            />
            <Textarea
              label="Excerpt"
              value={form.excerpt}
              onChange={(e) => set('excerpt', e.target.value)}
            />
            <Textarea
              label="Content"
              value={form.content}
              onChange={(e) => set('content', e.target.value)}
            />
            <Input
              label="SEO slug"
              value={form.seo_slug}
              onChange={(e) => set('seo_slug', e.target.value)}
            />
            <ImageField
              label="Cover"
              folder="articles"
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

export default function ArticleFormPage() {
  return (
    <Suspense fallback={<div style={{ padding: '2rem' }}>Đang tải…</div>}>
      <FormInner />
    </Suspense>
  );
}
