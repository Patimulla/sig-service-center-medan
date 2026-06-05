window.GISApp = (() => {
    const BRAND_COLORS = {
        Samsung: '#2457e0',
        Xiaomi: '#f17829',
        Oppo: '#1ba668',
        OPPO: '#1ba668',
        Vivo: '#3f6cff',
        Apple: '#718197',
        Realme: '#e3ac1c',
    };

    const DEFAULT_CENTER = { lat: 3.5920, lng: 98.6787 };
    const DEFAULT_ZOOM = 13;

    function normalizeBrand(brand) {
        if (!brand) {
            return 'Lainnya';
        }

        return brand === 'OPPO' ? 'Oppo' : brand;
    }

    function brandColor(brand) {
        return BRAND_COLORS[brand] || BRAND_COLORS[normalizeBrand(brand)] || '#2457e0';
    }

    function locationTypeKey(item) {
        if (item?.tipe_lokasi_key) {
            return item.tipe_lokasi_key;
        }

        const source = String(item?.tipe_lokasi || item?.estimasi_servis || '').toLowerCase();
        if (source.includes('mall')) {
            return 'mall';
        }

        if (source.includes('ruko')) {
            return 'ruko';
        }

        return 'gerai-mandiri';
    }

    function locationTypeLabel(key) {
        if (key === 'mall') {
            return 'Mall';
        }

        if (key === 'ruko') {
            return 'Ruko';
        }

        return 'Gerai Mandiri';
    }

    function formatRating(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed.toFixed(1) : '5.0';
    }

    function photoUrl(path) {
        if (!path) {
            return '';
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        return `${window.location.origin}/${String(path).replace(/^\/+/, '')}`;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTime(time) {
        if (!time) {
            return '-';
        }

        const parts = String(time).split(':');
        return parts.length >= 2 ? `${parts[0]}:${parts[1]}` : String(time);
    }

    function parseCoordinate(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function latLngFromItem(item) {
        const lat = parseCoordinate(item.latitude);
        const lng = parseCoordinate(item.longitude);

        if (lat === null || lng === null) {
            return null;
        }

        return [lat, lng];
    }

    function distanceKm(lat1, lng1, lat2, lng2) {
        const toRadians = (value) => value * (Math.PI / 180);
        const earthRadius = 6371;
        const dLat = toRadians(lat2 - lat1);
        const dLng = toRadians(lng2 - lng1);
        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLng / 2) ** 2;

        return 2 * earthRadius * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function formatDistance(distance) {
        if (!Number.isFinite(distance)) {
            return '-';
        }

        return distance < 1
            ? `${Math.round(distance * 1000)} m`
            : `${distance.toFixed(1)} km`;
    }

    function directionsUrl(item, origin) {
        const destination = `${item.latitude},${item.longitude}`;
        const params = new URLSearchParams({
            api: '1',
            destination,
            travelmode: 'driving',
        });

        if (origin && Number.isFinite(origin.lat) && Number.isFinite(origin.lng)) {
            params.set('origin', `${origin.lat},${origin.lng}`);
        }

        return `https://www.google.com/maps/dir/?${params.toString()}`;
    }

    async function fetchJson(url) {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }

        return response.json();
    }

    function createMap(targetId, options = {}) {
        const map = L.map(targetId, {
            center: options.center || [DEFAULT_CENTER.lat, DEFAULT_CENTER.lng],
            zoom: options.zoom || DEFAULT_ZOOM,
            zoomControl: options.zoomControl ?? true,
            scrollWheelZoom: options.scrollWheelZoom ?? true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        return map;
    }

    function createMarkerIcon(brand) {
        const color = brandColor(brand);
        const html = `
            <svg width="30" height="40" viewBox="0 0 30 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M15 0C6.716 0 0 6.716 0 15c0 10.248 15 25 15 25s15-14.752 15-25C30 6.716 23.284 0 15 0Z" fill="${color}"/>
                <circle cx="15" cy="15" r="5.5" fill="#ffffff"/>
                <circle cx="15" cy="15" r="2.5" fill="${color}"/>
            </svg>`;

        return L.divIcon({
            html,
            className: '',
            iconSize: [30, 40],
            iconAnchor: [15, 40],
            popupAnchor: [0, -36],
        });
    }

    function popupHtml(item, detailUrl) {
        return `
            <div class="map-popup">
                <span class="brand-badge" style="background:${brandColor(item.brand)}">${escapeHtml(item.brand)}</span>
                <h3 class="map-popup__title">${escapeHtml(item.nama_tempat)}</h3>
                <div class="map-popup__meta">
                    <span>${escapeHtml(item.alamat || '-')}</span>
                    <span>${escapeHtml(formatTime(item.jam_buka))} - ${escapeHtml(formatTime(item.jam_tutup))}</span>
                    <span>${escapeHtml(item.hari_operasional || '-')}</span>
                </div>
                <div class="map-popup__actions">
                    <a class="mini-button is-primary" href="${detailUrl}">Detail</a>
                    <a class="mini-button" href="${directionsUrl(item)}" target="_blank" rel="noopener noreferrer">Rute</a>
                </div>
            </div>
        `;
    }

    function renderMarkers(layer, data, detailBase, optionsOrOnSelect) {
        layer.clearLayers();
        const registry = new Map();
        const options = typeof optionsOrOnSelect === 'function'
            ? { onSelect: optionsOrOnSelect }
            : (optionsOrOnSelect || {});
        const popupMaxWidth = Number.isFinite(options.popupMaxWidth) ? options.popupMaxWidth : 280;

        data.forEach((item) => {
            const latLng = latLngFromItem(item);
            if (!latLng) {
                return;
            }

            const marker = L.marker(latLng, {
                icon: createMarkerIcon(item.brand),
            }).addTo(layer);

            const popupMarkup = typeof options.popupRenderer === 'function'
                ? options.popupRenderer(item, `${detailBase}/${item.id}`)
                : popupHtml(item, `${detailBase}/${item.id}`);

            marker.bindPopup(popupMarkup, { maxWidth: popupMaxWidth });
            marker.on('click', () => {
                if (typeof options.onSelect === 'function') {
                    options.onSelect(item);
                }
            });
            marker.on('popupopen', () => {
                if (typeof options.onPopupOpen === 'function') {
                    options.onPopupOpen(marker, item);
                }
            });
            registry.set(item.id, marker);
        });

        return registry;
    }

    function fitBounds(map, data, padding = [36, 36], maxZoom = 15) {
        const points = data
            .map(latLngFromItem)
            .filter(Boolean);

        if (!points.length) {
            return;
        }

        const bounds = L.latLngBounds(points);
        map.fitBounds(bounds, { padding, maxZoom });
    }

    function geoJsonContainsPoint(geojson, latitude, longitude) {
        if (!geojson) {
            return false;
        }

        let source = geojson;
        if (typeof source === 'string') {
            try {
                source = JSON.parse(source);
            } catch (error) {
                return false;
            }
        }

        if (!source || typeof source !== 'object') {
            return false;
        }

        const type = String(source.type || '').toLowerCase();
        if (type === 'featurecollection') {
            return (source.features || []).some((feature) => geoJsonContainsPoint(feature, latitude, longitude));
        }

        if (type === 'feature') {
            return geoJsonContainsPoint(source.geometry || null, latitude, longitude);
        }

        if (type === 'polygon') {
            return polygonContainsPoint(source.coordinates || [], latitude, longitude);
        }

        if (type === 'multipolygon') {
            return (source.coordinates || []).some((polygon) => polygonContainsPoint(polygon, latitude, longitude));
        }

        return false;
    }

    function polygonContainsPoint(rings, latitude, longitude) {
        if (!Array.isArray(rings) || !rings.length) {
            return false;
        }

        if (!pointInRing(rings[0], latitude, longitude)) {
            return false;
        }

        return !rings.slice(1).some((hole) => pointInRing(hole, latitude, longitude));
    }

    function pointInRing(ring, latitude, longitude) {
        if (!Array.isArray(ring) || ring.length < 3) {
            return false;
        }

        const x = longitude;
        const y = latitude;
        let inside = false;

        for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            const current = ring[i];
            const previous = ring[j];
            if (!Array.isArray(current) || !Array.isArray(previous)) {
                continue;
            }

            const xi = Number(current[0]);
            const yi = Number(current[1]);
            const xj = Number(previous[0]);
            const yj = Number(previous[1]);
            const intersects = ((yi > y) !== (yj > y))
                && (x < (((xj - xi) * (y - yi)) / ((yj - yi) || 0.0000000001)) + xi);

            if (intersects) {
                inside = !inside;
            }
        }

        return inside;
    }

    function serviceMatches(item, keys) {
        if (!keys.length) {
            return true;
        }

        const haystack = `${item.jenis_layanan || ''} ${item.estimasi_servis || ''}`.toLowerCase();

        return keys.every((key) => {
            if (key === 'garansi') {
                return haystack.includes('garansi');
            }

            if (key === 'hardware') {
                return haystack.includes('perbaikan') || haystack.includes('hardware');
            }

            if (key === 'parts') {
                return haystack.includes('sparepart') || haystack.includes('aksesoris') || haystack.includes('cadang');
            }

            return true;
        });
    }

    function toKecamatanOptions(data) {
        return [...new Set(data.map((item) => item.kecamatan).filter(Boolean))].sort((a, b) => a.localeCompare(b));
    }

    function initNav() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const topbar = document.querySelector('.topbar');

        if (!toggle || !topbar) {
            return;
        }

        toggle.addEventListener('click', () => {
            topbar.classList.toggle('is-open');
        });
    }

    document.addEventListener('DOMContentLoaded', initNav);

    return {
        BRAND_COLORS,
        DEFAULT_CENTER,
        DEFAULT_ZOOM,
        normalizeBrand,
        brandColor,
        locationTypeKey,
        locationTypeLabel,
        formatRating,
        photoUrl,
        escapeHtml,
        formatTime,
        parseCoordinate,
        latLngFromItem,
        distanceKm,
        formatDistance,
        directionsUrl,
        fetchJson,
        createMap,
        createMarkerIcon,
        renderMarkers,
        fitBounds,
        geoJsonContainsPoint,
        serviceMatches,
        toKecamatanOptions,
    };
})();
