<?php
// dev.php - HIGH PRECISION UPDATE

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
    $yaw   = (float) $_POST['yaw']; 
    $pitch = (float) $_POST['pitch'];
    
    if (isset($_FILES['panorama_img']) && $_FILES['panorama_img']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['panorama_img']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $counter = 1;
            while (true) {
                $slugCandidate = "pano" . $counter;
                $cekDB = $conn->query("SELECT id FROM scenes WHERE slug = '$slugCandidate'");
                if ($cekDB->num_rows > 0) $counter++; else break;
            }
            
            $slug = "pano" . $counter;      
            $dest_path = $uploadDir . $slug . "." . $ext; 

            if(move_uploaded_file($_FILES['panorama_img']['tmp_name'], $dest_path)) {
                $sql = "INSERT INTO scenes (slug, title, image_path, initial_pitch, initial_yaw) 
                        VALUES ('$slug', '$judul', '$dest_path', $pitch, $yaw)";
                if ($conn->query($sql)) $message = "✅ Scene $slug tersimpan.";
                else $message = "❌ DB Error: " . $conn->error;
            } else {
                $message = "❌ Gagal upload.";
            }
        }
    }
}

// 2. TAMBAH HOTSPOT (NAVIGASI / INFO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_hotspot') {
    $sourceId = (int) $_POST['source_id'];
    $text     = $conn->real_escape_string($_POST['text']);
    $hYaw     = (float) $_POST['h_yaw'];
    $hPitch   = (float) $_POST['h_pitch'];
    $type     = $_POST['hotspot_type']; // scene atau info

    if ($type === 'scene') {
        $targetId = (int) $_POST['target_id'];
        $isMutual = isset($_POST['is_mutual']); 
        
        if ($sourceId && $targetId && $sourceId !== $targetId) {
            $resTarget = $conn->query("SELECT slug FROM scenes WHERE id=$targetId");
            $resSource = $conn->query("SELECT slug FROM scenes WHERE id=$sourceId");
            
            if ($resTarget->num_rows > 0) {
                $slugTarget = $resTarget->fetch_assoc()['slug'];
                $conn->query("INSERT INTO hotspots (scene_id, target_slug, pitch, yaw, text, type) 
                              VALUES ($sourceId, '$slugTarget', $hPitch, $hYaw, '$text', 'scene')");

                if ($isMutual && $resSource->num_rows > 0) {
                    $slugSource = $resSource->fetch_assoc()['slug'];
                    $returnYaw = ($hYaw + 180) > 360 ? ($hYaw + 180 - 360) : ($hYaw + 180);
                    $conn->query("INSERT INTO hotspots (scene_id, target_slug, pitch, yaw, text, type) 
                                  VALUES ($targetId, '$slugSource', " . ($hPitch * -1) . ", $returnYaw, 'Kembali', 'scene')");
                }
                $message = "✅ Hotspot Navigasi berhasil.";
            }
        } else {
            $message = "❌ Scene Asal dan Tujuan harus dipilih.";
        }
    } else {
        if ($sourceId) {
            $sql = "INSERT INTO hotspots (scene_id, target_slug, pitch, yaw, text, type) 
                    VALUES ($sourceId, NULL, $hPitch, $hYaw, '$text', 'info')";
            if($conn->query($sql)) $message = "✅ Info Hotspot berhasil.";
            else $message = "❌ Gagal: " . $conn->error;
        }
    }
}

// 3. UPDATE KOORDINAT SCENE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_scene_view') {
    $id = (int)$_POST['scene_id'];
    $yaw = (float)$_POST['new_yaw'];
    $pitch = (float)$_POST['new_pitch'];
    
    $conn->query("UPDATE scenes SET initial_yaw = $yaw, initial_pitch = $pitch WHERE id = $id");
    $message = "✅ Tampilan awal Scene diperbarui.";
}

// 4. UPDATE KOORDINAT HOTSPOT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_hotspot_pos') {
    $id = (int)$_POST['hotspot_id'];
    $yaw = (float)$_POST['new_h_yaw'];
    $pitch = (float)$_POST['new_h_pitch'];
    
    $conn->query("UPDATE hotspots SET yaw = $yaw, pitch = $pitch WHERE id = $id");
    $message = "✅ Posisi Hotspot diperbarui.";
}

// HAPUS DATA
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $res = $conn->query("SELECT image_path FROM scenes WHERE id=$id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (file_exists($row['image_path'])) unlink($row['image_path']);
    }
    $conn->query("DELETE FROM hotspots WHERE scene_id=$id");
    $conn->query("DELETE FROM scenes WHERE id=$id");
    header("Location: dev.php"); exit;
}
if (isset($_GET['del_hotspot_id'])) {
    $conn->query("DELETE FROM hotspots WHERE id=" . (int)$_GET['del_hotspot_id']);
    header("Location: dev.php"); exit;
}

// LOAD DATA
$scenes = [];
$qScene = $conn->query("SELECT * FROM scenes ORDER BY id ASC");
if ($qScene) {
    while ($row = $qScene->fetch_assoc()) {
        $hid = $row['id'];
        $qHotspot = $conn->query("SELECT * FROM hotspots WHERE scene_id=$hid");
        $row['hotspots'] = [];
        while ($h = $qHotspot->fetch_assoc()) { $row['hotspots'][] = $h; }
        $scenes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dev Mode: Precision Manager</title>
    <style>
        body { font-family: monospace; max-width: 1100px; margin: 20px auto; background: #222; color: #eee; }
        .box { background: #333; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #444; }
        h3 { color: #00ffaa; border-bottom: 1px solid #555; padding-bottom: 10px; }
        input, select { padding: 8px; background: #ddd; border: none; width: 100%; box-sizing: border-box;}
        .row { display: flex; gap: 20px; margin-bottom: 10px; } .col { flex: 1; }
        button { background: #27ae60; color: white; border: none; padding: 10px; font-weight: bold; cursor: pointer; width: 100%; }
        button.mini { width: auto; padding: 5px 10px; font-size: 11px; background: #007bff; }
        button.mini-del { width: auto; padding: 5px 10px; font-size: 11px; background: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #2a2a2a; }
        th, td { border: 1px solid #555; padding: 8px; text-align: left; vertical-align: top; }
        img.thumb { width: 80px; border: 1px solid #fff; }
        .alert { background: #f39c12; color: #000; padding: 10px; text-align: center; margin-bottom: 15px;}
        .hs-row { display: flex; align-items: center; gap: 5px; background: #1a1a1a; padding: 4px; margin-bottom: 2px; border-left: 3px solid #777; }
        .hs-scene { border-left-color: #00ffaa; }
        .hs-info { border-left-color: #00d2ff; }
        .input-mini { width: 70px; padding: 3px; font-size: 11px; text-align: center; }
        form.inline-form { display: inline-flex; gap: 5px; align-items: center; width: 100%; }
    </style>
    <script>
        function toggleTarget() {
            var type = document.getElementById('hs_type').value;
            var targetDiv = document.getElementById('target_scene_div');
            var mutualDiv = document.getElementById('mutual_div');
            if (type === 'info') {
                targetDiv.style.display = 'none';
                mutualDiv.style.display = 'none';
                document.getElementById('target_input').required = false;
            } else {
                targetDiv.style.display = 'block';
                mutualDiv.style.display = 'block';
                document.getElementById('target_input').required = true;
            }
        }
    </script>
</head>
<body>

<?php if ($message) echo "<div class='alert'>$message</div>"; ?>

<div class="box">
    <h3>1. Tambah Panorama Baru</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_scene">
        <div class="row">
            <div class="col"><label>Judul</label><input type="text" name="judul" required placeholder="Contoh: Taman"></div>
            <div class="col"><label>File</label><input type="file" name="panorama_img" required accept="image/*"></div>
        </div>
        <div class="row">
            <div class="col"><label>Start Yaw</label><input type="number" name="yaw" value="0" step="0.0001"></div>
            <div class="col"><label>Start Pitch</label><input type="number" name="pitch" value="0" step="0.0001"></div>
        </div>
        <button type="submit">UPLOAD SCENE</button>
    </form>
</div>

<div class="box" style="border-top: 4px solid #00d2ff;">
    <h3>2. Tambah Hotspot (Presisi Tinggi)</h3>
    <form method="POST">
        <input type="hidden" name="action" value="add_hotspot">
        
        <div class="row">
            <div class="col">
                <label>Lokasi Hotspot</label>
                <select name="source_id" required>
                    <?php foreach($scenes as $s): echo "<option value='{$s['id']}'>{$s['title']}</option>"; endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label>Tipe</label>
                <select name="hotspot_type" id="hs_type" onchange="toggleTarget()">
                    <option value="scene">Navigasi (Pindah Scene)</option>
                    <option value="info">Info Saja (Teks)</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col" id="target_scene_div">
                <label>Target Scene</label>
                <select name="target_id" id="target_input">
                    <option value="">-- Pilih --</option>
                    <?php foreach($scenes as $s): echo "<option value='{$s['id']}'>{$s['title']}</option>"; endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label>Label</label>
                <input type="text" name="text" placeholder="Ketik label..." required>
            </div>
        </div>

        <div class="row">
            <div class="col"><label>Posisi Yaw</label><input type="number" name="h_yaw" value="0" step="0.0001"></div>
            <div class="col"><label>Posisi Pitch</label><input type="number" name="h_pitch" value="0" step="0.0001"></div>
        </div>

        <div id="mutual_div" style="margin-bottom:10px;">
            <label><input type="checkbox" name="is_mutual" value="1" checked style="width:auto;"> Buat Link Balik Otomatis?</label>
        </div>

        <button type="submit" style="background:#00d2ff; color:black;">TAMBAHKAN HOTSPOT</button>
    </form>
</div>

<div class="box">
    <h3>Data & Edit Koordinat (High Precision)</h3>
    <?php if (empty($scenes)): ?>
        <p>Belum ada data.</p>
    <?php else: ?>
        <table>
            <tr><th width="15%">Preview</th><th width="35%">Scene Default View</th><th>Hotspots List</th><th width="5%">Aksi</th></tr>
            <?php foreach ($scenes as $s): ?>
            <tr>
                <td>
                    <img src="<?php echo $s['image_path']; ?>" class="thumb"><br>
                    <small style="color:#00ffaa"><?php echo $s['slug']; ?></small>
                </td>
                <td>
                    <b><?php echo $s['title']; ?></b><br>
                    
                    <form method="POST" class="inline-form" style="margin-top:5px; background:#444; padding:5px; border-radius:4px;">
                        <input type="hidden" name="action" value="update_scene_view">
                        <input type="hidden" name="scene_id" value="<?php echo $s['id']; ?>">
                        
                        <div>
                            <small>Y:</small>
                            <input type="number" name="new_yaw" value="<?php echo $s['initial_yaw']; ?>" step="0.0001" class="input-mini">
                        </div>
                        <div>
                            <small>P:</small>
                            <input type="number" name="new_pitch" value="<?php echo $s['initial_pitch']; ?>" step="0.0001" class="input-mini">
                        </div>
                        <button type="submit" class="mini">Update</button>
                    </form>
                </td>
                <td>
                    <?php foreach($s['hotspots'] as $h): ?>
                        <?php $isInfo = ($h['type'] == 'info'); ?>
                        <div class="hs-row <?php echo $isInfo ? 'hs-info' : 'hs-scene'; ?>">
                            <div style="flex:1">
                                <span style="font-weight:bold; font-size:12px; color:<?php echo $isInfo?'#00d2ff':'#00ffaa';?>">
                                    [<?php echo strtoupper($h['type']); ?>]
                                </span> 
                                <?php echo $h['text']; ?>
                            </div>
                            
                            <form method="POST" class="inline-form" style="width:auto;">
                                <input type="hidden" name="action" value="update_hotspot_pos">
                                <input type="hidden" name="hotspot_id" value="<?php echo $h['id']; ?>">
                                <input type="number" name="new_h_yaw" value="<?php echo $h['yaw']; ?>" step="0.0001" class="input-mini" placeholder="Y">
                                <input type="number" name="new_h_pitch" value="<?php echo $h['pitch']; ?>" step="0.0001" class="input-mini" placeholder="P">
                                <button type="submit" class="mini" title="Simpan">Save</button>
                            </form>

                            <a href="?del_hotspot_id=<?php echo $h['id']; ?>" class="del" onclick="return confirm('Hapus?')">
                                <button class="mini-del">X</button>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </td>
                <td><a href="?delete_id=<?php echo $s['id']; ?>" onclick="return confirm('Hapus Scene?')"><button class="mini-del">DEL</button></a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>