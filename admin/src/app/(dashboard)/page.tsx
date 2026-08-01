'use client';

import Link from 'next/link';
import {
  Anchor,
  Briefcase,
  Building2,
  Compass,
  FolderKanban,
  FolderTree,
  Globe2,
  Image,
  Mail,
  Map,
  MessageSquare,
  Newspaper,
  Ship,
  SlidersHorizontal,
  Users,
} from 'lucide-react';
import { PageHeader } from '@/components/ui/Page';

const cards = [
  { href: '/tours/packages/', title: 'Gói Tour', desc: 'Sản phẩm tour.', icon: Map },
  { href: '/cruises/packages/', title: 'Gói Cruise', desc: 'Sản phẩm du thuyền.', icon: Ship },
  {
    href: '/tours/destinations/',
    title: 'Danh mục Tour',
    desc: 'Quốc gia / điểm đến.',
    icon: Globe2,
  },
  { href: '/cruises/types/', title: 'Loại du thuyền', desc: 'Phân nhóm cruise.', icon: Anchor },
  { href: '/tours/categories/', title: 'Chủ đề Tour', desc: 'Nhóm lọc tour.', icon: FolderTree },
  {
    href: '/services/categories/',
    title: 'Danh mục dịch vụ',
    desc: 'Cluster dịch vụ.',
    icon: FolderKanban,
  },
  {
    href: '/services/products/',
    title: 'Sản phẩm dịch vụ',
    desc: 'Catalog dịch vụ.',
    icon: Briefcase,
  },
  {
    href: '/content/slides/',
    title: 'Slider trang chủ',
    desc: 'Hero slides.',
    icon: SlidersHorizontal,
  },
  { href: '/content/articles/', title: 'Bài viết', desc: 'Blog / guide.', icon: Newspaper },
  { href: '/brand/team/', title: 'Đội ngũ', desc: 'Team members.', icon: Users },
  { href: '/brand/company/', title: 'Công ty', desc: 'About / profile.', icon: Building2 },
  { href: '/leads/quick/', title: 'Yêu cầu nhanh', desc: 'Lead inbox.', icon: Mail },
  { href: '/leads/comments/', title: 'Bình luận', desc: 'Duyệt comment.', icon: MessageSquare },
  {
    href: '/tours/themes/',
    title: 'Phong cách du lịch',
    desc: 'Travel styles (Cài đặt).',
    icon: Compass,
  },
  { href: '/settings/media/', title: 'Thư viện Media', desc: 'Quản lý file ảnh.', icon: Image },
];

export default function DashboardPage() {
  return (
    <div>
      <PageHeader
        eyebrow="Tổng quan"
        title="Xin chào, Admin"
        description="Console quản trị ViTravel — sản phẩm, nội dung, thương hiệu và leads."
      />

      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(16rem, 1fr))',
          gap: '1.25rem',
        }}
      >
        {cards.map((card) => {
          const Icon = card.icon;
          return (
            <Link
              key={card.href}
              href={card.href}
              className="ui-card"
              style={{
                padding: '1.5rem',
                display: 'block',
                transition: 'transform 180ms var(--admin-ease), box-shadow 180ms',
              }}
            >
              <div
                style={{
                  width: '2.75rem',
                  height: '2.75rem',
                  borderRadius: '0.75rem',
                  background: 'var(--admin-primary-100)',
                  color: 'var(--admin-primary-700)',
                  display: 'grid',
                  placeItems: 'center',
                  marginBottom: '1rem',
                }}
              >
                <Icon size={20} />
              </div>
              <h2 style={{ fontSize: '1.2rem', fontWeight: 800, marginBottom: '0.4rem' }}>
                {card.title}
              </h2>
              <p style={{ color: 'var(--admin-muted)', fontSize: '0.95rem' }}>{card.desc}</p>
            </Link>
          );
        })}
      </div>
    </div>
  );
}
