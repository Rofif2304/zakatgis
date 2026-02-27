<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Zakat Masjid — ZakatGIS</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--h:#0F5132;--h2:#1A7A4A;--e:#C9A84C;--krem:#FDF6E3}
body{font-family:'DM Sans',sans-serif;background:#f4f0e6;min-height:100vh}
.topbar{background:var(--h);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,.2)}
.logo{font-family:'Cinzel',serif;color:var(--e);font-size:1.2rem;display:flex;align-items:center;gap:10px}
.btn-login{background:var(--e);color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;transition:.2s}
.btn-login:hover{opacity:.85}
.hero{background:linear-gradient(135deg,var(--h),var(--h2));color:#fff;padding:36px 28px;text-align:center}
.hero h1{font-family:'Cinzel',serif;color:var(--e);font-size:1.6rem;margin-bottom:6px}
.hero p{opacity:.75;font-size:.9rem}
#map{height:420px;width:100%}
.container{max-width:1100px;margin:0 auto;padding:24px 20px}
.legenda{display:flex;gap:20px;flex-wrap:wrap;margin:14px 0;padding:14px 18px;background:#fff;border-radius:12px;border:1px solid rgba(0,0,0,.06)}
.leg-item{display:flex;align-items:center;gap:8px;font-size:.82rem;color:#555}
.dot{width:12px;height:12px;border-radius:50%}
.kartu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:20px}
.kartu{background:#fff;border-radius:14px;padding:18px;border:1px solid rgba(0,0,0,.06);border-left:4px solid var(--warna,#999)}
.kartu h3{font-size:.95rem;color:#1a1208;margin-bottom:8px}
.kartu .baris{display:flex;justify-content:space-between;font-size:.8rem;padding:4px 0;border-bottom:1px solid rgba(0,0,0,.04)}
.kartu .baris:last-child{border:none}
.kartu .label{color:#888}
.kartu .val{font-weight:600;color:#1a1208}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.surplus{background:rgba(15,81,50,.1);color:#0F5132}
.defisit{background:rgba(220,53,69,.1);color:#dc3545}
.seimbang{background:rgba(108,117,125,.1);color:#6c757d}
.loading{text-align:center;padding:40px;color:#aaa}
.peta-wrap{background:#fff;border-radius:16px;overflow:hidden;border:1px solid rgba(0,0,0,.06);margin-bottom:8px}
.sek-judul{font-family:'Cinzel',serif;font-size:1rem;color:var(--h);margin-bottom:12px}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">🕌 ZakatGIS</div>
  <button class="btn-login" onclick="window.location.href='index.php'">🔑 Login Sistem</button>
</div>

<div class="hero">
  <h1>Status Zakat Masjid</h1>
  <p>Informasi publik distribusi zakat antar masjid secara transparan</p>
</div>

<div class="container">

  <!-- Peta -->
  <div class="peta-wrap">
    <div id="map"></div>
  </div>

  <div class="legenda">
    <div class="leg-item"><div class="dot" style="background:#28a745"></div>Surplus — Zakat melebihi kebutuhan</div>
    <div class="leg-item"><div class="dot" style="background:#dc3545"></div>Defisit — Zakat kurang dari kebutuhan</div>
    <div class="leg-item"><div class="dot" style="background:#6c757d"></div>Seimbang</div>
  </div>

  <!-- Kartu Masjid -->
  <div class="sek-judul">📋 Daftar Status Zakat Masjid</div>
  <div id="kartuGrid" class="kartu-grid">
    <div class="loading">Memuat data...</div>
  </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([-6.9175, 107.6191], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);

const warna = s => s==='surplus'?'#28a745':s==='defisit'?'#dc3545':'#6c757d';

async function muat() {
  const r = await fetch('api/index.php?action=view_status_zakat');
  const d = await r.json();
  if (!d.success) return;

  const grid = document.getElementById('kartuGrid');
  grid.innerHTML = '';

  d.data.forEach(m => {
    const c = warna(m.status_zakat);
    // Marker peta
    const ico = L.divIcon({
      className:'',
      html:`<div style="background:${c};width:26px;height:26px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:.65rem;">🕌</div>`,
      iconSize:[26,26],iconAnchor:[13,13]
    });
    L.marker([parseFloat(m.latitude),parseFloat(m.logtitude)],{icon:ico})
     .bindPopup(`<div style="padding:12px;min-width:190px;">
        <strong style="font-family:serif;color:#0F5132;">${m.nama_masjid}</strong><br>
        <small style="color:#888;">${m.kecamatan||''}, ${m.kota||''}</small>
        <hr style="margin:6px 0;border-color:#eee;">
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0;">
          <span>Status</span><strong style="color:${c}">${m.status_zakat.toUpperCase()}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0;">
          <span>Zakat</span><span>Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0;">
          <span>Kebutuhan</span><span>Rp ${Number(m.jumlah_supply||0).toLocaleString('id-ID')}</span>
        </div>
     </div>`,{maxWidth:240})
     .addTo(map);

    // Kartu
    grid.innerHTML += `
      <div class="kartu" style="--warna:${c}">
        <h3>${m.nama_masjid} <span class="badge ${m.status_zakat}">${m.status_zakat}</span></h3>
        <div class="baris"><span class="label">Kecamatan</span><span class="val">${m.kecamatan||'—'}</span></div>
        <div class="baris"><span class="label">Zakat Tersedia</span><span class="val">Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</span></div>
        <div class="baris"><span class="label">Kebutuhan</span><span class="val">Rp ${Number(m.jumlah_supply||0).toLocaleString('id-ID')}</span></div>
        <div class="baris"><span class="label">Update</span><span class="val">${m.tanggal_input||'—'}</span></div>
      </div>`;
  });

  if (!d.data.length) grid.innerHTML = '<div class="loading">Belum ada data masjid</div>';
}
muat();
</script>
</body>
</html>