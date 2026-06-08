'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

interface LogEntry { employee_name: string; employee_code: string; clock_in: string; clock_out: string; normal_hours: string; overtime_hours: string; status: string; gps_lat: string; gps_lng: string; source: string; }
interface Stats { hadir: number; terlambat: number; lembur: number; total: number; }

export default function AttendancePage() {
  const today = new Date().toISOString().split('T')[0];
  const [date, setDate] = useState(today);
  const [log, setLog] = useState<LogEntry[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);

  const load = async (d: string) => {
    try {
      const [s, l] = await Promise.all([api.get(`/attendance/summary?date=${d}`), api.get(`/attendance/log?date=${d}`)]);
      setStats(s.data); setLog(l.data || []);
    } catch {}
  };

  useEffect(() => { load(date); }, [date]);

  const statusBadge = (s: string) => {
    const m: Record<string,string> = { hadir:'bg-primary-50 text-primary-700', terlambat:'bg-yellow-50 text-yellow-700', lembur:'bg-indigo-50 text-indigo-700' };
    return `inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ${m[s]||'bg-slate-100 text-slate-600'}`;
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-slate-900">Presensi</h1>
        <input type="date" value={date} onChange={e => setDate(e.target.value)} className="h-10 px-4 border border-slate-200 rounded-lg text-sm"/>
      </div>

      {stats && <div className="grid grid-cols-4 gap-4 mb-6">
        {[{l:'Hadir',v:stats.hadir,c:'text-primary-600'},{l:'Terlambat',v:stats.terlambat,c:'text-yellow-600'},{l:'Lembur',v:stats.lembur,c:'text-indigo-600'},{l:'Total',v:stats.total,c:'text-slate-600'}].map(s=>(
          <div key={s.l} className="bg-white rounded-xl shadow-sm p-5"><div className="text-xs text-slate-400 mb-1">{s.l}</div><div className={`text-3xl font-bold ${s.c}`}>{s.v}</div></div>
        ))}
      </div>}

      <div className="bg-white rounded-xl shadow-sm">
        <table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
          <th className="px-5 py-3">Karyawan</th><th className="px-5 py-3">Clock In</th><th className="px-5 py-3">Clock Out</th><th className="px-5 py-3">Normal</th><th className="px-5 py-3">Lembur</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Lokasi</th></tr></thead>
          <tbody>{log.length===0?<tr><td colSpan={7} className="text-center py-16 text-slate-400 text-sm">Belum ada presensi hari ini</td></tr>:log.map((e,i)=>(
            <tr key={i} className="border-b border-slate-50 hover:bg-slate-50">
              <td className="px-5 py-3"><div className="font-medium text-sm">{e.employee_name}</div><div className="text-xs text-slate-400">{e.employee_code}</div></td>
              <td className="px-5 py-3 text-sm">{e.clock_in?.substring(11,16)||'-'}</td><td className="px-5 py-3 text-sm">{e.clock_out?.substring(11,16)||'-'}</td>
              <td className="px-5 py-3 text-sm">{e.normal_hours||'0'} jam</td><td className="px-5 py-3 text-sm">{e.overtime_hours||'0'} jam</td>
              <td className="px-5 py-3"><span className={statusBadge(e.status)}>{e.status}</span></td>
              <td className="px-5 py-3 text-sm text-slate-500">{e.gps_lat?`${Number(e.gps_lat).toFixed(4)}, ${Number(e.gps_lng).toFixed(4)}`:e.source||'-'}</td>
            </tr>))}</tbody></table>
      </div>
    </div>
  );
}
