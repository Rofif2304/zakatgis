<?php
session_start();

// Kalau sudah login, langsung ke dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Koneksi database langsung (tidak pakai require agar tidak ada masalah path)
$error = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=zakatgis;charset=utf8mb4", "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) {
    $error = 'Koneksi database gagal: ' . $e->getMessage();
    $pdo = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Username tidak ditemukan!';
        } elseif ($password !== $user['password'] && !password_verify($password, $user['password'])) {
            $error = 'Password salah! Silahkan coba lagi';
        } else {
            // Cari masjid kalau pengurus
            $masjid_id = null; $nama_masjid = null;
            if ($user['role'] === 'pengurus') {
                $ms = $pdo->prepare("SELECT id, nama_masjid FROM masjid WHERE user_id = ? LIMIT 1");
                $ms->execute([$user['id']]);
                $m = $ms->fetch();
                if ($m) { $masjid_id = $m['id']; $nama_masjid = $m['nama_masjid']; }
            }
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['nama']        = $user['nama'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['role']        = $user['role'];
            $_SESSION['masjid_id']   = $masjid_id;
            $_SESSION['nama_masjid'] = $nama_masjid;
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ZakatGIS - Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#0F5132;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif}
.kotak{background:#FDF6E3;border-radius:16px;padding:40px 36px;width:360px;box-shadow:0 10px 40px rgba(0,0,0,.3)}
h2{text-align:center;color:#0F5132;margin-bottom:8px;font-size:1.4rem}
p.sub{text-align:center;color:#7A6840;font-size:.82rem;margin-bottom:22px;font-style:italic}
label{display:block;font-size:.72rem;font-weight:700;color:#7A6840;text-transform:uppercase;margin-bottom:5px}
input{width:100%;padding:11px 12px;border:1.5px solid #ddd;border-radius:8px;font-size:.9rem;margin-bottom:14px;background:#fff}
input:focus{outline:none;border-color:#0F5132}
.btn-masuk{width:100%;padding:12px;background:#0F5132;color:#C9A84C;border:none;border-radius:8px;font-size:.95rem;font-weight:700;cursor:pointer;margin-bottom:8px}
.btn-masuk:hover{background:#1A7A4A}
.btn-visitor{width:100%;padding:11px;background:none;border:1.5px solid #0F5132;border-radius:8px;font-size:.85rem;color:#0F5132;font-weight:600;cursor:pointer}
.error{background:#fff0f0;border:1px solid #ffcdd2;color:#c62828;padding:10px 12px;border-radius:8px;font-size:.85rem;margin-bottom:14px}
.info{margin-top:16px;padding:12px;background:rgba(15,81,50,.07);border-radius:8px;font-size:.76rem;color:#4A3B1C;line-height:2}
</style>
</head>
<body>
<div class="kotak">
  <h2>🕌 ZakatGIS</h2>
  <p class="sub">Sistem Koordinasi Distribusi Zakat</p>

  <?php if ($error): ?>
    <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="admin" autocomplete="off" autofocus>
    <label>Password</label>
    <input type="password" name="password" placeholder="password123" autocomplete="off">
    <button type="submit" class="btn-masuk">🔐 Masuk ke Sistem</button>
  </form>

  <button class="btn-visitor" onclick="location.href='visitor.php'">👁 Lihat Status Zakat (Tanpa Login)</button>

  <div class="info">
   <strong>Informasi:</strong><br>
   Silakan hubungi nomor yang tertera untuk informasi dan pendaftaran Sistem Koordinasi Zakat.<br>
   No WhatsApp: <b>+628581430755 (Admin A.N Beni)</b>
  </div>
</div>
</body>
</html>