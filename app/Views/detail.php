<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Detail Service Center - GeoSC Medan',
        'description' => 'Lihat detail lengkap service center, foto lokasi, rating, jam operasional, tipe lokasi, dan rute dari posisi Anda.',
    ]) ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css">
</head>
<body data-service-center-id="<?= esc($serviceCenterId) ?>">
<div class="app-shell">
    <?= view('partials/gis_nav', ['current' => 'map']) ?>

    <main class="page-shell">
        <div class="detail-shell">
            <section class="panel detail-hero">
                <div class="detail-hero__content">
                    <div class="detail-hero__copy" id="detailHeroCopy">
                        <span class="pill"><a href="<?= base_url('peta') ?>">&larr; Kembali ke peta</a></span>
                        <h1 class="section-title">Menyiapkan data service center...</h1>
                    </div>
                    <div class="detail-hero__visual detail-photo-frame" id="detailPhotoFrame">
                        <div class="detail-photo-frame__placeholder">Foto lokasi service center</div>
                    </div>
                </div>
            </section>

            <section class="detail-grid">
                <div class="card-stack">
                    <article class="panel info-card" id="operationalCard">
                        <h3>Jam Operasional</h3>
                        <div class="hours-list">
                            <div class="hours-row"><span>Memuat jadwal...</span><strong>-</strong></div>
                        </div>
                    </article>

                    <article class="panel info-card" id="servicesCard">
                        <h3>Layanan Tersedia</h3>
                        <div class="feature-list">
                            <span class="feature-pill">Memuat data layanan...</span>
                        </div>
                    </article>
                </div>

                <div class="card-stack">
                    <article class="panel info-card">
                        <h3>Lokasi & Rute</h3>
                        <div id="detailMap" class="mini-map"></div>
                        <div class="detail-stat">
                            <p class="detail-stat__value" id="detailDistance">-</p>
                            <p class="detail-stat__label" id="detailDistanceLabel">Ringkasan lokasi dan rute akan tampil di sini</p>
                        </div>
                        <div class="admin-form-actions" style="padding-top: 16px;">
                            <button class="btn btn--primary" id="detailUseLocation" type="button">Gunakan Lokasi Saya</button>
                            <a class="btn btn--ghost" id="detailPhoneLink" href="#">Hubungi</a>
                        </div>
                    </article>

                    <article class="panel info-card" id="summaryCard">
                        <h3>Ringkasan Lokasi</h3>
                    </article>
                </div>
            </section>

            <section>
                <div class="map-panel__header">
                    <div>
                        <h2 class="map-panel__title">Service center di sekitar lokasi ini</h2>
                    </div>
                </div>
                <div class="related-grid" id="relatedGrid">
                    <div class="empty-state panel">Memuat rekomendasi lokasi sekitar.</div>
                </div>
            </section>
        </div>
    </main>

    <?= view('partials/gis_footer') ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const config = {
        detailId: document.body.dataset.serviceCenterId,
        apiBase: <?= json_encode(base_url('api/service-center')) ?>,
        detailBase: <?= json_encode(base_url('service-center')) ?>,
    };

    const map = GISApp.createMap('detailMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 15,
        scrollWheelZoom: false,
    });

    const markerLayer = L.layerGroup().addTo(map);
    let routingControl = null;
    let currentItem = null;

    function buildFeatureList(item) {
        const source = (item.jenis_layanan || '')
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean);

        return source.length ? source.slice(0, 8) : ['Informasi layanan belum tersedia'];
    }

    function updatePhotoFrame(item) {
        const frame = document.getElementById('detailPhotoFrame');
        const photoUrl = GISApp.photoUrl(item.foto_lokasi);
        if (!photoUrl) {
            frame.innerHTML = '<div class="detail-photo-frame__placeholder">Foto lokasi belum tersedia</div>';
            return;
        }

        frame.innerHTML = `<img class="detail-photo-frame__image" src="${photoUrl}" alt="${GISApp.escapeHtml(item.nama_tempat)}">`;
    }

    function renderHero(item) {
        document.title = `${item.nama_tempat} - GeoSC Medan`;
        document.getElementById('detailHeroCopy').innerHTML = `
            <span class="pill"><a href="<?= base_url('peta') ?>">&larr; Kembali ke peta</a></span>
            <p class="section-kicker" style="margin-top:18px; color:#9dc0ff;">${GISApp.escapeHtml(item.brand)} · ${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}</p>
            <h1 class="section-title">${GISApp.escapeHtml(item.nama_tempat)}</h1>
            <p class="section-copy">${GISApp.escapeHtml(item.alamat || '-')}</p>
            <div class="hero-actions">
                <a class="btn btn--primary" href="${GISApp.directionsUrl(item)}" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
                <a class="btn btn--secondary" href="${config.detailBase}/${item.id}?focus=${item.id}">Tetap di Detail</a>
            </div>
            <div class="hero-notes">
                <span class="badge">Rating ${GISApp.formatRating(item.rating)}</span>
                <span class="badge">${GISApp.escapeHtml(GISApp.formatTime(item.jam_buka))} - ${GISApp.escapeHtml(GISApp.formatTime(item.jam_tutup))}</span>
                <span class="badge">${GISApp.escapeHtml(item.hari_operasional || 'Jadwal menyesuaikan')}</span>
            </div>
        `;

        document.getElementById('detailPhoneLink').href = `tel:${(item.no_telepon || '').replace(/[^0-9+]/g, '')}`;
        updatePhotoFrame(item);
    }

    function renderOperational(item) {
        document.getElementById('operationalCard').innerHTML = `
            <h3>Jam Operasional</h3>
            <div class="hours-list">
                <div class="hours-row"><span>Hari Operasional</span><strong>${GISApp.escapeHtml(item.hari_operasional || '-')}</strong></div>
                <div class="hours-row"><span>Jam Buka</span><strong>${GISApp.escapeHtml(GISApp.formatTime(item.jam_buka))}</strong></div>
                <div class="hours-row"><span>Jam Tutup</span><strong>${GISApp.escapeHtml(GISApp.formatTime(item.jam_tutup))}</strong></div>
                <div class="hours-row"><span>No. Telepon</span><strong>${GISApp.escapeHtml(item.no_telepon || '-')}</strong></div>
                <div class="hours-row"><span>Tipe Lokasi</span><strong>${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}</strong></div>
            </div>
        `;
    }

    function renderServices(item) {
        const features = buildFeatureList(item);
        document.getElementById('servicesCard').innerHTML = `
            <h3>Layanan Tersedia</h3>
            <div class="feature-list">
                ${features.map((feature) => `<span class="feature-pill">${GISApp.escapeHtml(feature)}</span>`).join('')}
            </div>
        `;
    }

    function renderSummary(item, nearby) {
        const nearbyLabel = nearby.length ? `${nearby.length} lokasi serupa di sekitar` : 'Belum ada lokasi serupa yang terdeteksi';
        document.getElementById('summaryCard').innerHTML = `
            <h3>Ringkasan Lokasi</h3>
            <p class="section-copy" style="margin-top:0;">
                ${GISApp.escapeHtml(item.nama_tempat)} berada di kawasan ${GISApp.escapeHtml(item.kecamatan || 'Medan')} dengan tipe lokasi
                ${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}. Detail ini menampilkan foto lokasi,
                rating service center, jam operasional, nomor telepon, serta rute interaktif dari posisi pengguna.
            </p>
            <div class="meta-row" style="margin-top:16px;">
                <span class="pill">${GISApp.escapeHtml(item.brand)}</span>
                <span class="pill">${GISApp.escapeHtml(nearbyLabel)}</span>
            </div>
        `;
    }

    function renderRelated(items) {
        const container = document.getElementById('relatedGrid');

        if (!items.length) {
            container.innerHTML = '<div class="empty-state panel">Belum ada lokasi lain yang cukup dekat untuk ditampilkan.</div>';
            return;
        }

        container.innerHTML = items.map((item) => `
            <article class="related-card">
                <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                <h3 class="related-card__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                <div class="related-card__meta">
                    <span>${GISApp.escapeHtml(item.alamat || '-')}</span>
                    <span>${GISApp.escapeHtml(GISApp.formatDistance(parseFloat(item.jarak_km || 0)))} dari titik ini</span>
                    <span>Rating ${GISApp.formatRating(item.rating)}</span>
                </div>
                <div class="related-card__actions">
                    <a class="mini-button is-primary" href="${config.detailBase}/${item.id}">Detail Lokasi</a>
                    <a class="mini-button" href="${GISApp.directionsUrl(item)}" target="_blank" rel="noopener noreferrer">Buka Rute</a>
                </div>
            </article>
        `).join('');
    }

    function drawRoute(origin, item) {
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(origin.lat, origin.lng),
                L.latLng(parseFloat(item.latitude), parseFloat(item.longitude)),
            ],
            lineOptions: {
                styles: [{ color: '#234ee2', opacity: 0.82, weight: 5 }],
            },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false,
            createMarker: () => null,
        }).addTo(map);

        routingControl.on('routesfound', (event) => {
            const route = event.routes?.[0];
            if (!route) {
                return;
            }

            const km = route.summary.totalDistance / 1000;
            const minutes = Math.round(route.summary.totalTime / 60);
            document.getElementById('detailDistance').textContent = GISApp.formatDistance(km);
            document.getElementById('detailDistanceLabel').textContent = `Rute dari posisi Anda ke lokasi ini sekitar ${minutes} menit melalui Leaflet Routing Machine.`;
        });
    }

    async function init() {
        try {
            const detailResponse = await GISApp.fetchJson(`${config.apiBase}/${encodeURIComponent(config.detailId)}`);
            const item = detailResponse.data;
            currentItem = item;
            renderHero(item);
            renderOperational(item);
            renderServices(item);

            GISApp.renderMarkers(markerLayer, [item], config.detailBase);
            map.setView([parseFloat(item.latitude), parseFloat(item.longitude)], 15);
            document.getElementById('detailDistance').textContent = `Rating ${GISApp.formatRating(item.rating)}`;
            document.getElementById('detailDistanceLabel').textContent = `${GISApp.locationTypeLabel(GISApp.locationTypeKey(item))} · ${item.kecamatan || 'Medan'}`;

            const relatedResponse = await GISApp.fetchJson(`${config.apiBase}/nearest?lat=${item.latitude}&lng=${item.longitude}&limit=4`);
            const relatedItems = (relatedResponse.data || []).filter((related) => related.id !== item.id).slice(0, 3);
            renderSummary(item, relatedItems);
            renderRelated(relatedItems);
        } catch (error) {
            document.getElementById('detailHeroCopy').innerHTML = `
                <span class="pill"><a href="<?= base_url('peta') ?>">&larr; Kembali ke peta</a></span>
                <h1 class="section-title" style="margin-top:18px;">Detail service center tidak dapat dimuat</h1>
                <p class="section-copy">Pastikan ID lokasi valid atau coba refresh halaman.</p>
            `;
            document.getElementById('relatedGrid').innerHTML = '<div class="empty-state panel">Data lokasi tidak tersedia.</div>';
            console.error(error);
        }
    }

    document.getElementById('detailUseLocation').addEventListener('click', () => {
        if (!currentItem) {
            return;
        }

        if (!navigator.geolocation) {
            drawRoute(GISApp.DEFAULT_CENTER, currentItem);
            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            drawRoute({
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            }, currentItem);
        }, () => {
            drawRoute(GISApp.DEFAULT_CENTER, currentItem);
        }, {
            enableHighAccuracy: true,
            timeout: 8000,
        });
    });

    init();
})();
</script>
</body>
</html>
