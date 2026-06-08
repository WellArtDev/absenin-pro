<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Report', 'report');
?>

<div class="page-header"><h1>Report Payroll</h1></div>

<div class="card">
  <div style="display:flex;gap:var(--space-lg);align-items:flex-end;margin-bottom:var(--space-2xl)">
    <div class="form-group" style="margin-bottom:0"><label>Dari</label><input type="date" class="input" id="startDate" value="<?= date('Y-m-01') ?>"></div>
    <div class="form-group" style="margin-bottom:0"><label>Sampai</label><input type="date" class="input" id="endDate" value="<?= date('Y-m-d') ?>"></div>
    <button class="btn btn-primary" onclick="generate()">Generate</button>
    <button class="btn btn-secondary" onclick="exportCsv()">📥 Export CSV</button>
  </div>

  <table><thead><tr><th>Nama</th><th>NIK</th><th>Hari Kerja</th><th>Jam Normal</th><th>Jam Lembur</th><th>Cuti</th><th>Izin</th><th>Sakit</th></tr></thead>
  <tbody id="reportTable"><tr><td colspan="8" class="empty">Pilih periode lalu klik Generate</td></tr></tbody></table>
</div>

<script>
function generate() {
  const s=document.getElementById('startDate').value,e=document.getElementById('endDate').value;
  API.get(`/attendance/report?start=${s}&end=${e}`).then(r => {
    const tb=document.getElementById('reportTable');
    if(!r.data.length){tb.innerHTML='<tr><td colspan="8" class="empty">Tidak ada data</td></tr>';return;}
    tb.innerHTML=r.data.map(d=>`<tr><td><strong>${d.name}</strong></td><td>${d.employee_code}</td><td>${d.work_days}</td><td>${d.total_normal}</td><td>${d.total_overtime}</td><td>-</td><td>-</td><td>-</td></tr>`).join('');
  }).catch(e=>API.toast(e.message,'error'));
}
function exportCsv(){ const s=document.getElementById('startDate').value,e=document.getElementById('endDate').value; API.download(`/attendance/report/csv?start=${s}&end=${e}`); }
</script>
<?php renderFooter(); ?>
