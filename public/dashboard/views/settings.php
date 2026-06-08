<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Pengaturan', 'settings');
?>

<div class="page-header"><h1>Pengaturan</h1></div>

<div class="card">
  <h3 style="margin-bottom:var(--space-xl)">Jam Kerja</h3>
  <div style="display:flex;gap:var(--space-lg);align-items:flex-end;margin-bottom:var(--space-2xl)">
    <div class="form-group"><label>Jam Masuk</label><input type="time" class="input" id="workStart" value="09:00"></div>
    <div class="form-group"><label>Jam Pulang</label><input type="time" class="input" id="workEnd" value="17:00"></div>
    <div class="form-group"><label>Toleransi Terlambat (menit)</label><input type="number" class="input" id="tolerance" value="15" style="width:80px"></div>
  </div>

  <h3 style="margin-bottom:var(--space-xl)">Mode Lembur</h3>
  <div style="display:flex;gap:var(--space-xl);align-items:center;margin-bottom:var(--space-2xl)">
    <label style="display:flex;align-items:center;gap:var(--space-sm);font:var(--text-body)"><input type="radio" name="otMode" value="auto" checked onchange="toggleOvertime()"> Auto Lembur</label>
    <label style="display:flex;align-items:center;gap:var(--space-sm);font:var(--text-body)"><input type="radio" name="otMode" value="checkout" onchange="toggleOvertime()"> Checkout → Clock-in Lembur</label>
    <div class="form-group" style="margin-bottom:0"><label>Grace Period (menit)</label><input type="number" class="input" id="gracePeriod" value="30" style="width:80px"></div>
  </div>

  <button class="btn btn-primary" onclick="saveSettings()">Simpan Pengaturan</button>
</div>

<script>
function toggleOvertime(){ const ck=document.querySelector('input[name="otMode"]:checked').value==='checkout'; document.getElementById('gracePeriod').disabled=!ck; }

function saveSettings(){
  const settings = {
    work_start: document.getElementById('workStart').value,
    work_end: document.getElementById('workEnd').value,
    tolerance_minutes: document.getElementById('tolerance').value,
    overtime_mode: document.querySelector('input[name="otMode"]:checked').value,
  };
  if(settings.overtime_mode==='checkout') settings.grace_period = document.getElementById('gracePeriod').value;
  API.put('/settings', settings).then(()=>API.toast('Pengaturan disimpan')).catch(e=>API.toast(e.message,'error'));
}
</script>
<?php renderFooter(); ?>
