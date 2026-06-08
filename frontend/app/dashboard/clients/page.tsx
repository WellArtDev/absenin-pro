'use client';

import { useState, useEffect, FormEvent } from 'react';
import { api } from '@/lib/api';

interface Client { id: string; name: string; address: string; gps_lat: number; gps_lng: number; radius_meters: number; }
const empty = { id:'', name:'', address:'', gps_lat:0, gps_lng:0, radius_meters:10 };

export default function ClientsPage() {
  const [clients, setClients] = useState<Client[]>([]);
  const [show, setShow] = useState(false);
  const [form, setForm] = useState({...empty});
  const [toast, setToast] = useState('');

  const load = async () => { try { const r=await api.get('/clients'); setClients(r.data||[]); } catch {} };
  useEffect(() => { load(); }, []);

  const tt = (m:string) => { setToast(m); setTimeout(()=>setToast(''),3000); };
  const open = (c?:Client) => { setForm(c?{...c}:{...empty}); setShow(true); };
  const save = async (e:FormEvent) => { e.preventDefault();
    try { form.id ? await api.put(`/clients/${form.id}`, form) : await api.post('/clients', form); setShow(false); load(); tt('Klien disimpan'); }
    catch(err) { tt(err instanceof Error ? err.message : 'Gagal'); }
  };
  const del = async (id:string) => { if(!confirm('Hapus?'))return; await api.del(`/clients/${id}`); load(); tt('Klien dihapus'); };

  return (<div>
    {toast&&<div className="fixed top-4 right-4 bg-white rounded-lg shadow-lg px-5 py-3 text-sm border-l-3 border-primary-600 z-50">{toast}</div>}
    <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-bold text-slate-900">Klien & Lokasi</h1><button onClick={()=>open()} className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">+ Tambah Klien</button></div>
    <div className="bg-white rounded-xl shadow-sm"><table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100"><th className="px-5 py-3">Nama</th><th className="px-5 py-3">Alamat</th><th className="px-5 py-3">GPS</th><th className="px-5 py-3">Radius</th><th className="px-5 py-3"/></tr></thead>
    <tbody>{clients.length===0?<tr><td colSpan={5} className="text-center py-16 text-slate-400">Belum ada klien</td></tr>:clients.map(c=><tr key={c.id} className="border-b border-slate-50 hover:bg-slate-50">
      <td className="px-5 py-3 font-medium text-sm">{c.name}</td><td className="px-5 py-3 text-sm text-slate-500">{c.address||'-'}</td><td className="px-5 py-3 text-sm text-slate-500">{Number(c.gps_lat).toFixed(6)}, {Number(c.gps_lng).toFixed(6)}</td><td className="px-5 py-3 text-sm">{c.radius_meters}m</td>
      <td className="px-5 py-3"><button onClick={()=>open(c)} className="h-8 w-8 rounded hover:bg-slate-100">✏️</button><button onClick={()=>del(c.id)} className="h-8 w-8 rounded hover:bg-red-50 text-red-500">🗑️</button></td>
    </tr>)}</tbody></table></div>
    {show&&<div className="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" onClick={e=>e.target===e.currentTarget&&setShow(false)}><div className="bg-white rounded-xl shadow-xl p-8 w-full max-w-md"><h3 className="text-lg font-semibold mb-5">{form.id?'Edit':'Tambah'} Klien</h3>
      <form onSubmit={save} className="space-y-3">
        {[['Nama','name','text'],['Alamat','address','text'],['Latitude','gps_lat','number'],['Longitude','gps_lng','number'],['Radius (m)','radius_meters','number']].map(([l,k,t])=><div key={k}><label className="block text-xs font-medium text-slate-500 mb-0.5">{l}</label><input type={t} step="any" value={form[k as keyof typeof form]} onChange={e=>setForm({...form,[k]:t==='number'?parseFloat(e.target.value):e.target.value})} className="w-full h-10 px-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary-600" required/></div>)}
        <div className="flex gap-3 justify-end pt-4"><button type="button" onClick={()=>setShow(false)} className="h-10 px-5 text-sm text-slate-500 hover:bg-slate-100 rounded-lg">Batal</button><button type="submit" className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">Simpan</button></div>
      </form></div></div>}
  </div>);
}
