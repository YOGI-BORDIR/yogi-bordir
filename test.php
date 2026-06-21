<?php
require_once 'config/database.php';
$db = getDB();
$result = $db->query("SELECT * FROM layanan");
if(!$result) {
    echo "ERROR: " . $db->error;
} else {
    echo "Jumlah data: " . $result->num_rows . "<br>";
    while($r = $result->fetch_assoc()) {
        echo $r['id_layanan'] . " - " . $r['judul'] . " - " . $r['ikon'] . "<br>";
    }
}
?>
