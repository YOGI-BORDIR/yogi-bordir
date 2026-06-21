<?php
// Mengambil kredensial dari environment variable Railway secara mutlak.
// Jika di localhost dan variabel kosong, baru pakai nilai default di sebelah kanan ?:.
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

// Siasat agar dinamis: Mengikuti database Railway, kalau di lokal pakai 'yogi_bordir'
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'yogi_bordir');

function getDB() {
    // Memastikan semua variabel global terisi dengan benar
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        // Biar kelihatan jelas di log jika erornya karena masalah koneksi/nama db
        die("Koneksi ke database gagal (" . $conn->connect_errno . "): " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>