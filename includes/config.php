<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'zakatgis');
define('UPLOAD_PATH', __DIR__ . '/../uploads/documents/');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() { return !empty($_SESSION['user_id']); }
function isAdmin()    { return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function isPengurus() { return !empty($_SESSION['role']) && $_SESSION['role'] === 'pengurus'; }
function currentUser(){ return $_SESSION ?? []; }
function sanitize($s) { return htmlspecialchars(strip_tags(trim((string)($s ?? '')))); }

function requireLogin() {
    if (!isLoggedIn()) { header('Location: /zakatgis-v2/index.php'); exit; }
}
function requireAdmin() {
    if (!isLoggedIn()) { header('Location: /zakatgis-v2/index.php'); exit; }
    if (!isAdmin())    { header('Location: /zakatgis-v2/dashboard.php'); exit; }
}