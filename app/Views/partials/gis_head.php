<?php
$cssPath = FCPATH . 'css' . DIRECTORY_SEPARATOR . 'gis-app.css';
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= esc($description ?? 'Sistem Informasi Geografis service center smartphone di Kota Medan.') ?>">
<title><?= esc($title ?? 'GeoSC Medan') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= base_url('css/gis-app.css') ?>?v=<?= esc((string) $cssVersion) ?>">
