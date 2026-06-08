<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
$auth = Auth::requireSession();
$GLOBALS['auth_user_name'] = $auth['user_name'];
require_once __DIR__ . '/../components/layout.php';
renderHeader('Klien', 'clients');
?>

<div class="page-header"><h1>Klien & Lokasi</h1><button class="btn btn-primary" onclick="showForm()">+ Tambah Klien</button></div>

<div class="card"><table><thead><tr><th>Nama</th><th>Alamat</th><th>GPS</th><th>Radius</th><th></th></tr></thead>
<tbody id="clientTable"><tr><td colspan="5" class="empty">Memuat...</td></tr></tbody></table></div>

<div class="modal-overlay" id="modal" style="display:none" onclick="if(event.target===this)closeForm()">
  <div class="modal"><h3 id="modalTitle">Tambah Klien</h3>
    <form onsubmit="saveClient(event)"><input type="hidden" id="cId">
      <div class="form-group"><label>Nama</label><input class="input" id="cName" required></div>
      <div class="form-group"><label>Alamat</label><input class="input" id="cAddr"></div>
      <div class="form-group"><label>Latitude</label><input class="input" type="number" step="any" id="cLat" required></div>
      <div class="form-group"><label>Longitude</label><input class="input" type="number" step="any" id="cLng" required></div>
      <div class="form-group"><label>Radius (meter)</label><input class="input" type="number" id="cRadius" value="10"></div>
      <div class="form-actions">
        <button type="button" class="btn btn-ghost" onclick="closeForm()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div></form></div></div>

<script>
function loadClients(){ API.get('/clients').then(r=>{
  const tb=document.getElementById('clientTable');
  if(!r.data.length){tb.innerHTML='<tr><td colspan="5" class="empty">Belum ada klien</td></tr>';return;}
  tb.innerHTML=r.data.map(c=>`<tr><td><strong>${c.name}</strong></td><td>${c.address||'-'}</td><td>${Number(c.gps_lat).toFixed(6)}, ${Number(c.gps_lng).toFixed(6)}</td><td>${c.radius_meters}m</td>
  <td><button class="btn btn-ghost" style="height:32px;padding:0 8px" onclick="editClient('${c.id}','${c.name}','${c.address||''}',${c.gps_lat},${c.gps_lng},${c.radius_meters})">✏️</button>
  <button class="btn btn-ghost" style="height:32px;padding:0 8px;color:var(--color-danger)" onclick="delClient('${c.id}')">🗑️</button></td></tr>`).join('');
})}
function showForm(){document.getElementById('modalTitle').textContent='Tambah Klien';document.getElementById('cId').value='';document.querySelector('#modal form').reset();document.getElementById('modal').style.display='flex'}
function closeForm(){document.getElementById('modal').style.display='none'}
function editClient(id,name,addr,lat,lng,r){document.getElementById('modalTitle').textContent='Edit Klien';document.getElementById('cId').value=id;document.getElementById('cName').value=name;document.getElementById('cAddr').value=addr;document.getElementById('cLat').value=lat;document.getElementById('cLng').value=lng;document.getElementById('cRadius').value=r;document.getElementById('modal').style.display='flex'}
function saveClient(e){e.preventDefault();const id=document.getElementById('cId').value,b={name:document.getElementById('cName').value,address:document.getElementById('cAddr').value,gps_lat:parseFloat(document.getElementById('cLat').value),gps_lng:parseFloat(document.getElementById('cLng').value),radius_meters:parseInt(document.getElementById('cRadius').value)};
(id?API.put(`/clients/${id}`,b):API.post('/clients',b)).then(()=>{closeForm();loadClients();API.toast('Klien disimpan')}).catch(e=>API.toast(e.message,'error'))}
function delClient(id){if(!confirm('Hapus klien?'))return;API.del(`/clients/${id}`).then(()=>{loadClients();API.toast('Klien dihapus')})}
loadClients();
</script>
<?php renderFooter(); ?>
