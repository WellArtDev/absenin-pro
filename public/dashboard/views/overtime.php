<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Lembur', 'overtime');
?>

<div class="page-header"><h1>Lembur</h1><input type="date" class="input" id="dateFilter" value="<?= date('Y-m-d') ?>" onchange="loadOvertime()"></div>

<div class="card"><table><thead><tr><th>Karyawan</th><th>Jam Normal</th><th>Jam Lembur</th><th>Total</th><th>Dispute</th><th></th></tr></thead>
<tbody id="otTable"><tr><td colspan="6" class="empty">Memuat...</td></tr></tbody></table></div>

<script>
function loadOvertime() {
  API.get(`/attendance/report?start=${document.getElementById('dateFilter').value}&end=${document.getElementById('dateFilter').value}`).then(r => {
    const tb = document.getElementById('otTable');
    if(!r.data.length){tb.innerHTML='<tr><td colspan="6" class="empty">Tidak ada data</td></tr>';return;}
    tb.innerHTML = r.data.map(s => `<tr>
      <td><strong>${s.name}</strong><br><small>${s.employee_code}</small></td>
      <td>${s.total_normal} jam</td><td>${s.total_overtime} jam</td>
      <td>${(Number(s.total_normal)+Number(s.total_overtime)).toFixed(1)} jam</td>
      <td>-</td>
      <td></td>
    </tr>`).join('');
  }).catch(()=>{});
}
loadOvertime();
</script>
<?php renderFooter(); ?>
