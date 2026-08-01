'use client';

import type { ReactNode } from 'react';
import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Save } from 'lucide-react';
import toast from '@/lib/toast';
import { languagesApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import type { LocaleOption } from '@/lib/locale';

type Field =
  | { key: string; label: string; type?: 'text' | 'textarea' | 'number' | 'switch' }
  | {
      key: string;
      label: string;
      type: 'custom';
      render: (v: unknown, set: (v: unknown) => void) => ReactNode;
    };

type Props = {
  eyebrow: string;
  listHref: string;
  queryKey: string;
  titleNew: string;
  titleEdit: string;
  fields: Field[];
  empty: Record<string, unknown>;
  getFn: (id: number, locale: string) => Promise<Record<string, unknown>>;
  createFn: (body: Record<string, unknown>) => Promise<{ id: number }>;
  updateFn: (id: number, body: Record<string, unknown>) => Promise<{ id: number }>;
  languagesFrom?: (data: Record<string, unknown> | undefined) => LocaleOption[];
  mapDetail?: (d: Record<string, unknown>) => Record<string, unknown>;
  mapPayload?: (form: Record<string, unknown>, locale: string) => Record<string, unknown>;
  withLocale?: boolean;
};

function Inner(props: Props) {
  const search = useSearchParams();
  const idParam = search.get('id');
  const id = idParam ? Number(idParam) : null;
  const isNew = !id;
  const router = useRouter();
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<Record<string, unknown>>(props.empty);
  const snapshotRef = useRef(JSON.stringify(props.empty));
  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  const detailQuery = useQuery({
    queryKey: [props.queryKey, id, locale],
    queryFn: () => props.getFn(id!, locale),
    enabled: !!id,
  });

  const languagesQuery = useQuery({
    queryKey: ['languages-options'],
    queryFn: async () => {
      const res = await languagesApi.list();
      return (res.items || []).map((l) => ({
        code: String(l.code || ''),
        name: String(l.name || l.code || ''),
        name_native: String(l.name_native || ''),
        is_default: !!l.is_default,
      })) as LocaleOption[];
    },
    enabled: props.withLocale !== false,
    staleTime: 60_000,
  });

  useEffect(() => {
    if (!detailQuery.data) return;
    const mapped = props.mapDetail ? props.mapDetail(detailQuery.data) : detailQuery.data;
    const next = { ...props.empty, ...mapped };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]); // eslint-disable-line react-hooks/exhaustive-deps

  const save = useMutation({
    mutationFn: async () => {
      const payload = props.mapPayload
        ? props.mapPayload(form, locale)
        : { ...form, locale };
      return isNew ? props.createFn(payload) : props.updateFn(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? 'Đã tạo' : 'Đã lưu');
      await qc.invalidateQueries({ queryKey: [props.queryKey] });
      router.replace(`${props.listHref.replace(/\/$/, '')}/form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const languages =
    props.languagesFrom?.(detailQuery.data) ||
    ((detailQuery.data?.languages as LocaleOption[]) ?? languagesQuery.data ?? []);

  return (
    <div>
      <PageHeader
        eyebrow={props.eyebrow}
        title={isNew ? props.titleNew : props.titleEdit}
        id={isNew ? null : id}
        actions={
          <HeadActions
            secondary={
              <HeadSecondary
                href={props.listHref}
                icon={ArrowLeft}
                title="Quay lại"
                subtitle="Về danh sách"
              />
            }
          />
        }
      />

      {props.withLocale !== false ? (
        <LocaleSwitcher
          languages={languages}
          value={locale}
          onChange={(code) => setLocale(code, { confirmIfDirty: true, isDirty })}
          translatedLocales={
            (detailQuery.data?.translated_locales as string[]) ?? (isNew ? [] : undefined)
          }
        />
      ) : null}

      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-layout"
      >
        <div className="ui-form-layout__main ui-form-stack">
          <FormSection title="Thông tin">
            {props.fields.map((field) => {
              if (field.type === 'custom') {
                return (
                  <div key={field.key}>
                    {field.render(form[field.key], (v) =>
                      setForm((prev) => ({ ...prev, [field.key]: v })),
                    )}
                  </div>
                );
              }
              if (field.type === 'switch') {
                return (
                  <Switch
                    key={field.key}
                    label={field.label}
                    checked={!!form[field.key]}
                    onChange={(v) => setForm((prev) => ({ ...prev, [field.key]: v }))}
                  />
                );
              }
              if (field.type === 'textarea') {
                return (
                  <Textarea
                    key={field.key}
                    label={field.label}
                    value={String(form[field.key] ?? '')}
                    onChange={(e) =>
                      setForm((prev) => ({ ...prev, [field.key]: e.target.value }))
                    }
                  />
                );
              }
              return (
                <Input
                  key={field.key}
                  label={field.label}
                  type={field.type === 'number' ? 'number' : 'text'}
                  value={String(form[field.key] ?? '')}
                  onChange={(e) =>
                    setForm((prev) => ({ ...prev, [field.key]: e.target.value }))
                  }
                />
              );
            })}
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

export function ResourceFormPage(props: Props) {
  return (
    <Suspense fallback={<EmptyForm />}>
      <Inner {...props} />
    </Suspense>
  );
}

function EmptyForm() {
  return <div style={{ padding: '2rem' }}>Đang tải form…</div>;
}
