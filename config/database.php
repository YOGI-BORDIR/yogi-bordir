<?php
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'yogi_bordir');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

// Kita ganti fungsi getDB menggunakan PDO Class bawaan PHP core
function getDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
        
        // PDO sudah pasti terinstall otomatis di PHP 8.4 cloud mana pun
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi database via PDO gagal: " . $e->getMessage());
    }
}

// Siasat agar kodingan lama kamu yang pakai murni $conn / $koneksi tidak eror:
// Kita buat object tiruan (Wrapper) minimalis agar fungsi mysqli_ query kamu tetap jalan.
class mysqli_wrapper {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }
    public function query($sql) { return $this->pdo->query($sql); }
    public function set_charset($charset) { return true; }
}

$koneksi = new mysqli_wrapper(getDB());