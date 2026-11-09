/** @type {import('next').NextConfig} */
const nextConfig = {
  turbopack: { root: __dirname },
  reactStrictMode: true,
  images: {
    remotePatterns: [
      { protocol: 'http', hostname: 'localhost' },
      { protocol: 'http', hostname: '127.0.0.1' },
      { protocol: 'https', hostname: 'sipdt.sangihekab.go.id' },
      { protocol: 'https', hostname: 'kominfo.sangihekab.go.id' },
      { protocol: 'https', hostname: 'sangihekab.go.id', pathname: '/wp-content/uploads/**' },
    ],
  },
  async rewrites() {
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
    return [
      { source: '/images/:path*', destination: `${apiBase}/images/:path*`, has: [{ type: 'query', key: 'api', value: '1' }] },
      { source: '/logo.png', destination: `${apiBase}/logo.png` },
      { source: '/api/proxy/:path*', destination: `${apiBase}/api/v1/:path*` },
      { source: '/media/:path*', destination: `${apiBase}/storage/:path*` },
    ];
  },
};
module.exports = nextConfig;
