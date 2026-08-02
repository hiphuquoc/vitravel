'use client';

import type { ReactNode } from 'react';
import Link from 'next/link';
import { Save } from 'lucide-react';
import { Button } from '@/components/ui/Button';

type FormFooterProps = {
  /** Href nút Hủy — bỏ qua nếu không cần. */
  cancelHref?: string;
  cancelLabel?: string;
  submitLabel: string;
  loading?: boolean;
  /** Nút / nội dung phụ bên trái (trước Hủy). */
  leading?: ReactNode;
  className?: string;
};

/** Thanh sticky đáy form — Hủy + Lưu dùng chung mọi trang edit. */
export function FormFooter({
  cancelHref,
  cancelLabel = 'Hủy',
  submitLabel,
  loading = false,
  leading,
  className = 'ui-form-footer',
}: FormFooterProps) {
  return (
    <div className={className}>
      {leading}
      {cancelHref ? (
        <Link href={cancelHref}>
          <Button type="button" variant="secondary">
            {cancelLabel}
          </Button>
        </Link>
      ) : null}
      <Button type="submit" loading={loading}>
        <Save size={17} />
        {submitLabel}
      </Button>
    </div>
  );
}
