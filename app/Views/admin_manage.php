<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Kelola Service Center - Admin Panel',
        'description' => 'Kelola data service center, upload foto lokasi, rating, dan koordinat interaktif langsung dari panel admin Supabase.',
    ]) ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= view('partials/admin_sidebar', ['current' => 'manage']) ?>

    <main class="admin-main">
        <div class="admin-page">
            <section class="admin-page__header">
                <div>
                    <h1>Kelola Service Center</h1>
                </div>
                <button class="btn btn--primary" id="addLocationButton" type="button">Tambah Lokasi Baru</button>
            </section>

            <section class="admin-summary-grid" id="manageSummary">
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Total Lokasi</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Brand Aktif</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0</p><p class="admin-summary-card__label">Kecamatan</p></article>
                <article class="panel admin-summary-card"><p class="admin-summary-card__value">0.0</p><p class="admin-summary-card__label">Rating Rata-rata</p></article>
            </section>

            <section class="panel admin-card admin-table-shell">
                <div class="admin-card__header">
                    <div class="admin-header-stack">
                        <h2 class="admin-card__title">Daftar Lokasi Aktif</h2>
                        <span class="pill" id="manageCount">0 lokasi</span>
                    </div>
                    <div class="manage-table-controls">
                        <div class="manage-sort-box">
                            <label class="field-label" for="manageSort">Sort</label>
                            <select class="select-field" id="manageSort">
                                <option value="name-asc">Nama A-Z</option>
                                <option value="name-desc">Nama Z-A</option>
                                <option value="rating-desc">Rating Tertinggi</option>
                                <option value="rating-asc">Rating Terendah</option>
                                <option value="brand-asc">Brand A-Z</option>
                                <option value="district-asc">Kecamatan A-Z</option>
                            </select>
                        </div>
                        <button class="btn btn--ghost" id="manageFilterToggle" type="button" aria-expanded="true">Filter</button>
                    </div>
                </div>

                <div class="manage-filter-panel" id="manageFilterPanel">
                    <div class="admin-toolbar">
                        <input class="input-field" id="manageSearch" type="search" placeholder="Cari nama, alamat, atau brand...">
                        <select class="select-field" id="manageBrand">
                            <option value="all">Semua Brand</option>
                        </select>
                        <select class="select-field" id="manageDistrict">
                            <option value="all">Semua Kecamatan</option>
                        </select>
                    </div>
                </div>

                <div class="admin-table-scroll">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama &amp; Brand</th>
                                <th>Rating</th>
                                <th>Tipe Lokasi</th>
                                <th>Kecamatan</th>
                                <th>Koordinat</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="manageTableBody">
                            <tr><td colspan="7">Memuat data service center...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="admin-pager">
                    <span class="table-subtle" id="manageRange">Memuat rentang data...</span>
                    <div class="pager-list" id="managePager"></div>
                </div>
            </section>

            <section class="admin-manage-layout">
                <article class="panel admin-card" id="manageFormCard">
                    <div class="admin-card__header">
                        <div>
                            <h2 class="admin-card__title" id="manageFormTitle">Tambah Service Center</h2>
                        </div>
                        <span class="status-tag is-success" id="manageFormMode">Mode tambah</span>
                    </div>

                    <form id="serviceCenterForm" enctype="multipart/form-data">
                        <input type="hidden" id="formId" name="id">
                        <input type="hidden" id="removePhotoFlag" name="remove_foto_lokasi" value="0">

                        <div class="admin-form-grid">
                            <div class="field-stack">
                                <label class="field-label" for="nama_tempat">Nama Service Center</label>
                                <input class="input-field" id="nama_tempat" name="nama_tempat" type="text" required>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="brand">Brand</label>
                                <select class="select-field" id="brand" name="brand" required>
                                    <option value="">Pilih brand</option>
                                </select>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="rating">Rating</label>
                                <input class="input-field" id="rating" name="rating" type="number" min="0" max="5" step="0.1" value="5.0" required>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="no_telepon">Nomor Telepon</label>
                                <input class="input-field" id="no_telepon" name="no_telepon" type="text">
                            </div>

                            <div class="field-stack field-span-2">
                                <label class="field-label" for="alamat">Alamat Lengkap</label>
                                <textarea class="textarea-field" id="alamat" name="alamat" required></textarea>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="kecamatan">Kecamatan</label>
                                <select class="select-field" id="kecamatan" name="kecamatan" required>
                                    <option value="">Pilih kecamatan</option>
                                </select>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="jam_buka">Jam Buka</label>
                                <input class="input-field" id="jam_buka" name="jam_buka" type="time" required>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="jam_tutup">Jam Tutup</label>
                                <input class="input-field" id="jam_tutup" name="jam_tutup" type="time" required>
                            </div>

                            <div class="field-stack field-span-2">
                                <label class="field-label" for="hari_operasional">Hari Operasional</label>
                                <input class="input-field" id="hari_operasional" name="hari_operasional" type="text" required>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="tipe_lokasi_key">Tipe Lokasi</label>
                                <select class="select-field" id="tipe_lokasi_key" name="tipe_lokasi_key" required>
                                    <option value="">Pilih tipe lokasi</option>
                                    <option value="mall">Mall</option>
                                    <option value="ruko">Ruko</option>
                                    <option value="gerai-mandiri">Gerai Mandiri</option>
                                </select>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="jenis_layanan">Layanan</label>
                                <input class="input-field" id="jenis_layanan" name="jenis_layanan" type="text" required>
                            </div>

                            <div class="field-span-2">
                                <div class="coordinate-shell">
                                    <div class="coordinate-shell__header">
                                        <h3 class="coordinate-shell__title">Koordinat Lokasi</h3>
                                        <div class="coordinate-mode-list" id="coordinateModeList">
                                            <button class="btn btn--primary" id="useGpsButton" type="button">GPS Perangkat</button>
                                            <button class="btn btn--ghost is-active" id="manualModeButton" type="button">Input Manual</button>
                                            <button class="btn btn--ghost" id="markerModeButton" type="button">Geser Marker</button>
                                        </div>
                                    </div>
                                    <div id="adminLocationMap" class="mini-map"></div>
                                </div>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="latitude">Latitude</label>
                                <input class="input-field" id="latitude" name="latitude" type="number" step="0.0000001" required>
                            </div>

                            <div class="field-stack">
                                <label class="field-label" for="longitude">Longitude</label>
                                <input class="input-field" id="longitude" name="longitude" type="number" step="0.0000001" required>
                            </div>

                            <div class="field-stack field-span-2">
                                <label class="field-label" for="foto_lokasi_file">Foto Lokasi</label>
                                <input class="input-field" id="foto_lokasi_file" name="foto_lokasi_file" type="file" accept=".jpg,.jpeg,.png,.webp">
                            </div>

                            <div class="field-span-2">
                                <div class="photo-upload-shell">
                                    <div class="photo-upload-preview" id="photoPreview"></div>
                                    <div class="admin-form-actions" style="padding-top:0;">
                                        <button class="btn btn--ghost" id="removePhotoButton" type="button">Hapus Foto</button>
                                    </div>
                                </div>
                            </div>

                            <div class="field-span-2 admin-form-actions">
                                <button class="btn btn--primary" id="manageSubmitButton" type="submit">Tambah Lokasi</button>
                                <button class="btn btn--ghost" id="manageResetButton" type="button">Reset Form</button>
                            </div>
                        </div>
                    </form>
                </article>
            </section>
        </div>
    </main>
</div>

<div class="status-panel" id="submitStatusPanel" aria-live="polite" aria-atomic="true"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const readApi = <?= json_encode(base_url('api/service-center')) ?>;
    const writeApi = <?= json_encode(base_url('admin-api/service-center')) ?>;
    const brandsApi = <?= json_encode(base_url('api/brands')) ?>;
    const districtsApi = <?= json_encode(base_url('api/districts')) ?>;
    const detailBase = <?= json_encode(base_url('service-center')) ?>;
    const pageSize = 5;
    let allData = [];
    let filtered = [];
    let brands = [];
    let districts = [];
    let currentPage = 1;
    let currentPhotoUrl = '';
    let coordinateMode = 'manual';

    const state = {
        search: '',
        brand: 'all',
        district: 'all',
        sort: 'name-asc',
    };

    const map = GISApp.createMap('adminLocationMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 12,
    });
    let locationMarker = L.marker([GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng], {
        draggable: true,
    }).addTo(map);

    const elements = {
        summary: document.getElementById('manageSummary'),
        brandSummary: document.getElementById('brandSummaryList'),
        count: document.getElementById('manageCount'),
        range: document.getElementById('manageRange'),
        pager: document.getElementById('managePager'),
        body: document.getElementById('manageTableBody'),
        brandFilter: document.getElementById('manageBrand'),
        districtFilter: document.getElementById('manageDistrict'),
        search: document.getElementById('manageSearch'),
        sort: document.getElementById('manageSort'),
        filterToggle: document.getElementById('manageFilterToggle'),
        filterPanel: document.getElementById('manageFilterPanel'),
        form: document.getElementById('serviceCenterForm'),
        formId: document.getElementById('formId'),
        formTitle: document.getElementById('manageFormTitle'),
        formMode: document.getElementById('manageFormMode'),
        submitButton: document.getElementById('manageSubmitButton'),
        resetButton: document.getElementById('manageResetButton'),
        addButton: document.getElementById('addLocationButton'),
        statusPanel: document.getElementById('submitStatusPanel'),
        photoInput: document.getElementById('foto_lokasi_file'),
        photoPreview: document.getElementById('photoPreview'),
        removePhotoFlag: document.getElementById('removePhotoFlag'),
        useGpsButton: document.getElementById('useGpsButton'),
        manualModeButton: document.getElementById('manualModeButton'),
        markerModeButton: document.getElementById('markerModeButton'),
    };
    let statusPanelTimer = null;
    let pendingDeleteId = null;

    function updateSubmitButtonLabel() {
        elements.submitButton.textContent = elements.formId.value ? 'Update Lokasi' : 'Tambah Lokasi';
    }

    function statusIconMarkup(type) {
        if (type === 'loading') {
            return '<span class="status-panel__spinner" aria-hidden="true"></span>';
        }

        if (type === 'success') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9.55 16.6 5.4 12.45l-1.4 1.4 5.55 5.55L20 8.95l-1.4-1.4-9.05 9.05Z" fill="currentColor"/>
                </svg>
            `;
        }

        if (type === 'confirm') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3 1.7 20h20.6L12 3Zm1 13h-2v-2h2v2Zm0-4h-2V8h2v4Z" fill="currentColor"/>
                </svg>
            `;
        }

        return `
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="m13.41 12 4.3-4.29-1.42-1.42-4.29 4.3-4.29-4.3-1.42 1.42 4.3 4.29-4.3 4.29 1.42 1.42 4.29-4.3 4.29 4.3 1.42-1.42L13.41 12Z" fill="currentColor"/>
                </svg>
        `;
    }

    function showStatusPanel(type, message, details = {}) {
        const detailItems = Object.values(details || {});
        window.clearTimeout(statusPanelTimer);
        if (!elements.statusPanel) {
            return;
        }

        elements.statusPanel.className = `status-panel is-visible is-${type}`;
        elements.statusPanel.style.display = 'flex';
        elements.statusPanel.style.opacity = '1';
        elements.statusPanel.style.pointerEvents = 'auto';
        elements.statusPanel.innerHTML = `
            <div class="status-panel__icon">${statusIconMarkup(type)}</div>
            <div class="status-panel__body">
                <strong>${GISApp.escapeHtml(message)}</strong>
                ${detailItems.length ? `<ul>${detailItems.map((item) => `<li>${GISApp.escapeHtml(item)}</li>`).join('')}</ul>` : ''}
            </div>
        `;

        if (type !== 'loading') {
            statusPanelTimer = window.setTimeout(() => {
                hideStatusPanel();
            }, 3200);
        }
    }

    function showDeleteConfirm(item) {
        pendingDeleteId = item.id;
        window.clearTimeout(statusPanelTimer);
        elements.statusPanel.className = 'status-panel is-visible is-confirm';
        elements.statusPanel.style.display = 'flex';
        elements.statusPanel.style.opacity = '1';
        elements.statusPanel.style.pointerEvents = 'auto';
        elements.statusPanel.innerHTML = `
            <div class="status-panel__icon">${statusIconMarkup('confirm')}</div>
            <div class="status-panel__body">
                <strong>Hapus lokasi "${GISApp.escapeHtml(item.nama_tempat)}"?</strong>
                <div class="status-panel__actions">
                    <button class="mini-button" type="button" data-confirm-cancel>Batal</button>
                    <button class="mini-button is-danger" type="button" data-confirm-delete>Hapus</button>
                </div>
            </div>
        `;
    }

    function hideStatusPanel() {
        window.clearTimeout(statusPanelTimer);
        if (!elements.statusPanel) {
            return;
        }

        pendingDeleteId = null;
        elements.statusPanel.className = 'status-panel';
        elements.statusPanel.style.display = 'none';
        elements.statusPanel.style.opacity = '0';
        elements.statusPanel.style.pointerEvents = 'none';
        elements.statusPanel.innerHTML = '';
    }

    function setCoordinateMode(mode) {
        coordinateMode = mode;
        elements.useGpsButton.classList.toggle('btn--primary', mode === 'gps');
        elements.useGpsButton.classList.toggle('btn--ghost', mode !== 'gps');
        elements.manualModeButton.classList.toggle('btn--primary', mode === 'manual');
        elements.manualModeButton.classList.toggle('btn--ghost', mode !== 'manual');
        elements.markerModeButton.classList.toggle('btn--primary', mode === 'marker');
        elements.markerModeButton.classList.toggle('btn--ghost', mode !== 'marker');
        elements.useGpsButton.classList.toggle('is-active', mode === 'gps');
        elements.manualModeButton.classList.toggle('is-active', mode === 'manual');
        elements.markerModeButton.classList.toggle('is-active', mode === 'marker');
    }

    function parseMinutes(time) {
        if (!time) {
            return null;
        }

        const [hour, minute] = String(time).split(':').map((value) => parseInt(value, 10));
        if (!Number.isFinite(hour) || !Number.isFinite(minute)) {
            return null;
        }

        return (hour * 60) + minute;
    }

    function isOpenNow(item) {
        const openMinutes = parseMinutes(item.jam_buka);
        const closeMinutes = parseMinutes(item.jam_tutup);
        const now = new Date();
        const currentMinutes = (now.getHours() * 60) + now.getMinutes();

        if (openMinutes === null || closeMinutes === null) {
            return false;
        }

        return currentMinutes >= openMinutes && currentMinutes <= closeMinutes;
    }

    function updatePreviewFromPath(path) {
        currentPhotoUrl = path ? GISApp.photoUrl(path) : '';
        if (!currentPhotoUrl) {
            elements.photoPreview.innerHTML = '';
            return;
        }

        elements.photoPreview.innerHTML = `<img src="${currentPhotoUrl}" alt="Preview foto lokasi">`;
    }

    function syncMarkerToInputs(lat, lng) {
        document.getElementById('latitude').value = Number(lat).toFixed(7);
        document.getElementById('longitude').value = Number(lng).toFixed(7);
        locationMarker.setLatLng([lat, lng]);
        map.panTo([lat, lng], { animate: true });
        applyDetectedDistrict(lat, lng);
    }

    function detectDistrictByPoint(lat, lng) {
        return districts.find((district) => GISApp.geoJsonContainsPoint(district.geojson, lat, lng)) || null;
    }

    function applyDetectedDistrict(lat, lng) {
        const matchedDistrict = detectDistrictByPoint(lat, lng);
        if (matchedDistrict) {
            document.getElementById('kecamatan').value = matchedDistrict.name;
            return;
        }

        document.getElementById('kecamatan').value = '';
    }

    function useGpsCoordinates() {
        hideStatusPanel();
        if (!navigator.geolocation) {
            showStatusPanel('error', 'Perangkat ini tidak mendukung akses GPS.');
            return;
        }

        const previousLabel = elements.useGpsButton.textContent;
        elements.useGpsButton.disabled = true;
        elements.useGpsButton.textContent = 'Mengambil GPS...';

        navigator.geolocation.getCurrentPosition((position) => {
            setCoordinateMode('gps');
            syncMarkerToInputs(position.coords.latitude, position.coords.longitude);
            elements.useGpsButton.disabled = false;
            elements.useGpsButton.textContent = previousLabel;
        }, () => {
            elements.useGpsButton.disabled = false;
            elements.useGpsButton.textContent = previousLabel;
            showStatusPanel('error', 'Lokasi GPS tidak dapat diambil. Pastikan izin lokasi perangkat aktif.');
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
        });
    }

    function renderSummary() {
        const brandMap = filtered.reduce((accumulator, item) => {
            if (!accumulator[item.brand]) {
                accumulator[item.brand] = { total: 0, ratingTotal: 0 };
            }
            accumulator[item.brand].total += 1;
            accumulator[item.brand].ratingTotal += parseFloat(item.rating || 5);
            return accumulator;
        }, {});
        const districtsCount = new Set(filtered.map((item) => item.kecamatan).filter(Boolean)).size;
        const averageRating = filtered.length
            ? (filtered.reduce((total, item) => total + parseFloat(item.rating || 5), 0) / filtered.length).toFixed(1)
            : '0.0';
        const openNow = filtered.filter(isOpenNow).length;

        elements.summary.innerHTML = `
            <article class="panel admin-summary-card"><p class="admin-summary-card__value">${filtered.length}</p><p class="admin-summary-card__label">Total Lokasi</p></article>
            <article class="panel admin-summary-card"><p class="admin-summary-card__value">${Object.keys(brandMap).length}</p><p class="admin-summary-card__label">Brand Aktif</p></article>
            <article class="panel admin-summary-card"><p class="admin-summary-card__value">${districtsCount}</p><p class="admin-summary-card__label">Kecamatan</p></article>
            <article class="panel admin-summary-card"><p class="admin-summary-card__value">${averageRating}</p><p class="admin-summary-card__label">Rating Rata-rata</p></article>
        `;

        if (!elements.brandSummary) {
            return;
        }

        elements.brandSummary.innerHTML = Object.entries(brandMap)
            .sort((a, b) => b[1].total - a[1].total)
            .map(([brand, stats]) => `
                <div class="admin-metric-row">
                    <span>${GISApp.escapeHtml(brand)}</span>
                    <strong>${stats.total} lokasi • ${(stats.ratingTotal / stats.total).toFixed(1)}</strong>
                </div>
            `).join('') || '<div class="admin-metric-row"><span>Tidak ada data</span><strong>-</strong></div>';
    }

    function renderPagination() {
        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        elements.pager.innerHTML = Array.from({ length: totalPages }, (_, index) => {
            const page = index + 1;
            return `<button class="pager-button ${page === currentPage ? 'is-active' : ''}" type="button" data-page="${page}">${page}</button>`;
        }).join('');

        elements.pager.querySelectorAll('[data-page]').forEach((button) => {
            button.addEventListener('click', () => {
                currentPage = parseInt(button.dataset.page, 10);
                renderTable();
                renderPagination();
            });
        });
    }

    function renderTable() {
        const start = (currentPage - 1) * pageSize;
        const items = filtered.slice(start, start + pageSize);
        elements.count.textContent = `${filtered.length} lokasi`;

        if (!items.length) {
            elements.body.innerHTML = '<tr><td colspan="7">Tidak ada data service center untuk filter saat ini.</td></tr>';
            elements.range.textContent = 'Tidak ada data untuk ditampilkan.';
            return;
        }

        elements.body.innerHTML = items.map((item) => `
            <tr>
                <td>
                    <div class="table-brand">
                        <span class="table-brand__name">${GISApp.escapeHtml(item.nama_tempat)}</span>
                        <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                    </div>
                </td>
                <td><span class="table-rating">${GISApp.formatRating(item.rating)}</span></td>
                <td>${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))}</td>
                <td>${GISApp.escapeHtml(item.kecamatan || '-')}</td>
                <td class="table-subtle">${GISApp.escapeHtml(item.latitude || '-')}<br>${GISApp.escapeHtml(item.longitude || '-')}</td>
                <td>${item.foto_lokasi ? '<span class="status-tag is-success">Ada foto</span>' : '<span class="status-tag is-warning">Belum ada</span>'}</td>
                <td>
                    <div class="table-actions">
                        <button class="mini-button is-primary" type="button" data-edit="${item.id}">Edit</button>
                        <button class="mini-button is-danger" type="button" data-delete="${item.id}">Hapus</button>
                        <a class="mini-button" href="${detailBase}/${item.id}">Detail</a>
                    </div>
                </td>
            </tr>
        `).join('');

        elements.range.textContent = `Menampilkan ${start + 1}-${Math.min(start + pageSize, filtered.length)} dari ${filtered.length} lokasi`;
    }

    function applyFilters() {
        filtered = allData
            .filter((item) => state.brand === 'all' || item.brand === state.brand)
            .filter((item) => state.district === 'all' || item.kecamatan === state.district)
            .filter((item) => {
                if (!state.search) {
                    return true;
                }

                const haystack = `${item.nama_tempat} ${item.alamat} ${item.kecamatan} ${item.brand}`.toLowerCase();
                return haystack.includes(state.search.toLowerCase());
            });

        filtered.sort((left, right) => {
            if (state.sort === 'name-desc') {
                return String(right.nama_tempat || '').localeCompare(String(left.nama_tempat || ''));
            }

            if (state.sort === 'rating-desc') {
                return parseFloat(right.rating || 0) - parseFloat(left.rating || 0);
            }

            if (state.sort === 'rating-asc') {
                return parseFloat(left.rating || 0) - parseFloat(right.rating || 0);
            }

            if (state.sort === 'brand-asc') {
                return String(left.brand || '').localeCompare(String(right.brand || ''))
                    || String(left.nama_tempat || '').localeCompare(String(right.nama_tempat || ''));
            }

            if (state.sort === 'district-asc') {
                return String(left.kecamatan || '').localeCompare(String(right.kecamatan || ''))
                    || String(left.nama_tempat || '').localeCompare(String(right.nama_tempat || ''));
            }

            return String(left.nama_tempat || '').localeCompare(String(right.nama_tempat || ''));
        });

        currentPage = 1;
        renderSummary();
        renderTable();
        renderPagination();
    }

    function populateFilters() {
        elements.brandFilter.innerHTML = '<option value="all">Semua Brand</option>' +
            brands.map((brand) => `<option value="${GISApp.escapeHtml(brand.name)}">${GISApp.escapeHtml(brand.name)}</option>`).join('');
        elements.districtFilter.innerHTML = '<option value="all">Semua Kecamatan</option>' +
            districts.map((district) => `<option value="${GISApp.escapeHtml(district.name)}">${GISApp.escapeHtml(district.name)}</option>`).join('');

        document.getElementById('brand').innerHTML = '<option value="">Pilih brand</option>' +
            brands.map((brand) => `<option value="${GISApp.escapeHtml(brand.name)}">${GISApp.escapeHtml(brand.name)}</option>`).join('');
        document.getElementById('kecamatan').innerHTML = '<option value="">Pilih kecamatan</option>' +
            districts.map((district) => `<option value="${GISApp.escapeHtml(district.name)}">${GISApp.escapeHtml(district.name)}</option>`).join('');
    }

    function resetForm() {
        elements.form.reset();
        elements.formId.value = '';
        elements.removePhotoFlag.value = '0';
        document.getElementById('rating').value = '5.0';
        elements.formTitle.textContent = 'Tambah Service Center';
        elements.formMode.textContent = 'Mode tambah';
        updateSubmitButtonLabel();
        updatePreviewFromPath('');
        setCoordinateMode('manual');
        syncMarkerToInputs(GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng);
        hideStatusPanel();
    }

    function fillForm(item) {
        elements.formId.value = item.id || '';
        elements.removePhotoFlag.value = '0';
        document.getElementById('nama_tempat').value = item.nama_tempat || '';
        document.getElementById('brand').value = item.brand || '';
        document.getElementById('rating').value = GISApp.formatRating(item.rating);
        document.getElementById('no_telepon').value = item.no_telepon || '';
        document.getElementById('alamat').value = item.alamat || '';
        document.getElementById('kecamatan').value = item.kecamatan || '';
        document.getElementById('jam_buka').value = (item.jam_buka || '').slice(0, 5);
        document.getElementById('jam_tutup').value = (item.jam_tutup || '').slice(0, 5);
        document.getElementById('hari_operasional').value = item.hari_operasional || '';
        document.getElementById('tipe_lokasi_key').value = GISApp.locationTypeKey(item);
        document.getElementById('jenis_layanan').value = item.jenis_layanan || '';
        document.getElementById('latitude').value = item.latitude || '';
        document.getElementById('longitude').value = item.longitude || '';
        elements.photoInput.value = '';
        updatePreviewFromPath(item.foto_lokasi || '');
        setCoordinateMode('manual');
        syncMarkerToInputs(parseFloat(item.latitude || GISApp.DEFAULT_CENTER.lat), parseFloat(item.longitude || GISApp.DEFAULT_CENTER.lng));

        elements.formTitle.textContent = 'Edit Service Center';
        elements.formMode.textContent = 'Mode edit';
        updateSubmitButtonLabel();
        document.getElementById('manageFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        hideStatusPanel();
    }

    async function loadData() {
        const [dataResponse, brandResponse, districtResponse] = await Promise.all([
            GISApp.fetchJson(readApi),
            GISApp.fetchJson(brandsApi),
            GISApp.fetchJson(districtsApi),
        ]);
        allData = dataResponse.data || [];
        brands = brandResponse.data || [];
        districts = districtResponse.data || [];
        populateFilters();
        applyFilters();
    }

    async function submitForm(event) {
        event.preventDefault();
        hideStatusPanel();

        const formData = new FormData(elements.form);
        const id = elements.formId.value;
        const url = id ? `${writeApi}/${id}` : writeApi;
        const isUpdate = Boolean(id);
        elements.submitButton.disabled = true;
        showStatusPanel('loading', isUpdate ? 'Memperbarui data lokasi...' : 'Menambahkan data lokasi...');

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            if (!response.ok) {
                const error = new Error(data.message || 'Gagal menyimpan data.');
                error.details = data.errors || {};
                throw error;
            }

            await loadData();
            resetForm();
            showStatusPanel('success', isUpdate ? 'Data service center berhasil diperbarui.' : 'Service center baru berhasil ditambahkan.');
        } catch (error) {
            console.error(error);
            showStatusPanel('error', error.message || 'Terjadi kesalahan saat menyimpan data.', error.details);
        } finally {
            elements.submitButton.disabled = false;
            updateSubmitButtonLabel();
        }
    }

    async function handleDelete(id) {
        const item = allData.find((entry) => entry.id === id);
        if (!item) {
            return;
        }

        showDeleteConfirm(item);
    }

    async function confirmDelete(id) {
        const item = allData.find((entry) => entry.id === id);
        if (!item) {
            hideStatusPanel();
            return;
        }

        showStatusPanel('loading', `Menghapus lokasi "${item.nama_tempat}"...`);

        try {
            const response = await fetch(`${writeApi}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            if (!response.ok) {
                const error = new Error(data.message || 'Gagal menghapus data.');
                error.details = data.errors || {};
                throw error;
            }

            await loadData();
            if (elements.formId.value === id) {
                resetForm();
            }
            showStatusPanel('success', `Lokasi "${item.nama_tempat}" berhasil dihapus.`);
        } catch (error) {
            console.error(error);
            showStatusPanel('error', error.message || 'Gagal menghapus data service center.', error.details);
        }
    }

    map.on('click', (event) => {
        setCoordinateMode('marker');
        syncMarkerToInputs(event.latlng.lat, event.latlng.lng);
    });

    locationMarker.on('dragend', () => {
        const latLng = locationMarker.getLatLng();
        setCoordinateMode('marker');
        syncMarkerToInputs(latLng.lat, latLng.lng);
    });

    ['latitude', 'longitude'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('input', () => {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                setCoordinateMode('manual');
                locationMarker.setLatLng([lat, lng]);
                map.panTo([lat, lng], { animate: true });
                applyDetectedDistrict(lat, lng);
            }
        });
    });

    elements.photoInput.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (!file) {
            if (!currentPhotoUrl) {
                updatePreviewFromPath('');
            }
            return;
        }

        elements.removePhotoFlag.value = '0';
        const reader = new FileReader();
        reader.onload = () => {
            elements.photoPreview.innerHTML = `<img src="${reader.result}" alt="Preview foto lokasi">`;
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('removePhotoButton').addEventListener('click', () => {
        elements.photoInput.value = '';
        elements.removePhotoFlag.value = '1';
        updatePreviewFromPath('');
    });

    elements.statusPanel.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-cancel]')) {
            hideStatusPanel();
            return;
        }

        if (event.target.closest('[data-confirm-delete]') && pendingDeleteId) {
            const deleteId = pendingDeleteId;
            pendingDeleteId = null;
            confirmDelete(deleteId);
        }
    });

    elements.search.addEventListener('input', (event) => {
        state.search = event.target.value.trim();
        applyFilters();
    });

    elements.addButton.addEventListener('click', () => {
        resetForm();
        document.getElementById('manageFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    elements.useGpsButton.addEventListener('click', useGpsCoordinates);
    elements.manualModeButton.addEventListener('click', () => {
        setCoordinateMode('manual');
        document.getElementById('latitude').focus();
    });
    elements.markerModeButton.addEventListener('click', () => {
        setCoordinateMode('marker');
        map.panTo(locationMarker.getLatLng(), { animate: true });
    });

    elements.brandFilter.addEventListener('change', (event) => {
        state.brand = event.target.value;
        applyFilters();
    });

    elements.districtFilter.addEventListener('change', (event) => {
        state.district = event.target.value;
        applyFilters();
    });

    elements.sort.addEventListener('change', (event) => {
        state.sort = event.target.value;
        applyFilters();
    });

    elements.filterToggle.addEventListener('click', () => {
        const isHidden = elements.filterPanel.hasAttribute('hidden');
        if (isHidden) {
            elements.filterPanel.removeAttribute('hidden');
        } else {
            elements.filterPanel.setAttribute('hidden', '');
        }

        elements.filterToggle.setAttribute('aria-expanded', String(isHidden));
    });

    elements.form.addEventListener('submit', submitForm);
    elements.resetButton.addEventListener('click', resetForm);

    elements.body.addEventListener('click', (event) => {
        const editId = event.target.dataset.edit;
        const deleteId = event.target.dataset.delete;

        if (editId) {
            const item = allData.find((entry) => entry.id === editId);
            if (item) {
                fillForm(item);
            }
            return;
        }

        if (deleteId) {
            handleDelete(deleteId);
        }
    });

    resetForm();
    loadData().catch((error) => {
        console.error(error);
        elements.body.innerHTML = '<tr><td colspan="7">Gagal memuat data dari Supabase.</td></tr>';
        showStatusPanel('error', 'Koneksi ke data service center gagal dimuat.');
    });
})();
</script>
</body>
</html>
