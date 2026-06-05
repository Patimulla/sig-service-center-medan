<?php
$current = $current ?? 'home';
$searchValue = $searchValue ?? '';

$navItems = [
    'home'    => ['label' => 'Beranda', 'url' => base_url('/')],
    'map'     => ['label' => 'Peta Service Center', 'url' => base_url('peta')],
    'nearest' => ['label' => 'Cari Terdekat', 'url' => base_url('cari-terdekat')],
    'about'   => ['label' => 'Tentang', 'url' => base_url('tentang')],
];
?>
<header class="topbar">
    <div class="topbar-shell">
        <a class="brand-lockup" href="<?= base_url('/') ?>">
            <img class="brand-lockup__logo" src="<?= base_url('images/geosc-medan-logo.svg') ?>" alt="GeoSC Medan">
            <span class="brand-lockup__text">
                <strong class="brand-lockup__title">GeoSC Medan</strong>
            </span>
        </a>

        <nav class="nav-links" aria-label="Navigasi utama">
            <?php foreach ($navItems as $key => $item): ?>
                <a class="nav-link <?= $current === $key ? 'is-active' : '' ?>" href="<?= $item['url'] ?>">
                    <?= esc($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <form class="nav-search" action="<?= base_url('peta') ?>" method="get">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.4-3.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <input type="search" name="search" placeholder="Cari alamat atau nama service center..." value="<?= esc($searchValue) ?>">
        </form>
        <button class="nav-toggle" type="button" aria-label="Buka navigasi" data-nav-toggle><span></span></button>
    </div>
</header>
