<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Karyawan', 'employees');
?>

<div class="page-header">
  <h1>Karyawan</h1>
  <div style="display:flex;gap:var(--space-md)">
    <input type="text" class="input input-search" id="searchInput" placeholder="Cari nama, kode, email...">
    <button class="btn btn-primary" onclick="openModal()">+ Tambah</button>
    <button class="btn btn-secondary" onclick="document.getElementById('csvInput').click()">📥 Import CSV</button>
    <input type="file" id="csvInput" accept=".csv" style="display:none" onchange="importCsv(this)">
  </div>
</div>

<div class="card">
  <table>
    <thead><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Divisi</th><th>Status</th><th>Join</th><th></th></tr></thead>
    <tbody id="employeeTable"><tr><td colspan="7" class="empty"><div class="empty-icon">📋</div>Memuat data...</td></tr></tbody>
  </table>
</div>

<div class="modal-overlay" id="modalOverlay" style="display:none" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <h3 id="modalTitle">Tambah Karyawan</h3>
    <form id="employeeForm" onsubmit="saveEmployee(event)">
      <input type="hidden" id="empId">
      <div class="form-group"><label>Kode Karyawan *</label><input class="input" id="empCode" required></div>
      <div class="form-group"><label>Nama *</label><input class="input" id="empName" required></div>
      <div class="form-group"><label>Email *</label><input class="input" type="email" id="empEmail" required></div>
      <div class="form-group"><label>Telepon *</label><input class="input" id="empPhone" required></div>
      <div class="form-group"><label>KTP *</label><input class="input" id="empKtp" required></div>
      <div class="form-group"><label>NPWP *</label><input class="input" id="empNpwp" required></div>
      <div class="form-group"><label>Tgl Lahir *</label><input class="input" type="date" id="empBirthDate" required></div>
      <div class="form-group"><label>Tempat Lahir *</label><input class="input" id="empBirthPlace" required></div>
      <div class="form-group"><label>Alamat *</label><input class="input" id="empAddress" required></div>
      <div class="form-group"><label>Kontak Darurat Nama *</label><input class="input" id="empEmergName" required></div>
      <div class="form-group"><label>Kontak Darurat Telp *</label><input class="input" id="empEmergPhone" required></div>
      <div class="form-group"><label>Divisi ID *</label><input class="input" id="empDiv" required></div>
      <div class="form-group"><label>Jabatan ID *</label><input class="input" id="empPos" required></div>
      <div class="form-group"><label>Status Kerja *</label>
        <select class="input" id="empStatus" required style="width:100%">
          <option value="tetap">Tetap</option><option value="kontrak">Kontrak</option><option value="probation">Probation</option>
        </select>
      </div>
      <div class="form-group"><label>Tgl Masuk *</label><input class="input" type="date" id="empJoin" required></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
let currentPage = 1;

function loadEmployees(search = '') {
  API.get(`/employees?page=${currentPage}&limit=20&search=${encodeURIComponent(search)}`).then(r => {
    const tbody = document.getElementById('employeeTable');
    if (!r.data.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty"><div class="empty-icon">📋</div>Belum ada karyawan</td></tr>'; return; }
    tbody.innerHTML = r.data.map(e => `<tr>
      <td>${e.employee_code}</td><td><strong>${e.name}</strong></td><td>${e.email}</td>
      <td>${e.division_id||'-'}</td>
      <td><span class="badge badge-${e.is_active?'success':'danger'}">${e.is_active?'Aktif':'Nonaktif'}</span></td>
      <td>${e.join_date}</td>
      <td>
        <button class="btn btn-ghost" style="height:32px;padding:0 8px" onclick="editEmployee('${e.id}','${e.employee_code}','${e.name}','${e.email}','${e.phone}','','','${e.birth_date}','${e.birth_place}','${e.address}','${e.emergency_contact_name}','${e.emergency_contact_phone}','${e.division_id}','${e.position_id}','${e.employment_status}','${e.join_date}')">✏️</button>
        ${e.is_active?`<button class="btn btn-ghost" style="height:32px;padding:0 8px;color:var(--color-danger)" onclick="deactivate('${e.id}')">🗑️</button>`:''}
      </td></tr>`).join('');
  }).catch(e => API.toast(e.message, 'error'));
}

const searchDebounced = API.debounce(v => { currentPage=1; loadEmployees(v); });
document.getElementById('searchInput').addEventListener('input', e => searchDebounced(e.target.value));

function openModal() { document.getElementById('modalTitle').textContent='Tambah Karyawan'; document.getElementById('employeeForm').reset(); document.getElementById('empId').value=''; document.getElementById('modalOverlay').style.display='flex'; }
function closeModal() { document.getElementById('modalOverlay').style.display='none'; }

function editEmployee(id,code,name,email,phone,ktp,npwp,birth,bplace,addr,en,ep,div,pos,status,join) {
  document.getElementById('modalTitle').textContent='Edit Karyawan';
  document.getElementById('empId').value=id;
  document.getElementById('empCode').value=code;document.getElementById('empName').value=name;document.getElementById('empEmail').value=email;
  document.getElementById('empPhone').value=phone;document.getElementById('empKtp').value='';document.getElementById('empNpwp').value='';
  document.getElementById('empBirthDate').value=birth;document.getElementById('empBirthPlace').value=bplace;
  document.getElementById('empAddress').value=addr.replace(/\\n/g,'');document.getElementById('empEmergName').value=en;document.getElementById('empEmergPhone').value=ep;
  document.getElementById('empDiv').value=div;document.getElementById('empPos').value=pos;document.getElementById('empStatus').value=status;
  document.getElementById('empJoin').value=join;document.getElementById('modalOverlay').style.display='flex';
}

function saveEmployee(e) {
  e.preventDefault();
  const id=document.getElementById('empId').value;
  const body={
    employee_code:document.getElementById('empCode').value,name:document.getElementById('empName').value,email:document.getElementById('empEmail').value,
    phone:document.getElementById('empPhone').value,ktp_number:'0000000000000000',npwp_number:'000000000000000',
    birth_date:document.getElementById('empBirthDate').value,birth_place:document.getElementById('empBirthPlace').value,
    address:document.getElementById('empAddress').value,emergency_contact_name:document.getElementById('empEmergName').value,
    emergency_contact_phone:document.getElementById('empEmergPhone').value,division_id:document.getElementById('empDiv').value,
    position_id:document.getElementById('empPos').value,employment_status:document.getElementById('empStatus').value,
    join_date:document.getElementById('empJoin').value
  };
  const p = id ? API.put(`/employees/${id}`, body) : API.post('/employees', body);
  p.then(()=>{closeModal();loadEmployees();API.toast(id?'Karyawan diupdate':'Karyawan ditambahkan');}).catch(e=>API.toast(e.message,'error'));
}

function deactivate(id) { if(!confirm('Nonaktifkan karyawan ini?')) return;
  API.del(`/employees/${id}`).then(()=>{loadEmployees();API.toast('Karyawan dinonaktifkan');}).catch(e=>API.toast(e.message,'error')); }

function importCsv(input) {
  const file=input.files[0];if(!file)return;
  const fd=new FormData();fd.append('file',file);
  fetch('/api/v1/employees/import',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(r.success) API.toast(`${r.data.imported} berhasil diimpor. ${r.data.errors.length} gagal.`);
    else API.toast(r.message,'error');
    loadEmployees();input.value='';
  }).catch(e=>API.toast(e.message,'error'));
}

loadEmployees();
</script>
<?php renderFooter(); ?>
