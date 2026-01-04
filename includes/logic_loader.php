<?php
// includes/logic_loader.php

// --- KONFIGURASI ---
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'virtual_tour';
$json_path = 'data/tour.json';

// FITUR: DEBUG MODE
$showDebugUI = true; 

$critical_error = false;
$json_content = '{}';
$isDebug = $showDebugUI; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if (file_exists($json_path)) {
        $json_content = file_get_contents($json_path);
        error_log("DB Down, loading from JSON cache.");
    } else {
        $critical_error = true;
        $json_content = '{}';
    }
} else {
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
                // Tentukan Tipe Hotspot (scene vs info)
                $hsType = isset($hs['type']) ? $hs['type'] : 'scene';
                
                $hotspotConfig = [
                    "pitch" => (float)$hs['pitch'],
                    "yaw"   => (float)$hs['yaw'],
                    "type"  => $hsType,
                    "text"  => $hs['text']
                ];

                // Hanya tambahkan sceneId (target) jika tipenya 'scene'
                if ($hsType === 'scene' && !empty($hs['target_slug'])) {
                    $hotspotConfig['sceneId'] = $hs['target_slug'];
                }

                $scenesData[$sceneSlug]['hotSpots'][] = $hotspotConfig;
            }
        }

        // Rakit Config
        $finalConfig = [
            "default" => [
                "firstScene" => $firstSceneSlug,
                "author" => "Virtual Tour Admin",
                "sceneFadeDuration" => 1000,
                "autoLoad" => true,
                "compass" => true,
                "hotSpotDebug" => $showDebugUI 
            ],
            "scenes" => $scenesData
        ];

        // Update JSON File
        $new_json_content = json_encode($finalConfig, JSON_PRETTY_PRINT);
        if (is_dir(dirname($json_path))) {
            file_put_contents($json_path, $new_json_content);
        }
        $json_content = $new_json_content;

    } else {
        $json_content = json_encode(["default" => ["firstScene" => ""], "scenes" => []]);
    }
}
?>