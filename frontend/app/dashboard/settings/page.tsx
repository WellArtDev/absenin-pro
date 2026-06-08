'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

export default function SettingsPage() {
  const [workStart, setWorkStart] = useState('09:00');
  const [workEnd, setWorkEnd] = useState('17:00');
  const [tolerance, setTolerance] = useState('15');
  const [otMode, setOtMode] = useState('auto');
  const [gracePeriod, setGracePeriod] = useState('30');
  const [toast, setToast] = useState('');
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    api.get('/settings').then(r => {
      const d = r.data;
      if (d.work_start) setWorkStart(d.work_start);
      if (d.work_end) setWorkEnd(d.work_end);
      if (d.tolerance_minutes) setTolerance(String(d.tolerance_minutes));
      if (d.overtime_mode) setOtMode(d.overtime_mode);
      if (d.grace_period) setGracePeriod(String(d.grace_period));
      setLoaded(true);
    }).catch(() => setLoaded(true));
  }, []);

  const save = async () => {
    const body: Record<string,string> = { work_start:workStart, work_end:workEnd, tolerance_minutes:tolerance, overtime_mode:otMode };
    if (otMode==='checkout') body.grace_period = gracePeriod;
    try { await api.put('/settings', body); setToast('Pengaturan disimpan'); setTimeout(()=>setToast(''),3000); } catch {}
  };

  if (!loaded) return <div className="animate-pulse"><div className="bg-white rounded-xl h-64"/></div>;

  return (<div>
    {toast&&<div className="fixed top-4 right-4 bg-white rounded-lg shadow-lg px-5 py-3 text-sm border-l-3 border-primary-600 z-50">{toast}</div>}
    <h1 className="text-2xl font-bold text-slate-900 mb-6">Pengaturan</h1>
    <div className="bg-white rounded-xl shadow-sm p-6">
      <h3 className="text-base font-semibold mb-4">Jam Kerja</h3>
      <div className="flex gap-4 items-end mb-6">
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Jam Masuk</label><input type="time" value={workStart} onChange={e=>setWorkStart(e.target.value)} className="h-10 px-3 border border-slate-200 rounded-lg text-sm"/></div>
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Jam Pulang</label><input type="time" value={workEnd} onChange={e=>setWorkEnd(e.target.value)} className="h-10 px-3 border border-slate-200 rounded-lg text-sm"/></div>
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Toleransi (menit)</label><input type="number" value={tolerance} onChange={e=>setTolerance(e.target.value)} className="h-10 w-20 px-3 border border-slate-200 rounded-lg text-sm"/></div>
      </div>

      <h3 className="text-base font-semibold mb-4">Mode Lembur</h3>
      <div className="flex gap-6 items-center mb-4">
        <label className="flex items-center gap-2 text-sm"><input type="radio" name="ot" value="auto" checked={otMode==='auto'} onChange={()=>setOtMode('auto')}/> Auto Lembur</label>
        <label className="flex items-center gap-2 text-sm"><input type="radio" name="ot" value="checkout" checked={otMode==='checkout'} onChange={()=>setOtMode('checkout')}/> Checkout → Clock-in Lembur</label>
        <div><label className="block text-xs font-medium text-slate-500 mb-1">Grace Period (menit)</label><input type="number" value={gracePeriod} onChange={e=>setGracePeriod(e.target.value)} disabled={otMode!=='checkout'} className="h-10 w-20 px-3 border border-slate-200 rounded-lg text-sm disabled:opacity-30"/></div>
      </div>

      <button onClick={save} className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">Simpan Pengaturan</button>
    </div>
  </div>);
}
