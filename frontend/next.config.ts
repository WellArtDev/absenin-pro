/** @type {import('next').NextConfig} */
const nextConfig = {
  rewrites: async () => [
    {
      source: '/api/v1/:path*',
      destination: `${process.env.NEXT_PUBLIC_API_URL || 'https://api.absenin.com'}/api/v1/:path*`,
    },
  ],
};

export default nextConfig;
