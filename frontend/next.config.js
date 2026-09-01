/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  images: {
    remotePatterns: [
      { protocol: 'http', hostname: 'localhost' },
      { protocol: 'http', hostname: '127.0.0.1' },
      { protocol: 'https', hostname: 'sipdt.sangihekab.go.id' },
      { protocol: 'https', hostname: 'kominfo.sangihekab.go.id' },
    ],
  },
  async rewrites() {
    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
    return [
      { source: '/api/proxy/:path*', destination: `${apiBase}/api/v1/:path*` },
    ];
  },
};
module.exports = nextConfig;
