<?php
$creator = [
    'photo' => base_url('images/profil.jpeg'),
    'name' => 'Pati Mulla Sadra Siregar',
    'role' => 'Developer',
    'bio' => 'Mahasiswa yang merancang keseluruhan sistem website GeoSC Medan, mulai dari desain database, backend dengan CodeIgniter 4, hingga frontend dengan HTML, CSS, dan JavaScript.',
    'phone' => '081260273817',
    'facebook' => 'https://www.facebook.com/share/1EFvoqPMRm/',
    'instagram' => 'https://www.instagram.com/patimullasadra_?igsh=MW1ndGNraWNoNmo1cw==',
    'email' => 'patimullasadrasiregar@gmail.com',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Tentang - GeoSC Medan',
        'description' => 'Tentang GeoSC Medan, fitur utama sistem, manfaat bagi pengguna umum, serta profil dan kontak pembuat.',
    ]) ?>
</head>
<body>
<div class="app-shell">
    <?= view('partials/gis_nav', ['current' => 'about']) ?>

    <main class="page-shell">
        <section class="panel about-hero">
            <div class="about-hero__content">
                <div class="about-hero__copy">
                    <span class="pill">GeoSC Medan</span>
                    <h1 class="section-title">Pusat informasi service center smartphone resmi di Kota Medan.</h1>
                    <p class="section-copy">GeoSC Medan membantu pengguna menemukan lokasi service center, membandingkan pilihan terdekat, melihat informasi penting, dan menavigasi rute dengan lebih cepat.</p>
                    <div class="hero-actions hero-actions--centered">
                        <a class="btn btn--primary" href="<?= base_url('peta') ?>">Buka Peta</a>
                        <a class="btn btn--secondary" href="<?= base_url('cari-terdekat') ?>">Cari Terdekat</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section">
            <div class="about-section__header">
                <h2 class="section-title">Fitur Pengguna Umum</h2>
            </div>
            <div class="about-feature-grid">
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#128506;</div>
                    <h3>Peta Interaktif</h3>
                    <p>Peta menampilkan sebaran service center resmi dengan marker, popup informasi, dan tampilan area layanan yang lebih mudah dibaca.</p>
                </article>
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#128269;</div>
                    <h3>Pencarian dan Filter</h3>
                    <p>Pengguna dapat mencari berdasarkan nama atau alamat, lalu memfilter hasil menurut brand, kecamatan, rating, dan tipe lokasi.</p>
                </article>
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#128205;</div>
                    <h3>Lokasi Terdekat</h3>
                    <p>Sistem dapat memanfaatkan lokasi perangkat untuk menampilkan service center terdekat dan area radius pencarian yang dipilih.</p>
                </article>
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#11088;</div>
                    <h3>Detail Lokasi</h3>
                    <p>Setiap lokasi menampilkan foto, rating, jam operasional, nomor telepon, jenis layanan, dan tipe lokasi dalam panel detail yang ringkas.</p>
                </article>
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#128694;</div>
                    <h3>Rute Navigasi</h3>
                    <p>Pengguna bisa langsung melihat jalur menuju service center, estimasi jarak, dan waktu tempuh tanpa keluar dari peta.</p>
                </article>
                <article class="panel about-feature-card">
                    <div class="about-feature-card__icon">&#127919;</div>
                    <h3>Fokus Wilayah Medan</h3>
                    <p>Sistem difokuskan untuk Kota Medan agar informasi lokasi, filter kecamatan, dan tampilan area layanan tetap relevan bagi pengguna.</p>
                </article>
            </div>
        </section>

        <section class="about-section">
            <div class="about-section__header">
                <h2 class="section-title">Kegunaan Sistem</h2>
            </div>
            <div class="about-usage-grid">
                <article class="panel about-usage-card">
                    <h3>Untuk Mencari Lokasi Resmi</h3>
                    <p>Membantu pengguna menemukan service center yang sesuai dengan brand perangkat tanpa harus mencari dari banyak sumber yang terpisah.</p>
                </article>
                <article class="panel about-usage-card">
                    <h3>Untuk Membandingkan Pilihan</h3>
                    <p>Pengguna dapat melihat beberapa lokasi sekaligus, lalu memilih berdasarkan jarak, rating, alamat, atau jenis lokasi yang paling nyaman.</p>
                </article>
                <article class="panel about-usage-card">
                    <h3>Untuk Menentukan Rute Kunjungan</h3>
                    <p>Setelah memilih lokasi, sistem langsung membantu menampilkan rute dari posisi pengguna sehingga perjalanan lebih efisien.</p>
                </article>
            </div>
        </section>

        <section class="about-section">
            <div class="about-section__header">
                <h2 class="section-title">Profil Pembuat</h2>
            </div>
            <div class="about-profile panel">
                <div class="about-profile__media">
                    <img src="<?= esc($creator['photo']) ?>" alt="Foto profil pembuat">
                </div>
                <div class="about-profile__body">
                    <span class="pill">Profil</span>
                    <h3><?= esc($creator['name']) ?></h3>
                    <p class="about-profile__role"><?= esc($creator['role']) ?></p>
                    <p class="about-profile__bio"><?= esc($creator['bio']) ?></p>
                    <div class="about-contact-list">
                        <a class="about-contact-item" href="tel:<?= esc($creator['phone']) ?>">
                            <span class="about-contact-item__icon">&#128222;</span>
                            <span><?= esc($creator['phone']) ?></span>
                        </a>
                        <a class="about-contact-item" href="<?= esc($creator['facebook']) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="about-contact-item__icon">f</span>
                            <span>Facebook</span>
                        </a>
                        <a class="about-contact-item" href="<?= esc($creator['instagram']) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="about-contact-item__icon">&#9678;</span>
                            <span>Instagram</span>
                        </a>
                        <a class="about-contact-item" href="mailto:<?= esc($creator['email']) ?>">
                            <span class="about-contact-item__icon">&#9993;</span>
                            <span><?= esc($creator['email']) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/gis_footer') ?>
</div>
</body>
</html>
