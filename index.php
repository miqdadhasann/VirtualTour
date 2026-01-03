<?php
// 1. Panggil Logic Loader (Otak)
require_once 'includes/logic_loader.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour <?php echo $isDebug ? '[DEBUG]' : ''; ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <link rel="stylesheet" href="assets/styles/styles.css"/>

</head>
<body>

    <?php include 'includes/ui_overlay.php'; ?>

    <div id="panorama"></div>

    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script>
        // Data JSON disuntikkan dari PHP (logic_loader.php)
        // Jika ada critical error, kita render objek kosong agar JS tidak crash total
        var tourData = <?php echo $critical_error ? '{}' : $json_content; ?>;

        <?php if (!$critical_error): ?>
            pannellum.viewer('panorama', tourData);
        <?php endif; ?>
    </script>

</body>
</html>