<style>
    .overlay-box {
        position: absolute;
        top: 10px;
        left: 1200px;
        z-index: 10;
        padding: 15px;
        border-radius: 8px;
        max-width: 300px;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        font-family: sans-serif;
        margin-left: 20px;

    }

    /* Tampilan Mode Normal */
    .mode-normal {
        background: rgba(255, 255, 255, 0.9);
        border-left: 5px solid #007bff;
        color: #333;
    }

    /* Tampilan Mode Debug */
    .mode-debug {
        background: rgba(0, 0, 0, 0.85);
        color: #cc0000;
        border: 2px solid #ddd;
    }
    .mode-debug h2 { font-size: 14px; margin: 0 0 10px 0; text-transform: uppercase; border-bottom: 1px solid #333; padding-bottom: 5px; }
    .mode-debug p { font-size: 12px; margin-bottom: 5px; color: #ddd; }
    .mode-debug code { background: #333; padding: 2px 4px; border-radius: 3px; color: #fff; }
    .mode-error { background: #ffcccc; color: #cc0000; border: 2px solid red; }
</style>

<?php if ($critical_error): ?>
    <div class="overlay-box mode-error">
        <h3>System Error</h3>
        <p><?php echo $critical_error; ?></p>
    </div>

<?php elseif ($isDebug): ?>
    <div class="overlay-box mode-debug">
        <h2>⚠️ Hotspot Debug On</h2>
        <p>1. Buka Console (F12)</p>
        <p>2. Double Klik untuk cari titik hotspot</p>
        <p>3. Salin koordinat ke JSON (lokasi JSON: data\tour.json)</p>
        <p>4. Refresh halaman setelah perubahan</p>
        <p>NOTE: Matiin mode debug: Ke tour.json di <u>data\tour.json</u> abis itu cari <code>hotSpotDebug</code> ganti value nya jadi <b>false</b></p>
    </div>

<?php else: ?>
    <div class="overlay-box mode-normal">
        <h3 style="margin:0 0 5px 0; font-size:1.1rem;">Kampus Virtual Tour</h3>
        <p style="margin:0; font-size:0.9rem;">Geser layar untuk navigasi.</p>
    </div>

<?php endif; ?>