<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Cuti & Izin', 'leave');
?>

<div class="page-header"><h1>Cuti & Izin</h1></div>

<div class="card"><h3 style="margin-bottom:var(--space-lg)">Menunggu Approval</h3>
<table><thead><tr><th>Karyawan</th><th>Tipe</th><th>Tanggal</th><th>Hari</th><th>Alasan</th><th></th></tr></thead>
<tbody id="pendingTable"><tr><td colspan="6" class="empty">Memuat...</td></tr></tbody></table></div>

<div class="card"><h3 style="margin-bottom:var(--space-lg)">Riwayat</h3>
<table><thead><tr><th>Karyawan</th><th>Tipe</th><th>Tanggal</th><th>Status</th></tr></thead>
<tbody id="historyTable"><tr><td colspan="4" class="empty">Memuat...</td></tr></tbody></table></div>

<script>
function loadLeaves() {
  API.get('/leaves?status=pending').then(r => {
    const tb = document.getElementById('pendingTable');
    if(!r.data.length){tb.innerHTML='<tr><td colspan="6" class="empty"><div class="empty-icon">✅</div>Semua pengajuan sudah diproses</td></tr>';return;}
    tb.innerHTML = r.data.map(l => `<tr>
      <td><strong>${l.employee_name}</strong><br><small>${l.employee_code}</small></td>
      <td><span class="badge badge-${l.leave_type==='cuti_tahunan'?'info':'warning'}">${l.leave_type}</span></td>
      <td>${l.start_date} - ${l.end_date}</td><td>${l.days_count} hari</td>
      <td>${l.reason?.substring(0,40)}</td>
      <td style="display:flex;gap:8px">
        <button class="btn btn-primary" style="height:32px;padding:0 12px;font-size:12px" onclick="approve('${l.id}')">Setujui</button>
        <button class="btn btn-ghost" style="height:32px;padding:0 12px;font-size:12px;color:var(--color-danger)" onclick="reject('${l.id}')">Tolak</button>
      </td></tr>`).join('');
  });
  API.get('/leaves?limit=50').then(r => {
    if(!r.data.length) return;
    document.getElementById('historyTable').innerHTML = r.data.map(l => `<tr>
      <td><strong>${l.employee_name}</strong></td>
      <td>${l.leave_type}</td><td>${l.start_date} - ${l.end_date}</td>
      <td><span class="badge badge-${l.status==='disetujui'?'success':l.status==='ditolak'?'danger':'warning'}">${l.status}</span></td>
    </tr>`).join('');
  }).catch(()=>{});
}

function approve(id) { API.post(`/leaves/${id}/approve`, {approved_by:'web'}).then(()=>{API.toast('Disetujui');loadLeaves();}).catch(e=>API.toast(e.message,'error')); }
function reject(id) { const reason=prompt('Alasan penolakan:');if(!reason)return;
  API.post(`/leaves/${id}/reject`,{reason}).then(()=>{API.toast('Ditolak');loadLeaves();}).catch(e=>API.toast(e.message,'error')); }

loadLeaves();
</script>
<?php renderFooter(); ?>
