'use client';

import { useMutation } from '@tanstack/react-query';
import { Trash2 } from 'lucide-react';
import toast from '@/lib/toast';
import { cacheApi } from '@/lib/services';
import { Button } from '@/components/ui/Button';
import { PageHeader } from '@/components/ui/Page';

export default function CachePage() {
  const clear = useMutation({
    mutationFn: () => cacheApi.clear(),
    onSuccess: (data) => {
      toast.success(`Đã xoá ${data.cleared} file cache HTML`);
    },
    onError: (err: Error) => toast.error(err.message),
  });

  return (
    <div>
      <PageHeader
        eyebrow="Cài đặt"
        title="Xóa HTML cache"
        description="Xóa cache HTML trang public và menu."
      />
      <div className="ui-card" style={{ padding: '1.5rem', maxWidth: 32 * 16 }}>
        <p style={{ color: 'var(--admin-muted)', marginBottom: '1rem' }}>
          Thao tác này xóa toàn bộ file cache HTML đã render.
        </p>
        <Button
          type="button"
          onClick={() => {
            if (confirm('Xóa toàn bộ HTML cache?')) clear.mutate();
          }}
          disabled={clear.isPending}
        >
          <Trash2 size={16} />
          {clear.isPending ? 'Đang xóa…' : 'Xóa cache ngay'}
        </Button>
      </div>
    </div>
  );
}
