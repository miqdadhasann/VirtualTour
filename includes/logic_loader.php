<?php
// FILE: includes/logic_loader.php

$json_path = 'data/tour.json';

// 1. Validasi Keberadaan File
if (!file_exists($json_path)) {
    // Kita set variabel error, nanti index.php yang memutuskan cara menampilkannya
    $critical_error = "File konfigurasi tidak ditemukan di: " . $json_path;
    $isDebug = false;
    $json_content = '{}';
} else {
    // 2. Load Data
    $json_content = file_get_contents($json_path);
    $tour_data = json_decode($json_content, true);
    
    // 3. Tentukan Status Debug
    // Menggunakan operator null coalescing (??) agar tidak error jika key tidak ada
    $isDebug = $tour_data['default']['hotSpotDebug'] ?? false;
    $critical_error = null;
}
?>