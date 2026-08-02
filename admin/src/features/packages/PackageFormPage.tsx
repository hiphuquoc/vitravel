'use client';

import { FormEvent, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  ArrowLeft,
  CalendarDays,
  CircleHelp,
  FileText,
  Package,
  Plus,
  Ship,
  Tags,
} from 'lucide-react';
import toast from '@/lib/toast';
import {
  categoriesApi,
  cruisePackagesApi,
  packagesApi,
  themesApi,
  type PackageType,
} from '@/lib/services';
import type { PackageFaq, PackageItineraryDay } from '@/lib/types';
import { useEditLocale } from '@/hooks/useEditLocale';
import { Button } from '@/components/ui/Button';
import { Input, MoneyInput, MultiSelect, Select, Switch, Textarea } from '@/components/ui/Field';
import { PageHeader } from '@/components/ui/Page';
import { FormCluster, FormSection } from '@/components/ui/FormSection';
import { SeoBox } from '@/components/ui/SeoBox';
import { LocaleSwitcher } from '@/components/ui/LocaleSwitcher';
import { Repeater } from '@/components/ui/Repeater';
import { emptyImageField, ImageField, type ImageFieldState } from '@/components/ui/ImageField';
import { FormMediaAside, FormThumbCard } from '@/components/ui/FormMediaAside';
import { FormFooter } from '@/components/ui/FormFooter';
import { HeadActions, HeadSecondary } from '@/components/ui/HeadActions';
import { ViewPublicButton } from '@/components/ui/ViewPublicButton';
import { publicPageUrl } from '@/lib/publicUrl';

const MEAL_OPTIONS = [
  { value: '', label: '— Không gồm —' },
  { value: 'Sáng', label: 'Sáng' },
  { value: 'Trưa', label: 'Trưa' },
  { value: 'Tối', label: 'Tối' },
  { value: 'Sáng; Trưa', label: 'Sáng; Trưa' },
  { value: 'Sáng; Tối', label: 'Sáng; Tối' },
  { value: 'Trưa; Tối', label: 'Trưa; Tối' },
  { value: 'Sáng; Trưa; Tối', label: 'Sáng; Trưa; Tối' },
];

type FormState = {
  title: string;
  code: string;
  country_id: string;
  duration_days: string;
  duration_nights: string;
  price_from: string;
  currency: string;
  status: string;
  sort: string;
  discount_badge: string;
  cruise_type: string;
  departure_port: string;
  boat_class: string;
  nights_on_board: string;
  start_location: string;
  end_location: string;
  summary: string;
  highlights_intro: string;
  featured_quote_text: string;
  featured_quote_author: string;
  places_to_visit: string;
  highlight_bullets: string;
  inclusions: string;
  exclusions: string;
  notes: string;
  seo_slug: string;
  seo_title: string;
  seo_description: string;
  seo_parent_id: string;
  rating_aggregate_star: string;
  rating_aggregate_count: string;
  is_featured: boolean;
  is_hot_deal: boolean;
  travel_style_ids: number[];
  category_ids: number[];
  itinerary: PackageItineraryDay[];
  faqs: PackageFaq[];
  cover: ImageFieldState;
};

const emptyDay = (n = 1): PackageItineraryDay => ({
  id: null,
  day_number: n,
  meals_included: '',
  transport_icons: '',
  title: '',
  content: '',
  overnight_at: '',
});

const emptyFaq = (): PackageFaq => ({
  id: null,
  question: '',
  answer: '',
});

const empty: FormState = {
  title: '',
  code: '',
  country_id: '',
  duration_days: '7',
  duration_nights: '6',
  price_from: '',
  currency: '',
  status: 'draft',
  sort: '0',
  discount_badge: '',
  cruise_type: '',
  departure_port: '',
  boat_class: '',
  nights_on_board: '',
  start_location: '',
  end_location: '',
  summary: '',
  highlights_intro: '',
  featured_quote_text: '',
  featured_quote_author: '',
  places_to_visit: '',
  highlight_bullets: '',
  inclusions: '',
  exclusions: '',
  notes: '',
  seo_slug: '',
  seo_title: '',
  seo_description: '',
  seo_parent_id: '',
  rating_aggregate_star: '',
  rating_aggregate_count: '',
  is_featured: false,
  is_hot_deal: false,
  travel_style_ids: [],
  category_ids: [],
  itinerary: [],
  faqs: [],
  cover: emptyImageField(),
};

const COPY: Record<
  PackageType,
  {
    eyebrow: string;
    listHref: string;
    addTitle: string;
    editTitle: string;
    addDesc: string;
    codeLabel: string;
    featuredLabel: string;
    saveLabel: string;
    createdMsg: string;
    savedMsg: string;
    queryKey: string;
  }
> = {
  tour: {
    eyebrow: 'Tour',
    listHref: '/tours/packages/',
    addTitle: 'Thêm gói tour',
    editTitle: 'Chỉnh sửa gói tour',
    addDesc: 'Tạo sản phẩm tour mới.',
    codeLabel: 'Mã tour',
    featuredLabel: 'Tour nổi bật',
    saveLabel: 'Lưu gói tour',
    createdMsg: 'Đã tạo gói tour',
    savedMsg: 'Đã lưu gói tour',
    queryKey: 'packages',
  },
  cruise: {
    eyebrow: 'Du thuyền',
    listHref: '/cruises/packages/',
    addTitle: 'Thêm gói cruise',
    editTitle: 'Chỉnh sửa gói cruise',
    addDesc: 'Tạo sản phẩm du thuyền mới.',
    codeLabel: 'Mã cruise',
    featuredLabel: 'Cruise nổi bật',
    saveLabel: 'Lưu gói cruise',
    createdMsg: 'Đã tạo gói cruise',
    savedMsg: 'Đã lưu gói cruise',
    queryKey: 'cruise-packages',
  },
};

function PackageFormInner({ kind }: { kind: PackageType }) {
  const copy = COPY[kind];
  const api = kind === 'cruise' ? cruisePackagesApi : packagesApi;
  const isCruise = kind === 'cruise';
  const search = useSearchParams();
  const idParam = search.get('id');
  const id = idParam ? Number(idParam) : null;
  const isNew = !id;
  const router = useRouter();
  const qc = useQueryClient();
  const { locale, setLocale } = useEditLocale();
  const [form, setForm] = useState<FormState>(empty);
  const snapshotRef = useRef<string>(JSON.stringify(empty));

  const metaQuery = useQuery({
    queryKey: ['packages-meta', locale],
    queryFn: () => api.meta(locale),
  });

  useEffect(() => {
    if (!isNew) return;
    const def = metaQuery.data?.default_currency;
    if (!def) return;
    setForm((prev) => (prev.currency ? prev : { ...prev, currency: def }));
  }, [isNew, metaQuery.data?.default_currency]);

  const themesQuery = useQuery({
    queryKey: ['themes-all', locale],
    queryFn: () => themesApi.list({ per_page: 100, is_active: true, locale }),
  });
  const categoriesQuery = useQuery({
    queryKey: ['categories-all', locale],
    queryFn: () => categoriesApi.list({ per_page: 100, locale }),
    enabled: !isCruise,
  });

  const detailQuery = useQuery({
    queryKey: [copy.queryKey.slice(0, -1), id, locale],
    queryFn: () => api.get(id!, locale),
    enabled: !!id,
  });

  const isDirty = useMemo(() => JSON.stringify(form) !== snapshotRef.current, [form]);

  useEffect(() => {
    if (!detailQuery.data) return;
    const d = detailQuery.data;
    const next: FormState = {
      title: d.title || '',
      code: d.code || '',
      country_id: d.country_id ? String(d.country_id) : '',
      duration_days: String(d.duration_days || 7),
      duration_nights: String(d.duration_nights || 6),
      price_from: d.price_from != null ? String(Math.round(Number(d.price_from))) : '',
      currency: d.currency || metaQuery.data?.default_currency || 'VND',
      status: d.status || 'draft',
      sort: String(d.sort || 0),
      discount_badge: d.discount_badge || '',
      cruise_type: d.cruise_type || '',
      departure_port: d.departure_port || '',
      boat_class: d.boat_class || '',
      nights_on_board: d.nights_on_board != null ? String(d.nights_on_board) : '',
      start_location: d.start_location || '',
      end_location: d.end_location || '',
      summary: d.summary || '',
      highlights_intro: d.highlights_intro || '',
      featured_quote_text: d.featured_quote_text || '',
      featured_quote_author: d.featured_quote_author || '',
      places_to_visit: d.places_to_visit || '',
      highlight_bullets: d.highlight_bullets || '',
      inclusions: d.inclusions || '',
      exclusions: d.exclusions || '',
      notes: d.notes || '',
      seo_slug: d.seo?.slug || '',
      seo_title: d.seo?.title || '',
      seo_description: d.seo?.description || '',
      seo_parent_id: d.seo?.parent_id ? String(d.seo.parent_id) : '',
      rating_aggregate_star:
        d.seo?.rating_aggregate_star != null ? String(d.seo.rating_aggregate_star) : '',
      rating_aggregate_count:
        d.seo?.rating_aggregate_count != null ? String(d.seo.rating_aggregate_count) : '',
      is_featured: !!d.is_featured,
      is_hot_deal: !!d.is_hot_deal,
      travel_style_ids: d.travel_style_ids || [],
      category_ids: d.category_ids || [],
      itinerary: (d.itinerary || []).map((row, i) => ({
        id: row.id ?? null,
        day_number: row.day_number || i + 1,
        meals_included: row.meals_included || '',
        transport_icons: row.transport_icons || '',
        title: row.title || '',
        content: row.content || '',
        overnight_at: row.overnight_at || '',
      })),
      faqs: (d.faqs || []).map((row) => ({
        id: row.id ?? null,
        question: row.question || '',
        answer: row.answer || '',
      })),
      cover: emptyImageField(d.cover ?? null),
    };
    setForm(next);
    snapshotRef.current = JSON.stringify(next);
  }, [detailQuery.data, locale]);

  const badgeOptions = useMemo(() => {
    const base = metaQuery.data?.discount_badges ?? [
      { value: '', label: '— Không có —' },
      { value: 'Ưu đãi đặc biệt', label: 'Ưu đãi đặc biệt' },
      { value: 'Bán chạy nhất', label: 'Bán chạy nhất' },
      { value: 'Bán chạy', label: 'Bán chạy' },
      { value: 'Mới', label: 'Mới' },
      { value: 'Hot deal', label: 'Hot deal' },
    ];
    if (form.discount_badge && !base.some((b) => b.value === form.discount_badge)) {
      return [...base, { value: form.discount_badge, label: form.discount_badge }];
    }
    return base;
  }, [metaQuery.data?.discount_badges, form.discount_badge]);

  const currencyOptions = useMemo(() => {
    const base = (metaQuery.data?.currencies ?? []).map((c) => ({
      value: c.value,
      label: c.label,
    }));
    if (form.currency && !base.some((o) => o.value === form.currency)) {
      return [...base, { value: form.currency, label: form.currency }];
    }
    return base;
  }, [metaQuery.data?.currencies, form.currency]);

  const cruiseTypeOptions = useMemo(() => {
    const base = (metaQuery.data?.cruise_types ?? [])
      .filter((t) => t.is_active !== false || t.slug === form.cruise_type)
      .map((t) => ({ value: t.slug, label: t.name || t.slug }));
    if (form.cruise_type && !base.some((o) => o.value === form.cruise_type)) {
      return [...base, { value: form.cruise_type, label: form.cruise_type }];
    }
    return base;
  }, [metaQuery.data?.cruise_types, form.cruise_type]);

  const save = useMutation({
    mutationFn: async () => {
      const payload = {
        title: form.title,
        code: form.code || null,
        country_id: Number(form.country_id),
        country_ids: [Number(form.country_id)],
        duration_days: Number(form.duration_days),
        duration_nights: Number(form.duration_nights),
        price_from: form.price_from ? Number(form.price_from) : null,
        currency: form.currency || metaQuery.data?.default_currency || 'VND',
        status: form.status,
        sort: Number(form.sort) || 0,
        discount_badge: form.discount_badge || null,
        cruise_type: isCruise ? form.cruise_type || null : null,
        departure_port: isCruise ? form.departure_port || null : null,
        boat_class: isCruise ? form.boat_class || null : null,
        nights_on_board:
          isCruise && form.nights_on_board !== '' ? Number(form.nights_on_board) : null,
        start_location: form.start_location || null,
        end_location: form.end_location || null,
        summary: form.summary || null,
        highlights_intro: form.highlights_intro || null,
        featured_quote_text: form.featured_quote_text || null,
        featured_quote_author: form.featured_quote_author || null,
        places_to_visit: form.places_to_visit || null,
        highlight_bullets: form.highlight_bullets || null,
        inclusions: form.inclusions || null,
        exclusions: form.exclusions || null,
        notes: form.notes || null,
        seo_slug: form.seo_slug || null,
        seo_title: form.seo_title || null,
        seo_description: form.seo_description || null,
        seo_parent_id: form.seo_parent_id ? Number(form.seo_parent_id) : null,
        rating_aggregate_star: form.rating_aggregate_star
          ? Number(form.rating_aggregate_star)
          : null,
        rating_aggregate_count: form.rating_aggregate_count
          ? Number(form.rating_aggregate_count)
          : null,
        is_featured: form.is_featured,
        is_hot_deal: form.is_hot_deal,
        travel_style_ids: form.travel_style_ids,
        category_ids: isCruise ? [] : form.category_ids,
        itinerary: form.itinerary.map((row, i) => ({
          id: row.id || undefined,
          day_number: Number(row.day_number) || i + 1,
          meals_included: row.meals_included || null,
          transport_icons: row.transport_icons || null,
          title: row.title,
          content: row.content || null,
          overnight_at: row.overnight_at || null,
        })),
        faqs: form.faqs.map((row) => ({
          id: row.id || undefined,
          question: row.question,
          answer: row.answer || null,
        })),
        locale,
        cover_media_id: form.cover.media?.id ?? null,
        remove_cover: form.cover.remove,
      };
      return isNew ? api.create(payload) : api.update(id!, payload);
    },
    onSuccess: async (data) => {
      toast.success(isNew ? copy.createdMsg : copy.savedMsg);
      await qc.invalidateQueries({ queryKey: [copy.queryKey] });
      await qc.invalidateQueries({ queryKey: [copy.queryKey.slice(0, -1), data.id] });
      router.replace(`${copy.listHref}form/?id=${data.id}&locale=${locale}`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    if (!form.country_id) {
      toast.error('Vui lòng chọn quốc gia');
      return;
    }
    if (isCruise && !form.cruise_type) {
      toast.error('Vui lòng chọn loại cruise');
      return;
    }
    save.mutate();
  };

  const set = <K extends keyof FormState>(key: K, value: FormState[K]) =>
    setForm((prev) => ({ ...prev, [key]: value }));

  return (
    <div>
      <PageHeader
        eyebrow={copy.eyebrow}
        title={isNew ? copy.addTitle : copy.editTitle}
        id={isNew ? null : id}
        description={isNew ? copy.addDesc : undefined}
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
                href={copy.listHref}
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

      <form onSubmit={onSubmit} className="ui-form-layout">
        <div className="ui-form-layout__main ui-form-stack">
        <SeoBox
          value={{
            seo_title: form.seo_title,
            seo_slug: form.seo_slug,
            seo_description: form.seo_description,
            seo_parent_id: form.seo_parent_id,
            rating_aggregate_star: form.rating_aggregate_star,
            rating_aggregate_count: form.rating_aggregate_count,
          }}
          onChange={(key, v) => setForm((prev) => ({ ...prev, [key]: v }))}
          parents={metaQuery.data?.seo_parents ?? []}
          description={
            isCruise
              ? 'Chọn trang cha (loại cruise) → URL = {parent}/{slug}.'
              : 'Chọn trang cha (quốc gia) → URL = {parent}/{slug}.'
          }
        />

        <FormSection
          icon={isCruise ? Ship : Package}
          title="Thông tin cơ bản"
          description="Định danh sản phẩm, điểm đến và thông số bán hàng."
        >
          <FormCluster title="Định danh">
            <Input
              label="Tiêu đề"
              value={form.title}
              onChange={(e) => set('title', e.target.value)}
              required
            />
            <Input
              label={copy.codeLabel}
              value={form.code}
              onChange={(e) => set('code', e.target.value)}
              placeholder={isCruise ? 'VD: CR-HL-2D' : 'VD: VT-HN-7D'}
            />
          </FormCluster>

          <FormCluster title="Điểm đến & trạng thái">
            <Select
              label="Quốc gia"
              value={form.country_id}
              onChange={(v) => {
                set('country_id', v);
                if (!isCruise) {
                  const parent = (metaQuery.data?.seo_parents ?? []).find(
                    (p) => String(p.reference_id ?? '') === String(v),
                  );
                  if (parent) set('seo_parent_id', String(parent.id));
                }
              }}
              placeholder="Chọn quốc gia"
              searchable
              required
              options={(metaQuery.data?.countries ?? []).map((c) => ({
                value: c.id,
                label: c.name || `#${c.id}`,
              }))
              }
            />
            <Select
              label="Trạng thái"
              value={form.status}
              onChange={(v) => set('status', v)}
              required
              options={(metaQuery.data?.statuses ?? []).map((s) => ({
                value: s.value,
                label: s.label,
              }))}
            />
          </FormCluster>

          {isCruise ? (
            <FormCluster title="Thông tin du thuyền">
              <Select
                label="Loại cruise"
                value={form.cruise_type}
                onChange={(v) => {
                  set('cruise_type', v);
                  const ct = (metaQuery.data?.cruise_types ?? []).find((t) => t.slug === v);
                  if (ct) {
                    const parent = (metaQuery.data?.seo_parents ?? []).find(
                      (p) => String(p.reference_id ?? '') === String(ct.id),
                    );
                    if (parent) set('seo_parent_id', String(parent.id));
                  }
                }}
                placeholder="Chọn loại"
                searchable
                required
                options={cruiseTypeOptions}
                hint="Quản lý loại tại mục Loại du thuyền"
              />
              <Input
                label="Cảng khởi hành"
                value={form.departure_port}
                onChange={(e) => set('departure_port', e.target.value)}
              />
              <Input
                label="Hạng tàu"
                value={form.boat_class}
                onChange={(e) => set('boat_class', e.target.value)}
              />
              <Input
                label="Đêm trên tàu"
                type="number"
                min={0}
                value={form.nights_on_board}
                onChange={(e) => set('nights_on_board', e.target.value)}
              />
            </FormCluster>
          ) : null}

          <FormCluster title="Thời lượng & giá">
            <Input
              label="Số ngày"
              type="number"
              min={1}
              value={form.duration_days}
              onChange={(e) => set('duration_days', e.target.value)}
              required
            />
            <Input
              label="Số đêm"
              type="number"
              min={0}
              value={form.duration_nights}
              onChange={(e) => set('duration_nights', e.target.value)}
            />
            <MoneyInput
              label="Giá từ"
              value={form.price_from}
              onValueChange={(v) => set('price_from', v)}
              hint="Hiển thị dạng 28,000,000"
            />
            <Select
              label="Tiền tệ"
              value={form.currency}
              onChange={(v) => set('currency', v)}
              searchable
              required
              options={currencyOptions}
              hint="Theo config/currency.php (enabled)"
            />
          </FormCluster>

          <FormCluster title="Nhãn & thứ tự">
            <Select
              label="Badge khuyến mãi"
              value={form.discount_badge}
              onChange={(v) => set('discount_badge', v)}
              options={badgeOptions.map((b) => ({ value: b.value, label: b.label }))}
              hint="Hiển thị trên card / sidebar"
            />
            <Input
              label="Thứ tự"
              type="number"
              value={form.sort}
              onChange={(e) => set('sort', e.target.value)}
            />
          </FormCluster>

          <div className="ui-form-flags">
            <Switch
              label={copy.featuredLabel}
              checked={form.is_featured}
              onChange={(v) => set('is_featured', v)}
            />
            <Switch label="Hot deal" checked={form.is_hot_deal} onChange={(v) => set('is_hot_deal', v)} />
          </div>
        </FormSection>

        <FormSection icon={FileText} title="Nội dung" description="Tóm tắt, điểm nhấn và danh sách bao gồm.">
          <FormCluster>
            <Input
              label="Điểm khởi hành"
              value={form.start_location}
              onChange={(e) => set('start_location', e.target.value)}
            />
            <Input
              label="Điểm kết thúc"
              value={form.end_location}
              onChange={(e) => set('end_location', e.target.value)}
            />
          </FormCluster>
          <FormCluster cols={1}>
            <Textarea label="Tóm tắt" value={form.summary} onChange={(e) => set('summary', e.target.value)} />
            <Textarea
              label="Intro highlights"
              value={form.highlights_intro}
              onChange={(e) => set('highlights_intro', e.target.value)}
            />
            <Textarea
              label="Điểm tham quan"
              value={form.places_to_visit}
              onChange={(e) => set('places_to_visit', e.target.value)}
              hint="Mỗi dòng một địa điểm"
            />
            <Textarea
              label="Điểm nổi bật"
              value={form.highlight_bullets}
              onChange={(e) => set('highlight_bullets', e.target.value)}
              hint="Mỗi dòng một mục"
            />
            <Textarea
              label="Bao gồm"
              value={form.inclusions}
              onChange={(e) => set('inclusions', e.target.value)}
              hint="Mỗi dòng một mục"
            />
            <Textarea
              label="Không bao gồm"
              value={form.exclusions}
              onChange={(e) => set('exclusions', e.target.value)}
              hint="Mỗi dòng một mục"
            />
            <Textarea
              label="Lưu ý"
              value={form.notes}
              onChange={(e) => set('notes', e.target.value)}
              hint="Mỗi dòng một mục"
            />
          </FormCluster>
          <FormCluster title="Quote">
            <Input
              label="Nội dung quote"
              value={form.featured_quote_text}
              onChange={(e) => set('featured_quote_text', e.target.value)}
            />
            <Input
              label="Tác giả"
              value={form.featured_quote_author}
              onChange={(e) => set('featured_quote_author', e.target.value)}
            />
          </FormCluster>
        </FormSection>

        <FormSection
          icon={CalendarDays}
          title="Lịch trình từng ngày"
          description="Accordion trên trang chi tiết — thêm / sắp xếp từng ngày."
          actions={
            <Button
              type="button"
              variant="secondary"
              onClick={() => set('itinerary', [...form.itinerary, emptyDay(form.itinerary.length + 1)])}
            >
              <Plus size={16} />
              Thêm ngày
            </Button>
          }
        >
          <Repeater
            items={form.itinerary}
            onChange={(items) =>
              set(
                'itinerary',
                items.map((row, i) => ({ ...row, day_number: Number(row.day_number) || i + 1 })),
              )
            }
            createItem={() => emptyDay(form.itinerary.length + 1)}
            addLabel="Thêm ngày"
            emptyHint="Chưa có lịch trình. Thêm ngày để hiển thị trên public."
            keyOf={(row, i) => row.id ?? `new-day-${i}`}
            renderItem={(row, index, { update }) => (
              <div className="ui-form-grid ui-form-grid--2">
                <Input
                  label="Ngày"
                  type="number"
                  min={1}
                  value={String(row.day_number || index + 1)}
                  onChange={(e) => update({ day_number: Number(e.target.value) || index + 1 })}
                />
                <Select
                  label="Bữa ăn"
                  value={row.meals_included}
                  onChange={(v) => update({ meals_included: v })}
                  options={
                    row.meals_included && !MEAL_OPTIONS.some((o) => o.value === row.meals_included)
                      ? [...MEAL_OPTIONS, { value: row.meals_included, label: row.meals_included }]
                      : MEAL_OPTIONS
                  }
                />
                <Input
                  label="Tiêu đề ngày"
                  value={row.title}
                  onChange={(e) => update({ title: e.target.value })}
                />
                <Input
                  label="Nghỉ đêm tại"
                  value={row.overnight_at}
                  onChange={(e) => update({ overnight_at: e.target.value })}
                />
                <div className="ui-form-grid" style={{ gridColumn: '1 / -1' }}>
                  <Textarea
                    label="Nội dung chi tiết"
                    value={row.content}
                    onChange={(e) => update({ content: e.target.value })}
                  />
                </div>
                <Input
                  label="Phương tiện (icon)"
                  value={row.transport_icons}
                  onChange={(e) => update({ transport_icons: e.target.value })}
                  hint="plane, car, boat, cruise, trekking, walking, bike"
                />
              </div>
            )}
          />
        </FormSection>

        <FormSection
          icon={CircleHelp}
          title="Câu hỏi thường gặp"
          description="FAQ cuối trang chi tiết."
          actions={
            <Button
              type="button"
              variant="secondary"
              onClick={() => set('faqs', [...form.faqs, emptyFaq()])}
            >
              <Plus size={16} />
              Thêm FAQ
            </Button>
          }
        >
          <Repeater
            items={form.faqs}
            onChange={(items) => set('faqs', items)}
            createItem={emptyFaq}
            addLabel="Thêm FAQ"
            emptyHint="Chưa có FAQ."
            keyOf={(row, i) => row.id ?? `new-faq-${i}`}
            renderItem={(row, _i, { update }) => (
              <div className="ui-form-grid">
                <Input
                  label="Câu hỏi"
                  value={row.question}
                  onChange={(e) => update({ question: e.target.value })}
                />
                <Textarea
                  label="Trả lời"
                  value={row.answer}
                  onChange={(e) => update({ answer: e.target.value })}
                />
              </div>
            )}
          />
        </FormSection>

        <FormSection
          icon={Tags}
          title="Phân loại"
          description={
            isCruise
              ? 'Gắn phong cách du lịch (tuỳ chọn).'
              : 'Gắn chủ đề & danh mục để filter trên site.'
          }
        >
          <MultiSelect
            label="Phong cách du lịch"
            placeholder="Chọn một hoặc nhiều…"
            searchable
            value={form.travel_style_ids}
            onChange={(ids) => set('travel_style_ids', ids)}
            options={(themesQuery.data?.items ?? []).map((t) => ({
              value: t.id,
              label: t.name || t.code,
            }))}
            hint={
              form.travel_style_ids.length
                ? `Đã chọn ${form.travel_style_ids.length}`
                : 'Có thể chọn nhiều'
            }
          />
          {!isCruise ? (
            <MultiSelect
              label="Chủ đề Tour"
              placeholder="Chọn một hoặc nhiều danh mục…"
              searchable
              value={form.category_ids}
              onChange={(ids) => set('category_ids', ids)}
              options={(categoriesQuery.data?.items ?? []).map((c) => ({
                value: c.id,
                label: c.name || `#${c.id}`,
              }))}
              hint={
                form.category_ids.length
                  ? `Đã chọn ${form.category_ids.length} danh mục`
                  : 'Có thể chọn nhiều'
              }
            />
          ) : null}
        </FormSection>

        <FormFooter
          cancelHref={copy.listHref}
          submitLabel={copy.saveLabel}
          loading={save.isPending}
        />
        </div>

        <FormMediaAside>
          <FormThumbCard>
            <ImageField
              ariaLabel="Ảnh đại diện"
              folder="packages"
              aspectRatio="3 / 2"
              variant="card"
              value={form.cover}
              onChange={(cover) => set('cover', cover)}
            />
          </FormThumbCard>
        </FormMediaAside>
      </form>
    </div>
  );
}

export function PackageFormPage({ kind }: { kind: PackageType }) {
  return (
    <Suspense fallback={<div>Đang tải form…</div>}>
      <PackageFormInner kind={kind} />
    </Suspense>
  );
}
