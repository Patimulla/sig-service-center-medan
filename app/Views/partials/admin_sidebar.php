<?php
$current = $current ?? 'dashboard';
$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
    ['key' => 'manage', 'label' => 'Kelola Service Center', 'url' => base_url('admin/kelola-service-center')],
    ['key' => 'add', 'label' => 'Tambah Lokasi', 'url' => base_url('admin/kelola-service-center#manageFormCard')],
    ['key' => 'brand', 'label' => 'Kelola Brand', 'url' => base_url('admin/kelola-brand')],
    ['key' => 'district', 'label' => 'Kelola Kecamatan', 'url' => base_url('admin/kelola-kecamatan')],
    ['key' => 'analysis', 'label' => 'Analisis GIS', 'url' => base_url('admin/analisis-spasial')],
    ['key' => 'query', 'label' => 'Spatial Query', 'url' => base_url('admin/analisis-spasial#spatialQuery')],
    ['key' => 'report', 'label' => 'Laporan', 'url' => base_url('admin/analisis-spasial#reporting')],
];

$iconMap = [
    'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4V4Zm9 0h7v4h-7V4ZM4 13h4v7H4v-7Zm6 0h10v7H10v-7Z" fill="currentColor"/></svg>',
    'manage' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-11Zm3 1.5v2h10V8H7Zm0 4v4h4v-4H7Zm6 0v4h4v-4h-4Z" fill="currentColor"/></svg>',
    'add' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z" fill="currentColor"/></svg>',
    'brand' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6.5A2.5 2.5 0 0 1 7.5 4h4A2.5 2.5 0 0 1 14 6.5v4A2.5 2.5 0 0 1 11.5 13h-4A2.5 2.5 0 0 1 5 10.5v-4Zm6.5 10L14 14l5.5 5.5-2.5 2.5-5.5-5.5Z" fill="currentColor"/></svg>',
    'district' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 6 5-2 6 2 5-2v14l-5 2-6-2-5 2V6Zm6 0v12l4 1.33V7.33L10 6Zm-4 1.44v10.89l2-.8V6.64l-2 .8Zm10-.11v10.89l2-.8V6.53l-2 .8Z" fill="currentColor"/></svg>',
    'analysis' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19V9h3v10H5Zm5 0V5h3v14h-3Zm5 0v-7h3v7h-3Z" fill="currentColor"/></svg>',
    'query' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h10v2H7V5Zm-2 4h14v2H5V9Zm2 4h10v2H7v-2Zm-2 4h14v2H5v-2Z" fill="currentColor"/></svg>',
    'report' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l5 5v13H6V3Zm8 1.5V9h4.5L14 4.5ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z" fill="currentColor"/></svg>',
    'site' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4a8 8 0 1 1 0 16 8 8 0 0 1 0-16Zm0 2a6 6 0 0 0-1 11.92V15H8.08A6 6 0 0 0 11 17.92V6.08A6.03 6.03 0 0 0 6.08 11H9v2H6.08A6.03 6.03 0 0 0 11 17.92V6.08c.32-.05.66-.08 1-.08Zm1 11.92A6.03 6.03 0 0 0 17.92 13H15v-2h2.92A6.03 6.03 0 0 0 13 6.08v11.84Z" fill="currentColor"/></svg>',
    'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-8v-2h8V6h-8V4Zm1.59 3.59L13 9l-2 2h7v2h-7l2 2-1.41 1.41L7.17 12l4.42-4.41Z" fill="currentColor"/></svg>',
];
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
        <img class="admin-sidebar__logo" src="<?= base_url('images/geosc-medan-logo.svg') ?>" alt="GeoSC Medan">
        <div>
            <h1 class="admin-sidebar__title">GeoSC Medan</h1>
        </div>
    </div>

    <nav class="admin-sidebar__nav" aria-label="Navigasi admin">
        <?php foreach ($menu as $item): ?>
            <a class="admin-nav-link <?= $current === $item['key'] ? 'is-active' : '' ?>" href="<?= $item['url'] ?>">
                <span class="admin-nav-link__icon"><?= $iconMap[$item['key']] ?? $iconMap['dashboard'] ?></span>
                <?= esc($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar__footer">
        <a class="admin-nav-link" href="<?= base_url('/') ?>">
            <span class="admin-nav-link__icon"><?= $iconMap['site'] ?></span>
            Lihat Situs
        </a>
        <a class="admin-nav-link is-danger" href="<?= base_url('admin/logout') ?>">
            <span class="admin-nav-link__icon"><?= $iconMap['logout'] ?></span>
            Logout
        </a>
    </div>
</aside>
