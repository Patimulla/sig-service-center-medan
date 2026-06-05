<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Admin Dashboard - GeoSC Medan',
        'description' => 'Dashboard admin untuk memantau data service center resmi yang tersimpan di Supabase.',
    ]) ?>
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= view('partials/admin_sidebar', ['current' => 'dashboard']) ?>

    <main class="admin-main">
        <div class="admin-page">
            <section class="panel admin-hero">
                <h1 class="section-title" style="font-size: clamp(1.6rem, 2.4vw, 2.7rem);">Dashboard Service Center</h1>
                <div class="hero-actions">
                    <a class="btn btn--primary" href="<?= base_url('admin/kelola-service-center') ?>">Kelola Data</a>
                    <a class="btn btn--secondary" href="<?= base_url('admin/analisis-spasial') ?>">Buka Analisis GIS</a>
                </div>
            </section>

            <section class="admin-summary-grid" id="adminStats">
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Total lokasi</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Brand aktif</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Kecamatan</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Lokasi unggulan</p></article>
            </section>

            <section class="admin-grid">
                <article class="panel info-card">
                    <h3>Distribusi Brand</h3>
                    <table class="admin-table" id="brandTable">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2">Memuat data brand...</td></tr>
                        </tbody>
                    </table>
                </article>

                <article class="panel info-card">
                    <h3>Lokasi Teratas</h3>
                    <div class="card-stack dashboard-highlights" id="adminHighlights">
                        <div class="empty-state">Sedang memuat lokasi unggulan.</div>
                    </div>
                </article>

                <article class="panel info-card">
                    <h3>Aksi Cepat</h3>
                    <div class="card-stack" id="quick-actions">
                        <a class="mini-button is-primary" href="<?= base_url('admin/kelola-service-center') ?>">Kelola Service Center</a>
                        <a class="mini-button" href="<?= base_url('admin/analisis-spasial') ?>">Jalankan Analisis</a>
                        <a class="mini-button" href="<?= base_url('peta') ?>">Review Peta Publik</a>
                    </div>
                </article>
            </section>
        </div>
    </main>
</div>

<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const apiBase = <?= json_encode(base_url('api/service-center')) ?>;
    const mapPage = <?= json_encode(base_url('peta')) ?>;

    async function init() {
        try {
            const response = await GISApp.fetchJson(apiBase);
            const data = response.data || [];
            const byBrand = data.reduce((accumulator, item) => {
                accumulator[item.brand] = (accumulator[item.brand] || 0) + 1;
                return accumulator;
            }, {});
            const districts = new Set(data.map((item) => item.kecamatan).filter(Boolean)).size;

            document.getElementById('adminStats').innerHTML = `
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">${data.length}</p><p class="admin-summary-card__label">Total lokasi</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">${Object.keys(byBrand).length}</p><p class="admin-summary-card__label">Brand aktif</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">${districts}</p><p class="admin-summary-card__label">Kecamatan</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">${Math.min(4, data.length)}</p><p class="admin-summary-card__label">Lokasi unggulan</p></article>
            `;

            document.querySelector('#brandTable tbody').innerHTML = Object.entries(byBrand)
                .sort((a, b) => b[1] - a[1])
                .map(([brand, total]) => `
                    <tr>
                        <td><span class="brand-badge" style="background:${GISApp.brandColor(brand)}">${GISApp.escapeHtml(brand)}</span></td>
                        <td>${total}</td>
                    </tr>
                `)
                .join('');

            const featured = [...data]
                .sort((a, b) => {
                    const ratingDiff = parseFloat(b.rating || 0) - parseFloat(a.rating || 0);
                    if (ratingDiff !== 0) {
                        return ratingDiff;
                    }

                    return a.nama_tempat.localeCompare(b.nama_tempat);
                })
                .slice(0, 4);

            document.getElementById('adminHighlights').innerHTML = featured
                .map((item) => `
                    <article class="service-tile">
                        <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                        <h3 class="service-tile__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                        <div class="service-tile__meta">
                            <div class="info-item">
                                <span class="info-item__icon" aria-hidden="true">&#128205;</span>
                                <span class="info-item__text">${GISApp.escapeHtml(item.alamat || '-')}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-item__icon" aria-hidden="true">&#9733;</span>
                                <span class="info-item__text">Rating ${GISApp.formatRating(item.rating)}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-item__icon" aria-hidden="true">&#127970;</span>
                                <span class="info-item__text">${GISApp.escapeHtml(item.kecamatan || 'Medan')} &middot; ${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-item__icon" aria-hidden="true">&#128337;</span>
                                <span class="info-item__text">${GISApp.escapeHtml(GISApp.formatTime(item.jam_buka))} - ${GISApp.escapeHtml(GISApp.formatTime(item.jam_tutup))}</span>
                            </div>
                        </div>
                        <div class="tile-actions">
                            <a class="mini-button is-primary" href="${mapPage}?focus=${encodeURIComponent(item.id)}">Detail</a>
                            <a class="mini-button" href="${mapPage}?route=${encodeURIComponent(item.id)}">Rute</a>
                        </div>
                    </article>
                `)
                .join('');
        } catch (error) {
            console.error(error);
            document.getElementById('adminHighlights').innerHTML = '<div class="empty-state">Gagal memuat dashboard admin.</div>';
        }
    }

    init();
})();
</script>
</body>
</html>
