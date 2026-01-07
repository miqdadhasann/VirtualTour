<?php
// includes/logic_loader.php
// REVISI: Read-Only Logic untuk performa maksimal

require_once 'config.php'; 

// Default variables
$json_content = '{}';
$critical_error = false;
$showDebugUI = true; // Set false nanti saat production
$isDebug = $showDebugUI;

if ($db_status === 'connected') {
    // Cek apakah file JSON sudah ada?
    if (file_exists(JSON_PATH)) {
        // FASTEST: Load langsung dari file
        $json_content = file_get_contents(JSON_PATH);
    } else {
        // FALLBACK: Jika file hilang, generate baru
        require_once 'includes/functions.php';
        regenerateTourJson($conn);
        $json_content = file_get_contents(JSON_PATH);
    }

} else {
    // OFFLINE MODE / DB DOWN
    if (file_exists(JSON_PATH)) {
        $json_content = file_get_contents(JSON_PATH);
        error_log("VirtualTour: DB Down, serving JSON cache.");
    } else {
        $critical_error = "Database Error & No Cache Available.";
    }
}
?>