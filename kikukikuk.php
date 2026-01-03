<?php
// Pastikan path file JSON benar (gunakan slash /)
$file_path = 'data/tour.json';

if (!file_exists($file_path)) {
    die("Error Kritis: File 'data/tour.json' tidak ditemukan di server.");
}

$json_data = file_get_contents($file_path);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour Kampus</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    
    <link rel="stylesheet" href="assets/styles/styles.css"> 
</head>
<body>

    <div class="controls">
        <h3>Kampus Virtual Tour</h3>
        <small>Gunakan mouse/touch untuk navigasi</small>
    </div>

    <div id="panorama"></div>

    <script>
        var tourConfig = <?php echo $json_data; ?>;
        
        pannellum.viewer('panorama', tourConfig);
    </script>

</body>
</html>