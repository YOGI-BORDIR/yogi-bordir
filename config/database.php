<?php
// Mengambil data host dan port langsung dari environment internal Railway.
// Jika di localhost (kosong), baru dia mundur pakai '127.0.0.1' dan '3306'.
define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

function getDB() {
    // Memasukkan DB_PORT secara eksplisit agar tidak tertukar dengan port internal cloud
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>