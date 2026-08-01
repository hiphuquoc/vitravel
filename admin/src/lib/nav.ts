import {
  Anchor,
  Briefcase,
  Building2,
  Compass,
  FolderKanban,
  FolderTree,
  Globe2,
  Image,
  Languages,
  LayoutDashboard,
  Mail,
  Map,
  MessageSquare,
  Newspaper,
  Ship,
  SlidersHorizontal,
  Star,
  Trash2,
  Users,
  Video,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

export type NavItem = {
  label: string;
  href: string;
  icon: LucideIcon;
  match?: string;
  soon?: boolean;
};

export type NavGroup = {
  key: string;
  title: string;
  items: NavItem[];
};

/** Grouped admin navigation — khớp Blade `config/menu.php`. */
export const NAV_GROUPS: NavGroup[] = [
  {
    key: 'overview',
    title: 'Tổng quan',
    items: [{ label: 'Bảng điều khiển', href: '/', icon: LayoutDashboard }],
  },
  {
    key: 'products',
    title: 'Sản phẩm',
    items: [
      { label: 'Gói Tour', href: '/tours/packages/', icon: Map, match: '/tours/packages' },
      { label: 'Gói Cruise', href: '/cruises/packages/', icon: Ship, match: '/cruises/packages' },
      {
        label: 'Danh mục Tour',
        href: '/tours/destinations/',
        icon: Globe2,
        match: '/tours/destinations',
      },
      { label: 'Loại du thuyền', href: '/cruises/types/', icon: Anchor, match: '/cruises/types' },
      {
        label: 'Chủ đề Tour',
        href: '/tours/categories/',
        icon: FolderTree,
        match: '/tours/categories',
      },
      {
        label: 'Danh mục dịch vụ',
        href: '/services/categories/',
        icon: FolderKanban,
        match: '/services/categories',
      },
      {
        label: 'Sản phẩm dịch vụ',
        href: '/services/products/',
        icon: Briefcase,
        match: '/services/products',
      },
    ],
  },
  {
    key: 'content',
    title: 'Nội dung',
    items: [
      { label: 'Slider trang chủ', href: '/content/slides/', icon: SlidersHorizontal, match: '/content/slides' },
      { label: 'Nội dung trang chủ', href: '/content/home/', icon: LayoutDashboard, match: '/content/home' },
      { label: 'Bài viết', href: '/content/articles/', icon: Newspaper, match: '/content/articles' },
      {
        label: 'Chuyên mục Blog',
        href: '/content/blog-categories/',
        icon: FolderTree,
        match: '/content/blog-categories',
      },
    ],
  },
  {
    key: 'brand',
    title: 'Thương hiệu',
    items: [
      { label: 'Đội ngũ', href: '/brand/team/', icon: Users, match: '/brand/team' },
      { label: 'Văn phòng', href: '/brand/offices/', icon: Building2, match: '/brand/offices' },
      { label: 'Công ty', href: '/brand/company/', icon: Building2, match: '/brand/company' },
      { label: 'Giá trị cốt lõi', href: '/brand/values/', icon: Star, match: '/brand/values' },
      { label: 'Lý do chọn', href: '/brand/reasons/', icon: Star, match: '/brand/reasons' },
      { label: 'Đại diện NN', href: '/brand/references/', icon: Users, match: '/brand/references' },
      { label: 'Cảm nhận KH', href: '/brand/reviews/', icon: MessageSquare, match: '/brand/reviews' },
      {
        label: 'Nền tảng ĐG',
        href: '/brand/platforms/',
        icon: Star,
        match: '/brand/platforms',
      },
      { label: 'Thư viện ảnh', href: '/brand/gallery/', icon: Image, match: '/brand/gallery' },
      { label: 'Video', href: '/brand/videos/', icon: Video, match: '/brand/videos' },
    ],
  },
  {
    key: 'leads',
    title: 'Khách hàng tiềm năng',
    items: [
      { label: 'Yêu cầu nhanh', href: '/leads/quick/', icon: Mail, match: '/leads/quick' },
      { label: 'Tour riêng', href: '/leads/custom/', icon: Map, match: '/leads/custom' },
      { label: 'Liên hệ', href: '/leads/contacts/', icon: Mail, match: '/leads/contacts' },
      { label: 'Bình luận', href: '/leads/comments/', icon: MessageSquare, match: '/leads/comments' },
    ],
  },
  {
    key: 'settings',
    title: 'Cài đặt',
    items: [
      { label: 'Ngôn ngữ', href: '/settings/languages/', icon: Languages, match: '/settings/languages' },
      { label: 'Xóa HTML cache', href: '/settings/cache/', icon: Trash2, match: '/settings/cache' },
      {
        label: 'Phong cách du lịch',
        href: '/tours/themes/',
        icon: Compass,
        match: '/tours/themes',
      },
      { label: 'Thư viện Media', href: '/settings/media/', icon: Image, match: '/settings/media' },
      {
        label: 'Hub Tours',
        href: '/settings/hubs/tours_hub/',
        icon: Globe2,
        match: '/settings/hubs',
      },
    ],
  },
];

function normalizePath(pathname: string): string {
  if (!pathname || pathname === '/') return '/';
  return pathname.replace(/\/$/, '') || '/';
}

export function isNavActive(pathname: string, item: NavItem): boolean {
  const path = normalizePath(pathname);
  if (item.href === '/' || item.href === '') return path === '/';
  const base = normalizePath(item.match || item.href);
  return path === base || path.startsWith(`${base}/`);
}
