'use client';

import { useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import toast from '@/lib/toast';
import { mediaApi } from '@/lib/services';
import { Input } from '@/components/ui/Field';
import { EmptyState, PageHeader } from '@/components/ui/Page';
import {
  DEFAULT_LIST_PER_PAGE,
  EntityActions,
  EntityList,
  EntityMain,
  EntityPagination,
  EntityRow,
  EntityThumb,
} from '@/components/ui/EntityList';

export default function MediaLibraryPage() {
  const qc = useQueryClient();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(DEFAULT_LIST_PER_PAGE);

  const key = useMemo(() => ['media-library', { search, page, perPage }], [search, page, perPage]);

  const listQuery = useQuery({
    queryKey: key,
    queryFn: () =>
      mediaApi.library({
        search: search || undefined,
        page,
        per_page: perPage,
      }),
  });

  const remove = useMutation({
    mutationFn: (id: number) => mediaApi.removeLibrary(id),
    onSuccess: async () => {
      toast.success('Đã xóa media');
      await qc.invalidateQueries({ queryKey: ['media-library'] });
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const items = listQuery.data?.items ?? [];
  const meta = listQuery.data?.meta;

  return (
    <div>
      <PageHeader
        eyebrow="Cài đặt"
        title="Thư viện Media"
        description="Danh sách media đã upload — có thể xóa."
      />
      <div className="ui-toolbar">
        <div className="ui-toolbar__search">
          <Input
            label="Tìm kiếm"
            value={search}
            onChange={(e) => {
              setPage(1);
              setSearch(e.target.value);
            }}
          />
        </div>
      </div>

      <EntityPagination
        page={meta?.current_page ?? page}
        lastPage={meta?.last_page ?? 1}
        total={meta?.total ?? 0}
        perPage={perPage}
        unitLabel="file"
        loading={listQuery.isLoading}
        onPageChange={setPage}
        onPerPageChange={(n) => {
          setPage(1);
          setPerPage(n);
        }}
      />

      <EntityList
        loading={listQuery.isLoading}
        empty={
          items.length === 0 ? (
            <EmptyState title="Chưa có media" description="Upload từ form entity." />
          ) : undefined
        }
      >
        {items.map((item) => (
          <EntityRow key={item.id} media>
            <EntityThumb src={item.url_thumb || item.url} alt={item.alt || item.filename || ''} />
            <EntityMain
              title={item.filename || `Media #${item.id}`}
              facts={
                <>
                  <span>{item.path || ''}</span>
                  {item.width && item.height ? (
                    <span>
                      {' '}
                      · {item.width}×{item.height}
                    </span>
                  ) : null}
                </>
              }
            />
            <EntityActions
              onDelete={() => {
                if (confirm('Xóa media này?')) remove.mutate(item.id);
              }}
            />
          </EntityRow>
        ))}
      </EntityList>
    </div>
  );
}
