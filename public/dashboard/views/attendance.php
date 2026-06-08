<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Presensi', 'attendance');
?>

<div class="page-header"><h1>Presensi</h1><input type="date" class="input" id="dateFilter" value="<?= date('Y-m-d') ?>" onchange="loadLog()"></div>

<div class="stats-grid" id="statsGrid"></div>

<div class="card"><table><thead><tr><th>Karyawan</th><th>Clock In</th><th>Clock Out</th><th>Normal</th><th>Lembur</th><th>Status</th><th>Lokasi</th></tr></thead>
<tbody id="logTable"><tr><td colspan="7" class="empty">Memuat...</td></tr></tbody></table></div>

<script>
function loadLog() {
  const d = document.getElementById('dateFilter').value;
  API.get(`/attendance/summary?date=${d}`).then(r => {
    const g = document.getElementById('statsGrid');
    g.innerHTML = `<div class="stat-card"><div class="label">Hadir</div><div class="value" style="color:var(--color-primary-600)">${r.data.hadir||0}</div></div>
      <div class="stat-card"><div class="label">Terlambat</div><div class="value" style="color:#F59E0B">${r.data.terlambat||0}</div></div>
      <div class="stat-card"><div class="label">Lembur</div><div class="value" style="color:#3B82F6">${r.data.lembur||0}</div></div>
      <div class="stat-card"><div class="label">Total</div><div class="value">${r.data.total||0}</div></div>`;
  });
  API.get(`/attendance/log?date=${d}`).then(r => {
    const tb = document.getElementById('logTable');
    if(!r.data.length){tb.innerHTML='<tr><td colspan="7" class="empty"><div class="empty-icon">📋</div>Belum ada presensi hari ini</td></tr>';return;}
    tb.innerHTML = r.data.map(s => `<tr>
      <td><strong>${s.employee_name}</strong><br><small style="color:var(--color-text-tertiary)">${s.employee_code}</small></td>
      <td>${s.clock_in?.substring(11,16)||'-'}</td><td>${s.clock_out?.substring(11,16)||'-'}</td>
      <td>${s.normal_hours||'0'} jam</td><td>${s.overtime_hours||'0'} jam</td>
      <td><span class="badge badge-${s.status==='hadir'?'success':s.status==='lembur'?'info':'warning'}">${s.status}</span></td>
      <td>${s.gps_lat?`${Number(s.gps_lat).toFixed(4)}, ${Number(s.gps_lng).toFixed(4)}`:s.source}</td>
    </tr>`).join('');
  }).catch(()=>{});
}
loadLog();
</script>
<?php renderFooter(); ?>
