<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour Debug Mode</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    
    <style>
        body, html { height: 100%; margin: 0; padding: 0; font-family: Arial, sans-serif; overflow: hidden; }
        #panorama { width: 100%; height: 100%; }
        .controls {
            position: absolute; top: 10px; left: 10px; z-index: 10;
            background: rgba(255, 0, 0, 0.8); /* Merah agar mencolok */
            color: white; padding: 10px; border-radius: 5px;
        }
    </style>
</head>
<body>

    <div class="controls">
        <h3>MODE DEBUG AKTIF</h3>
        <small>1. Buka Console (F12 -> Console)<br>2. Klik objek di layar<br>3. Lihat koordinat Pitch/Yaw muncul!</small>
    </div>

    <div id="panorama"></div>

    <script>
        var tourConfig = {
            "default": {
                "firstScene": "halaman_depan",
                "author": "Kampus Virtual",
                "sceneFadeDuration": 1000,
                "autoLoad": true,
                
                // FITUR SAKTI: Ini akan mencetak koordinat saat diklik
                "hotSpotDebug": true 
            },
            "scenes": {
                "halaman_depan": {
                    "title": "Halaman Depan Kampus",
                    "type": "equirectangular",
                    "panorama": "assets/img/pano1.jpg", 
                    "hotspots": [
                        // HOTSPOT 1: Titik Test (Harus Muncul)
                        {
                            "pitch": 0,
                            "yaw": 0,
                            "type": "info",
                            "text": "SAYA DI TENGAH LAYAR!"
                        },
                        // HOTSPOT 2: Titik Lobby (Saya koreksi perkiraan posisinya ke tanah)
                        {
                            "pitch": -30, 
                            "yaw": 132.9,
                            "type": "scene",
                            "text": "Masuk ke Lobby (Perkiraan)",
                            "sceneId": "lobby_utama"
                        }
                    ]
                },
                "lobby_utama": {
                    "title": "Lobby Utama",
                    "type": "equirectangular",
                    "panorama": "assets/img/pano2.jpg",
                    "hotspots": [
                        {
                            "pitch": 0,
                            "yaw": -50,
                            "type": "scene",
                            "text": "Keluar ke Halaman",
                            "sceneId": "halaman_depan"
                        }
                    ]
                }
            }
        };

        pannellum.viewer('panorama', tourConfig);
    </script>

</body>
</html>