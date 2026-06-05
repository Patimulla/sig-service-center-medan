<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Analisis Spasial GIS - Admin Panel',
        'description' => 'Visualisasi persebaran, nearest service center, heatmap, radius, dan polygon batas kecamatan pada panel admin GIS.',
    ]) ?>
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= view('partials/admin_sidebar', ['current' => 'analysis']) ?>

    <main class="admin-main">
        <div class="admin-page">
            <section class="admin-page__header" id="reporting">
                <div>
                    <h1>Analisis Spasial Persebaran</h1>
                </div>
                <div class="admin-form-actions" style="padding-top:0;">
                    <button class="btn btn--secondary" id="exportExcel" type="button">Export Excel</button>
                    <button class="btn btn--primary" id="exportPdf" type="button">Export PDF</button>
                </div>
            </section>

            <section class="analysis-layout">
                <div class="analysis-main">
                    <article class="panel admin-card analysis-map-wrap">
                        <div class="analysis-legend">
                            <span class="pill" id="analysisVisibleCount">0 lokasi</span>
                            <span class="pill" id="analysisDistrictCount">0 kecamatan</span>
                            <span class="pill" id="analysisNearestSummary">-</span>
                        </div>
                        <div id="analysisMap" class="analysis-map"></div>
                    </article>
                </div>

                <aside class="analysis-side">
                    <section class="panel admin-card">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">Parameter Analisis</h2>
                        </div>
                        <div class="control-panel__body">
                            <div class="filter-group">
                                <label for="analysisBrand">Pilih brand</label>
                                <select class="select-field" id="analysisBrand">
                                    <option value="all">Semua Brand</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="analysisDistrict">Pilih kecamatan</label>
                                <select class="select-field" id="analysisDistrict">
                                    <option value="all">Semua Kecamatan</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="analysisRadius">Radius analisis area</label>
                                <div class="slider-stack">
                                    <input id="analysisRadius" type="range" min="1" max="10" value="5">
                                    <div class="slider-values">
                                        <span>1 km</span>
                                        <strong id="analysisRadiusValue">5 km</strong>
                                        <span>10 km</span>
                                    </div>
                                </div>
                            </div>

                            <div class="toggle-row">
                                <span>Topologi Heatmap</span>
                                <label class="switch is-on" id="heatToggleWrap">
                                    <input id="heatToggle" type="checkbox" checked>
                                    <span></span>
                                </label>
                            </div>

                            <div class="toggle-row">
                                <span>Analisis Nearest Service Center</span>
                                <label class="switch" id="neighborToggleWrap">
                                    <input id="neighborToggle" type="checkbox">
                                    <span></span>
                                </label>
                            </div>

                            <div class="toggle-row">
                                <span>Polygon Kecamatan</span>
                                <label class="switch is-on" id="polygonToggleWrap">
                                    <input id="polygonToggle" type="checkbox" checked>
                                    <span></span>
                                </label>
                            </div>

                            <button class="btn btn--primary" id="runAnalysis" type="button">Jalankan Analisis</button>
                        </div>
                    </section>
                </aside>
            </section>

            <section class="panel admin-card analysis-stats-wide">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">Statistik Area</h2>
                </div>
                <div class="admin-summary-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                    <article class="panel admin-summary-card">
                        <p class="admin-summary-card__value" id="analysisTotal">0</p>
                        <p class="admin-summary-card__label">Total Center</p>
                    </article>
                    <article class="panel admin-summary-card">
                        <p class="admin-summary-card__value" id="densestDistrict">-</p>
                        <p class="admin-summary-card__label">Kepadatan Tertinggi</p>
                    </article>
                </div>

                <div class="distribution-bars" style="margin-top: 18px;">
                    <div class="distribution-row">
                        <span>&lt; 1 km</span>
                        <div class="distribution-bar"><span id="bucketA" style="width:0%"></span></div>
                        <strong id="bucketALabel">0%</strong>
                    </div>
                    <div class="distribution-row">
                        <span>1 - 3 km</span>
                        <div class="distribution-bar"><span id="bucketB" style="width:0%"></span></div>
                        <strong id="bucketBLabel">0%</strong>
                    </div>
                    <div class="distribution-row">
                        <span>&gt; 3 km</span>
                        <div class="distribution-bar"><span id="bucketC" style="width:0%"></span></div>
                        <strong id="bucketCLabel">0%</strong>
                    </div>
                </div>
            </section>

            <section class="panel admin-card" id="spatialQuery">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">Spatial Query</h2>
                </div>
                <div class="query-preview" id="queryPreview">Menyiapkan query analisis...</div>
            </section>

            <section class="report-grid">
                <article class="report-card">
                    <h4>Area Fokus</h4>
                    <p class="section-copy" id="focusAreaCopy" style="margin:0;"></p>
                </article>
                <article class="report-card">
                    <h4>Brand Dominan</h4>
                    <p class="section-copy" id="focusBrandCopy" style="margin:0;"></p>
                </article>
                <article class="report-card">
                    <h4>Catatan Operasional</h4>
                    <p class="section-copy" id="focusOpsCopy" style="margin:0;"></p>
                </article>
            </section>
        </div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://unpkg.com/jspdf-autotable@latest/dist/jspdf.plugin.autotable.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const apiBase = <?= json_encode(base_url('api/service-center')) ?>;
    const districtsApi = <?= json_encode(base_url('api/districts')) ?>;
    const detailBase = <?= json_encode(base_url('service-center')) ?>;
    const map = GISApp.createMap('analysisMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 12,
        zoomControl: false,
    });
    L.control.zoom({ position: 'bottomleft' }).addTo(map);

    const baseLayer = L.layerGroup().addTo(map);
    const centerLayer = L.layerGroup().addTo(map);
    const heatLayer = L.layerGroup().addTo(map);
    const neighborLayer = L.layerGroup().addTo(map);
    const radiusLayer = L.layerGroup().addTo(map);
    const polygonLayer = L.geoJSON(null, {
        style: {
            color: '#d84b4b',
            weight: 2,
            opacity: 0.8,
            fillColor: '#f08d8d',
            fillOpacity: 0.12,
        },
    }).addTo(map);

    const state = {
        brand: 'all',
        district: 'all',
        radius: 5,
        heatmap: true,
        neighbors: false,
        polygons: true,
    };
    let allData = [];
    let filtered = [];
    let districts = [];

    function renderCenterMarker() {
        centerLayer.clearLayers();

        L.marker([GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng], {
            icon: GISApp.createMarkerIcon('Samsung'),
            zIndexOffset: 900,
        })
            .bindPopup(`
                <div class="map-popup">
                    <span class="brand-badge" style="background:${GISApp.brandColor('Samsung')}">Pusat Kota</span>
                    <h3 class="map-popup__title">Titik Tengah Kota Medan</h3>
                    <div class="map-popup__meta">
                        <span>${GISApp.DEFAULT_CENTER.lat.toFixed(4)}, ${GISApp.DEFAULT_CENTER.lng.toFixed(4)}</span>
                    </div>
                </div>
            `, { maxWidth: 240 })
            .addTo(centerLayer);
    }

    function syncSwitchStyles() {
        document.getElementById('heatToggleWrap').classList.toggle('is-on', state.heatmap);
        document.getElementById('neighborToggleWrap').classList.toggle('is-on', state.neighbors);
        document.getElementById('polygonToggleWrap').classList.toggle('is-on', state.polygons);
    }

    function densityBuckets(data) {
        const center = GISApp.DEFAULT_CENTER;
        const totals = { a: 0, b: 0, c: 0 };
        data.forEach((item) => {
            const lat = GISApp.parseCoordinate(item.latitude);
            const lng = GISApp.parseCoordinate(item.longitude);
            if (lat == null || lng == null) {
                return;
            }
            const distance = GISApp.distanceKm(center.lat, center.lng, lat, lng);
            if (distance < 1) {
                totals.a += 1;
            } else if (distance <= 3) {
                totals.b += 1;
            } else {
                totals.c += 1;
            }
        });
        return totals;
    }

    function nearestPairs(data) {
        const pairs = [];
        const used = new Set();
        data.forEach((item) => {
            const from = GISApp.latLngFromItem(item);
            if (!from) {
                return;
            }

            let nearest = null;
            let nearestDistance = Number.POSITIVE_INFINITY;
            data.forEach((candidate) => {
                if (candidate.id === item.id) {
                    return;
                }

                const to = GISApp.latLngFromItem(candidate);
                if (!to) {
                    return;
                }

                const distance = GISApp.distanceKm(from[0], from[1], to[0], to[1]);
                if (distance < nearestDistance) {
                    nearestDistance = distance;
                    nearest = candidate;
                }
            });

            if (!nearest) {
                return;
            }

            const pairKey = [item.id, nearest.id].sort().join(':');
            if (used.has(pairKey)) {
                return;
            }

            used.add(pairKey);
            pairs.push({
                from: item,
                to: nearest,
                distance: nearestDistance,
            });
        });

        return pairs.slice(0, 18);
    }

    function renderStats() {
        const districtCounts = filtered.reduce((accumulator, item) => {
            accumulator[item.kecamatan] = (accumulator[item.kecamatan] || 0) + 1;
            return accumulator;
        }, {});
        const densest = Object.entries(districtCounts).sort((a, b) => b[1] - a[1])[0];
        const buckets = densityBuckets(filtered);
        const total = Math.max(filtered.length, 1);
        const bucketA = Math.round((buckets.a / total) * 100);
        const bucketB = Math.round((buckets.b / total) * 100);
        const bucketC = Math.round((buckets.c / total) * 100);
        const brandCounts = filtered.reduce((accumulator, item) => {
            accumulator[item.brand] = (accumulator[item.brand] || 0) + 1;
            return accumulator;
        }, {});
        const dominantBrand = Object.entries(brandCounts).sort((a, b) => b[1] - a[1])[0];
        const nearestPair = nearestPairs(filtered)[0];

        document.getElementById('analysisVisibleCount').textContent = `${filtered.length} lokasi`;
        document.getElementById('analysisDistrictCount').textContent = `${Object.keys(districtCounts).length} kecamatan`;
        document.getElementById('analysisNearestSummary').textContent = nearestPair ? `${nearestPair.distance.toFixed(2)} km terdekat` : '-';
        document.getElementById('analysisTotal').textContent = filtered.length;
        document.getElementById('densestDistrict').textContent = densest ? densest[0] : '-';
        document.getElementById('bucketA').style.width = `${bucketA}%`;
        document.getElementById('bucketB').style.width = `${bucketB}%`;
        document.getElementById('bucketC').style.width = `${bucketC}%`;
        document.getElementById('bucketALabel').textContent = `${bucketA}%`;
        document.getElementById('bucketBLabel').textContent = `${bucketB}%`;
        document.getElementById('bucketCLabel').textContent = `${bucketC}%`;

        document.getElementById('focusAreaCopy').textContent = densest
            ? `Kawasan ${densest[0]} menjadi titik terpadat dengan ${densest[1]} service center aktif pada filter sekarang.`
            : 'Belum ada area yang cukup data untuk dihitung.';
        document.getElementById('focusBrandCopy').textContent = dominantBrand
            ? `Brand ${dominantBrand[0]} mendominasi tampilan saat ini dengan ${dominantBrand[1]} lokasi terhubung.`
            : 'Belum ada brand dominan untuk ditampilkan.';
        document.getElementById('focusOpsCopy').textContent =
            `Radius analisis ${state.radius} km dengan heatmap ${state.heatmap ? 'aktif' : 'nonaktif'}, nearest ${state.neighbors ? 'aktif' : 'nonaktif'}, dan polygon kecamatan ${state.polygons ? 'aktif' : 'nonaktif'}.`;
    }

    function renderQuery() {
        const brandClause = state.brand === 'all' ? '-- semua brand' : `AND brand = '${state.brand}'`;
        const districtClause = state.district === 'all' ? '-- semua kecamatan' : `AND kecamatan = '${state.district}'`;
        const neighborClause = state.neighbors
            ? 'ORDER BY distance_to_nearest_service_center ASC'
            : 'ORDER BY kecamatan, brand';
        const heatClause = state.heatmap
            ? '-- heat overlay aktif untuk visualisasi kepadatan'
            : '-- heat overlay nonaktif';

        document.getElementById('queryPreview').textContent = [
            'SELECT',
            '  id, nama_service, brand, latitude, longitude, kecamatan, layanan, rating',
            'FROM service_center_medan sc',
            'WHERE ST_Distance(',
            '  ST_SetSRID(ST_MakePoint(sc.longitude, sc.latitude), 4326)::geography,',
            `  ST_SetSRID(ST_MakePoint(${GISApp.DEFAULT_CENTER.lng}, ${GISApp.DEFAULT_CENTER.lat}), 4326)::geography`,
            `) <= ${state.radius * 1000}`,
            `  ${brandClause}`,
            `  ${districtClause}`,
            neighborClause,
            heatClause,
        ].join('\n');
    }

    function renderPolygons() {
        polygonLayer.clearLayers();
        if (!state.polygons) {
            return;
        }

        districts
            .filter((district) => district.geojson)
            .filter((district) => state.district === 'all' || district.name === state.district)
            .forEach((district) => {
                try {
                    const parsed = JSON.parse(district.geojson);
                    polygonLayer.addData(parsed);
                } catch (error) {
                    console.error(error);
                }
            });
    }

    function renderRadiusCircle() {
        radiusLayer.clearLayers();

        L.circle([GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng], {
            radius: state.radius * 1000,
            color: '#234ee2',
            weight: 2,
            opacity: 0.95,
            fillColor: '#70cfff',
            fillOpacity: 0.12,
            dashArray: '10 8',
        }).addTo(radiusLayer);
    }

    function renderMap() {
        baseLayer.clearLayers();
        heatLayer.clearLayers();
        neighborLayer.clearLayers();
        renderCenterMarker();
        renderRadiusCircle();

        filtered.forEach((item) => {
            const latLng = GISApp.latLngFromItem(item);
            if (!latLng) {
                return;
            }

            if (state.heatmap) {
                L.circle(latLng, {
                    radius: 420,
                    color: GISApp.brandColor(item.brand),
                    weight: 1,
                    opacity: 0.26,
                    fillColor: GISApp.brandColor(item.brand),
                    fillOpacity: 0.18,
                }).addTo(heatLayer);

                L.circle(latLng, {
                    radius: 210,
                    color: GISApp.brandColor(item.brand),
                    weight: 0,
                    fillColor: GISApp.brandColor(item.brand),
                    fillOpacity: 0.28,
                }).addTo(heatLayer);
            }

            L.marker(latLng, {
                icon: GISApp.createMarkerIcon(item.brand),
            })
                .bindPopup(`
                    <div class="map-popup">
                        <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                        <h3 class="map-popup__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                        <div class="map-popup__meta">
                            <span>${GISApp.escapeHtml(item.kecamatan || '-')}</span>
                            <span>${GISApp.escapeHtml(item.alamat || '-')}</span>
                            <span>Rating ${GISApp.formatRating(item.rating)}</span>
                        </div>
                        <div class="map-popup__actions">
                            <a class="mini-button is-primary" href="${detailBase}/${item.id}">Detail</a>
                        </div>
                    </div>
                `, { maxWidth: 280 })
                .addTo(baseLayer);
        });

        if (state.neighbors) {
            nearestPairs(filtered).forEach((pair) => {
                const from = GISApp.latLngFromItem(pair.from);
                const to = GISApp.latLngFromItem(pair.to);
                if (!from || !to) {
                    return;
                }

                L.polyline([from, to], {
                    color: '#234ee2',
                    weight: 1.4,
                    opacity: 0.35,
                    dashArray: '6 6',
                }).addTo(neighborLayer);
            });
        }

        renderPolygons();
        GISApp.fitBounds(map, filtered, [30, 30], 13);
    }

    function applyFilters() {
        filtered = allData.filter((item) => {
            if (state.brand !== 'all' && item.brand !== state.brand) {
                return false;
            }

            if (state.district !== 'all' && item.kecamatan !== state.district) {
                return false;
            }

            const lat = GISApp.parseCoordinate(item.latitude);
            const lng = GISApp.parseCoordinate(item.longitude);
            if (lat == null || lng == null) {
                return false;
            }

            return GISApp.distanceKm(GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng, lat, lng) <= state.radius;
        });

        syncSwitchStyles();
        renderStats();
        renderQuery();
        renderMap();
    }

    function populateFilters() {
        const brands = [...new Set(allData.map((item) => item.brand).filter(Boolean))].sort((a, b) => a.localeCompare(b));
        document.getElementById('analysisBrand').innerHTML = '<option value="all">Semua Brand</option>' +
            brands.map((brand) => `<option value="${brand}">${brand}</option>`).join('');
        document.getElementById('analysisDistrict').innerHTML = '<option value="all">Semua Kecamatan</option>' +
            districts.map((district) => `<option value="${district.name}">${district.name}</option>`).join('');
    }

    function exportRow(item) {
        const lat = GISApp.parseCoordinate(item.latitude);
        const lng = GISApp.parseCoordinate(item.longitude);
        const distanceFromCenter = lat == null || lng == null
            ? ''
            : GISApp.distanceKm(GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng, lat, lng).toFixed(2);

        return {
            ID: item.id || '',
            NamaServiceCenter: item.nama_tempat || '',
            Brand: item.brand || '',
            Rating: GISApp.formatRating(item.rating),
            TipeLokasi: GISApp.locationTypeLabel(GISApp.locationTypeKey(item)),
            Kecamatan: item.kecamatan || '',
            Alamat: item.alamat || '',
            Telepon: item.no_telepon || '',
            JamBuka: GISApp.formatTime(item.jam_buka),
            JamTutup: GISApp.formatTime(item.jam_tutup),
            HariOperasional: item.hari_operasional || '',
            Layanan: item.jenis_layanan || '',
            Latitude: item.latitude || '',
            Longitude: item.longitude || '',
            JarakDariPusatKotaKm: distanceFromCenter,
            FotoLokasi: item.foto_lokasi || '',
        };
    }

    function activeSummaryRows() {
        return [
            { Parameter: 'Generated', Nilai: new Date().toLocaleString('id-ID') },
            { Parameter: 'Total Seluruh Lokasi', Nilai: allData.length },
            { Parameter: 'Total Hasil Filter Aktif', Nilai: filtered.length },
            { Parameter: 'Brand Aktif', Nilai: state.brand === 'all' ? 'Semua Brand' : state.brand },
            { Parameter: 'Kecamatan Aktif', Nilai: state.district === 'all' ? 'Semua Kecamatan' : state.district },
            { Parameter: 'Radius Analisis', Nilai: `${state.radius} km` },
            { Parameter: 'Heatmap', Nilai: state.heatmap ? 'Aktif' : 'Nonaktif' },
            { Parameter: 'Nearest Neighbor', Nilai: state.neighbors ? 'Aktif' : 'Nonaktif' },
            { Parameter: 'Polygon Kecamatan', Nilai: state.polygons ? 'Aktif' : 'Nonaktif' },
        ];
    }

    async function init() {
        try {
            const [serviceResponse, districtResponse] = await Promise.all([
                GISApp.fetchJson(apiBase),
                GISApp.fetchJson(districtsApi),
            ]);
            allData = serviceResponse.data || [];
            districts = districtResponse.data || [];
            populateFilters();
            applyFilters();
        } catch (error) {
            console.error(error);
            document.getElementById('queryPreview').textContent = 'Gagal memuat data analisis dari Supabase.';
        }
    }

    document.getElementById('analysisBrand').addEventListener('change', (event) => {
        state.brand = event.target.value;
        applyFilters();
    });

    document.getElementById('analysisDistrict').addEventListener('change', (event) => {
        state.district = event.target.value;
        applyFilters();
    });

    document.getElementById('analysisRadius').addEventListener('input', (event) => {
        state.radius = parseInt(event.target.value, 10);
        document.getElementById('analysisRadiusValue').textContent = `${state.radius} km`;
        applyFilters();
    });

    document.getElementById('heatToggle').addEventListener('change', (event) => {
        state.heatmap = event.target.checked;
        applyFilters();
    });

    document.getElementById('neighborToggle').addEventListener('change', (event) => {
        state.neighbors = event.target.checked;
        applyFilters();
    });

    document.getElementById('polygonToggle').addEventListener('change', (event) => {
        state.polygons = event.target.checked;
        applyFilters();
    });

    document.getElementById('runAnalysis').addEventListener('click', () => {
        applyFilters();
    });

    document.getElementById('exportExcel').addEventListener('click', () => {
        const workbook = XLSX.utils.book_new();
        const filteredRows = filtered.map(exportRow);
        const summarySheet = XLSX.utils.json_to_sheet(activeSummaryRows());
        const filteredSheet = XLSX.utils.json_to_sheet(filteredRows);

        XLSX.utils.book_append_sheet(workbook, summarySheet, 'Ringkasan');
        XLSX.utils.book_append_sheet(workbook, filteredSheet, 'Filter Aktif');
        XLSX.writeFile(workbook, 'laporan-analisis-gis-service-center.xlsx');
    });

    document.getElementById('exportPdf').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });
        const summary = activeSummaryRows();
        const exportRows = allData.map(exportRow);
        doc.setFontSize(16);
        doc.text('Laporan Analisis GIS GeoSC Medan', 14, 16);
        doc.setFontSize(10);
        doc.text(`Generated: ${new Date().toLocaleString('id-ID')}`, 14, 24);
        doc.text(`Filter brand: ${state.brand} | kecamatan: ${state.district} | radius: ${state.radius} km`, 14, 30);
        doc.autoTable({
            startY: 36,
            head: [['Parameter', 'Nilai']],
            body: summary.map((row) => [row.Parameter, String(row.Nilai)]),
            theme: 'grid',
            styles: { fontSize: 9, cellPadding: 3 },
            headStyles: { fillColor: [35, 78, 226] },
            columnStyles: {
                0: { cellWidth: 58 },
                1: { cellWidth: 205 },
            },
        });

        doc.setFontSize(12);
        doc.text('Data Lokasi Lengkap', 14, doc.lastAutoTable.finalY + 10);
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 14,
            head: [[
                'Nama',
                'Brand',
                'Rating',
                'Tipe',
                'Kecamatan',
                'Telepon',
                'Jam',
                'Alamat',
                'Koordinat',
            ]],
            body: exportRows.map((row) => [
                row.NamaServiceCenter,
                row.Brand,
                row.Rating,
                row.TipeLokasi,
                row.Kecamatan,
                row.Telepon,
                `${row.JamBuka} - ${row.JamTutup}`,
                row.Alamat,
                `${row.Latitude}, ${row.Longitude}`,
            ]),
            theme: 'striped',
            styles: {
                fontSize: 8,
                cellPadding: 2.5,
                overflow: 'linebreak',
                valign: 'middle',
            },
            headStyles: { fillColor: [20, 54, 165] },
            columnStyles: {
                0: { cellWidth: 42 },
                1: { cellWidth: 22 },
                2: { cellWidth: 16, halign: 'center' },
                3: { cellWidth: 24 },
                4: { cellWidth: 28 },
                5: { cellWidth: 28 },
                6: { cellWidth: 24 },
                7: { cellWidth: 70 },
                8: { cellWidth: 36 },
            },
        });
        doc.save('laporan-analisis-gis-service-center.pdf');
    });

    init();
})();
</script>
</body>
</html>
