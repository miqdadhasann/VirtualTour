<?php
// config.php - KONFIGURASI PUSAT
// Simpan file ini di folder utama (root directory)

// 1. DATABASE CONFIG
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'virtual_tour');

// 2. PATHS
define('BASE_PATH', __DIR__);
define('JSON_PATH', BASE_PATH . '/data/tour.json');

// 3. AUTO CONNECT DATABASE
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Flag error untuk ditangani logic_loader
    $db_status = 'error'; 
} else {
    $db_status = 'connected';
}
?>