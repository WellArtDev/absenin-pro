'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

interface OTEntry { name: string; employee_code: string; total_normal: string; total_overtime: string; work_days: string; }

export default function OvertimePage() {
  const today = new Date().toISOString().split('T')[0];
  const [date, setDate] = useState(today);
  const [data, setData] = useState<OTEntry[]>([]);

  const load = async (d: string) => {
    try { const r = await api.get(`/attendance/report?start=${d}&end=${d}`); setData(r.data||[]); } catch {}
  };
  useEffect(() => { load(date); }, [date]);

  return (<div>
    <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-bold text-slate-900">Lembur</h1><input type="date" value={date} onChange={e=>setDate(e.target.value)} className="h-10 px-4 border border-slate-200 rounded-lg text-sm"/></div>
    <div className="bg-white rounded-xl shadow-sm"><table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100"><th className="px-5 py-3">Karyawan</th><th className="px-5 py-3">Jam Normal</th><th className="px-5 py-3">Jam Lembur</th><th className="px-5 py-3">Total</th><th className="px-5 py-3">Hari Kerja</th></tr></thead>
    <tbody>{data.length===0?<tr><td colSpan={5} className="text-center py-16 text-slate-400">Tidak ada data</td></tr>:data.map((e,i)=><tr key={i} className="border-b border-slate-50 hover:bg-slate-50">
      <td className="px-5 py-3"><div className="font-medium text-sm">{e.name}</div><div className="text-xs text-slate-400">{e.employee_code}</div></td>
      <td className="px-5 py-3 text-sm">{e.total_normal} jam</td><td className="px-5 py-3 text-sm">{e.total_overtime} jam</td>
      <td className="px-5 py-3 text-sm font-medium">{(Number(e.total_normal)+Number(e.total_overtime)).toFixed(1)} jam</td><td className="px-5 py-3 text-sm">{e.work_days||'-'}</td>
    </tr>)}</tbody></table></div>
  </div>);
}
