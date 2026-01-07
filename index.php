<?php
// index.php

// 1. Panggil Logic Loader (Otak)
require_once 'includes/logic_loader.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour <?php echo $isDebug ? '[DEBUG MODE]' : ''; ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <link rel="stylesheet" href="assets/styles/styles.css"/>
    
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #000; }
        #panorama { width: 100%; height: 100vh; }
    </style>
</head>
<body>

    <?php 
    // --- LOGIKA UI OVERLAY ---
    // Overlay HANYA dimuat jika $isDebug bernilai true (sesuai setting di logic_loader)
    if ($isDebug && file_exists('includes/ui_overlay.php')) {
        include 'includes/ui_overlay.php';
    }
    ?>

    <div id="panorama"></div>

    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script>
        // Data JSON dari PHP
        var tourData = <?php echo $critical_error ? '{}' : $json_content; ?>;

        if (!tourData.scenes || Object.keys(tourData.scenes).length === 0) {
            document.getElementById('panorama').innerHTML = "<div style='color:white;text-align:center;padding-top:45vh;font-family:sans-serif;'><h1>Data Kosong</h1><p>Silakan input data via dev.php</p></div>";
        } else {
            <?php if (!$critical_error): ?>
                var viewer = pannellum.viewer('panorama', tourData);
                
                // Tambahan: Log ke console jika mode debug
                <?php if($isDebug): ?>
                    console.log("🔥 Debug Mode ON");
                    console.log("📂 JSON Updated at: data/tour.json");
                    
                    // Listener klik untuk developer (membantu cari koordinat)
                    viewer.on('mousedown', function(event) {
                        console.log('Pitch:', viewer.getPitch(), 'Yaw:', viewer.getYaw());
                    });
                <?php endif; ?>

            <?php endif; ?>
        }
    </script>

</body>
</html>