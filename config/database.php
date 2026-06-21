<?php
function env(array $keys, $default = null)
{
    foreach ($keys as $key) {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }
    return $default;
}

// Jika deployment menyediakan URL database (misalnya Railway / Heroku), gunakan dulu DATABASE_URL atau MYSQL_URL.
$databaseUrl = env(['DATABASE_URL', 'MYSQL_URL']);
if ($databaseUrl) {
    $parsed = parse_url($databaseUrl);
    define('DB_HOST', $parsed['host'] ?? '127.0.0.1');
    define('DB_USER', $parsed['user'] ?? 'root');
    define('DB_PASS', $parsed['pass'] ?? '');
    define('DB_NAME', isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway');
    define('DB_PORT', $parsed['port'] ?? '3306');
} else {
    // Wajib ganti 'localhost' menjadi '127.0.0.1' agar mysqli terhubung via TCP, bukan socket lokal Linux
    define('DB_HOST', env(['MYSQLHOST', 'MYSQL_HOST'], '127.0.0.1'));
    define('DB_USER', env(['MYSQLUSER', 'MYSQL_USER'], 'root'));
    define('DB_PASS', env(['MYSQLPASSWORD', 'MYSQL_PASSWORD'], ''));
    define('DB_NAME', env(['MYSQLDATABASE', 'MYSQL_DATABASE'], 'railway'));
    define('DB_PORT', env(['MYSQLPORT', 'MYSQL_PORT'], '3306'));
}

function getDB()
{
    // Memasukkan variabel konstanta di atas ke fungsi mysqli asli kamu
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        $message = "Koneksi gagal: " . $conn->connect_error;
        if (!env(['MYSQLHOST', 'MYSQL_HOST', 'DATABASE_URL', 'MYSQL_URL'])) {
            $message .= ". Pastikan MYSQLHOST / MYSQL_HOST, MYSQLUSER / MYSQL_USER, MYSQLPASSWORD / MYSQL_PASSWORD, MYSQLDATABASE / MYSQL_DATABASE, MYSQLPORT / MYSQL_PORT atau DATABASE_URL terpasang.";
        }
        die($message);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
