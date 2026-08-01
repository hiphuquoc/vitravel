'use client';

import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Save } from 'lucide-react';
import toast from '@/lib/toast';
import { companyProfileApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import type { LocaleOption } from '@/lib/locale';

export default function CompanyPage() {
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<Record<string, string>>({});
  const snapshotRef = useRef('');
  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  const query = useQuery({
    queryKey: ['company-profile', locale],
    queryFn: () => companyProfileApi.get(locale),
  });

  useEffect(() => {
    if (!query.data) return;
    const d = query.data as Record<string, unknown>;
    const next: Record<string, string> = {};
    for (const key of [
      'license_number',
      'contact_email',
      'contact_phone',
      'contact_whatsapp',
      'slogan',
      'about_page_title',
      'about_page_subtitle',
      'about_seo_title',
      'about_seo_description',
      'mission_title',
      'mission_text',
      'vision_title',
      'vision_text',
      'sales_policy_title',
      'sales_policy_content',
      'values_section_title',
      'reasons_section_title',
      'reference_section_title',
    ]) {
      next[key] = String(d[key] ?? '');
    }
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [query.data]);

  const save = useMutation({
    mutationFn: () => companyProfileApi.update({ ...form, locale }),
    onSuccess: async () => {
      toast.success('Đã lưu hồ sơ công ty');
      await qc.invalidateQueries({ queryKey: ['company-profile'] });
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const languages = ((query.data as { languages?: LocaleOption[] } | undefined)?.languages) ?? [];

  return (
    <div>
      <PageHeader eyebrow="Thương hiệu" title="Công ty" description="Trang Về chúng tôi." />
      <LocaleSwitcher
        languages={languages}
        value={locale}
        onChange={(c) => setLocale(c, { confirmIfDirty: true, isDirty })}
        translatedLocales={
          (query.data as { translated_locales?: string[] } | undefined)?.translated_locales
        }
      />
      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-stack"
      >
        <FormSection title="Liên hệ">
          <Input
            label="License"
            value={form.license_number || ''}
            onChange={(e) => setForm((p) => ({ ...p, license_number: e.target.value }))}
          />
          <Input
            label="Email"
            value={form.contact_email || ''}
            onChange={(e) => setForm((p) => ({ ...p, contact_email: e.target.value }))}
          />
          <Input
            label="Phone"
            value={form.contact_phone || ''}
            onChange={(e) => setForm((p) => ({ ...p, contact_phone: e.target.value }))}
          />
          <Input
            label="WhatsApp"
            value={form.contact_whatsapp || ''}
            onChange={(e) => setForm((p) => ({ ...p, contact_whatsapp: e.target.value }))}
          />
          <Input
            label="Slogan"
            value={form.slogan || ''}
            onChange={(e) => setForm((p) => ({ ...p, slogan: e.target.value }))}
          />
        </FormSection>
        <FormSection title="About">
          <Input
            label="Page title"
            value={form.about_page_title || ''}
            onChange={(e) => setForm((p) => ({ ...p, about_page_title: e.target.value }))}
          />
          <Textarea
            label="Subtitle"
            value={form.about_page_subtitle || ''}
            onChange={(e) => setForm((p) => ({ ...p, about_page_subtitle: e.target.value }))}
          />
          <Input
            label="SEO title"
            value={form.about_seo_title || ''}
            onChange={(e) => setForm((p) => ({ ...p, about_seo_title: e.target.value }))}
          />
          <Textarea
            label="SEO description"
            value={form.about_seo_description || ''}
            onChange={(e) => setForm((p) => ({ ...p, about_seo_description: e.target.value }))}
          />
        </FormSection>
        <FormSection title="Mission / Vision">
          <Input
            label="Mission title"
            value={form.mission_title || ''}
            onChange={(e) => setForm((p) => ({ ...p, mission_title: e.target.value }))}
          />
          <Textarea
            label="Mission text"
            value={form.mission_text || ''}
            onChange={(e) => setForm((p) => ({ ...p, mission_text: e.target.value }))}
          />
          <Input
            label="Vision title"
            value={form.vision_title || ''}
            onChange={(e) => setForm((p) => ({ ...p, vision_title: e.target.value }))}
          />
          <Textarea
            label="Vision text"
            value={form.vision_text || ''}
            onChange={(e) => setForm((p) => ({ ...p, vision_text: e.target.value }))}
          />
        </FormSection>
        <Button type="submit" disabled={save.isPending}>
          <Save size={16} />
          Lưu
        </Button>
      </form>
    </div>
  );
}
