-- ZakatGIS Database
-- Hapus tabel lama kalau ada
DROP TABLE IF EXISTS supply_zakat;
DROP TABLE IF EXISTS surat_pengajuan;
DROP TABLE IF EXISTS data_zakat;
DROP TABLE IF EXISTS masjid;
DROP TABLE IF EXISTS users;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','pengurus') DEFAULT 'pengurus',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel masjid
CREATE TABLE masjid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_masjid VARCHAR(150) NOT NULL,
    alamat TEXT,
    kecamatan VARCHAR(100),
    kota VARCHAR(100),
    latitude DECIMAL(10,7),
    logtitude DECIMAL(10,7),
    telepon VARCHAR(20),
    user_id INT,
    status_verifikasi ENUM('menunggu','terverifikasi','ditolak') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel data_zakat
CREATE TABLE data_zakat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_masjid INT NOT NULL,
    jumlah_zakat DECIMAL(15,2) DEFAULT 0,
    jumlah_supply DECIMAL(15,2) DEFAULT 0,
    tanggal_input DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_masjid) REFERENCES masjid(id) ON DELETE CASCADE
);

-- Tabel supply_zakat
CREATE TABLE supply_zakat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_zakat INT,
    id_masjid_asal INT,
    id_masjid_tujuan INT,
    tanggal_distribusi DATE,
    jumlah_distribusi DECIMAL(15,2),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel surat_pengajuan
CREATE TABLE surat_pengajuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_masjid INT,
    id_user INT,
    jumlah_diminta DECIMAL(15,2),
    keterangan TEXT,
    file VARCHAR(255),
    status ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
    alasan_penolakan TEXT,
    id_masjid_donatur INT,
    tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_respon TIMESTAMP NULL
);

-- ============================================================
-- DATA DEMO
-- Password 'password123' dalam bentuk plain text
-- ============================================================

INSERT INTO users (id, nama, username, password, role) VALUES
(1, 'Sistem Admin',  'admin',  'password123', 'admin'),
(2, 'Ahmad Fauzi',   'ahmad',  'password123', 'pengurus'),
(3, 'Budi Santoso',  'budi',   'password123', 'pengurus'),
(4, 'Candra Wijaya', 'candra', 'password123', 'pengurus'),
(5, 'Dian Prasetyo', 'dian',   'password123', 'pengurus'),
(6, 'Eko Susanto',   'eko',    'password123', 'pengurus');

INSERT INTO masjid (id, nama_masjid, alamat, kecamatan, kota, latitude, logtitude, telepon, user_id, status_verifikasi) VALUES
(1, 'Masjid Al-Ikhlas',      'Jl. Merdeka No. 1',      'Sukasari',  'Bandung', -6.9175, 107.6191, '022-1111111', 2, 'terverifikasi'),
(2, 'Masjid Ar-Rahman',      'Jl. Pahlawan No. 22',    'Coblong',   'Bandung', -6.8951, 107.6271, '022-2222222', 3, 'terverifikasi'),
(3, 'Masjid Baitul Makmur',  'Jl. Cihampelas No. 45',  'Cidadap',   'Bandung', -6.8820, 107.6050, '022-3333333', 4, 'terverifikasi'),
(4, 'Masjid Nurul Huda',     'Jl. Dago No. 78',        'Coblong',   'Bandung', -6.8700, 107.6145, '022-4444444', 5, 'terverifikasi'),
(5, 'Masjid Al-Falah',       'Jl. Buah Batu No. 100',  'Lengkong',  'Bandung', -6.9407, 107.6321, '022-5555555', 6, 'terverifikasi');

INSERT INTO data_zakat (id_masjid, jumlah_zakat, jumlah_supply, tanggal_input) VALUES
(1, 15000000,  8000000, CURDATE()),
(2,  3000000, 12000000, CURDATE()),
(3, 20000000,  9000000, CURDATE()),
(4,  2000000, 10000000, CURDATE()),
(5,  8000000,  8000000, CURDATE());