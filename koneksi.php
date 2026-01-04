<?php
// koneksi.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'virtual_tour';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi dummy untuk contoh
function get_tours($conn) {
    return mysqli_query($conn, "SELECT * FROM tours"); // Asumsi ada tabel 'tours'
}
?>