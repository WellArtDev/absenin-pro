'use client';

import { useState, useEffect, useCallback } from 'react';
import { api } from '@/lib/api';

interface Summary { hadir: number; terlambat: number; izin: number; cuti: number; total: number }
interface LogEntry {
  employee_name: string; employee_code: string; position: string;
  clock_in: string; clock_out: string; status: string;
}

export default function DashboardHome() {
  const today = new Date().toISOString().split('T')[0];
  const [date] = useState(today);
  const [summary, setSummary] = useState<Summary | null>(null);
  const [entries, setEntries] = useState<LogEntry[]>([]);
  const [filter, setFilter] = useState('Semua');
  const [loading, setLoading] = useState(true);
  const [lastRefresh, setLastRefresh] = useState<Date>(new Date());

  const fetchData = useCallback(async () => {
    try {
      const [s, l] = await Promise.all([
        api.get(`/attendance/summary?date=${date}`),
        api.get(`/attendance/log?date=${date}`),
      ]);
      setSummary(s.data);
      setEntries(l.data || []);
      setLastRefresh(new Date());
    } catch { /* empty */ }
    setLoading(false);
  }, [date]);

  useEffect(() => { fetchData(); const i = setInterval(fetchData, 60000); return () => clearInterval(i); }, [fetchData]);

  const filtered = filter === 'Semua' ? entries : entries.filter(e => {
    if (filter === 'Hadir') return e.status === 'hadir';
    if (filter === 'Terlambat') return e.status === 'terlambat';
    if (filter === 'Izin') return e.status === 'izin';
    if (filter === 'Cuti') return e.status === 'cuti';
    return true;
  });

  const statusBadge = (s: string) => {
    const map: Record<string, string> = { hadir: 'bg-primary-50 text-primary-700', terlambat: 'bg-yellow-50 text-yellow-700', izin: 'bg-blue-50 text-blue-700', cuti: 'bg-purple-50 text-purple-700', lembur: 'bg-indigo-50 text-indigo-700' };
    return `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${map[s] || 'bg-slate-100 text-slate-600'}`;
  };

  if (loading) return <div className="animate-pulse space-y-6"><div className="grid grid-cols-4 gap-4">{Array(4).fill(0).map((_,i)=><div key={i} className="bg-white rounded-xl h-24"/>)}</div><div className="bg-white rounded-xl h-64"/></div>;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-slate-900">Dashboard</h1>
        <div className="flex items-center gap-3 text-sm text-slate-400">
          <span>{new Intl.DateTimeFormat('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' }).format(new Date(date))}</span>
          <button onClick={fetchData} className="text-primary-600 hover:text-primary-700 text-xs">&#x21bb; Refresh</button>
        </div>
      </div>

      {summary && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          {[{ label: 'Hadir', value: summary.hadir, color: 'text-primary-600' }, { label: 'Terlambat', value: summary.terlambat, color: 'text-yellow-600' }, { label: 'Izin', value: summary.izin ?? 0, color: 'text-blue-600' }, { label: 'Cuti', value: summary.cuti ?? 0, color: 'text-purple-600' }].map(s => (
            <div key={s.label} className="bg-white rounded-xl shadow-sm p-5">
              <div className="text-xs text-slate-400 mb-1">{s.label}</div>
              <div className={`text-3xl font-bold ${s.color}`}>{s.value}</div>
            </div>
          ))}
        </div>
      )}

      <div className="bg-white rounded-xl shadow-sm">
        <div className="flex gap-1 px-5 pt-5 border-b border-slate-100 pb-3">
          {['Semua','Hadir','Terlambat','Izin','Cuti'].map(f => (
            <button key={f} onClick={() => setFilter(f)} className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${filter===f ? 'bg-primary-50 text-primary-700' : 'text-slate-500 hover:bg-slate-50'}`}>{f}</button>
          ))}
        </div>
        {filtered.length === 0 ? (
          <div className="text-center py-16 text-slate-400 text-sm">Belum ada presensi hari ini</div>
        ) : (
          <table className="w-full">
            <thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
              <th className="px-5 py-3">Karyawan</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Clock In</th><th className="px-5 py-3">Clock Out</th></tr></thead>
            <tbody>
              {filtered.map((e, i) => (
                <tr key={i} className="border-b border-slate-50 hover:bg-slate-50">
                  <td className="px-5 py-3"><div className="font-medium text-sm">{e.employee_name}</div><div className="text-xs text-slate-400">{e.employee_code}</div></td>
                  <td className="px-5 py-3"><span className={statusBadge(e.status)}>{e.status === 'hadir' ? 'Hadir' : e.status === 'terlambat' ? 'Terlambat' : e.status}</span></td>
                  <td className="px-5 py-3 text-sm">{e.clock_in?.substring(11,16) || '-'}</td>
                  <td className="px-5 py-3 text-sm">{e.clock_out?.substring(11,16) || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      <div className="text-xs text-slate-300 mt-3 text-right">Terakhir refresh: {lastRefresh.toLocaleTimeString('id-ID')}</div>
    </div>
  );
}
