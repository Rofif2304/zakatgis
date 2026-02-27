<?php
require_once '../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'login':               doLogin();              break;
    case 'logout':              doLogout();             break;
    case 'get_users':           requireAdmin(); getUsers();          break;
    case 'create_user':         requireAdmin(); createUser();        break;
    case 'update_user':         requireAdmin(); updateUser();        break;
    case 'delete_user':         requireAdmin(); deleteUser();        break;
    case 'get_masjid':                          getMasjid();         break;
    case 'get_masjid_detail':                   getMasjidDetail();   break;
    case 'get_masjid_menunggu': requireAdmin(); getMasjidMenunggu(); break;
    case 'pendataan_masjid':    requireLogin(); pendataanMasjid();   break;
    case 'update_masjid':       requireLogin(); updateMasjid();      break;
    case 'delete_masjid':       requireAdmin(); deleteMasjid();      break;
    case 'verifikasi_masjid':   requireAdmin(); verifikasiMasjid();  break;
    case 'input_data_zakat':    requireLogin(); inputDataZakat();    break;
    case 'get_data_zakat':                      getDataZakat();      break;
    case 'get_rekap_zakat':     requireLogin(); getRekapZakat();     break;
    case 'tambah_distribusi':   requireLogin(); tambahDistribusi();  break;
    case 'lihat_distribusi':                    lihatDistribusi();   break;
    case 'buat_surat_pengajuan':requireLogin(); buatSuratPengajuan(); break;
    case 'get_surat_pengajuan': requireLogin(); getSuratPengajuan(); break;
    case 'tinjau_pengajuan':    requireLogin(); tinjauPengajuan();   break;
    case 'ubah_status':         requireLogin(); ubahStatus();        break;
    case 'view_status_zakat':                   viewStatusZakat();   break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak dikenal']);
}

function doLogin() {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi']);
        return;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan']);
        return;
    }

    if ($password !== $user['password'] && !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
        return;
    }

    $masjid = null;
    if ($user['role'] === 'pengurus') {
        $ms = $db->prepare("SELECT id, nama_masjid FROM masjid WHERE user_id = ? LIMIT 1");
        $ms->execute([$user['id']]);
        $masjid = $ms->fetch(PDO::FETCH_ASSOC);
    }

    $_SESSION['user_id']     = $user['id'];
    $_SESSION['nama']        = $user['nama'];
    $_SESSION['username']    = $user['username'];
    $_SESSION['role']        = $user['role'];
    $_SESSION['masjid_id']   = $masjid['id'] ?? null;
    $_SESSION['nama_masjid'] = $masjid['nama_masjid'] ?? null;

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user'    => [
            'id'          => $user['id'],
            'nama'        => $user['nama'],
            'username'    => $user['username'],
            'role'        => $user['role'],
            'masjid_id'   => $_SESSION['masjid_id'],
            'nama_masjid' => $_SESSION['nama_masjid'],
        ]
    ]);
}

function doLogout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
}

function getUsers() {
    $rows = getDB()->query("SELECT id, nama, username, role, created_at FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
}

function createUser() {
    $db   = getDB();
    $nama = sanitize($_POST['nama'] ?? '');
    $un   = sanitize($_POST['username'] ?? '');
    $pw   = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'pengurus');

    if (!$nama || !$un || !$pw) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        return;
    }

    $cek = $db->prepare("SELECT id FROM users WHERE username = ?");
    $cek->execute([$un]);
    if ($cek->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        return;
    }

    $db->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)")
       ->execute([$nama, $un, password_hash($pw, PASSWORD_DEFAULT), $role]);

    echo json_encode(['success' => true, 'message' => 'User berhasil dibuat']);
}

function updateUser() {
    $db   = getDB();
    $id   = intval($_POST['id'] ?? 0);
    $nama = sanitize($_POST['nama'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $pw   = $_POST['password'] ?? '';

    if ($pw) {
        $db->prepare("UPDATE users SET nama = ?, role = ?, password = ? WHERE id = ?")
           ->execute([$nama, $role, password_hash($pw, PASSWORD_DEFAULT), $id]);
    } else {
        $db->prepare("UPDATE users SET nama = ?, role = ? WHERE id = ?")
           ->execute([$nama, $role, $id]);
    }
    echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
}

function deleteUser() {
    $id = intval($_POST['id'] ?? 0);
    if ($id === 1) {
        echo json_encode(['success' => false, 'message' => 'Admin utama tidak bisa dihapus']);
        return;
    }
    getDB()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
}

function getMasjid() {
    $db   = getDB();
    $stmt = $db->query("
        SELECT m.*, u.nama AS nama_pengurus,
               dz.jumlah_zakat, dz.jumlah_supply, dz.tanggal_input
        FROM masjid m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN data_zakat dz ON dz.id = (
            SELECT id FROM data_zakat WHERE id_masjid = m.id
            ORDER BY tanggal_input DESC, id DESC LIMIT 1
        )
        WHERE m.status_verifikasi = 'terverifikasi'
        ORDER BY m.nama_masjid
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $z = (float)($r['jumlah_zakat'] ?? 0);
        $s = (float)($r['jumlah_supply'] ?? 0);
        if ($z > $s)     $r['status_zakat'] = 'surplus';
        elseif ($z < $s) $r['status_zakat'] = 'defisit';
        else             $r['status_zakat'] = 'seimbang';
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getMasjidDetail() {
    $id   = intval($_GET['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT m.*, u.nama AS nama_pengurus,
               dz.jumlah_zakat, dz.jumlah_supply, dz.tanggal_input
        FROM masjid m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN data_zakat dz ON dz.id = (
            SELECT id FROM data_zakat WHERE id_masjid = m.id
            ORDER BY tanggal_input DESC, id DESC LIMIT 1
        )
        WHERE m.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Masjid tidak ditemukan']);
        return;
    }
    echo json_encode(['success' => true, 'data' => $row]);
}

function getMasjidMenunggu() {
    $stmt = getDB()->query("SELECT * FROM masjid WHERE status_verifikasi = 'menunggu' ORDER BY created_at DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function pendataanMasjid() {
    $db   = getDB();
    $nama = sanitize($_POST['nama_masjid'] ?? '');
    $lat  = $_POST['latitude']  ?? '';
    $lng  = $_POST['logtitude'] ?? '';
    $errors = [];
    if (!$nama)            $errors[] = 'Nama masjid wajib diisi';
    if (!is_numeric($lat)) $errors[] = 'Latitude tidak valid';
    if (!is_numeric($lng)) $errors[] = 'Longitude tidak valid';
    if ($errors) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    $user = currentUser();
    $db->prepare("INSERT INTO masjid (nama_masjid, alamat, kecamatan, kota, latitude, logtitude, telepon, user_id, status_verifikasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')")
       ->execute([$nama, sanitize($_POST['alamat'] ?? ''), sanitize($_POST['kecamatan'] ?? ''), sanitize($_POST['kota'] ?? ''), (float)$lat, (float)$lng, sanitize($_POST['telepon'] ?? ''), $user['user_id']]);
    echo json_encode(['success' => true, 'message' => 'Data masjid berhasil dikirim, menunggu verifikasi admin']);
}

function updateMasjid() {
    $db   = getDB();
    $id   = intval($_POST['id'] ?? 0);
    $user = currentUser();
    if (!isAdmin()) {
        $cek = $db->prepare("SELECT id FROM masjid WHERE id = ? AND user_id = ?");
        $cek->execute([$id, $user['user_id']]);
        if (!$cek->fetch()) { echo json_encode(['success' => false, 'message' => 'Akses ditolak']); return; }
    }
    $nama = sanitize($_POST['nama_masjid'] ?? '');
    $lat  = $_POST['latitude']  ?? '';
    $lng  = $_POST['logtitude'] ?? '';
    if (!$nama || !is_numeric($lat) || !is_numeric($lng)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']); return;
    }
    $db->prepare("UPDATE masjid SET nama_masjid=?, alamat=?, kecamatan=?, kota=?, latitude=?, logtitude=?, telepon=? WHERE id=?")
       ->execute([$nama, sanitize($_POST['alamat'] ?? ''), sanitize($_POST['kecamatan'] ?? ''), sanitize($_POST['kota'] ?? ''), (float)$lat, (float)$lng, sanitize($_POST['telepon'] ?? ''), $id]);
    echo json_encode(['success' => true, 'message' => 'Data masjid berhasil diperbarui']);
}

function deleteMasjid() {
    getDB()->prepare("DELETE FROM masjid WHERE id = ?")->execute([intval($_POST['id'] ?? 0)]);
    echo json_encode(['success' => true, 'message' => 'Masjid berhasil dihapus']);
}

function verifikasiMasjid() {
    $id     = intval($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    if (!in_array($status, ['terverifikasi', 'ditolak'])) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']); return;
    }
    getDB()->prepare("UPDATE masjid SET status_verifikasi = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['success' => true, 'message' => $status === 'terverifikasi' ? 'Masjid berhasil diverifikasi' : 'Masjid ditolak']);
}

function inputDataZakat() {
    $user      = currentUser();
    $id_masjid = intval($_POST['id_masjid'] ?? $user['masjid_id'] ?? 0);
    if (!$id_masjid) { echo json_encode(['success' => false, 'message' => 'Masjid tidak ditemukan']); return; }
    getDB()->prepare("INSERT INTO data_zakat (id_masjid, jumlah_zakat, jumlah_supply, tanggal_input) VALUES (?, ?, ?, ?)")
           ->execute([$id_masjid, floatval($_POST['jumlah_zakat'] ?? 0), floatval($_POST['jumlah_supply'] ?? 0), sanitize($_POST['tanggal_input'] ?? date('Y-m-d'))]);
    echo json_encode(['success' => true, 'message' => 'Data zakat berhasil diinput']);
}

function getDataZakat() {
    $id_masjid = intval($_GET['id_masjid'] ?? 0);
    $db = getDB();
    if ($id_masjid) {
        $stmt = $db->prepare("SELECT * FROM data_zakat WHERE id_masjid = ? ORDER BY tanggal_input DESC");
        $stmt->execute([$id_masjid]);
    } else {
        $stmt = $db->query("SELECT dz.*, m.nama_masjid FROM data_zakat dz JOIN masjid m ON dz.id_masjid = m.id ORDER BY dz.tanggal_input DESC");
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function getRekapZakat() {
    $stmt = getDB()->query("
        SELECT m.id, m.nama_masjid, m.kecamatan,
               dz.jumlah_zakat, dz.jumlah_supply, dz.tanggal_input,
               (dz.jumlah_zakat - dz.jumlah_supply) AS selisih
        FROM masjid m
        LEFT JOIN data_zakat dz ON dz.id = (
            SELECT id FROM data_zakat WHERE id_masjid = m.id
            ORDER BY tanggal_input DESC, id DESC LIMIT 1
        )
        WHERE m.status_verifikasi = 'terverifikasi'
        ORDER BY m.nama_masjid
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $s = (float)($r['selisih'] ?? 0);
        $r['status_zakat'] = $s > 0 ? 'surplus' : ($s < 0 ? 'defisit' : 'seimbang');
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

function tambahDistribusi() {
    $user = currentUser();
    $id_zakat         = intval($_POST['id_zakat'] ?? 0);
    $id_masjid_asal   = intval($_POST['id_masjid_asal']   ?? $user['masjid_id'] ?? 0);
    $id_masjid_tujuan = intval($_POST['id_masjid_tujuan'] ?? 0);
    $jumlah           = floatval($_POST['jumlah_distribusi'] ?? 0);
    if (!$id_zakat || !$id_masjid_asal || !$id_masjid_tujuan || $jumlah <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']); return;
    }
    getDB()->prepare("INSERT INTO supply_zakat (id_zakat, id_masjid_asal, id_masjid_tujuan, tanggal_distribusi, jumlah_distribusi, catatan) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$id_zakat, $id_masjid_asal, $id_masjid_tujuan, sanitize($_POST['tanggal_distribusi'] ?? date('Y-m-d')), $jumlah, sanitize($_POST['catatan'] ?? '')]);
    echo json_encode(['success' => true, 'message' => 'Distribusi berhasil dicatat']);
}

function lihatDistribusi() {
    $stmt = getDB()->query("
        SELECT sz.*, m1.nama_masjid AS nama_asal, m2.nama_masjid AS nama_tujuan
        FROM supply_zakat sz
        JOIN masjid m1 ON sz.id_masjid_asal   = m1.id
        JOIN masjid m2 ON sz.id_masjid_tujuan = m2.id
        ORDER BY sz.created_at DESC
    ");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function buatSuratPengajuan() {
    $user       = currentUser();
    $id_masjid  = intval($user['masjid_id'] ?? 0);
    $jumlah     = floatval($_POST['jumlah_diminta'] ?? 0);
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    $id_donatur = intval($_POST['id_masjid_donatur'] ?? 0) ?: null;
    if (!$id_masjid || $jumlah <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']); return;
    }
    $namaFile = null;
    if (!empty($_FILES['file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','doc','docx','jpg','jpeg','png'])) {
            echo json_encode(['success' => false, 'message' => 'Format file tidak diizinkan']); return;
        }
        if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
        $namaFile = 'surat_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_PATH . $namaFile);
    }
    getDB()->prepare("INSERT INTO surat_pengajuan (id_masjid, id_user, jumlah_diminta, keterangan, file, id_masjid_donatur) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$id_masjid, $user['user_id'], $jumlah, $keterangan, $namaFile, $id_donatur]);
    echo json_encode(['success' => true, 'message' => 'Surat pengajuan berhasil dikirim']);
}

function getSuratPengajuan() {
    $db   = getDB();
    $user = currentUser();
    if (isAdmin()) {
        $stmt = $db->query("SELECT sp.*, m1.nama_masjid AS nama_pemohon, m2.nama_masjid AS nama_donatur FROM surat_pengajuan sp JOIN masjid m1 ON sp.id_masjid=m1.id LEFT JOIN masjid m2 ON sp.id_masjid_donatur=m2.id ORDER BY sp.tanggal_pengajuan DESC");
    } else {
        $mid  = intval($user['masjid_id'] ?? 0);
        $stmt = $db->prepare("SELECT sp.*, m1.nama_masjid AS nama_pemohon, m2.nama_masjid AS nama_donatur FROM surat_pengajuan sp JOIN masjid m1 ON sp.id_masjid=m1.id LEFT JOIN masjid m2 ON sp.id_masjid_donatur=m2.id WHERE sp.id_masjid=? OR sp.id_masjid_donatur=? ORDER BY sp.tanggal_pengajuan DESC");
        $stmt->execute([$mid, $mid]);
    }
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function tinjauPengajuan() {
    $id   = intval($_GET['id'] ?? 0);
    $stmt = getDB()->prepare("SELECT sp.*, m1.nama_masjid AS nama_pemohon, u.nama AS nama_pengaju FROM surat_pengajuan sp JOIN masjid m1 ON sp.id_masjid=m1.id JOIN users u ON sp.id_user=u.id WHERE sp.id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']); return; }
    echo json_encode(['success' => true, 'data' => $row]);
}

function ubahStatus() {
    $db      = getDB();
    $id      = intval($_POST['id'] ?? 0);
    $status  = sanitize($_POST['status'] ?? '');
    $alasan  = sanitize($_POST['alasan_penolakan'] ?? '');
    $donatur = intval($_POST['id_masjid_donatur'] ?? 0) ?: null;
    if (!in_array($status, ['disetujui', 'ditolak'])) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']); return;
    }
    $db->prepare("UPDATE surat_pengajuan SET status=?, alasan_penolakan=?, id_masjid_donatur=?, tanggal_respon=NOW() WHERE id=?")
       ->execute([$status, $alasan, $donatur, $id]);
    if ($status === 'disetujui' && $donatur) {
        $sp = $db->prepare("SELECT * FROM surat_pengajuan WHERE id=?"); $sp->execute([$id]);
        $p  = $sp->fetch(PDO::FETCH_ASSOC);
        $dz = $db->prepare("SELECT id FROM data_zakat WHERE id_masjid=? ORDER BY tanggal_input DESC LIMIT 1"); $dz->execute([$donatur]);
        $z  = $dz->fetch(PDO::FETCH_ASSOC);
        if ($z && $p) {
            $db->prepare("INSERT INTO supply_zakat (id_zakat, id_masjid_asal, id_masjid_tujuan, tanggal_distribusi, jumlah_distribusi, catatan) VALUES (?,?,?,CURDATE(),?,?)")
               ->execute([$z['id'], $donatur, $p['id_masjid'], $p['jumlah_diminta'], 'Distribusi dari pengajuan #'.$id]);
        }
    }
    echo json_encode(['success' => true, 'message' => $status === 'disetujui' ? 'Pengajuan disetujui' : 'Pengajuan ditolak']);
}

function viewStatusZakat() {
    $stmt = getDB()->query("SELECT m.id, m.nama_masjid, m.kecamatan, m.kota, m.latitude, m.logtitude, dz.jumlah_zakat, dz.jumlah_supply, dz.tanggal_input FROM masjid m LEFT JOIN data_zakat dz ON dz.id=(SELECT id FROM data_zakat WHERE id_masjid=m.id ORDER BY tanggal_input DESC LIMIT 1) WHERE m.status_verifikasi='terverifikasi' ORDER BY m.nama_masjid");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $z = (float)($r['jumlah_zakat'] ?? 0); $s = (float)($r['jumlah_supply'] ?? 0);
        $r['status_zakat'] = $z > $s ? 'surplus' : ($z < $s ? 'defisit' : 'seimbang');
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}