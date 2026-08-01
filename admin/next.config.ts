import type { NextConfig } from 'next';

const basePath = '/he-thong';
const isProdBuild = process.env.ADMIN_BUILD === '1';

const nextConfig: NextConfig = {
  reactStrictMode: true,
  poweredByHeader: false,
  basePath,
  // Chỉ static export khi build production (npm run build). Dev = live HMR.
  ...(isProdBuild ? { output: 'export' as const } : {}),
  trailingSlash: true,
  images: { unoptimized: true },
  sassOptions: {
    includePaths: ['./src/styles'],
  },
  env: {
    NEXT_PUBLIC_BASE_PATH: basePath,
    NEXT_PUBLIC_SITE_ORIGIN:
      process.env.NEXT_PUBLIC_SITE_ORIGIN || process.env.ADMIN_API_ORIGIN || '',
  },
  async rewrites() {
    if (isProdBuild) return [];
    const origin = (process.env.ADMIN_API_ORIGIN || 'https://vitravel.dev').replace(/\/$/, '');
    return [
      {
        source: '/api/:path*',
        destination: `${origin}/api/:path*`,
        basePath: false,
      },
    ];
  },
};

export default nextConfig;
