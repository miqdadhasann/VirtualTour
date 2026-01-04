<?php
// dev.php - REVISI FIX COLUMN NAME

// --- KONEKSI DATABASE ---
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'virtual_tour';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Gagal: " . $conn->connect_error); }

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$message = "";

// --- LOGIC HANDLING ---

// 1. TAMBAH SCENE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_scene') {
    $judul = $conn->real_escape_string($_POST['judul']);
    // Perhatikan nama variabel disesuaikan dengan input form
    $yaw   = (float) $_POST['yaw']; 
    $pitch = (float) $_POST['pitch'];
    
    if (isset($_FILES['panorama_img']) && $_FILES['panorama_img']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['panorama_img']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            
            // Auto-Naming Slug (pano1, pano2...)
            $counter = 1;
            while (true) {
                $slugCandidate = "pano" . $counter;
                $cekDB = $conn->query("SELECT id FROM scenes WHERE slug = '$slugCandidate'");
                if ($cekDB->num_rows > 0) {
                    $counter++;
                } else {
                    break;
                }
            }
            
            $slug = "pano" . $counter;      
            $newFileName = $slug . "." . $ext; 
            $dest_path = $uploadDir . $newFileName;

            if(move_uploaded_file($_FILES['panorama_img']['tmp_name'], $dest_path)) {
                // INSERT MENGGUNAKAN NAMA KOLOM YANG BENAR (initial_pitch, initial_yaw)
                $sql = "INSERT INTO scenes (slug, title, image_path, initial_pitch, initial_yaw) 
                        VALUES ('$slug', '$judul', '$dest_path', $pitch, $yaw)";
                
                if ($conn->query($sql)) {
                    $message = "✅ Scene <b>$slug</b> tersimpan.";
                } else {
                    $message = "❌ DB Error: " . $conn->error;
                }
            } else {
                $message = "❌ Gagal upload file.";
            }
        }
    }
}

// 2. TAMBAH HOTSPOT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_hotspot') {
    $sourceId = (int) $_POST['source_id'];
    $targetId = (int) $_POST['target_id'];
    $text     = $conn->real_escape_string($_POST['text']);
    $hYaw     = (float) $_POST['h_yaw'];
    $hPitch   = (float) $_POST['h_pitch'];
    $isMutual = isset($_POST['is_mutual']); 

    if ($sourceId && $targetId && $sourceId !== $targetId) {
        // Ambil Slug untuk target_slug
        $resTarget = $conn->query("SELECT slug FROM scenes WHERE id=$targetId");
        $resSource = $conn->query("SELECT slug FROM scenes WHERE id=$sourceId"); // Untuk return point
        
        if ($resTarget->num_rows > 0) {
            $slugTarget = $resTarget->fetch_assoc()['slug'];
            
            // Insert Link Maju
            $conn->query("INSERT INTO hotspots (scene_id, target_slug, pitch, yaw, text) 
                          VALUES ($sourceId, '$slugTarget', $hPitch, $hYaw, '$text')");

            // Insert Link Balik (Return Point)
            if ($isMutual && $resSource->num_rows > 0) {
                $slugSource = $resSource->fetch_assoc()['slug'];
                $returnYaw = ($hYaw + 180) > 360 ? ($hYaw + 180 - 360) : ($hYaw + 180);
                $returnText = "Kembali";
                
                $conn->query("INSERT INTO hotspots (scene_id, target_slug, pitch, yaw, text) 
                              VALUES ($targetId, '$slugSource', " . ($hPitch * -1) . ", $returnYaw, '$returnText')");
            }
            $message = "✅ Hotspot berhasil.";
        }
    }
}

// 3. HAPUS DATA
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $res = $conn->query("SELECT image_path FROM scenes WHERE id=$id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (file_exists($row['image_path'])) unlink($row['image_path']);
    }
    $conn->query("DELETE FROM hotspots WHERE scene_id=$id");
    $conn->query("DELETE FROM scenes WHERE id=$id");
    header("Location: dev.php");
    exit;
}

if (isset($_GET['del_hotspot_id'])) {
    $conn->query("DELETE FROM hotspots WHERE id=" . (int)$_GET['del_hotspot_id']);
    header("Location: dev.php");
    exit;
}

// --- LOAD DATA ---
$scenes = [];
$qScene = $conn->query("SELECT * FROM scenes ORDER BY id ASC");
while ($row = $qScene->fetch_assoc()) {
    // Ambil hotspots
    $hid = $row['id'];
    $qHotspot = $conn->query("SELECT * FROM hotspots WHERE scene_id=$hid");
    $row['hotspots'] = [];
    while ($h = $qHotspot->fetch_assoc()) {
        $row['hotspots'][] = $h;
    }
    $scenes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dev: DB Manager</title>
    <style>
        body { font-family: sans-serif; background: #222; color: #fff; padding: 20px; }
        .box { background: #333; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        input, select { padding: 8px; width: 100%; margin-bottom: 10px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #444; }
        th, td { border: 1px solid #555; padding: 8px; text-align: left; vertical-align: top; }
        img.thumb { width: 100px; height: auto; display: block; }
        a.del { color: #ff5555; text-decoration: none; font-weight: bold; cursor: pointer; }
        .alert { background: gold; color: black; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

<?php if ($message) echo "<div class='alert'>$message</div>"; ?>

<div class="box">
    <h3>1. Tambah Panorama</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_scene">
        <label>Judul</label><input type="text" name="judul" required placeholder="Lobby">
        <label>File Gambar</label><input type="file" name="panorama_img" required>
        <div style="display:flex; gap:10px;">
            <div><label>Yaw (0-360)</label><input type="number" name="yaw" value="0"></div>
            <div><label>Pitch (-90 s/d 90)</label><input type="number" name="pitch" value="0"></div>
        </div>
        <button type="submit">SIMPAN SCENE</button>
    </form>
</div>

<div class="box">
    <h3>2. Tambah Link (Hotspot)</h3>
    <form method="POST">
        <input type="hidden" name="action" value="add_hotspot">
        <div style="display:flex; gap:10px;">
            <div style="flex:1">
                <label>Dari</label>
                <select name="source_id">
                    <?php foreach($scenes as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1">
                <label>Ke</label>
                <select name="target_id">
                    <?php foreach($scenes as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <label>Teks</label><input type="text" name="text" placeholder="Masuk ke...">
        <div style="display:flex; gap:10px;">
            <div><label>Yaw Link</label><input type="number" name="h_yaw" value="0"></div>
            <div><label>Pitch Link</label><input type="number" name="h_pitch" value="0"></div>
        </div>
        <label><input type="checkbox" name="is_mutual" checked style="width:auto"> Link Bolak-balik?</label>
        <button type="submit">SIMPAN HOTSPOT</button>
    </form>
</div>

<div class="box">
    <h3>Data Database</h3>
    <table>
        <tr>
            <th>Gambar</th>
            <th>Info</th>
            <th>Link (Hotspots)</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($scenes as $s): ?>
        <tr>
            <td>
                <?php if(file_exists($s['image_path'])): ?>
                    <img src="<?php echo $s['image_path']; ?>" class="thumb">
                <?php else: ?>
                    <span style="color:red">File Hilang</span>
                <?php endif; ?>
                <small><?php echo $s['slug']; ?></small>
            </td>
            <td>
                <b><?php echo $s['title']; ?></b><br>
                Start Yaw: <?php echo $s['initial_yaw']; ?><br>
                Start Pitch: <?php echo $s['initial_pitch']; ?>
            </td>
            <td>
                <?php foreach($s['hotspots'] as $h): ?>
                    <div style="background:#222; padding:3px; margin:2px;">
                        To: <?php echo $h['target_slug']; ?>
                        <a href="?del_hotspot_id=<?php echo $h['id']; ?>" class="del">x</a>
                    </div>
                <?php endforeach; ?>
            </td>
            <td><a href="?delete_id=<?php echo $s['id']; ?>" class="del" onclick="return confirm('Hapus?')">Hapus</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>