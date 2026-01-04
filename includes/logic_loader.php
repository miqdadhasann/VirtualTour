<?php
// includes/logic_loader.php

// --- KONFIGURASI ---
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'virtual_tour';
$json_path = 'data/tour.json'; // Lokasi file JSON output

// FITUR: DEBUG MODE (Ubah ini jadi false jika sudah production)
$showDebugUI = true; 

// Variabel default
$critical_error = false;
$json_content = '{}';
$isDebug = $showDebugUI; // Variabel ini yang akan dibaca index.php

// 1. KONEKSI DATABASE
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // SKENARIO FALLBACK: Jika DB mati, coba baca dari file JSON terakhir
    if (file_exists($json_path)) {
        $json_content = file_get_contents($json_path);
        // Tetap set error, tapi konten jalan via file backup
        error_log("DB Down, loading from JSON cache.");
    } else {
        $critical_error = true;
        $json_content = '{}';
    }
} else {
    // 2. LOGIC: AMBIL DATA DARI DB
    $scenesData = [];
    $firstSceneSlug = "";
    
    $sqlScenes = "SELECT * FROM scenes ORDER BY id ASC";
    $resultScenes = $conn->query($sqlScenes);

    if ($resultScenes && $resultScenes->num_rows > 0) {
        while($row = $resultScenes->fetch_assoc()) {
            
            if ($firstSceneSlug === "") {
                $firstSceneSlug = $row['slug'];
            }

            $sceneId = $row['id'];
            $sceneSlug = $row['slug'];

            // Struktur Scene
            $scenesData[$sceneSlug] = [
                "title" => $row['title'],
                "type"  => "equirectangular",
                "panorama" => $row['image_path'],
                "pitch" => (float)$row['initial_pitch'],
                "yaw"   => (float)$row['initial_yaw'],
                "hotSpots" => []
            ];

            // Ambil Hotspot
            $sqlHotspots = "SELECT * FROM hotspots WHERE scene_id = $sceneId";
            $resultHotspots = $conn->query($sqlHotspots);

            while($hs = $resultHotspots->fetch_assoc()) {
                $scenesData[$sceneSlug]['hotSpots'][] = [
                    "pitch"   => (float)$hs['pitch'],
                    "yaw"     => (float)$hs['yaw'],
                    "type"    => "scene",
                    "text"    => $hs['text'],
                    "sceneId" => $hs['target_slug']
                ];
            }
        }

        // 3. RAKIT CONFIG & SINKRONISASI DEBUG
        $finalConfig = [
            "default" => [
                "firstScene" => $firstSceneSlug,
                "author" => "Virtual Tour Admin",
                "sceneFadeDuration" => 1000,
                "autoLoad" => true,
                "compass" => true,
                // Sinkronkan setting Pannellum dengan variabel PHP
                "hotSpotDebug" => $showDebugUI 
            ],
            "scenes" => $scenesData
        ];

        // 4. UPDATE FILE JSON (WRITE BACK)
        // Encode data DB ke format JSON
        $new_json_content = json_encode($finalConfig, JSON_PRETTY_PRINT);
        
        // Tulis ke file fisik (data/tour.json)
        // Pastikan folder 'data' memiliki permission write (chmod 777 atau 755)
        if (is_dir(dirname($json_path))) {
            file_put_contents($json_path, $new_json_content);
        }

        // Set konten untuk dikirim ke index.php
        $json_content = $new_json_content;

    } else {
        // Jika tabel kosong
        $json_content = json_encode(["default" => ["firstScene" => ""], "scenes" => []]);
    }
}
?>