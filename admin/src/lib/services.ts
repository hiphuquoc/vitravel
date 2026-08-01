import { ApiClientError, apiRequest, getApiBase, getToken } from './api';
import type {
  AdminUser,
  CruiseType,
  CruiseTypeDetail,
  CruiseTypeOption,
  Country,
  CountryDetail,
  MediaFolder,
  MediaImage,
  Option,
  PackageDetail,
  PackageListItem,
  Paginated,
  ServiceCategory,
  ServiceCategoryDetail,
  ServiceItem,
  ServiceDetail,
  TourCategory,
  TourCategoryDetail,
  TravelStyle,
  TravelStyleDetail,
  ValueLabel,
} from './types';
import type { LocaleOption } from './locale';

export type PackageType = 'tour' | 'cruise';

export const authApi = {
  login: (email: string, password: string, deviceName?: string) =>
    apiRequest<{ token: string; token_type: string; user: AdminUser }>('/auth/login', {
      method: 'POST',
      auth: false,
      body: { email, password, device_name: deviceName || 'ViTravel Admin' },
    }),
  me: () => apiRequest<AdminUser>('/auth/me'),
  logout: () => apiRequest<null>('/auth/logout', { method: 'POST' }),
};

export const metaApi = {
  languages: () =>
    apiRequest<{ default_code: string; items: LocaleOption[] }>('/meta/languages'),
};

function packagesApiFor(type: PackageType) {
  return {
    list: (query?: Record<string, string | number | boolean | undefined>) =>
      apiRequest<Paginated<PackageListItem>>('/packages', { query: { type, ...query } }),
    get: (id: number, locale = 'vi') =>
      apiRequest<PackageDetail>(`/packages/${id}`, { query: { locale } }),
    create: (body: Record<string, unknown>) =>
      apiRequest<PackageDetail>('/packages', { method: 'POST', body: { type, ...body } }),
    update: (id: number, body: Record<string, unknown>) =>
      apiRequest<PackageDetail>(`/packages/${id}`, { method: 'PUT', body: { type, ...body } }),
    remove: (id: number) => apiRequest<null>(`/packages/${id}`, { method: 'DELETE' }),
    meta: (locale = 'vi') =>
      apiRequest<{
        countries: Option[];
        travel_styles: Option[];
        cruise_types: CruiseTypeOption[];
        currencies: ValueLabel[];
        default_currency: string;
        statuses: ValueLabel[];
        discount_badges: ValueLabel[];
        languages: LocaleOption[];
        default_locale: string;
      }>('/packages/meta', { query: { locale } }),
  };
}

export const packagesApi = packagesApiFor('tour');
export const cruisePackagesApi = packagesApiFor('cruise');

export const categoriesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<
      Paginated<TourCategory> & { type_options: ValueLabel[] }
    >('/tour-categories', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<TourCategoryDetail>(`/tour-categories/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<TourCategoryDetail>('/tour-categories', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<TourCategoryDetail>(`/tour-categories/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/tour-categories/${id}`, { method: 'DELETE' }),
  meta: (locale = 'vi') =>
    apiRequest<{
      countries: Option[];
      type_options: ValueLabel[];
      languages: LocaleOption[];
      default_locale: string;
    }>('/tour-categories/meta', {
      query: { locale },
    }),
};

export const themesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<Paginated<TravelStyle>>('/travel-styles', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<TravelStyleDetail>(`/travel-styles/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<TravelStyleDetail>('/travel-styles', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<TravelStyleDetail>(`/travel-styles/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/travel-styles/${id}`, { method: 'DELETE' }),
};

export const cruiseTypesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<
      Paginated<CruiseType> & { languages?: LocaleOption[]; default_locale?: string }
    >('/cruise-types', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<CruiseTypeDetail>(`/cruise-types/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<CruiseTypeDetail>('/cruise-types', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<CruiseTypeDetail>(`/cruise-types/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/cruise-types/${id}`, { method: 'DELETE' }),
};

export const countriesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<Paginated<Country>>('/countries', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<CountryDetail>(`/countries/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<CountryDetail>('/countries', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<CountryDetail>(`/countries/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/countries/${id}`, { method: 'DELETE' }),
  meta: (locale = 'vi') =>
    apiRequest<{
      languages: LocaleOption[];
      default_locale: string;
      hub_seo_id: number;
      seo_parents: { id: number; label: string }[];
      home_grid_sizes: ValueLabel[];
    }>('/countries/meta', { query: { locale } }),
};

export const serviceCategoriesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<Paginated<ServiceCategory>>('/service-categories', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<ServiceCategoryDetail>(`/service-categories/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<ServiceCategoryDetail>('/service-categories', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<ServiceCategoryDetail>(`/service-categories/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/service-categories/${id}`, { method: 'DELETE' }),
  meta: (locale = 'vi', cluster?: string) =>
    apiRequest<{
      languages: LocaleOption[];
      default_locale: string;
      clusters: ValueLabel[];
      hub_seo_id: number | null;
      seo_parents: { id: number; label: string }[];
    }>('/service-categories/meta', { query: { locale, cluster } }),
};

export const servicesApi = {
  list: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<Paginated<ServiceItem>>('/services', { query }),
  get: (id: number, locale = 'vi') =>
    apiRequest<ServiceDetail>(`/services/${id}`, { query: { locale } }),
  create: (body: Record<string, unknown>) =>
    apiRequest<ServiceDetail>('/services', { method: 'POST', body }),
  update: (id: number, body: Record<string, unknown>) =>
    apiRequest<ServiceDetail>(`/services/${id}`, { method: 'PUT', body }),
  remove: (id: number) => apiRequest<null>(`/services/${id}`, { method: 'DELETE' }),
  meta: (locale = 'vi', cluster?: string) =>
    apiRequest<{
      languages: LocaleOption[];
      default_locale: string;
      cluster: string;
      clusters: ValueLabel[];
      categories: Option[];
      countries: Option[];
      statuses: ValueLabel[];
      hub_seo_id: number | null;
      seo_parents: { id: number; label: string }[];
    }>('/services/meta', { query: { locale, cluster } }),
};

export const mediaApi = {
  meta: () =>
    apiRequest<{
      max_upload_kb: number;
      accept: string[];
      folders: string[];
      hint: string;
    }>('/media/meta'),
  library: (query?: Record<string, string | number | boolean | undefined>) =>
    apiRequest<Paginated<MediaImage & { created_at?: string | null; path?: string | null }>>(
      '/media/library',
      { query },
    ),
  removeLibrary: (id: number) =>
    apiRequest<null>(`/media/library/${id}`, { method: 'DELETE' }),
  upload: (
    file: File,
    opts: {
      folder: MediaFolder;
      variant?: 'thumb' | 'card' | 'lg' | 'full';
      onProgress?: (pct: number) => void;
      signal?: AbortSignal;
    },
  ) =>
    new Promise<MediaImage>((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      const form = new FormData();
      form.append('file', file);
      form.append('folder', opts.folder);
      if (opts.variant) form.append('variant', opts.variant);

      xhr.open('POST', `${getApiBase()}/media/upload`);
      xhr.setRequestHeader('Accept', 'application/json');
      const token = getToken();
      if (token) xhr.setRequestHeader('Authorization', `Bearer ${token}`);

      xhr.upload.onprogress = (e) => {
        if (!e.lengthComputable || !opts.onProgress) return;
        opts.onProgress(Math.round((e.loaded / e.total) * 100));
      };

      const onAbort = () => xhr.abort();
      opts.signal?.addEventListener('abort', onAbort);

      xhr.onload = () => {
        opts.signal?.removeEventListener('abort', onAbort);
        let json: { success?: boolean; data?: MediaImage; error?: { message?: string; code?: string } } | null =
          null;
        try {
          json = JSON.parse(xhr.responseText);
        } catch {
          reject(new ApiClientError('Phản hồi upload không hợp lệ.', 'INVALID_RESPONSE', xhr.status));
          return;
        }
        if (xhr.status >= 200 && xhr.status < 300 && json?.success && json.data) {
          resolve(json.data);
          return;
        }
        reject(
          new ApiClientError(
            json?.error?.message || 'Upload ảnh thất bại.',
            json?.error?.code || 'UPLOAD_ERROR',
            xhr.status,
          ),
        );
      };

      xhr.onerror = () => {
        opts.signal?.removeEventListener('abort', onAbort);
        reject(new ApiClientError('Không kết nối được khi upload ảnh.', 'NETWORK_ERROR', 0));
      };

      xhr.onabort = () => {
        opts.signal?.removeEventListener('abort', onAbort);
        reject(new ApiClientError('Đã huỷ upload.', 'ABORTED', 0));
      };

      xhr.send(form);
    }),
};

type CrudListQuery = Record<string, string | number | boolean | undefined>;

function crudApi<TList, TDetail = TList>(base: string) {
  return {
    list: (query?: CrudListQuery) => apiRequest<Paginated<TList>>(base, { query }),
    get: (id: number, locale = 'vi') =>
      apiRequest<TDetail>(`${base}/${id}`, { query: { locale } }),
    create: (body: Record<string, unknown>) =>
      apiRequest<TDetail>(base, { method: 'POST', body }),
    update: (id: number, body: Record<string, unknown>) =>
      apiRequest<TDetail>(`${base}/${id}`, { method: 'PUT', body }),
    remove: (id: number) => apiRequest<null>(`${base}/${id}`, { method: 'DELETE' }),
  };
}

export const homeSlidesApi = {
  ...crudApi<Record<string, unknown>>('/home-slides'),
  meta: () => apiRequest<Record<string, unknown>>('/home-slides/meta'),
};

export const homeSectionsApi = {
  get: (locale = 'vi') => apiRequest<Record<string, unknown>>('/home-sections', { query: { locale } }),
  update: (body: Record<string, unknown>) =>
    apiRequest<Record<string, unknown>>('/home-sections', { method: 'PUT', body }),
};

export const blogCategoriesApi = {
  ...crudApi<Record<string, unknown>>('/blog-categories'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/blog-categories/meta', { query: { locale } }),
};

export const articlesApi = {
  ...crudApi<Record<string, unknown>>('/articles'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/articles/meta', { query: { locale } }),
};

export const teamMembersApi = {
  ...crudApi<Record<string, unknown>>('/team-members'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/team-members/meta', { query: { locale } }),
};

export const officesApi = {
  ...crudApi<Record<string, unknown>>('/offices'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/offices/meta', { query: { locale } }),
};

export const companyProfileApi = {
  get: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/company-profile', { query: { locale } }),
  update: (body: Record<string, unknown>) =>
    apiRequest<Record<string, unknown>>('/company-profile', { method: 'PUT', body }),
};

export const companyValuesApi = crudApi<Record<string, unknown>>('/company-values');
export const reasonsApi = crudApi<Record<string, unknown>>('/reasons');
export const referencePersonsApi = crudApi<Record<string, unknown>>('/reference-persons');
export const reviewsApi = crudApi<Record<string, unknown>>('/reviews');
export const reviewPlatformsApi = crudApi<Record<string, unknown>>('/review-platforms');

export const galleryAlbumsApi = {
  ...crudApi<Record<string, unknown>>('/gallery-albums'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/gallery-albums/meta', { query: { locale } }),
};

export const videosApi = {
  ...crudApi<Record<string, unknown>>('/videos'),
  meta: (locale = 'vi') =>
    apiRequest<Record<string, unknown>>('/videos/meta', { query: { locale } }),
};

export const leadsApi = {
  quickInquiries: (query?: CrudListQuery) =>
    apiRequest<Paginated<Record<string, unknown>> & { statuses?: ValueLabel[] }>(
      '/leads/quick-inquiries',
      { query },
    ),
  updateQuickInquiryStatus: (id: number, status: string) =>
    apiRequest(`/leads/quick-inquiries/${id}/status`, { method: 'PUT', body: { status } }),
  customTours: (query?: CrudListQuery) =>
    apiRequest<Paginated<Record<string, unknown>> & { statuses?: ValueLabel[] }>(
      '/leads/custom-tours',
      { query },
    ),
  updateCustomTourStatus: (id: number, status: string) =>
    apiRequest(`/leads/custom-tours/${id}/status`, { method: 'PUT', body: { status } }),
  contacts: (query?: CrudListQuery) =>
    apiRequest<Paginated<Record<string, unknown>> & { statuses?: ValueLabel[] }>(
      '/leads/contacts',
      { query },
    ),
  updateContactStatus: (id: number, status: string) =>
    apiRequest(`/leads/contacts/${id}/status`, { method: 'PUT', body: { status } }),
};

export const commentsApi = {
  list: (query?: CrudListQuery) =>
    apiRequest<Paginated<Record<string, unknown>>>('/comments', { query }),
  approve: (id: number) => apiRequest(`/comments/${id}/approve`, { method: 'POST' }),
  reject: (id: number) => apiRequest(`/comments/${id}/reject`, { method: 'POST' }),
};

export const languagesApi = {
  list: () => apiRequest<{ items: Record<string, unknown>[] }>('/languages'),
};

export const cacheApi = {
  clear: () => apiRequest<{ cleared: number }>('/cache/clear', { method: 'POST' }),
};

export const listingHubsApi = {
  get: (hubKey: string, locale = 'vi') =>
    apiRequest<Record<string, unknown>>(`/listing-hubs/${hubKey}`, { query: { locale } }),
  update: (hubKey: string, body: Record<string, unknown>) =>
    apiRequest<Record<string, unknown>>(`/listing-hubs/${hubKey}`, { method: 'PUT', body }),
};
