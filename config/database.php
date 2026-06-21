<?php
// Mengambil kredensial dari environment variable Railway.
// Jika tidak ada (seperti saat dijalankan di localhost), otomatis pakai nilai default setelah ?:
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'yogi_bordir');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

function getDB() {
    // Menambahkan parameter port pada koneksi mysqli
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>