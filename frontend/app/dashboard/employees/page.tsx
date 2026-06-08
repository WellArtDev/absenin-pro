'use client';

import { useState, useEffect, useCallback, FormEvent } from 'react';
import { api } from '@/lib/api';

interface Employee { id: string; employee_code: string; name: string; email: string; phone: string; division_id: string; position_id: string; employment_status: string; join_date: string; is_active: boolean; }
const emptyEmp = { id:'', employee_code:'', name:'', email:'', phone:'', ktp_number:'', npwp_number:'', birth_date:'', birth_place:'', address:'', emergency_contact_name:'', emergency_contact_phone:'', division_id:'', position_id:'', employment_status:'tetap', join_date:'' };

export default function EmployeesPage() {
  const [emps, setEmps] = useState<Employee[]>([]);
  const [search, setSearch] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState({...emptyEmp});
  const [toast, setToast] = useState('');
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);

  const load = useCallback(async (s = search, p = page) => {
    try {
      const r = await api.get(`/employees?page=${p}&limit=20&search=${encodeURIComponent(s)}`);
      setEmps(r.data); setTotal(r.meta?.total || 0);
    } catch {}
  }, [search, page]);

  useEffect(() => { load(); }, [load]);

  const debounce = (fn: Function, ms = 300) => { let t: NodeJS.Timeout; return (...a: unknown[]) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };
  const onSearch = debounce((v: string) => { setSearch(v); setPage(1); load(v, 1); });

  const showToast = (msg: string) => { setToast(msg); setTimeout(() => setToast(''), 3000); };

  const openNew = () => { setForm({...emptyEmp}); setShowModal(true); };
  const openEdit = (e: Employee) => { setForm({...form, id:e.id, employee_code:e.employee_code, name:e.name, email:e.email, phone:e.phone, division_id:e.division_id||'', position_id:e.position_id||'', employment_status:e.employment_status||'tetap', join_date:e.join_date||''}); setShowModal(true); };

  const save = async (e: FormEvent) => {
    e.preventDefault();
    try {
      if (form.id) { await api.put(`/employees/${form.id}`, form); showToast('Karyawan diupdate'); }
      else { await api.post('/employees', form); showToast('Karyawan ditambahkan'); }
      setShowModal(false); load();
    } catch (err) { showToast(err instanceof Error ? err.message : 'Gagal'); }
  };

  const deactivate = async (id: string) => {
    if (!confirm('Nonaktifkan karyawan ini?')) return;
    await api.del(`/employees/${id}`); showToast('Karyawan dinonaktifkan'); load();
  };

  const importCsv = async (file: File) => {
    const fd = new FormData(); fd.append('file', file);
    const r = await fetch('/api/v1/employees/import', { method:'POST', body:fd, credentials:'include' });
    const j = await r.json();
    if (j.success) showToast(`${j.data.imported} berhasil, ${j.data.errors.length} gagal`);
    else showToast(j.message);
    load();
  };

  return (
    <div>
      {toast && <div className="fixed top-4 right-4 bg-white rounded-lg shadow-lg px-5 py-3 text-sm border-l-3 border-primary-600 z-50 animate-[slideIn_0.2s_ease]">{toast}</div>}
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-slate-900">Karyawan</h1>
        <div className="flex gap-3">
          <input className="h-10 px-4 rounded-full bg-slate-100 border-0 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-primary-100" placeholder="Cari nama, kode, email..." onChange={e => onSearch(e.target.value)}/>
          <button onClick={openNew} className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">+ Tambah</button>
          <label className="h-10 px-5 bg-primary-50 text-primary-700 rounded-lg text-sm font-semibold border border-primary-200 hover:bg-primary-100 cursor-pointer flex items-center">📥 Import CSV<input type="file" accept=".csv" className="hidden" onChange={e => e.target.files?.[0] && importCsv(e.target.files[0])}/></label>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm">
        <table className="w-full"><thead><tr className="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
          <th className="px-5 py-3">Kode</th><th className="px-5 py-3">Nama</th><th className="px-5 py-3">Email</th><th className="px-5 py-3">Divisi</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Join</th><th className="px-5 py-3"/></tr></thead>
          <tbody>
            {emps.length===0 ? <tr><td colSpan={7} className="text-center py-16 text-slate-400 text-sm">Belum ada karyawan</td></tr> :
            emps.map(e => <tr key={e.id} className="border-b border-slate-50 hover:bg-slate-50">
              <td className="px-5 py-3 text-sm">{e.employee_code}</td>
              <td className="px-5 py-3 text-sm font-medium">{e.name}</td>
              <td className="px-5 py-3 text-sm text-slate-500">{e.email}</td>
              <td className="px-5 py-3 text-sm text-slate-500">{e.division_id||'-'}</td>
              <td className="px-5 py-3"><span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ${e.is_active?'bg-primary-50 text-primary-700':'bg-red-50 text-red-700'}`}>{e.is_active?'Aktif':'Nonaktif'}</span></td>
              <td className="px-5 py-3 text-sm text-slate-500">{e.join_date||'-'}</td>
              <td className="px-5 py-3"><div className="flex gap-1">
                <button onClick={() => openEdit(e)} className="h-8 w-8 flex items-center justify-center rounded hover:bg-slate-100 text-sm">✏️</button>
                {e.is_active && <button onClick={() => deactivate(e.id)} className="h-8 w-8 flex items-center justify-center rounded hover:bg-red-50 text-red-500 text-sm">🗑️</button>}
              </div></td>
            </tr>)}
          </tbody></table>
        {total > 20 && <div className="flex justify-between items-center px-5 py-3 text-sm text-slate-400 border-t"><span>{total} karyawan</span><div className="flex gap-2"><button disabled={page<=1} onClick={() => setPage(p=>p-1)} className="px-3 py-1 rounded hover:bg-slate-100 disabled:opacity-30">←</button><span>Hal {page}</span><button disabled={page*20>=total} onClick={() => setPage(p=>p+1)} className="px-3 py-1 rounded hover:bg-slate-100 disabled:opacity-30">→</button></div></div>}
      </div>

      {showModal && <div className="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50" onClick={e => e.target===e.currentTarget && setShowModal(false)}><div className="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg max-h-[80vh] overflow-y-auto">
        <h3 className="text-lg font-semibold mb-5">{form.id?'Edit':'Tambah'} Karyawan</h3>
        <form onSubmit={save} className="space-y-3">
          {[['Kode *','employee_code','text'],['Nama *','name','text'],['Email *','email','email'],['Telepon *','phone','text'],['KTP *','ktp_number','text'],['NPWP *','npwp_number','text'],['Tgl Lahir *','birth_date','date'],['Tempat Lahir *','birth_place','text'],['Alamat *','address','text'],['Kontak Darurat Nama *','emergency_contact_name','text'],['Kontak Darurat Telp *','emergency_contact_phone','text'],['Divisi ID *','division_id','text'],['Jabatan ID *','position_id','text'],['Tgl Masuk *','join_date','date']].map(([l,k,t]) => (
            <div key={k}><label className="block text-xs font-medium text-slate-500 mb-0.5">{l}</label><input type={t} value={(form as Record<string,string>)[k]} onChange={e => setForm({...form,[k]:e.target.value})} className="w-full h-10 px-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary-600 focus:ring-3 focus:ring-primary-100" required/></div>
          ))}
          <div><label className="block text-xs font-medium text-slate-500 mb-0.5">Status Kerja *</label><select value={form.employment_status} onChange={e => setForm({...form,employment_status:e.target.value})} className="w-full h-10 px-3 border border-slate-200 rounded-lg text-sm"><option value="tetap">Tetap</option><option value="kontrak">Kontrak</option><option value="probation">Probation</option></select></div>
          <div className="flex gap-3 justify-end pt-4"><button type="button" onClick={() => setShowModal(false)} className="h-10 px-5 text-sm text-slate-500 hover:bg-slate-100 rounded-lg">Batal</button><button type="submit" className="h-10 px-5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700">Simpan</button></div>
        </form>
      </div></div>}
    </div>
  );
}
