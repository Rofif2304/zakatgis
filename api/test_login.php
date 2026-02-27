<?php
// File test sementara untuk cek login
require_once '../includes/config.php';

$username = 'admin';
$password = 'password';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

echo "<h2>Hasil Cek Login</h2>";

if (!$user) {
    echo "<p style='color:red'>❌ User 'admin' TIDAK DITEMUKAN di database!</p>";
} else {
    echo "<p style='color:green'>✅ User ditemukan: " . $user['nama'] . "</p>";
    echo "<p>Username di DB: " . $user['username'] . "</p>";
    echo "<p>Role: " . $user['role'] . "</p>";
    echo "<p>Password hash (20 karakter pertama): " . substr($user['password'], 0, 20) . "...</p>";
    
    $cocok = password_verify($password, $user['password']);
    if ($cocok) {
        echo "<p style='color:green'>✅ Password 'password' COCOK! Login seharusnya berhasil.</p>";
    } else {
        echo "<p style='color:red'>❌ Password TIDAK COCOK. Hash di database bermasalah.</p>";
        
        // Buat hash baru dan tampilkan
        $hash_baru = password_hash('password', PASSWORD_DEFAULT);
        echo "<p>Hash baru yang perlu dimasukkan ke database:</p>";
        echo "<code style='word-break:break-all;background:#f0f0f0;padding:10px;display:block'>" . $hash_baru . "</code>";
        echo "<br>";
        echo "<p>Jalankan query SQL ini di phpMyAdmin:</p>";
        echo "<code style='word-break:break-all;background:#f0f0f0;padding:10px;display:block'>UPDATE users SET password = '" . $hash_baru . "' WHERE username = 'admin';</code>";
    }
}

echo "<hr>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p><a href='../index.php'>Kembali ke Login</a></p>";
?>
