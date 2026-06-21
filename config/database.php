<?php

define('DB_HOST', getenv('MYSQLHOST'));
define('DB_USER', getenv('MYSQLUSER'));
define('DB_PASS', getenv('MYSQLPASSWORD'));
define('DB_NAME', getenv('MYSQLDATABASE'));
define('DB_PORT', getenv('MYSQLPORT'));

function getDB()
{
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        (int) DB_PORT
    );

    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}