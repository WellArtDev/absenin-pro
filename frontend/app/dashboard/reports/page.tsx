'use client';

import { useState } from 'react';
import { api } from '@/lib/api';

interface ReportRow { name: string; employee_code: string; work_days: number; total_normal: number; total_overtime: number; }

export default function ReportsPage() {
  const today = new Date().toISOString().split('T')[0];
  const firstDay = `${new Date().getFullYear()}-${String(new Date().getMonth()+1).padStart(2,'0')}-01`;
  const [start, setStart] = useState(firstDay);
  const [end, setEnd] = useState(today);
  const [data, setData] = useState<ReportRow[]>([]);
  const [loaded, setLoaded] = useState(false);

  const generate = async () => {
    try { const r = await api.get(`/attendance/report?start=${start}&end=${end}`); setData(r.data||[]); setLoaded(true); } catch {}
  };

  return (<div>
    <h1 className="text-2xl font-bold text-slate-900 mb-6">Report Payroll</h1>
    <div className="bg-white rounded-xl shadow-sm p-6">
      <div className="flex gap-4 items-end mb-6">
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Dari</label><input type="date" value={start} onChange={e=>setStart(e.target.value)} className="h-10 px-3 border border-slate-200 rounded-lg text-sm"/></div>
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Sampai</label><input type="date" value={end} onChange={e=>setEnd(e.target.value)} className="h-10 px-3 border border-slate-200 rounded-lg text-sm"/></div>
        <button onClick={generate} className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">Generate</button>
        {loaded && <button onClick={() => window.open(`/api/v1/attendance/report/csv?start=${start}&end=${end}`)} className="h-10 px-5 bg-primary-50 text-primary-700 border border-primary-200 rounded-lg text-sm font-semibold hover:bg-primary-100">📥 Export CSV</button>}
      </div>
      {!loaded ? <div className="text-center py-16 text-slate-400 text-sm">Pilih periode lalu klik Generate</div> :
       data.length===0 ? <div className="text-center py-16 text-slate-400 text-sm">Tidak ada data</div> :
      <table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100"><th className="px-3 py-2">Nama</th><th className="px-3 py-2">NIK</th><th className="px-3 py-2">Hari Kerja</th><th className="px-3 py-2">Jam Normal</th><th className="px-3 py-2">Jam Lembur</th><th className="px-3 py-2">Cuti</th><th className="px-3 py-2">Izin</th><th className="px-3 py-2">Sakit</th></tr></thead>
      <tbody>{data.map((d,i)=><tr key={i} className="border-b border-slate-50"><td className="px-3 py-3 text-sm font-medium">{d.name}</td><td className="px-3 py-3 text-sm">{d.employee_code}</td><td className="px-3 py-3 text-sm">{d.work_days}</td><td className="px-3 py-3 text-sm">{d.total_normal}</td><td className="px-3 py-3 text-sm">{d.total_overtime}</td><td className="px-3 py-3 text-sm">-</td><td className="px-3 py-3 text-sm">-</td><td className="px-3 py-3 text-sm">-</td></tr>)}</tbody></table>}
    </div>
  </div>);
}
