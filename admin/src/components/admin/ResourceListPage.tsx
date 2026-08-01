'use client';

import type { ReactNode } from 'react';
import Link from 'next/link';
import { useMemo, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Plus } from 'lucide-react';
import toast from '@/lib/toast';
import { Button } from '@/components/ui/Button';
import { Input, Select } from '@/components/ui/Field';
import { Badge, EmptyState, PageHeader } from '@/components/ui/Page';
import { HeadActions, HeadCta } from '@/components/ui/HeadActions';
import {
  DEFAULT_LIST_PER_PAGE,
  EntityActions,
  EntityList,
  EntityMain,
  EntityPagination,
  EntityRow,
  EntityThumb,
} from '@/components/ui/EntityList';

type Row = Record<string, unknown> & { id: number };

type Props = {
  eyebrow: string;
  title: string;
  description?: string;
  queryKey: string;
  createHref: string;
  editHref: (id: number) => string;
  createLabel: string;
  createSubtitle?: string;
  unitLabel?: string;
  listFn: (q: Record<string, string | number | boolean | undefined>) => Promise<{
    items: Row[];
    meta?: { current_page: number; last_page: number; per_page: number; total: number };
  }>;
  removeFn?: (id: number) => Promise<unknown>;
  titleOf: (row: Row) => string;
  slugOf?: (row: Row) => string | null | undefined;
  badgeOf?: (row: Row) => ReactNode;
  thumbOf?: (row: Row) => string | null | undefined;
  statusOptions?: { value: string; label: string }[];
  statusKey?: string;
};

export function ResourceListPage({
  eyebrow,
  title,
  description,
  queryKey,
  createHref,
  editHref,
  createLabel,
  createSubtitle,
  unitLabel = 'mục',
  listFn,
  removeFn,
  titleOf,
  slugOf,
  badgeOf,
  thumbOf,
  statusOptions,
  statusKey = 'status',
}: Props) {
  const qc = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(DEFAULT_LIST_PER_PAGE);

  const key = useMemo(
    () => [queryKey, { search, status, page, perPage }],
    [queryKey, search, status, page, perPage],
  );

  const listQuery = useQuery({
    queryKey: key,
    queryFn: () =>
      listFn({
        search: search || undefined,
        [statusKey]: status || undefined,
        page,
        per_page: perPage,
      }),
  });

  const remove = useMutation({
    mutationFn: (id: number) => (removeFn ? removeFn(id) : Promise.resolve()),
    onSuccess: async () => {
      toast.success('Đã xóa');
      await qc.invalidateQueries({ queryKey: [queryKey] });
    },
    onError: (err: Error) => toast.error(err.message),
  });

  const items = listQuery.data?.items ?? [];
  const meta = listQuery.data?.meta;

  return (
    <div>
      <PageHeader
        eyebrow={eyebrow}
        title={title}
        description={description}
        actions={
          <HeadActions
            primary={
              <HeadCta
                href={createHref}
                icon={Plus}
                title={createLabel}
                subtitle={createSubtitle || 'Thêm mới'}
              />
            }
          />
        }
      />

      <div className="ui-toolbar">
        <div className="ui-toolbar__search">
          <Input
            label="Tìm kiếm"
            placeholder="Từ khóa…"
            value={search}
            onChange={(e) => {
              setPage(1);
              setSearch(e.target.value);
            }}
          />
        </div>
        {statusOptions ? (
          <div className="ui-toolbar__select">
            <Select
              label="Trạng thái"
              value={status}
              onChange={(v) => {
                setPage(1);
                setStatus(v);
              }}
              placeholder="Tất cả"
              options={statusOptions}
            />
          </div>
        ) : null}
      </div>

      <EntityPagination
        page={meta?.current_page ?? page}
        lastPage={meta?.last_page ?? 1}
        total={meta?.total ?? 0}
        perPage={perPage}
        unitLabel={unitLabel}
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
            <EmptyState
              title="Chưa có dữ liệu"
              description="Bấm thêm mới để bắt đầu."
              action={
                <Link href={createHref}>
                  <Button>
                    <Plus size={16} />
                    {createLabel}
                  </Button>
                </Link>
              }
            />
          ) : undefined
        }
      >
        {items.map((row) => {
          const thumb = thumbOf?.(row);
          return (
            <EntityRow key={row.id} media={!!thumb}>
              {thumb ? (
                <EntityThumb src={thumb} alt={titleOf(row)} href={editHref(row.id)} />
              ) : null}
              <EntityMain
                title={titleOf(row)}
                href={editHref(row.id)}
                slug={slugOf?.(row) || undefined}
                badges={badgeOf?.(row)}
              />
              <EntityActions
                editHref={editHref(row.id)}
                onDelete={
                  removeFn
                    ? () => {
                        if (confirm('Xóa mục này?')) remove.mutate(row.id);
                      }
                    : undefined
                }
              />
            </EntityRow>
          );
        })}
      </EntityList>
    </div>
  );
}
