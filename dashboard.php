<?php
require_once 'includes/config.php';
requireLogin();
$u = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — ZakatGIS</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --h:#0F5132;--h2:#1A7A4A;--e:#C9A84C;--e2:#E8C97A;--ed:#8B6914;
  --sb:#0B3D26;--krem:#FDF6E3;--krem2:#F5E8C0;
  --border:rgba(201,168,76,.15);--teks:#1A1208;--teks2:#4A3B1C;--teks3:#7A6840;
  --merah:#dc3545;--hijau:#28a745;--abu:#6c757d;
}
body{font-family:'DM Sans',sans-serif;background:#f4f0e6;color:var(--teks);min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{position:fixed;left:0;top:0;bottom:0;width:255px;background:var(--sb);z-index:100;display:flex;flex-direction:column;border-right:1px solid var(--border)}
.s-logo{padding:20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:11px}
.s-logo-ikon{width:38px;height:38px;background:linear-gradient(135deg,var(--ed),var(--e));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.s-logo-teks{font-family:'Cinzel',serif;font-size:1.1rem;color:var(--e);line-height:1.2}
.s-logo-sub{font-size:.6rem;color:rgba(201,168,76,.45);text-transform:uppercase;letter-spacing:.05em}
.s-user{padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.s-avatar{width:34px;height:34px;background:linear-gradient(135deg,var(--h2),var(--h));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--e);font-size:.8rem;font-weight:700;flex-shrink:0;border:1px solid var(--border)}
.s-nama{font-size:.82rem;font-weight:600;color:var(--krem);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.s-role{font-size:.66rem;color:var(--e);text-transform:uppercase;letter-spacing:.05em}
.s-nav{flex:1;padding:10px 0;overflow-y:auto}
.s-seksi{padding:8px 18px 3px;font-size:.62rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(201,168,76,.32);font-weight:700}
.s-item{display:flex;align-items:center;gap:11px;padding:10px 18px;color:rgba(253,246,227,.8);cursor:pointer;transition:.18s;font-size:.84rem;background:none;border:none;width:100%;text-align:left;position:relative}
.s-item:hover{background:rgba(201,168,76,.08);color:var(--e2)}
.s-item.aktif{background:rgba(201,168,76,.11);color:var(--e);font-weight:500}
.s-item.aktif::before{content:'';position:absolute;left:0;top:4px;bottom:4px;width:3px;background:var(--e);border-radius:0 3px 3px 0}
.s-item .ikon{width:18px;text-align:center;flex-shrink:0}
.s-footer{padding:10px 18px;border-top:1px solid var(--border)}

/* ── MAIN ── */
.main{margin-left:255px;min-height:100vh;display:flex;flex-direction:column}
.topbar{background:#fff;padding:13px 26px;border-bottom:1px solid rgba(0,0,0,.07);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:0 2px 8px rgba(0,0,0,.05)}
.topbar-judul{font-family:'Cinzel',serif;font-size:1.05rem;color:var(--h)}
.content{flex:1;padding:22px 26px}

/* ── PANEL ── */
.panel{display:none}
.panel.aktif{display:block}
.pg-judul{font-family:'Cinzel',serif;font-size:1.35rem;color:var(--h);margin-bottom:3px}
.pg-sub{font-size:.82rem;color:var(--teks3);margin-bottom:20px}

/* ── STATS ── */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.stat{background:#fff;border-radius:14px;padding:18px;border:1px solid rgba(0,0,0,.06);position:relative;overflow:hidden}
.stat::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--ac,var(--h))}
.stat-ikon{width:40px;height:40px;background:var(--ib,rgba(15,81,50,.08));border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:10px}
.stat-val{font-family:'Cinzel',serif;font-size:1.5rem;line-height:1;margin-bottom:3px}
.stat-lab{font-size:.75rem;color:var(--teks3)}

/* ── KARTU / TABEL ── */
.kartu{background:#fff;border-radius:14px;border:1px solid rgba(0,0,0,.06);overflow:hidden;margin-bottom:18px}
.kartu-head{padding:14px 18px;border-bottom:1px solid rgba(0,0,0,.06);display:flex;align-items:center;justify-content:space-between}
.kartu-judul{font-family:'Cinzel',serif;font-size:.88rem;color:var(--h)}
table{width:100%;border-collapse:collapse;font-size:.83rem}
th{background:#f9f7f2;padding:9px 14px;text-align:left;font-size:.69rem;text-transform:uppercase;letter-spacing:.07em;color:var(--teks3);font-weight:700;border-bottom:1px solid rgba(0,0,0,.06)}
td{padding:11px 14px;border-bottom:1px solid rgba(0,0,0,.04);color:var(--teks)}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fdfbf6}

/* ── BADGE STATUS ── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.surplus{background:rgba(40,167,69,.1);color:#155724}
.defisit{background:rgba(220,53,69,.1);color:#721c24}
.seimbang{background:rgba(108,117,125,.1);color:#383d41}
.menunggu{background:rgba(255,193,7,.15);color:#856404}
.disetujui{background:rgba(40,167,69,.1);color:#155724}
.ditolak{background:rgba(220,53,69,.1);color:#721c24}
.terverifikasi{background:rgba(40,167,69,.1);color:#155724}

/* ── BTN ── */
.btn{padding:8px 18px;border:none;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:600;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:6px}
.btn-p{background:var(--h);color:#fff}     .btn-p:hover{background:var(--h2);transform:translateY(-1px)}
.btn-e{background:linear-gradient(135deg,var(--ed),var(--e));color:#fff} .btn-e:hover{transform:translateY(-1px);box-shadow:0 5px 14px rgba(201,168,76,.35)}
.btn-d{background:#dc3545;color:#fff}      .btn-d:hover{background:#bb2d3b}
.btn-s{background:#e9ecef;color:#495057}   .btn-s:hover{background:#dee2e6}
.btn-w{background:#ffc107;color:#000}      .btn-w:hover{background:#e0a800}
.btn-sm{padding:5px 12px;font-size:.75rem}

/* ── FORM ── */
.grup{margin-bottom:16px}
label{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--teks3);margin-bottom:6px}
input[type=text],input[type=email],input[type=password],input[type=number],input[type=date],input[type=file],select,textarea{width:100%;padding:10px 13px;border:1.5px solid rgba(0,0,0,.12);border-radius:9px;font-family:'DM Sans',sans-serif;font-size:.88rem;color:var(--teks);background:#fafaf8;transition:.2s;outline:none}
input:focus,select:focus,textarea:focus{border-color:var(--h);background:#fff;box-shadow:0 0 0 3px rgba(15,81,50,.07)}
textarea{resize:vertical;min-height:80px}
.baris-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.alert{padding:10px 14px;border-radius:9px;font-size:.82rem;margin-bottom:14px;display:none}
.alert-ok{background:#f0fff4;border:1px solid #c3e6cb;color:#155724}
.alert-err{background:#fff0f0;border:1px solid #ffcdd2;color:#c62828}
.alert-inf{background:#e8f4fd;border:1px solid #b3d7f5;color:#0c5460}

/* ── MODAL ── */
.ov{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:none;align-items:center;justify-content:center;padding:20px}
.ov.aktif{display:flex}
.modal{background:#fff;border-radius:18px;width:100%;max-width:500px;max-height:88vh;overflow-y:auto;box-shadow:0 20px 70px rgba(0,0,0,.3)}
.modal-head{padding:22px 26px 14px;border-bottom:1px solid rgba(0,0,0,.07);display:flex;align-items:center;justify-content:space-between}
.modal-judul{font-family:'Cinzel',serif;font-size:1rem;color:var(--h)}
.modal-tutup{background:none;border:none;cursor:pointer;color:var(--teks3);font-size:1.2rem;transition:.2s}
.modal-tutup:hover{color:var(--teks)}
.modal-body{padding:22px 26px}
.modal-foot{padding:14px 26px 22px;display:flex;gap:10px;justify-content:flex-end}
.modal-lg{max-width:600px}

/* ── MAP ── */
.map-wrap{background:#fff;border-radius:14px;border:1px solid rgba(0,0,0,.06);overflow:hidden;margin-bottom:18px}
#map,#map2{height:400px;width:100%}
.legenda{padding:10px 18px;display:flex;gap:20px;background:#fafaf8;border-top:1px solid rgba(0,0,0,.05);flex-wrap:wrap}
.leg{display:flex;align-items:center;gap:8px;font-size:.78rem;color:var(--teks2)}
.dot{width:11px;height:11px;border-radius:50%;flex-shrink:0}

/* ── TIMELINE ── */
.tl-item{display:flex;gap:14px;padding:14px 0;border-bottom:1px dashed rgba(0,0,0,.07)}
.tl-item:last-child{border:none}
.tl-dot{width:34px;height:34px;border-radius:50%;background:rgba(15,81,50,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--h);font-size:.85rem}
.tl-jdl{font-weight:600;font-size:.85rem}
.tl-meta{font-size:.75rem;color:var(--teks3);margin-top:2px}

/* ── DUA KOLOM ── */
.dua-kol{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
@media(max-width:1000px){.stats{grid-template-columns:repeat(2,1fr)}.dua-kol{grid-template-columns:1fr}}

.loader-sm{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:putar .7s linear infinite}
@keyframes putar{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<!-- ======== SIDEBAR ======== -->
<aside class="sidebar">
  <div class="s-logo">
    <div class="s-logo-ikon">🕌</div>
    <div><div class="s-logo-teks">ZakatGIS</div><div class="s-logo-sub">Distribusi Zakat</div></div>
  </div>
  <div class="s-user">
    <div class="s-avatar"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
    <div style="flex:1;min-width:0">
      <div class="s-nama"><?= sanitize($u['nama']) ?></div>
      <div class="s-role"><?= $u['role']==='admin'?'Sistem Admin':'Amil Zakat/DKM' ?></div>
    </div>
  </div>
  <nav class="s-nav">
    <div class="s-seksi">Utama</div>
    <button class="s-item aktif" onclick="panel('dashboard')"><span class="ikon"><i class="fas fa-th-large"></i></span>Dashboard</button>
    <button class="s-item" onclick="panel('peta')"><span class="ikon"><i class="fas fa-map-marked-alt"></i></span>Peta GIS</button>

    <?php if($u['role']==='admin'): ?>
    <div class="s-seksi">Admin</div>
    <button class="s-item" onclick="panel('verifikasi')"><span class="ikon"><i class="fas fa-check-circle"></i></span>Verifikasi Masjid</button>
    <button class="s-item" onclick="panel('kelola-masjid')"><span class="ikon"><i class="fas fa-mosque"></i></span>Kelola Data Masjid</button>
    <button class="s-item" onclick="panel('kelola-user')"><span class="ikon"><i class="fas fa-users"></i></span>Kelola User</button>
    <button class="s-item" onclick="panel('rekap-zakat')"><span class="ikon"><i class="fas fa-chart-bar"></i></span>Kelola Rekap Zakat</button>
    <button class="s-item" onclick="panel('semua-pengajuan')"><span class="ikon"><i class="fas fa-list-check"></i></span>Semua Pengajuan</button>
    <?php endif; ?>

    <?php if($u['role']==='pengurus'): ?>
    <div class="s-seksi">Amil Zakat/DKM</div>
    <button class="s-item" onclick="panel('pendataan')"><span class="ikon"><i class="fas fa-mosque"></i></span>Pendataan Masjid</button>
    <button class="s-item" onclick="panel('jarak-masjid')"><span class="ikon"><i class="fas fa-route"></i></span>Jarak ke Masjid Lain</button>
    <button class="s-item" onclick="panel('data-zakat')"><span class="ikon"><i class="fas fa-coins"></i></span>Kelola Rekap Zakat</button>
    <button class="s-item" onclick="panel('pengajuan-saya')"><span class="ikon"><i class="fas fa-paper-plane"></i></span>Pengajuan Saya</button>
    <button class="s-item" onclick="panel('pengajuan-masuk')"><span class="ikon"><i class="fas fa-inbox"></i></span>Pengajuan Masuk</button>
    <?php endif; ?>

    <div class="s-seksi">Laporan</div>
    <button class="s-item" onclick="panel('distribusi')"><span class="ikon"><i class="fas fa-history"></i></span>Histori Distribusi</button>
  </nav>
  <div class="s-footer">
    <button class="s-item" onclick="logout()" style="color:rgba(220,53,69,.8)"><span class="ikon"><i class="fas fa-sign-out-alt"></i></span>Keluar</button>
  </div>
</aside>

<!-- ======== MAIN ======== -->
<main class="main">
  <div class="topbar">
    <div class="topbar-judul" id="pgJudul">Dashboard</div>
    <div style="display:flex;gap:10px;align-items:center">
      <?php if($u['role']==='pengurus'): ?>
      <button class="btn btn-e btn-sm" onclick="bukaModal('mAjukan')"><i class="fas fa-plus"></i> Ajukan Surat</button>
      <?php endif; ?>
      <button class="btn btn-s btn-sm" onclick="window.open('visitor.php','_blank')"><i class="fas fa-eye"></i> View Publik</button>
    </div>
  </div>

  <div class="content">

  <!-- ============ DASHBOARD ============ -->
  <div id="p-dashboard" class="panel aktif">
    <div class="pg-judul">Dashboard ZakatGIS</div>
    <p class="pg-sub">Ringkasan sistem koordinasi distribusi zakat antar masjid</p>
    <div class="stats">
      <div class="stat" style="--ac:var(--e)"><div class="stat-ikon">🕌</div><div class="stat-val" id="sMasjid">—</div><div class="stat-lab">Total Masjid</div></div>
      <div class="stat" style="--ac:#28a745;--ib:rgba(40,167,69,.08)"><div class="stat-ikon">📈</div><div class="stat-val" id="sSurplus">—</div><div class="stat-lab">Surplus</div></div>
      <div class="stat" style="--ac:#dc3545;--ib:rgba(220,53,69,.08)"><div class="stat-ikon">📉</div><div class="stat-val" id="sDefisit">—</div><div class="stat-lab">Defisit</div></div>
      <div class="stat" style="--ac:var(--h)"><div class="stat-ikon">💰</div><div class="stat-val" id="sTotal" style="font-size:1rem">—</div><div class="stat-lab">Total Zakat Tersedia</div></div>
    </div>
    <div class="map-wrap">
      <div style="padding:13px 18px;border-bottom:1px solid rgba(0,0,0,.06);font-family:'Cinzel',serif;font-size:.9rem;color:var(--h)"><i class="fas fa-map-marked-alt"></i> Peta Status Zakat</div>
      <div id="map"></div>
      <div class="legenda">
        <div class="leg"><div class="dot" style="background:#28a745"></div>Surplus</div>
        <div class="leg"><div class="dot" style="background:#dc3545"></div>Defisit</div>
        <div class="leg"><div class="dot" style="background:#6c757d"></div>Seimbang</div>
      </div>
    </div>
  </div>

  <!-- ============ PETA GIS ============ -->
  <div id="p-peta" class="panel">
    <div class="pg-judul">Peta GIS</div>
    <p class="pg-sub">Visualisasi lokasi dan status zakat masjid — Method: lihatPeta()</p>
    <div class="map-wrap">
      <div style="padding:11px 18px;border-bottom:1px solid rgba(0,0,0,.06);display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-s btn-sm" onclick="filterPeta('all')">Semua</button>
        <button class="btn btn-sm" style="background:#28a745;color:#fff" onclick="filterPeta('surplus')">Surplus</button>
        <button class="btn btn-d btn-sm" onclick="filterPeta('defisit')">Defisit</button>
        <button class="btn btn-s btn-sm" onclick="filterPeta('seimbang')">Seimbang</button>
      </div>
      <div id="map2" style="height:480px"></div>
      <div class="legenda">
        <div class="leg"><div class="dot" style="background:#28a745"></div>Surplus — Zakat melebihi kebutuhan</div>
        <div class="leg"><div class="dot" style="background:#dc3545"></div>Defisit — Zakat kurang dari kebutuhan</div>
        <div class="leg"><div class="dot" style="background:#6c757d"></div>Seimbang</div>
      </div>
    </div>
    <!-- Tabel lihatInformasi -->
    <div class="kartu">
      <div class="kartu-head"><span class="kartu-judul">📋 Informasi Masjid — lihatInformasi()</span></div>
      <table><thead><tr><th>Nama Masjid</th><th>Kecamatan</th><th>Zakat</th><th>Kebutuhan</th><th>Status</th></tr></thead>
      <tbody id="tbPeta"><tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ VERIFIKASI MASJID (Admin) ============ -->
  <div id="p-verifikasi" class="panel">
    <div class="pg-judul">Verifikasi Masjid Baru</div>
    <p class="pg-sub">Use Case: Verifikasi Masjid Baru — tinjau pendaftaran masjid dari Amil Zakat/DKM</p>
    <div class="kartu">
      <div class="kartu-head"><span class="kartu-judul">⏳ Menunggu Verifikasi</span></div>
      <table><thead><tr><th>Nama Masjid</th><th>Alamat</th><th>Kecamatan</th><th>Koordinat</th><th>Aksi</th></tr></thead>
      <tbody id="tbVerif"><tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ KELOLA DATA MASJID (Admin) ============ -->
  <div id="p-kelola-masjid" class="panel">
    <div class="pg-judul">Kelola Data Masjid</div>
    <p class="pg-sub">Use Case: Kelola Data Masjid — Activity: Form Edit, Hapus</p>
    <div class="kartu">
      <div class="kartu-head">
        <span class="kartu-judul">🕌 Semua Masjid Terverifikasi</span>
        <button class="btn btn-p btn-sm" onclick="bukaModal('mTambahMasjid')"><i class="fas fa-plus"></i> Tambah</button>
      </div>
      <table><thead><tr><th>Nama Masjid</th><th>Pengurus</th><th>Kecamatan/Kota</th><th>Koordinat</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody id="tbKelola"><tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ KELOLA USER (Admin) ============ -->
  <div id="p-kelola-user" class="panel">
    <div class="pg-judul">Kelola User</div>
    <p class="pg-sub">Method: createUser(), readUser(), updateUser(), Delete()</p>
    <div class="kartu">
      <div class="kartu-head">
        <span class="kartu-judul">👥 Daftar User</span>
        <button class="btn btn-p btn-sm" onclick="bukaModal('mTambahUser')"><i class="fas fa-plus"></i> Tambah User</button>
      </div>
      <table><thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
      <tbody id="tbUser"><tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ REKAP ZAKAT (Admin & Pengurus) ============ -->
  <div id="p-rekap-zakat" class="panel">
    <div class="pg-judul">Kelola Rekap Zakat</div>
    <p class="pg-sub">Use Case: Kelola Rekap Zakat — Method: inputDataZakat(), kelolaDataDistribusi()</p>
    <div class="kartu">
      <div class="kartu-head"><span class="kartu-judul">📊 Rekap Zakat Semua Masjid</span></div>
      <table><thead><tr><th>Nama Masjid</th><th>Kecamatan</th><th>Jumlah Zakat</th><th>Kebutuhan (Supply)</th><th>Selisih</th><th>Status</th></tr></thead>
      <tbody id="tbRekap"><tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ SEMUA PENGAJUAN (Admin) ============ -->
  <div id="p-semua-pengajuan" class="panel">
    <div class="pg-judul">Semua Surat Pengajuan</div>
    <p class="pg-sub">Monitor semua pengajuan distribusi zakat</p>
    <div class="kartu">
      <table><thead><tr><th>Tanggal</th><th>Pemohon</th><th>Donatur</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody id="tbSemuaPengajuan"><tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ PENDATAAN MASJID (Pengurus) ============ -->
  <div id="p-pendataan" class="panel">
    <div class="pg-judul">Pendataan Masjid</div>
    <p class="pg-sub">Use Case: Pendataan Masjid — Activity: Form Informasi Data Masjid</p>
    <div class="dua-kol">
      <div class="kartu">
        <div class="kartu-head"><span class="kartu-judul">📝 Form Informasi Data Masjid</span></div>
        <div style="padding:20px">
          <div id="alPendataan" class="alert"></div>
          <!-- Activity: validasi → jika valid simpan, jika tidak → Notifikasi Data Salah -->
          <div class="grup"><label>Nama Masjid *</label><input type="text" id="pNama" placeholder="Masjid Al-..."></div>
          <div class="grup"><label>Alamat</label><textarea id="pAlamat" placeholder="Jl. ..."></textarea></div>
          <div class="baris-form">
            <div class="grup"><label>Kecamatan</label><input type="text" id="pKec"></div>
            <div class="grup"><label>Kota</label><input type="text" id="pKota"></div>
          </div>
          <div class="baris-form">
            <div class="grup"><label>Latitude *</label><input type="number" id="pLat" step="0.000001" placeholder="-6.917"></div>
            <div class="grup"><label>Longitude *</label><input type="number" id="pLng" step="0.000001" placeholder="107.619"></div>
          </div>
          <div class="grup"><label>Telepon</label><input type="text" id="pTelp"></div>
          <button class="btn btn-p" onclick="submitPendataan()"><i class="fas fa-paper-plane"></i> Kirim untuk Diverifikasi</button>
        </div>
      </div>
      <div class="kartu">
        <div class="kartu-head"><span class="kartu-judul">🕌 Masjid Saya</span></div>
        <div style="padding:20px" id="infoMasjidSaya"><p style="color:#aaa;text-align:center;padding:20px">Memuat...</p></div>
      </div>
    </div>
  </div>

  <!-- ============ JARAK MASJID (Pengurus) ============ -->
  <div id="p-jarak-masjid" class="panel">
    <div class="pg-judul">Jarak ke Masjid Lain</div>
    <p class="pg-sub">Lihat jarak dari masjid Anda ke masjid surplus/defisit terdekat & terjauh</p>
    <div class="kartu">
      <div class="kartu-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
        <span class="kartu-judul">📍 Daftar Masjid Berdasarkan Jarak</span>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <select id="filterJarak" onchange="tampilJarak()" style="padding:6px 10px;border:1.5px solid #ddd;border-radius:8px;font-size:.82rem">
            <option value="semua">Semua Status</option>
            <option value="surplus">Surplus Saja</option>
            <option value="defisit">Defisit Saja</option>
          </select>
          <select id="urutJarak" onchange="tampilJarak()" style="padding:6px 10px;border:1.5px solid #ddd;border-radius:8px;font-size:.82rem">
            <option value="terdekat">Terdekat dulu</option>
            <option value="terjauh">Terjauh dulu</option>
          </select>
        </div>
      </div>
      <div id="listJarak" style="padding:16px">
        <p style="color:#aaa;text-align:center;padding:20px">Memuat data jarak...</p>
      </div>
    </div>
  </div>

  <!-- ============ DATA ZAKAT (Pengurus) ============ -->
  <div id="p-data-zakat" class="panel">
    <div class="pg-judul">Kelola Rekap Zakat</div>
    <p class="pg-sub">Method: inputDataZakat() — input data zakat masjid Anda</p>
    <div class="dua-kol">
      <div class="kartu">
        <div class="kartu-head"><span class="kartu-judul">📥 Input Data Zakat</span></div>
        <div style="padding:20px">
          <div id="alZakat" class="alert"></div>
          <div class="grup"><label>Jumlah Zakat Terkumpul (Rp)</label><input type="number" id="dzJumlah" placeholder="0" min="0"></div>
          <div class="grup"><label>Kebutuhan / Supply Zakat (Rp)</label><input type="number" id="dzSupply" placeholder="0" min="0"></div>
          <div class="grup"><label>Tanggal Input</label><input type="date" id="dzTanggal" value="<?= date('Y-m-d') ?>"></div>
          <button class="btn btn-p" onclick="submitDataZakat()"><i class="fas fa-save"></i> Simpan Data</button>
        </div>
      </div>
      <div class="kartu">
        <div class="kartu-head"><span class="kartu-judul">📊 Riwayat Input Zakat</span></div>
        <table><thead><tr><th>Tanggal</th><th>Zakat</th><th>Kebutuhan</th><th>Status</th></tr></thead>
        <tbody id="tbRiwayatZakat"><tr><td colspan="4" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
      </div>
    </div>
  </div>

  <!-- ============ PENGAJUAN SAYA (Pengurus) ============ -->
  <div id="p-pengajuan-saya" class="panel">
    <div class="pg-judul">Pengajuan Saya</div>
    <p class="pg-sub">Use Case: Ajukan permintaan — Method: buatSuratPengajuan()</p>
    <div class="kartu">
      <div class="kartu-head">
        <span class="kartu-judul">📋 Riwayat Pengajuan</span>
        <button class="btn btn-e btn-sm" onclick="bukaModal('mAjukan')"><i class="fas fa-plus"></i> Ajukan Baru</button>
      </div>
      <table><thead><tr><th>Tanggal</th><th>Jumlah Diminta</th><th>Ditujukan Ke</th><th>Status</th><th>Keterangan</th></tr></thead>
      <tbody id="tbPengajuanSaya"><tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ PENGAJUAN MASUK (Pengurus Surplus) ============ -->
  <div id="p-pengajuan-masuk" class="panel">
    <div class="pg-judul">Pengajuan Masuk</div>
    <p class="pg-sub">Use Case: Terima/tolak permintaan — Method: tinjauSuratPengajuan(), ubahStatus()</p>
    <div class="kartu">
      <table><thead><tr><th>Tanggal</th><th>Pemohon</th><th>Jumlah</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody id="tbMasuk"><tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr></tbody></table>
    </div>
  </div>

  <!-- ============ HISTORI DISTRIBUSI ============ -->
  <div id="p-distribusi" class="panel">
    <div class="pg-judul">Histori Distribusi Zakat</div>
    <p class="pg-sub">Method: lihatDistribusi() — rekam jejak distribusi transparan &amp; akuntabel</p>
    <div class="kartu">
      <div style="padding:18px" id="tlDistribusi"><p style="color:#aaa;text-align:center;padding:20px">Memuat...</p></div>
    </div>
  </div>

  </div><!-- /content -->
</main>

<!-- ============ MODALS ============ -->

<!-- Modal: Ajukan Surat Pengajuan -->
<div class="ov" id="mAjukan">
  <div class="modal">
    <div class="modal-head"><span class="modal-judul">📤 Ajukan Surat Pengajuan Zakat</span><button class="modal-tutup" onclick="tutupModal('mAjukan')">×</button></div>
    <div class="modal-body">
      <div id="alAjukan" class="alert"></div>
      <div class="grup"><label>Jumlah Zakat Diminta (Rp) *</label><input type="number" id="ajJumlah" placeholder="0" min="0"></div>
      <div class="grup"><label>Pilih Masjid Donatur (Opsional)</label>
        <select id="ajDonatur"><option value="">— Pilih masjid surplus —</option></select>
      </div>
      <div class="grup"><label>Keterangan</label><textarea id="ajKet" placeholder="Jelaskan kebutuhan zakat..."></textarea></div>
      <div class="grup"><label>Upload Surat Pengajuan (PDF/DOC/JPG/PNG, maks 5MB)</label>
        <input type="file" id="ajFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-s" onclick="tutupModal('mAjukan')">Batal</button>
      <button class="btn btn-e" onclick="submitAjukan()" id="btnAjukan"><i class="fas fa-paper-plane"></i> Kirim</button>
    </div>
  </div>
</div>

<!-- Modal: Tinjau Pengajuan (Setujui/Tolak) -->
<div class="ov" id="mTinjau">
  <div class="modal modal-lg">
    <div class="modal-head"><span class="modal-judul" id="mTinjauJudul">Tinjau Pengajuan</span><button class="modal-tutup" onclick="tutupModal('mTinjau')">×</button></div>
    <div class="modal-body">
      <div id="mTinjauDetail" style="background:#f9f7f2;padding:14px;border-radius:10px;margin-bottom:14px;font-size:.83rem"></div>
      <div id="alTinjau" class="alert"></div>
      <div id="tinjauSetujuiForm" style="display:none">
        <div class="grup"><label>Pilih Masjid Donatur *</label>
          <select id="tDonatur"><option value="">— Pilih —</option></select>
        </div>
      </div>
      <div id="tinjauTolakForm" style="display:none">
        <div class="grup"><label>Alasan Penolakan</label>
          <textarea id="tAlasan" placeholder="Jelaskan alasan penolakan..."></textarea>
        </div>
      </div>
    </div>
    <div class="modal-foot" id="mTinjauFoot">
      <button class="btn btn-s" onclick="tutupModal('mTinjau')">Tutup</button>
    </div>
  </div>
</div>

<!-- Modal: Edit Masjid (Activity: Form Edit) -->
<div class="ov" id="mEditMasjid">
  <div class="modal modal-lg">
    <div class="modal-head"><span class="modal-judul">✏️ Edit Data Masjid</span><button class="modal-tutup" onclick="tutupModal('mEditMasjid')">×</button></div>
    <div class="modal-body">
      <div id="alEditMasjid" class="alert"></div>
      <input type="hidden" id="emId">
      <div class="grup"><label>Nama Masjid *</label><input type="text" id="emNama"></div>
      <div class="grup"><label>Alamat</label><textarea id="emAlamat"></textarea></div>
      <div class="baris-form">
        <div class="grup"><label>Kecamatan</label><input type="text" id="emKec"></div>
        <div class="grup"><label>Kota</label><input type="text" id="emKota"></div>
      </div>
      <div class="baris-form">
        <div class="grup"><label>Latitude *</label><input type="number" id="emLat" step="0.000001"></div>
        <div class="grup"><label>Longitude *</label><input type="number" id="emLng" step="0.000001"></div>
      </div>
      <div class="grup"><label>Telepon</label><input type="text" id="emTelp"></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-s" onclick="tutupModal('mEditMasjid')">Batal</button>
      <button class="btn btn-p" onclick="submitEditMasjid()"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </div>
  </div>
</div>

<!-- Modal: Tambah Masjid (Admin) -->
<div class="ov" id="mTambahMasjid">
  <div class="modal modal-lg">
    <div class="modal-head"><span class="modal-judul">🕌 Tambah Masjid</span><button class="modal-tutup" onclick="tutupModal('mTambahMasjid')">×</button></div>
    <div class="modal-body">
      <div id="alTambahMasjid" class="alert"></div>
      <div class="grup"><label>Nama Masjid *</label><input type="text" id="tmNama"></div>
      <div class="grup"><label>Alamat</label><textarea id="tmAlamat"></textarea></div>
      <div class="baris-form">
        <div class="grup"><label>Kecamatan</label><input type="text" id="tmKec"></div>
        <div class="grup"><label>Kota</label><input type="text" id="tmKota"></div>
      </div>
      <div class="baris-form">
        <div class="grup"><label>Latitude *</label><input type="number" id="tmLat" step="0.000001"></div>
        <div class="grup"><label>Longitude *</label><input type="number" id="tmLng" step="0.000001"></div>
      </div>
      <div class="grup"><label>Telepon</label><input type="text" id="tmTelp"></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-s" onclick="tutupModal('mTambahMasjid')">Batal</button>
      <button class="btn btn-p" onclick="submitTambahMasjid()"><i class="fas fa-plus"></i> Tambah</button>
    </div>
  </div>
</div>

<!-- Modal: Tambah User -->
<div class="ov" id="mTambahUser">
  <div class="modal">
    <div class="modal-head"><span class="modal-judul">👤 Tambah User</span><button class="modal-tutup" onclick="tutupModal('mTambahUser')">×</button></div>
    <div class="modal-body">
      <div id="alTambahUser" class="alert"></div>
      <div class="grup"><label>Nama Lengkap *</label><input type="text" id="tuNama"></div>
      <div class="grup"><label>Username *</label><input type="text" id="tuUsername"></div>
      <div class="grup"><label>Password *</label><input type="password" id="tuPassword"></div>
      <div class="grup"><label>Role</label>
        <select id="tuRole"><option value="pengurus">Amil Zakat/DKM (Pengurus)</option><option value="admin">Sistem Admin</option></select>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-s" onclick="tutupModal('mTambahUser')">Batal</button>
      <button class="btn btn-p" onclick="submitTambahUser()"><i class="fas fa-plus"></i> Buat User</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ========== STATE ==========
let masjidData = [];
let map1 = null, map2 = null;
let mk1 = [], mk2 = [];
let idTinjau = null;
const ROLE = '<?= $u['role'] ?>';
const MY_MID = <?= intval($u['masjid_id'] ?? 0) ?>;

const panelMap = {
  'dashboard':'Dashboard','peta':'Peta GIS','verifikasi':'Verifikasi Masjid Baru',
  'kelola-masjid':'Kelola Data Masjid','kelola-user':'Kelola User',
  'rekap-zakat':'Kelola Rekap Zakat','semua-pengajuan':'Semua Surat Pengajuan',
  'pendataan':'Pendataan Masjid','data-zakat':'Kelola Rekap Zakat',
  'pengajuan-saya':'Pengajuan Saya','pengajuan-masuk':'Pengajuan Masuk',
  'distribusi':'Histori Distribusi'
};

function panel(id) {
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('aktif'));
  document.querySelectorAll('.s-item').forEach(b=>b.classList.remove('aktif'));
  document.getElementById('p-'+id)?.classList.add('aktif');
  document.getElementById('pgJudul').textContent = panelMap[id]||'';
  document.querySelectorAll('.s-item').forEach(b=>{
    if(b.getAttribute('onclick')?.includes("'"+id+"'")) b.classList.add('aktif');
  });
  if(id==='peta') initMap2();
  if(id==='verifikasi') muatVerifikasi();
  if(id==='kelola-masjid') muatKelolaM();
  if(id==='kelola-user') muatUser();
  if(id==='rekap-zakat'||id==='semua-pengajuan') muatRekap();
  if(id==='semua-pengajuan') muatSemuaPengajuan();
  if(id==='pendataan') muatInfoMasjidSaya();
  if(id==='jarak-masjid') muatJarak();
  if(id==='data-zakat') muatRiwayatZakat();
  if(id==='pengajuan-saya') muatPengajuanSaya();
  if(id==='pengajuan-masuk') muatPengajuanMasuk();
  if(id==='distribusi') muatDistribusi();
}

// ========== API HELPER ==========
async function api(action, data={}) {
  const fd = new FormData();
  fd.append('action',action);
  for(const[k,v] of Object.entries(data)) fd.append(k,v);
  const r = await fetch('api/index.php',{method:'POST',body:fd});
  return r.json();
}
async function apiGet(action, p={}) {
  const q = new URLSearchParams({action,...p});
  return (await fetch('api/index.php?'+q)).json();
}

// ========== MAP ==========
const warna = s => s==='surplus'?'#28a745':s==='defisit'?'#dc3545':'#6c757d';
function buatIko(s) {
  const c=warna(s);
  return L.divIcon({className:'',html:`<div style="background:${c};width:26px;height:26px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:.65rem;">🕌</div>`,iconSize:[26,26],iconAnchor:[13,13]});
}
function popup(m) {
  const c=warna(m.status_zakat||'seimbang');
  return `<div style="padding:12px;min-width:200px;">
    <strong style="font-family:serif;color:#0F5132;">${m.nama_masjid}</strong><br>
    <small style="color:#888">${m.kecamatan||''}, ${m.kota||''}</small>
    <hr style="margin:6px 0;border-color:#eee">
    <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0"><span>Status</span><strong style="color:${c}">${(m.status_zakat||'seimbang').toUpperCase()}</strong></div>
    <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0"><span>Zakat</span><span>Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</span></div>
    <div style="display:flex;justify-content:space-between;font-size:.78rem;margin:3px 0"><span>Kebutuhan</span><span>Rp ${Number(m.jumlah_supply||0).toLocaleString('id-ID')}</span></div>
  </div>`;
}

function initMap1() {
  if(map1) return;
  map1 = L.map('map').setView([-6.9175,107.6191],12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map1);
  renderMk(map1,mk1);
}
function initMap2() {
  setTimeout(()=>{
    if(!map2){
      map2=L.map('map2').setView([-6.9175,107.6191],12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map2);
    }
    renderMk(map2,mk2);
    map2.invalidateSize();
  },120);
}
function renderMk(mp,arr) {
  arr.forEach(m=>mp.removeLayer(m.marker));
  arr.length=0;
  masjidData.forEach(m=>{
    const mk=L.marker([parseFloat(m.latitude),parseFloat(m.logtitude)],{icon:buatIko(m.status_zakat||'seimbang')});
    mk.bindPopup(popup(m),{maxWidth:250});
    mk.addTo(mp);
    arr.push({marker:mk,status:m.status_zakat||'seimbang'});
  });
}
function filterPeta(s) {
  if(!map2) return;
  mk2.forEach(({marker,status})=>{ if(s==='all'||status===s) marker.addTo(map2); else map2.removeLayer(marker); });
}

// ========== LOAD UTAMA ==========
async function muatDashboard() {
  const r = await apiGet('get_masjid');
  if(!r.success) return;
  masjidData = r.data;
  const sur = masjidData.filter(m=>m.status_zakat==='surplus').length;
  const def = masjidData.filter(m=>m.status_zakat==='defisit').length;
  const tot = masjidData.reduce((a,m)=>a+parseFloat(m.jumlah_zakat||0),0);
  document.getElementById('sMasjid').textContent = masjidData.length;
  document.getElementById('sSurplus').textContent = sur;
  document.getElementById('sDefisit').textContent = def;
  document.getElementById('sTotal').textContent = 'Rp '+Math.round(tot).toLocaleString('id-ID');
  initMap1();
  // Tabel peta
  document.getElementById('tbPeta').innerHTML = masjidData.map(m=>`
    <tr><td><strong>${m.nama_masjid}</strong></td><td>${m.kecamatan||'—'}</td>
    <td>Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</td>
    <td>Rp ${Number(m.jumlah_supply||0).toLocaleString('id-ID')}</td>
    <td><span class="badge ${m.status_zakat||'seimbang'}">${m.status_zakat||'seimbang'}</span></td></tr>`).join('');
  // Dropdown donatur
  const sel = document.getElementById('ajDonatur');
  sel.innerHTML = '<option value="">— Pilih masjid surplus —</option>';
  masjidData.filter(m=>m.status_zakat==='surplus').forEach(m=>{
    sel.innerHTML += `<option value="${m.id}">${m.nama_masjid}</option>`;
  });
  // Dropdown tinjau donatur
  const tsel = document.getElementById('tDonatur');
  tsel.innerHTML = '<option value="">— Pilih —</option>';
  masjidData.filter(m=>m.status_zakat==='surplus').forEach(m=>{
    tsel.innerHTML += `<option value="${m.id}">${m.nama_masjid}</option>`;
  });
}

// ========== VERIFIKASI MASJID ==========
async function muatVerifikasi() {
  const tbody = document.getElementById('tbVerif');
  tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Memuat...</td></tr>';
  const res2 = await apiGet('get_masjid_menunggu');
  if(!res2.success || !res2.data || !res2.data.length){
    tbody.innerHTML='<tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Tidak ada masjid menunggu verifikasi</td></tr>';
    return;
  }
  tbody.innerHTML = res2.data.map(m=>`
    <tr><td><strong>${m.nama_masjid}</strong></td><td>${m.alamat||'—'}</td><td>${m.kecamatan||'—'}</td>
    <td>${m.latitude}, ${m.logtitude}</td>
    <td>
      <button class="btn btn-p btn-sm" onclick="vMasjid(${m.id},'terverifikasi')"><i class="fas fa-check"></i> Verifikasi</button>
      <button class="btn btn-d btn-sm" onclick="vMasjid(${m.id},'ditolak')" style="margin-left:6px"><i class="fas fa-times"></i> Tolak</button>
    </td></tr>`).join('');
}
async function vMasjid(id, status) {
  const r = await api('verifikasi_masjid',{id,status});
  alert(r.message);
  muatVerifikasi();
  muatDashboard();
}

// ========== KELOLA MASJID ==========
async function muatKelolaM() {
  const r = await apiGet('get_masjid');
  const tbody = document.getElementById('tbKelola');
  if(!r.success||!r.data.length){tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Belum ada data</td></tr>';return;}
  tbody.innerHTML = r.data.map(m=>`
    <tr><td><strong>${m.nama_masjid}</strong></td><td>${m.nama_pengurus||'—'}</td><td>${m.kecamatan||'—'}, ${m.kota||'—'}</td>
    <td style="font-size:.78rem">${m.latitude}, ${m.logtitude}</td>
    <td><span class="badge ${m.status_zakat||'seimbang'}">${m.status_zakat||'—'}</span></td>
    <td>
      <button class="btn btn-w btn-sm" onclick="bukaEditMasjid(${m.id},'${m.nama_masjid}','${m.alamat||''}','${m.kecamatan||''}','${m.kota||''}',${m.latitude},${m.logtitude},'${m.telepon||''}')"><i class="fas fa-edit"></i> Edit</button>
      <button class="btn btn-d btn-sm" onclick="hapusMasjid(${m.id})" style="margin-left:5px"><i class="fas fa-trash"></i> Hapus</button>
    </td></tr>`).join('');
}
function bukaEditMasjid(id,nama,alamat,kec,kota,lat,lng,telp){
  document.getElementById('emId').value=id;
  document.getElementById('emNama').value=nama;
  document.getElementById('emAlamat').value=alamat;
  document.getElementById('emKec').value=kec;
  document.getElementById('emKota').value=kota;
  document.getElementById('emLat').value=lat;
  document.getElementById('emLng').value=lng;
  document.getElementById('emTelp').value=telp;
  document.getElementById('alEditMasjid').style.display='none';
  bukaModal('mEditMasjid');
}
async function submitEditMasjid(){
  const al=document.getElementById('alEditMasjid');
  const r=await api('update_masjid',{
    id:document.getElementById('emId').value,
    nama_masjid:document.getElementById('emNama').value,
    alamat:document.getElementById('emAlamat').value,
    kecamatan:document.getElementById('emKec').value,
    kota:document.getElementById('emKota').value,
    latitude:document.getElementById('emLat').value,
    logtitude:document.getElementById('emLng').value,
    telepon:document.getElementById('emTelp').value
  });
  al.style.display='block';
  al.className='alert '+(r.success?'alert-ok':'alert-err');
  al.textContent=r.message;
  if(r.success){setTimeout(()=>{tutupModal('mEditMasjid');muatKelolaM();muatDashboard();},900)}
}
async function hapusMasjid(id){
  if(!confirm('Yakin ingin menghapus masjid ini?')) return;
  const r=await api('delete_masjid',{id});
  alert(r.message);
  muatKelolaM();muatDashboard();
}
async function submitTambahMasjid(){
  const al=document.getElementById('alTambahMasjid');
  const r=await api('pendataan_masjid',{
    nama_masjid:document.getElementById('tmNama').value,
    alamat:document.getElementById('tmAlamat').value,
    kecamatan:document.getElementById('tmKec').value,
    kota:document.getElementById('tmKota').value,
    latitude:document.getElementById('tmLat').value,
    logtitude:document.getElementById('tmLng').value,
    telepon:document.getElementById('tmTelp').value
  });
  al.style.display='block';
  al.className='alert '+(r.success?'alert-ok':'alert-err');
  al.textContent=r.message;
  if(r.success){setTimeout(()=>{tutupModal('mTambahMasjid');muatKelolaM();muatDashboard();},900)}
}

// ========== KELOLA USER ==========
async function muatUser(){
  const r=await apiGet('get_users');
  const tb=document.getElementById('tbUser');
  if(!r.success||!r.data.length){tb.innerHTML='<tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Belum ada user</td></tr>';return;}
  tb.innerHTML=r.data.map(u=>`
    <tr><td><strong>${u.nama}</strong></td><td>${u.username}</td>
    <td><span class="badge ${u.role==='admin'?'terverifikasi':'menunggu'}">${u.role}</span></td>
    <td>${new Date(u.created_at).toLocaleDateString('id-ID')}</td>
    <td><button class="btn btn-d btn-sm" onclick="hapusUser(${u.id})"><i class="fas fa-trash"></i> Hapus</button></td></tr>`).join('');
}
async function submitTambahUser(){
  const al=document.getElementById('alTambahUser');
  const r=await api('create_user',{
    nama:document.getElementById('tuNama').value,
    username:document.getElementById('tuUsername').value,
    password:document.getElementById('tuPassword').value,
    role:document.getElementById('tuRole').value
  });
  al.style.display='block';al.className='alert '+(r.success?'alert-ok':'alert-err');al.textContent=r.message;
  if(r.success){setTimeout(()=>{tutupModal('mTambahUser');muatUser();},900)}
}
async function hapusUser(id){
  if(!confirm('Hapus user ini?')) return;
  const r=await api('delete_user',{id});
  alert(r.message);muatUser();
}

// ========== REKAP ZAKAT ==========
async function muatRekap(){
  const r=await apiGet('get_rekap_zakat');
  const tb=document.getElementById('tbRekap');
  if(!r.success||!r.data.length){tb.innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Belum ada data</td></tr>';return;}
  tb.innerHTML=r.data.map(m=>{
    const s=parseFloat(m.selisih||0);
    const warna=s>0?'color:#155724':s<0?'color:#721c24':'color:#383d41';
    return `<tr><td><strong>${m.nama_masjid}</strong></td><td>${m.kecamatan||'—'}</td>
    <td>Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</td>
    <td>Rp ${Number(m.jumlah_supply||0).toLocaleString('id-ID')}</td>
    <td style="${warna};font-weight:700">Rp ${Math.abs(s).toLocaleString('id-ID')} ${s>=0?'(+)':'(-)'}</td>
    <td><span class="badge ${m.status_zakat}">${m.status_zakat}</span></td></tr>`;
  }).join('');
}

// ========== SEMUA PENGAJUAN ==========
async function muatSemuaPengajuan(){
  const r=await apiGet('get_surat_pengajuan');
  const tb=document.getElementById('tbSemuaPengajuan');
  if(!r.success||!r.data.length){tb.innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Belum ada</td></tr>';return;}
  tb.innerHTML=r.data.map(p=>`
    <tr><td>${new Date(p.tanggal_pengajuan).toLocaleDateString('id-ID')}</td>
    <td>${p.nama_pemohon}</td><td>${p.nama_donatur||'—'}</td>
    <td>Rp ${Number(p.jumlah_diminta).toLocaleString('id-ID')}</td>
    <td><span class="badge ${p.status}">${p.status}</span></td>
    <td>${p.status==='menunggu'?`<button class="btn btn-p btn-sm" onclick="bukaTinjau(${p.id},'setujui')">Tinjau</button>`:'—'}</td></tr>`).join('');
}

// ========== PENDATAAN MASJID (Pengurus) ==========
async function muatInfoMasjidSaya(){
  if(!MY_MID){document.getElementById('infoMasjidSaya').innerHTML='<p style="color:#aaa;text-align:center;padding:20px">Belum ada masjid terdaftar</p>';return;}
  const r=await apiGet('get_masjid_detail',{id:MY_MID});
  if(!r.success) return;
  const m=r.data;
  document.getElementById('infoMasjidSaya').innerHTML=`
    <div style="display:grid;gap:10px">
      <div style="font-weight:700;font-size:1rem;color:var(--h)">${m.nama_masjid}</div>
      <div style="font-size:.82rem;color:#888">${m.alamat||'—'}</div>
      <div style="display:grid;gap:8px;margin-top:8px">
        ${[['Kecamatan',m.kecamatan||'—'],['Kota',m.kota||'—'],['Koordinat',m.latitude+', '+m.logtitude],['Telepon',m.telepon||'—'],
           ['Status Verifikasi',`<span class="badge ${m.status_verifikasi}">${m.status_verifikasi}</span>`],
           ['Zakat','Rp '+Number(m.jumlah_zakat||0).toLocaleString('id-ID')],
           ['Kebutuhan','Rp '+Number(m.jumlah_supply||0).toLocaleString('id-ID')]
        ].map(([l,v])=>`<div style="display:flex;justify-content:space-between;padding:8px;background:#f9f7f2;border-radius:8px;font-size:.82rem"><span style="color:#888">${l}</span><span style="font-weight:600">${v}</span></div>`).join('')}
      </div>
      <button class="btn btn-w btn-sm" onclick="bukaEditMasjid(${m.id},'${m.nama_masjid}','${(m.alamat||'').replace(/'/g,'')}','${m.kecamatan||''}','${m.kota||''}',${m.latitude},${m.logtitude},'${m.telepon||''}')">
        <i class="fas fa-edit"></i> Edit Data Masjid
      </button>
    </div>`;
}
async function submitPendataan(){
  const al=document.getElementById('alPendataan');
  const r=await api('pendataan_masjid',{
    nama_masjid:document.getElementById('pNama').value,
    alamat:document.getElementById('pAlamat').value,
    kecamatan:document.getElementById('pKec').value,
    kota:document.getElementById('pKota').value,
    latitude:document.getElementById('pLat').value,
    logtitude:document.getElementById('pLng').value,
    telepon:document.getElementById('pTelp').value
  });
  al.style.display='block';
  al.className='alert '+(r.success?'alert-ok':'alert-err');
  al.textContent=r.message;
  // Activity: jika error → Notifikasi Data Salah (sudah ditampilkan di alert)
}

// ========== DATA ZAKAT (Pengurus) ==========
async function muatRiwayatZakat(){
  if(!MY_MID) return;
  const r=await apiGet('get_data_zakat',{id_masjid:MY_MID});
  const tb=document.getElementById('tbRiwayatZakat');
  if(!r.success||!r.data.length){tb.innerHTML='<tr><td colspan="4" style="text-align:center;padding:20px;color:#aaa">Belum ada input</td></tr>';return;}
  tb.innerHTML=r.data.map(d=>{
    const s=parseFloat(d.jumlah_zakat)-parseFloat(d.jumlah_supply);
    const st=s>0?'surplus':s<0?'defisit':'seimbang';
    return `<tr><td>${d.tanggal_input}</td><td>Rp ${Number(d.jumlah_zakat).toLocaleString('id-ID')}</td>
    <td>Rp ${Number(d.jumlah_supply).toLocaleString('id-ID')}</td>
    <td><span class="badge ${st}">${st}</span></td></tr>`;
  }).join('');
}
async function submitDataZakat(){
  const al=document.getElementById('alZakat');
  const r=await api('input_data_zakat',{
    jumlah_zakat:document.getElementById('dzJumlah').value,
    jumlah_supply:document.getElementById('dzSupply').value,
    tanggal_input:document.getElementById('dzTanggal').value
  });
  al.style.display='block';al.className='alert '+(r.success?'alert-ok':'alert-err');al.textContent=r.message;
  if(r.success){muatRiwayatZakat();muatDashboard();}
}

// ========== PENGAJUAN (Pengurus) ==========
async function muatPengajuanSaya(){
  const r=await apiGet('get_surat_pengajuan');
  const tb=document.getElementById('tbPengajuanSaya');
  const mine=r.data?.filter(p=>parseInt(p.id_masjid)===MY_MID)||[];
  if(!mine.length){tb.innerHTML='<tr><td colspan="5" style="text-align:center;padding:20px;color:#aaa">Belum ada pengajuan</td></tr>';return;}
  tb.innerHTML=mine.map(p=>`
    <tr><td>${new Date(p.tanggal_pengajuan).toLocaleDateString('id-ID')}</td>
    <td>Rp ${Number(p.jumlah_diminta).toLocaleString('id-ID')}</td>
    <td>${p.nama_donatur||'—'}</td>
    <td><span class="badge ${p.status}">${p.status}</span></td>
    <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.keterangan||'—'}</td></tr>`).join('');
}
async function muatPengajuanMasuk(){
  const r=await apiGet('get_surat_pengajuan');
  const tb=document.getElementById('tbMasuk');
  const masuk=r.data?.filter(p=>parseInt(p.id_masjid)!==MY_MID&&p.status==='menunggu')||[];
  if(!masuk.length){tb.innerHTML='<tr><td colspan="6" style="text-align:center;padding:20px;color:#aaa">Tidak ada pengajuan masuk</td></tr>';return;}
  tb.innerHTML=masuk.map(p=>`
    <tr><td>${new Date(p.tanggal_pengajuan).toLocaleDateString('id-ID')}</td>
    <td><strong>${p.nama_pemohon}</strong></td>
    <td>Rp ${Number(p.jumlah_diminta).toLocaleString('id-ID')}</td>
    <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.keterangan||'—'}</td>
    <td><span class="badge ${p.status}">${p.status}</span></td>
    <td>
      <button class="btn btn-p btn-sm" onclick="bukaTinjau(${p.id},'setujui')"><i class="fas fa-check"></i> Setujui</button>
      <button class="btn btn-d btn-sm" onclick="bukaTinjau(${p.id},'tolak')" style="margin-left:5px"><i class="fas fa-times"></i> Tolak</button>
    </td></tr>`).join('');
}

function bukaTinjau(id, tipe) {
  idTinjau = id;
  const setForm=document.getElementById('tinjauSetujuiForm');
  const tolForm=document.getElementById('tinjauTolakForm');
  const foot=document.getElementById('mTinjauFoot');
  setForm.style.display='none'; tolForm.style.display='none';
  document.getElementById('alTinjau').style.display='none';

  if(tipe==='setujui'){
    document.getElementById('mTinjauJudul').textContent='✅ Setujui Pengajuan';
    setForm.style.display='block';
    foot.innerHTML=`<button class="btn btn-s" onclick="tutupModal('mTinjau')">Batal</button>
      <button class="btn btn-p" onclick="kirimRespon('disetujui')"><i class="fas fa-check"></i> Setujui</button>`;
  } else {
    document.getElementById('mTinjauJudul').textContent='❌ Tolak Pengajuan';
    tolForm.style.display='block';
    foot.innerHTML=`<button class="btn btn-s" onclick="tutupModal('mTinjau')">Batal</button>
      <button class="btn btn-d" onclick="kirimRespon('ditolak')"><i class="fas fa-times"></i> Tolak</button>`;
  }
  bukaModal('mTinjau');
}

async function kirimRespon(status) {
  const al=document.getElementById('alTinjau');
  const r=await api('ubah_status',{
    id:idTinjau, status,
    id_masjid_donatur: status==='disetujui'?document.getElementById('tDonatur').value:'',
    alasan_penolakan: status==='ditolak'?document.getElementById('tAlasan').value:''
  });
  al.style.display='block';al.className='alert '+(r.success?'alert-ok':'alert-err');al.textContent=r.message;
  if(r.success){setTimeout(()=>{tutupModal('mTinjau');muatPengajuanMasuk();muatPengajuanSaya();muatDashboard();},900)}
}

async function submitAjukan() {
  const al=document.getElementById('alAjukan');
  const btn=document.getElementById('btnAjukan');
  const fd=new FormData();
  fd.append('action','buat_surat_pengajuan');
  fd.append('jumlah_diminta',document.getElementById('ajJumlah').value);
  fd.append('id_masjid_donatur',document.getElementById('ajDonatur').value);
  fd.append('keterangan',document.getElementById('ajKet').value);
  const f=document.getElementById('ajFile').files[0];
  if(f) fd.append('file',f);
  btn.disabled=true;btn.innerHTML='<span class="loader-sm"></span> Mengirim...';
  const r=await (await fetch('api/index.php',{method:'POST',body:fd})).json();
  btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> Kirim';
  al.style.display='block';al.className='alert '+(r.success?'alert-ok':'alert-err');al.textContent=r.message;
  if(r.success){setTimeout(()=>{tutupModal('mAjukan');muatPengajuanSaya();},1000)}
}

// ========== DISTRIBUSI ==========
async function muatDistribusi(){
  const r=await apiGet('lihat_distribusi');
  const el=document.getElementById('tlDistribusi');
  if(!r.success||!r.data.length){el.innerHTML='<p style="color:#aaa;text-align:center;padding:20px">Belum ada histori distribusi</p>';return;}
  el.innerHTML=r.data.map(d=>`
    <div class="tl-item">
      <div class="tl-dot"><i class="fas fa-exchange-alt"></i></div>
      <div>
        <div class="tl-jdl"><strong>${d.nama_asal}</strong> → <strong>${d.nama_tujuan}</strong>
          <span style="color:#28a745;margin-left:8px">Rp ${Number(d.jumlah_distribusi).toLocaleString('id-ID')}</span></div>
        <div class="tl-meta">📅 ${new Date(d.tanggal_distribusi).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'})}${d.catatan?' • '+d.catatan:''}</div>
      </div>
    </div>`).join('');
}

// ========== MODAL & UTIL ==========
function bukaModal(id){document.getElementById(id).classList.add('aktif')}
function tutupModal(id){document.getElementById(id).classList.remove('aktif')}
document.querySelectorAll('.ov').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)el.classList.remove('aktif')}));
// ========== JARAK KE MASJID LAIN ==========
let dataMasjidJarak = [];

function hitungJarak(lat1, lng1, lat2, lng2) {
  const R = 6371000; // meter
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat/2)*Math.sin(dLat/2) +
            Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*
            Math.sin(dLng/2)*Math.sin(dLng/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c; // dalam meter
}

function formatJarak(meter) {
  if (meter < 1000) return Math.round(meter) + ' m';
  return (meter/1000).toFixed(1) + ' km';
}

async function muatJarak() {
  const container = document.getElementById('listJarak');
  container.innerHTML = '<p style="color:#aaa;text-align:center;padding:20px">Memuat...</p>';

  if (!MY_MID) {
    container.innerHTML = '<p style="color:#aaa;text-align:center;padding:20px">Masjid Anda belum terdaftar</p>';
    return;
  }

  // Ambil detail masjid saya
  const rSaya = await apiGet('get_masjid_detail', {id: MY_MID});
  if (!rSaya.success) { container.innerHTML = '<p style="color:#f00;text-align:center">Gagal memuat data</p>'; return; }

  const masjidSaya = rSaya.data;
  const latSaya = parseFloat(masjidSaya.latitude);
  const lngSaya = parseFloat(masjidSaya.logtitude);

  // Ambil semua masjid
  const rSemua = await apiGet('get_masjid');
  if (!rSemua.success) return;

  // Hitung jarak ke semua masjid kecuali masjid sendiri
  dataMasjidJarak = rSemua.data
    .filter(m => m.id != MY_MID)
    .map(m => {
      const jarak = hitungJarak(latSaya, lngSaya, parseFloat(m.latitude), parseFloat(m.logtitude));
      return { ...m, jarak };
    });

  tampilJarak();
}

function tampilJarak() {
  const container  = document.getElementById('listJarak');
  const filterVal  = document.getElementById('filterJarak').value;
  const urutVal    = document.getElementById('urutJarak').value;

  let data = [...dataMasjidJarak];

  // Filter status
  if (filterVal !== 'semua') {
    data = data.filter(m => m.status_zakat === filterVal);
  }

  // Urut jarak
  data.sort((a, b) => urutVal === 'terdekat' ? a.jarak - b.jarak : b.jarak - a.jarak);

  if (!data.length) {
    container.innerHTML = '<p style="color:#aaa;text-align:center;padding:20px">Tidak ada masjid ditemukan</p>';
    return;
  }

  const warnaBadge = { surplus: '#198754', defisit: '#dc3545', seimbang: '#6c757d' };

  container.innerHTML = data.map((m, i) => `
    <div style="display:flex;align-items:center;gap:14px;padding:14px 12px;border-bottom:1px solid #f0ead8;${i===0&&urutVal==='terdekat'?'background:#f0fdf4;border-radius:10px;':''}">
      <div style="min-width:52px;text-align:center">
        <div style="font-size:1.2rem;font-weight:800;color:${i===0?'#198754':'#0F5132'}">${i+1}</div>
        <div style="font-size:.65rem;color:#aaa">${urutVal==='terdekat'?'terdekat':'terjauh'}</div>
      </div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:.9rem;color:#1A1208">${m.nama_masjid}</div>
        <div style="font-size:.78rem;color:#888;margin-top:2px">${m.kecamatan||'—'}, ${m.kota||'—'}</div>
        <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <span style="background:${warnaBadge[m.status_zakat]||'#6c757d'};color:#fff;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600">${m.status_zakat||'—'}</span>
          <span style="font-size:.78rem;color:#666">Zakat: Rp ${Number(m.jumlah_zakat||0).toLocaleString('id-ID')}</span>
        </div>
      </div>
      <div style="text-align:right;min-width:70px">
        <div style="font-size:1.05rem;font-weight:800;color:#0F5132">${formatJarak(m.jarak)}</div>
        <div style="font-size:.7rem;color:#aaa">dari masjid Anda</div>
      </div>
    </div>
  `).join('');
}

async function logout(){
  try {
    await api('logout');
  } catch(e) {}
  window.location.replace('index.php');
}

// ========== INIT ==========
muatDashboard();
</script>
</body>
</html>