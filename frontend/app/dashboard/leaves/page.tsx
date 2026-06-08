'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

interface Leave { id: string; employee_name: string; employee_code: string; leave_type: string; start_date: string; end_date: string; days_count: number; reason: string; status: string; }

export default function LeavesPage() {
  const [pending, setPending] = useState<Leave[]>([]);
  const [history, setHistory] = useState<Leave[]>([]);
  const [toast, setToast] = useState('');

  const load = async () => {
    try {
      const [p, h] = await Promise.all([api.get('/leaves?status=pending'), api.get('/leaves?limit=50')]);
      setPending(p.data||[]); setHistory(h.data||[]);
    } catch {}
  };
  useEffect(() => { load(); }, []);

  const tt = (m:string) => { setToast(m); setTimeout(()=>setToast(''),3000); };
  const typeBadge = (t:string) => { const m:Record<string,string>={cuti_tahunan:'bg-blue-50 text-blue-700',izin:'bg-yellow-50 text-yellow-700',sakit:'bg-red-50 text-red-700'}; return `px-2.5 py-0.5 rounded-full text-xs font-medium ${m[t]||'bg-slate-100'}`; };
  const statusBadge = (s:string) => { const m:Record<string,string>={disetujui:'bg-primary-50 text-primary-700',ditolak:'bg-red-50 text-red-700',pending:'bg-yellow-50 text-yellow-700'}; return `px-2.5 py-0.5 rounded-full text-xs font-medium ${m[s]||'bg-slate-100'}`; };

  const approve = async (id:string) => { await api.post(`/leaves/${id}/approve`,{approved_by:'web'}); tt('Disetujui'); load(); };
  const reject = async (id:string) => { const r = prompt('Alasan penolakan:'); if(!r)return; await api.post(`/leaves/${id}/reject`,{reason:r}); tt('Ditolak'); load(); };

  return (<div>
    {toast&&<div className="fixed top-4 right-4 bg-white rounded-lg shadow-lg px-5 py-3 text-sm border-l-3 border-primary-600 z-50">{toast}</div>}
    <h1 className="text-2xl font-bold text-slate-900 mb-6">Cuti & Izin</h1>

    <div className="bg-white rounded-xl shadow-sm mb-6 p-5"><h3 className="text-base font-semibold mb-4">Menunggu Approval</h3>
      <table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100"><th className="px-3 py-2">Karyawan</th><th className="px-3 py-2">Tipe</th><th className="px-3 py-2">Tanggal</th><th className="px-3 py-2">Hari</th><th className="px-3 py-2">Alasan</th><th className="px-3 py-2"/></tr></thead>
      <tbody>{pending.length===0?<tr><td colSpan={6} className="text-center py-12 text-slate-400">✅ Semua pengajuan sudah diproses</td></tr>:pending.map(l=><tr key={l.id} className="border-b border-slate-50">
        <td className="px-3 py-3"><div className="font-medium text-sm">{l.employee_name}</div><div className="text-xs text-slate-400">{l.employee_code}</div></td>
        <td className="px-3 py-3"><span className={typeBadge(l.leave_type)}>{l.leave_type}</span></td>
        <td className="px-3 py-3 text-sm">{l.start_date} - {l.end_date}</td><td className="px-3 py-3 text-sm">{l.days_count} hari</td>
        <td className="px-3 py-3 text-sm text-slate-500 max-w-[120px] truncate">{l.reason||'-'}</td>
        <td className="px-3 py-3"><div className="flex gap-2"><button onClick={()=>approve(l.id)} className="h-8 px-3 bg-primary-600 text-white rounded text-xs font-semibold hover:bg-primary-700">Setujui</button><button onClick={()=>reject(l.id)} className="h-8 px-3 border border-red-200 text-red-500 rounded text-xs font-semibold hover:bg-red-50">Tolak</button></div></td>
      </tr>)}</tbody></table></div>

    <div className="bg-white rounded-xl shadow-sm p-5"><h3 className="text-base font-semibold mb-4">Riwayat</h3>
      <table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100"><th className="px-3 py-2">Karyawan</th><th className="px-3 py-2">Tipe</th><th className="px-3 py-2">Tanggal</th><th className="px-3 py-2">Status</th></tr></thead>
      <tbody>{history.length===0?<tr><td colSpan={4} className="text-center py-12 text-slate-400">Belum ada riwayat</td></tr>:history.map(l=><tr key={l.id} className="border-b border-slate-50">
        <td className="px-3 py-3 text-sm font-medium">{l.employee_name}</td><td className="px-3 py-3 text-sm">{l.leave_type}</td><td className="px-3 py-3 text-sm">{l.start_date} - {l.end_date}</td>
        <td className="px-3 py-3"><span className={statusBadge(l.status)}>{l.status}</span></td>
      </tr>)}</tbody></table></div>
  </div>);
}
