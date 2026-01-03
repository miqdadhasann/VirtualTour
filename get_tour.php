<?php
header('Content-Type: application/json');

// 1. Koneksi Database (Sesuaikan user/pass Anda)
$host = 'localhost';
$db   = 'virtual_tour';
$user = 'root';
$pass = ''; // Isi password mysql jika ada
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
    echo json_encode(["error" => "Koneksi Gagal: " . $e->getMessage()]);
    exit;
}

// 2. Struktur Dasar Pannellum
$output = [
    "default" => [
        "firstScene" => "depan", // Bisa dibuat dinamis nanti
        "sceneFadeDuration" => 1000,
        "autoLoad" => true
    ],
    "scenes" => []
];

// 3. Ambil semua Scenes
$stmt = $pdo->query("SELECT * FROM scenes");
while ($scene = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    // Siapkan wadah scene
    $sceneSlug = $scene['slug'];
    $output['scenes'][$sceneSlug] = [
        "title" => $scene['title'],
        "type" => "equirectangular",
        "panorama" => $scene['image_path'],
        "pitch" => (float)$scene['initial_pitch'],
        "yaw" => (float)$scene['initial_yaw'],
        "hotspots" => []
    ];

    // 4. Ambil Hotspot untuk scene ini
    // (Cara ini tidak efisien untuk ribuan data, tapi oke untuk <100 scene)
    $stmtHotspot = $pdo->prepare("SELECT * FROM hotspots WHERE scene_id = ?");
    $stmtHotspot->execute([$scene['id']]);
    
    while ($hs = $stmtHotspot->fetch(PDO::FETCH_ASSOC)) {
        $output['scenes'][$sceneSlug]['hotspots'][] = [
            "pitch" => (float)$hs['pitch'],
            "yaw" => (float)$hs['yaw'],
            "type" => "scene",
            "text" => $hs['text'],
            "sceneId" => $hs['target_slug']
        ];
    }
}

// 5. Output JSON
echo json_encode($output, JSON_PRETTY_PRINT);