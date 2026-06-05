<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Beranda - GeoSC Medan',
        'description' => 'Temukan service center resmi smartphone di Kota Medan dengan peta interaktif, pencarian brand, dan rute terdekat.',
    ]) ?>
</head>
<body>
<div class="app-shell">
    <?= view('partials/gis_nav', ['current' => 'home']) ?>

    <main class="page-shell">
        <section class="hero-grid">
            <div class="panel hero-visual hero-visual--landing">
                <div class="hero-copy hero-copy--centered">
                    <h1 class="section-title">Temukan service center resmi smartphone di Kota Medan dengan cepat dan akurat.</h1>

                    <div class="hero-actions hero-actions--centered">
                        <a class="btn btn--primary" href="<?= base_url('peta') ?>">
                            <span aria-hidden="true">&#128506;</span>
                            <span>Buka Peta Interaktif</span>
                        </a>
                        <a class="btn btn--secondary" href="<?= base_url('cari-terdekat') ?>">
                            <span aria-hidden="true">&#128269;</span>
                            <span>Cari Lokasi Terdekat</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats-grid" style="margin-top: 26px;">
            <article class="panel stat-card">
                <div class="stat-card__eyebrow">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="12" cy="10" r="2.8" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <p class="stat-card__value" id="statTotal">0</p>
                <p class="stat-card__label">Lokasi Tercatat</p>
            </article>

            <article class="panel stat-card">
                <div class="stat-card__eyebrow">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 3h8M8 21h8M6 7h12M6 17h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="stat-card__value" id="statBrands">0</p>
                <p class="stat-card__label">Brand Tersedia</p>
            </article>

            <article class="panel stat-card">
                <div class="stat-card__eyebrow">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7h16M7 4v16M17 4v16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="stat-card__value" id="statDistricts">0</p>
                <p class="stat-card__label">Kecamatan Terjangkau</p>
            </article>
        </section>

        <section class="panel brand-strip" id="brand-filter">
            <div class="brand-strip__header">
                <div>
                    <h2 class="brand-strip__title">Brand</h2>
                </div>
            </div>
            <div class="chip-list" id="brandStrip"></div>
        </section>

        <section style="margin-top: 42px;">
            <div class="explore-grid">
                <section class="panel map-panel">
                    <div class="map-panel__header">
                        <h3 class="map-panel__title">Peta</h3>
                        <a class="btn btn--ghost" href="<?= base_url('peta') ?>">Lihat peta penuh</a>
                    </div>
                    <div id="exploreMap" class="map-canvas"></div>
                </section>

                <aside class="panel results-column">
                    <div class="results-column__header">
                        <h3 class="results-column__title">Unggulan</h3>
                        <span class="pill" id="featuredHint">Memuat data...</span>
                    </div>
                    <div class="results-stack" id="featuredResults">
                        <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                        <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                        <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <?= view('partials/gis_footer') ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const config = {
        apiBase: <?= json_encode(base_url('api/service-center')) ?>,
        mapPage: <?= json_encode(base_url('peta')) ?>,
    };

    const exploreMap = GISApp.createMap('exploreMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 12,
        scrollWheelZoom: false,
    });

    const exploreLayer = L.layerGroup().addTo(exploreMap);

    function renderBrandStrip(data) {
        const brands = [...new Set(data.map((item) => item.brand).filter(Boolean))].sort((a, b) => a.localeCompare(b));
        const container = document.getElementById('brandStrip');
        container.innerHTML = brands.map((brand) => `
            <a class="chip-link" href="${config.mapPage}?brand=${encodeURIComponent(brand)}">
                <span class="brand-dot" style="background:${GISApp.brandColor(brand)}"></span>
                ${GISApp.escapeHtml(brand)}
            </a>
        `).join('');
    }

    function renderFeatured(data) {
        const featured = [...data]
            .sort((a, b) => {
                const ratingDiff = parseFloat(b.rating || 0) - parseFloat(a.rating || 0);
                if (ratingDiff !== 0) {
                    return ratingDiff;
                }

                return a.nama_tempat.localeCompare(b.nama_tempat);
            })
            .slice(0, 4);

        const container = document.getElementById('featuredResults');
        const hint = document.getElementById('featuredHint');
        hint.textContent = `${featured.length} lokasi ditampilkan`;

        container.innerHTML = featured.map((item) => `
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
                        <span class="info-item__text">${GISApp.escapeHtml(item.kecamatan || 'Medan')} · ${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item__icon" aria-hidden="true">&#128337;</span>
                        <span class="info-item__text">${GISApp.escapeHtml(GISApp.formatTime(item.jam_buka))} - ${GISApp.escapeHtml(GISApp.formatTime(item.jam_tutup))}</span>
                    </div>
                </div>
                <div class="tile-actions">
                    <a class="mini-button is-primary" href="${config.mapPage}?focus=${encodeURIComponent(item.id)}">Detail</a>
                    <a class="mini-button" href="${config.mapPage}?route=${encodeURIComponent(item.id)}">Rute</a>
                </div>
            </article>
        `).join('');
    }

    function popupRenderer(item) {
        return `
            <div class="map-popup">
                <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                <h3 class="map-popup__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                <div class="map-popup__meta">
                    <span>${GISApp.escapeHtml(item.alamat || '-')}</span>
                    <span>${GISApp.escapeHtml(item.kecamatan || 'Medan')} · Rating ${GISApp.formatRating(item.rating)}</span>
                </div>
                <div class="map-popup__actions">
                    <a class="mini-button is-primary" href="${config.mapPage}?focus=${encodeURIComponent(item.id)}">Detail</a>
                    <a class="mini-button" href="${config.mapPage}?route=${encodeURIComponent(item.id)}">Rute</a>
                </div>
            </div>
        `;
    }

    async function init() {
        try {
            const response = await GISApp.fetchJson(config.apiBase);
            const data = response.data || [];
            const districtCount = new Set(data.map((item) => item.kecamatan).filter(Boolean)).size;
            const brandCount = new Set(data.map((item) => item.brand).filter(Boolean)).size;

            document.getElementById('statTotal').textContent = data.length;
            document.getElementById('statBrands').textContent = brandCount;
            document.getElementById('statDistricts').textContent = districtCount;
            renderBrandStrip(data);
            renderFeatured(data);
            GISApp.renderMarkers(exploreLayer, data, config.mapPage, {
                popupRenderer,
                popupMaxWidth: 260,
            });
            GISApp.fitBounds(exploreMap, data, [30, 30], 13);
        } catch (error) {
            document.getElementById('featuredResults').innerHTML = '<div class="empty-state">Gagal memuat data service center. Silakan coba lagi.</div>';
            console.error(error);
        }
    }

    init();
})();
</script>
</body>
</html>
