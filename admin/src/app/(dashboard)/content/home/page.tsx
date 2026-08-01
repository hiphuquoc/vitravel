'use client';

import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Save } from 'lucide-react';
import toast from '@/lib/toast';
import { homeSectionsApi } from '@/lib/services';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormSection } from '@/components/ui/FormSection';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import type { LocaleOption } from '@/lib/locale';

type Section = {
  id: number;
  key: string;
  is_active: boolean;
  title?: string | null;
  subtitle?: string | null;
  body?: string | null;
  eyebrow?: string | null;
  cta_label?: string | null;
  cta_url?: string | null;
};

export default function HomeContentPage() {
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [sections, setSections] = useState<Section[]>([]);
  const [usps, setUsps] = useState<
    { id?: number; icon: string; title?: string | null; description?: string | null }[]
  >([]);
  const snapshotRef = useRef('');
  const isDirty = useMemo(
    () => JSON.stringify({ sections, usps }) !== snapshotRef.current,
    [sections, usps],
  );

  const query = useQuery({
    queryKey: ['home-sections', locale],
    queryFn: () => homeSectionsApi.get(locale),
  });

  useEffect(() => {
    if (!query.data) return;
    const d = query.data as {
      sections?: Section[];
      usps?: { id?: number; icon: string; title?: string | null; description?: string | null }[];
    };
    setSections((d.sections as Section[]) || []);
    setUsps(d.usps || []);
    snapshotRef.current = JSON.stringify({ sections: d.sections || [], usps: d.usps || [] });
  }, [query.data]);

  const save = useMutation({
    mutationFn: () =>
      homeSectionsApi.update({
        locale,
        sections,
        usps,
        pills: (query.data as { pills?: unknown[] })?.pills || [],
        featured_tours: (query.data as { featured_tours?: unknown[] })?.featured_tours || [],
        featured_cruises: (query.data as { featured_cruises?: unknown[] })?.featured_cruises || [],
        featured_countries:
          (query.data as { featured_countries?: unknown[] })?.featured_countries || [],
        featured_platforms:
          (query.data as { featured_platforms?: unknown[] })?.featured_platforms || [],
      }),
    onSuccess: async () => {
      toast.success('Đã lưu nội dung trang chủ');
      await qc.invalidateQueries({ queryKey: ['home-sections'] });
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const languages = ((query.data as { languages?: LocaleOption[] } | undefined)?.languages) ?? [];

  return (
    <div>
      <PageHeader
        eyebrow="Nội dung"
        title="Nội dung trang chủ"
        description="Chỉnh section copy + USP (featured lists giữ nguyên nếu không đổi)."
      />
      <LocaleSwitcher
        languages={languages}
        value={locale}
        onChange={(c) => setLocale(c, { confirmIfDirty: true, isDirty })}
      />
      <form
        onSubmit={(e: FormEvent) => {
          e.preventDefault();
          save.mutate();
        }}
        className="ui-form-stack"
      >
        {sections.map((s, idx) => (
          <FormSection key={s.id} title={`Section: ${s.key}`}>
            <Switch
              label="Active"
              checked={!!s.is_active}
              onChange={(v) =>
                setSections((prev) => prev.map((x, i) => (i === idx ? { ...x, is_active: v } : x)))
              }
            />
            <Input
              label="Eyebrow"
              value={s.eyebrow || ''}
              onChange={(e) =>
                setSections((prev) =>
                  prev.map((x, i) => (i === idx ? { ...x, eyebrow: e.target.value } : x)),
                )
              }
            />
            <Input
              label="Title"
              value={s.title || ''}
              onChange={(e) =>
                setSections((prev) =>
                  prev.map((x, i) => (i === idx ? { ...x, title: e.target.value } : x)),
                )
              }
            />
            <Textarea
              label="Subtitle"
              value={s.subtitle || ''}
              onChange={(e) =>
                setSections((prev) =>
                  prev.map((x, i) => (i === idx ? { ...x, subtitle: e.target.value } : x)),
                )
              }
            />
            <Textarea
              label="Body"
              value={s.body || ''}
              onChange={(e) =>
                setSections((prev) =>
                  prev.map((x, i) => (i === idx ? { ...x, body: e.target.value } : x)),
                )
              }
            />
          </FormSection>
        ))}
        <FormSection title="USP">
          {usps.map((u, idx) => (
            <div key={u.id || idx} style={{ display: 'grid', gap: '0.75rem', marginBottom: '1rem' }}>
              <Input
                label="Icon key"
                value={u.icon}
                onChange={(e) =>
                  setUsps((prev) =>
                    prev.map((x, i) => (i === idx ? { ...x, icon: e.target.value } : x)),
                  )
                }
              />
              <Input
                label="Title"
                value={u.title || ''}
                onChange={(e) =>
                  setUsps((prev) =>
                    prev.map((x, i) => (i === idx ? { ...x, title: e.target.value } : x)),
                  )
                }
              />
              <Textarea
                label="Description"
                value={u.description || ''}
                onChange={(e) =>
                  setUsps((prev) =>
                    prev.map((x, i) => (i === idx ? { ...x, description: e.target.value } : x)),
                  )
                }
              />
            </div>
          ))}
        </FormSection>
        <Button type="submit" disabled={save.isPending}>
          <Save size={16} />
          {save.isPending ? 'Đang lưu…' : 'Lưu trang chủ'}
        </Button>
      </form>
    </div>
  );
}
