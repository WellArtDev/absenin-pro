import Link from 'next/link';

export default function Home() {
  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-4xl font-bold text-primary-600 mb-4">Absenin</h1>
        <p className="text-slate-600 text-lg mb-8">
          SaaS Multi-Tenant — Presensi & Workforce Tracking
        </p>
        <div className="space-x-4">
          <Link
            href="/login"
            className="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors"
          >
            Masuk Dashboard
          </Link>
        </div>
      </div>
    </div>
  );
}
