<?php
// includes/functions.php

function regenerateTourJson($conn) {
    // 1. Ambil Semua Scene
    $scenesData = [];
    $firstSceneSlug = "";
    
    $sqlScenes = "SELECT * FROM scenes ORDER BY id ASC";
    $resultScenes = $conn->query($sqlScenes);

    if ($resultScenes && $resultScenes->num_rows > 0) {
        while($row = $resultScenes->fetch_assoc()) {
            if ($firstSceneSlug === "") $firstSceneSlug = $row['slug'];
            
            $sceneSlug = $row['slug'];
            
            // Struktur Dasar Scene
            $scenesData[$sceneSlug] = [
                "title" => $row['title'],
                "type"  => "equirectangular",
                "panorama" => $row['image_path'],
                "pitch" => (float)$row['initial_pitch'],
                "yaw"   => (float)$row['initial_yaw'],
                "hotSpots" => []
            ];

            // Ambil Hotspot untuk Scene ini
            $sid = $row['id'];
            $sqlHotspots = "SELECT * FROM hotspots WHERE scene_id = $sid";
            $resHotspots = $conn->query($sqlHotspots);

            while($hs = $resHotspots->fetch_assoc()) {
                $hsType = isset($hs['type']) ? $hs['type'] : 'scene';
                $hotspotConfig = [
                    "pitch" => (float)$hs['pitch'],
                    "yaw"   => (float)$hs['yaw'],
                    "type"  => $hsType,
                    "text"  => $hs['text']
                ];
                if ($hsType === 'scene' && !empty($hs['target_slug'])) {
                    $hotspotConfig['sceneId'] = $hs['target_slug'];
                }
                $scenesData[$sceneSlug]['hotSpots'][] = $hotspotConfig;
            }
        }
    }

    // 2. Susun Konfigurasi Akhir
    $finalConfig = [
        "default" => [
            "firstScene" => $firstSceneSlug,
            "author" => "Virtual Tour Admin",
            "sceneFadeDuration" => 1000,
            "autoLoad" => true,
            "compass" => true,
            "hotSpotDebug" => false 
        ],
        "scenes" => $scenesData
    ];

    // 3. Tulis ke File
    if (!is_dir(dirname(JSON_PATH))) mkdir(dirname(JSON_PATH), 0777, true);
    
    $jsonString = json_encode($finalConfig, JSON_PRETTY_PRINT);
    return file_put_contents(JSON_PATH, $jsonString);
}
?>