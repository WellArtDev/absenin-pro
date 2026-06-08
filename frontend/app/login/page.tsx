'use client';

import { useState, FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import { api } from '@/lib/api';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await api.post('/auth/login', { email, password });
      router.push('/dashboard');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login gagal');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-xl shadow-lg p-10 w-full max-w-[400px]">
        <h1 className="text-2xl font-bold text-slate-900 mb-1">Absenin</h1>
        <p className="text-sm text-slate-500 mb-8">Masuk ke dashboard HR</p>

        {error && (
          <div className="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="block text-xs font-medium text-slate-500 mb-1">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full h-11 px-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary-600 focus:ring-3 focus:ring-primary-100"
              required
              autoFocus
            />
          </div>

          <div className="mb-6">
            <label className="block text-xs font-medium text-slate-500 mb-1">Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full h-11 px-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary-600 focus:ring-3 focus:ring-primary-100"
              required
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full h-11 bg-primary-600 text-white rounded-lg font-semibold text-sm hover:bg-primary-700 disabled:opacity-50 transition-colors"
          >
            {loading ? 'Masuk...' : 'Masuk'}
          </button>
        </form>
      </div>
    </div>
  );
}
