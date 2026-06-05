<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Geografis Service Center Smartphone Kota Medan">
    <title>SIG Service Center Smartphone — Kota Medan</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --bg-glass: rgba(30, 41, 59, 0.85);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --accent-glow: rgba(99,102,241,0.25);
            --border: rgba(148,163,184,0.12);
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --samsung: #1428a0;
            --xiaomi: #ff6700;
            --oppo: #1a8a44;
            --vivo: #415fff;
            --apple: #a3aaae;
            --radius: 12px;
            --shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, var(--bg-secondary), #0f172a);
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            position: relative;
            backdrop-filter: blur(12px);
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-brand .logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
        }
        .header h1 {
            font-size: 16px; font-weight: 700;
            background: linear-gradient(135deg, #e2e8f0, #6366f1);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .header .subtitle { font-size: 11px; color: var(--text-muted); font-weight: 400; }
        .header-stats {
            display: flex; gap: 16px; font-size: 12px; color: var(--text-secondary);
        }
        .header-stats .stat-value {
            font-weight: 700; color: var(--accent-hover); margin-right: 4px;
        }

        /* ── LAYOUT ── */
        .app-layout {
            display: flex;
            height: 100vh;
            position: relative;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 58px;
            min-width: 58px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.25s ease, min-width 0.25s ease, background 0.25s ease;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 1003;
        }

        .sidebar.expanded {
            width: 340px;
            min-width: 280px;
        }

        .sidebar-panel {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease 0.05s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .sidebar.expanded .sidebar-panel {
            opacity: 1;
            pointer-events: auto;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 16px 14px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-brand .logo {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), #a855f7);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; color: #fff;
        }

        .sidebar-brand h1 {
            font-size: 14px;
            line-height: 1.2;
            font-weight: 700;
            color: var(--text-primary);
        }

        .sidebar-brand .subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 400;
        }

        .sidebar-hamburger {
            width: 46px;
            height: 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin: 16px auto;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.92);
            cursor: pointer;
            z-index: 1001;
        }

        .sidebar-hamburger span {
            width: 22px;
            height: 2px;
            background: var(--text-primary);
            border-radius: 999px;
            display: block;
        }

        .top-controls {
            position: absolute;
            top: 18px;
            left: 76px; /* 58px sidebar + 18px gap */
            right: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: none;
            z-index: 1002;
        }

        .top-controls .search-panel {
            pointer-events: all;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 999px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(12px);
            min-width: 200px;
            max-width: 280px;
            width: 280px;
            padding: 10px 16px;
            flex-shrink: 0;
        }

        .top-controls .filter-panel {
            pointer-events: all;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 0;
            background: transparent;
        }

        .top-controls .filter-panel .chip {
            margin: 0;
            padding: 8px 12px;
            min-height: 34px;
            font-size: 13px;
            border-radius: 999px;
            min-width: auto;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: #0f172a;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .top-controls .filter-panel .chip.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.18);
        }

        .top-controls .filter-panel .chip:hover {
            background: rgba(15, 23, 42, 0.08);
        }

        .top-controls .brand-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .top-controls .brand-all .brand-icon { background: #64748b; }
        .top-controls .brand-Samsung .brand-icon { background: var(--samsung); }
        .top-controls .brand-Xiaomi .brand-icon { background: var(--xiaomi); }
        .top-controls .brand-Oppo .brand-icon { background: var(--oppo); }
        .top-controls .brand-Vivo .brand-icon { background: var(--vivo); }
        .top-controls .brand-Apple .brand-icon { background: var(--apple); color: #1e293b; }

        .top-controls .search-panel .search-box {
            position: relative;
            margin-bottom: 0;
        }

        .top-controls .search-panel .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px;
        }

        .top-controls .search-panel input {
            width: 100%;
            padding: 10px 16px 10px 44px;
            background: transparent;
            border: none;
            color: #0f172a;
            font-size: 14px;
            outline: none;
        }

        .top-controls .search-panel input::placeholder {
            color: #64748b;
        }

        .leaflet-bottom.leaflet-right .leaflet-control-zoom,
        .leaflet-bottom.leaflet-right .leaflet-control-locate {
            margin-bottom: 8px;
        }

        .leaflet-control-zoom a,
        .leaflet-control-locate a {
            width: 32px;
            height: 32px;
            line-height: 32px;
            font-size: 16px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
        }

        .leaflet-control-locate a {
            padding: 0;
        }

        .leaflet-control-locate .leaflet-control-locate-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .top-controls .search-box {
            position: relative;
            margin-bottom: 0;
        }

        /* ── SEARCH & FILTER ── */
        .controls {
            padding: 16px;
            border-bottom: 1px solid var(--border);
        }
        .search-box {
            position: relative;
            margin-bottom: 12px;
        }
        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: border 0.2s;
        }
        .search-box input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }
        .search-box input::placeholder { color: var(--text-muted); }
        .search-box .search-icon {
            position: absolute;
            left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px;
        }

        .filter-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-secondary);
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .chip:hover { border-color: var(--accent); color: var(--text-primary); }
        .chip.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .chip.active[data-brand="Samsung"] { background: var(--samsung); border-color: var(--samsung); }
        .chip.active[data-brand="Xiaomi"]  { background: var(--xiaomi); border-color: var(--xiaomi); }
        .chip.active[data-brand="Oppo"]    { background: var(--oppo); border-color: var(--oppo); }
        .chip.active[data-brand="Vivo"]    { background: var(--vivo); border-color: var(--vivo); }
        .chip.active[data-brand="Apple"]   { background: var(--apple); border-color: var(--apple); color: #1e293b; }

        /* ── LIST ── */
        .list-container {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }
        .list-container::-webkit-scrollbar { width: 4px; }
        .list-container::-webkit-scrollbar-track { background: transparent; }
        .list-container::-webkit-scrollbar-thumb { background: var(--text-muted); border-radius: 4px; }

        .sc-card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sc-card:hover {
            border-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent-glow);
            transform: translateY(-1px);
        }
        .sc-card.active {
            border-color: var(--accent);
            background: rgba(99,102,241,0.08);
        }
        .sc-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .sc-card-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.3;
            flex: 1;
        }
        .brand-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
            margin-left: 8px;
        }
        .brand-Samsung { background: rgba(20,40,160,0.2); color: #6d7fff; }
        .brand-Xiaomi  { background: rgba(255,103,0,0.15); color: #ff8c3a; }
        .brand-Oppo    { background: rgba(26,138,68,0.2); color: #4ade80; }
        .brand-Vivo    { background: rgba(65,95,255,0.2); color: #818cf8; }
        .brand-Apple   { background: rgba(163,170,174,0.2); color: #cbd5e1; }

        .sc-card-info { font-size: 11px; color: var(--text-muted); line-height: 1.6; }
        .sc-card-info span { display: flex; align-items: center; gap: 6px; }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
        .empty-state .icon { font-size: 40px; margin-bottom: 12px; }
        .empty-state .title { font-size: 14px; font-weight: 600; color: var(--text-secondary); }
        .empty-state .desc { font-size: 12px; margin-top: 4px; }

        /* ── MAP ── */
        .map-container {
            flex: 1;
            position: relative;
        }
        .map-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 1001;
        }
        .sidebar.expanded ~ .map-container::after {
            opacity: 1;
        }
        #map { width: 100%; height: 100%; z-index: 1; }

        /* Leaflet popup override */
        .leaflet-popup-content-wrapper {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-radius: var(--radius) !important;
            box-shadow: var(--shadow) !important;
            border: 1px solid var(--border);
        }
        .leaflet-popup-tip { background: var(--bg-card) !important; }
        .leaflet-popup-content { margin: 14px !important; font-family: 'Inter', sans-serif !important; }
        .popup-title { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .popup-brand {
            display: inline-block;
            font-size: 10px; font-weight: 600;
            padding: 2px 8px; border-radius: 4px;
            margin-bottom: 8px;
        }
        .popup-info { font-size: 12px; color: var(--text-secondary); line-height: 1.7; }
        .popup-info strong { color: var(--text-primary); }

        /* ── LOADING OVERLAY ── */
        .loading-overlay {
            position: fixed; inset: 0;
            background: var(--bg-primary);
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; gap: 16px;
            z-index: 9999;
            transition: opacity 0.5s;
        }
        .loading-overlay.hidden { opacity: 0; pointer-events: none; }
        .spinner {
            width: 40px; height: 40px;
            border: 3px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 13px; color: var(--text-muted); }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .sidebar { width: 100%; min-width: 100%; position: absolute; z-index: 998; height: 100vh; }
            .sidebar.expanded { width: 100%; min-width: 100%; }
            .header h1 { font-size: 14px; }
            .header-stats { display: none; }
            .top-controls { left: 18px; right: 18px; max-width: none; }
            .top-controls .search-panel,
            .top-controls .filter-panel {
                position: static;
                width: auto;
                max-width: 100%;
            }
            .top-controls .filter-panel { margin-top: 10px; justify-content: flex-start; }
        }
    </style>
</head>
<body>

<!-- Loading -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Memuat peta service center...</div>
</div>

<!-- Layout -->
<div class="app-layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-hamburger" id="sidebarToggle" title="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="sidebar-panel">
            <div class="sidebar-brand">
                <div class="logo">SC</div>
                <div>
                    <h1>SIG Service Center Smartphone</h1>
                    <div class="subtitle">Kota Medan — Pemetaan Lokasi Resmi</div>
                </div>
            </div>
            <div class="list-container" id="listContainer">
                <!-- Cards rendered by JS -->
            </div>
        </div>
    </aside>

    <!-- Map -->
    <div class="map-container">
        <div class="top-controls">
            <div class="search-panel">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Cari alamat service center...">
                </div>
            </div>
            <div class="filter-panel">
                <button class="chip active brand-all" data-brand="all" id="chipAll"><span class="brand-icon">★</span>Semua</button>
                <button class="chip brand-Samsung" data-brand="Samsung"><span class="brand-icon">S</span>Samsung</button>
                <button class="chip brand-Xiaomi" data-brand="Xiaomi"><span class="brand-icon">X</span>Xiaomi</button>
                <button class="chip brand-Oppo" data-brand="Oppo"><span class="brand-icon">O</span>Oppo</button>
                <button class="chip brand-Vivo" data-brand="Vivo"><span class="brand-icon">V</span>Vivo</button>
                <button class="chip brand-Apple" data-brand="Apple"><span class="brand-icon">A</span>Apple</button>
            </div>
        </div>
        <div id="map"></div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function() {
    'use strict';

    // ── CONFIG ──
    const API_BASE   = '/api/service-center';
    const MEDAN_CENTER = [-0.0275 + 3.5920, 98.6787]; // [lat, lng]
    const DEFAULT_ZOOM = 13;

    const BRAND_COLORS = {
        Samsung: '#1428a0',
        Xiaomi:  '#ff6700',
        Oppo:    '#1a8a44',
        Vivo:    '#415fff',
        Apple:   '#a3aaae',
    };

    // ── STATE ──
    let allData     = [];
    let markers     = [];
    let activeCard  = null;
    let activeBrand = 'all';
    let map, markerGroup;

    // ── INIT MAP ──
    map = L.map('map', {
        center: MEDAN_CENTER,
        zoom: DEFAULT_ZOOM,
        zoomControl: false,
    });

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const LocateControl = L.Control.extend({
        options: { position: 'bottomright' },
        onAdd: function() {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-locate');
            const button = L.DomUtil.create('a', 'leaflet-control-locate-button', container);
            button.href = '#';
            button.title = 'Lokasi saya';
            button.innerHTML = '⌖';

            L.DomEvent.on(button, 'click', L.DomEvent.stopPropagation)
                .on(button, 'click', L.DomEvent.preventDefault)
                .on(button, 'click', () => {
                    map.locate({ setView: true, maxZoom: 16 });
                });

            return container;
        },
    });

    map.addControl(new LocateControl());

    map.on('locationfound', function(e) {
        L.circleMarker(e.latlng, {
            radius: 8,
            color: '#2563eb',
            fillColor: '#60a5fa',
            fillOpacity: 0.4,
        }).addTo(map);
    });

    map.on('locationerror', function() {
        console.warn('Lokasi tidak tersedia.');
    });

    // Dark tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="https://osm.org/copyright">OSM</a>',
        maxZoom: 19,
    }).addTo(map);

    markerGroup = L.layerGroup().addTo(map);

    // ── CUSTOM MARKER ──
    function createIcon(brand) {
        const color = BRAND_COLORS[brand] || '#6366f1';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="40" viewBox="0 0 28 40">
            <path d="M14 0C6.27 0 0 6.27 0 14c0 10.5 14 26 14 26s14-15.5 14-26C28 6.27 21.73 0 14 0z" fill="${color}" stroke="#fff" stroke-width="1.5"/>
            <circle cx="14" cy="14" r="6" fill="#fff" opacity="0.9"/>
        </svg>`;
        return L.divIcon({
            html: svg,
            className: '',
            iconSize: [28, 40],
            iconAnchor: [14, 40],
            popupAnchor: [0, -40],
        });
    }

    // ── POPUP CONTENT ──
    function popupHTML(item) {
        const brandClass = `brand-${item.brand}`;
        return `
            <div class="popup-title">${item.nama_tempat}</div>
            <span class="popup-brand ${brandClass}">${item.brand}</span>
            <div class="popup-info">
                <div>📍 ${item.alamat || '-'}</div>
                <div>🕐 ${formatTime(item.jam_buka)} – ${formatTime(item.jam_tutup)}</div>
                <div>📅 ${item.hari_operasional || '-'}</div>
                <div>📞 ${item.no_telepon || '-'}</div>
                ${item.jenis_layanan ? `<div>🔧 ${item.jenis_layanan}</div>` : ''}
                ${item.estimasi_servis ? `<div>⏱ Est: ${item.estimasi_servis}</div>` : ''}
            </div>
        `;
    }

    function formatTime(t) {
        if (!t) return '-';
        return t.substring(0, 5); // "09:00:00" → "09:00"
    }

    // ── RENDER MARKERS ──
    function renderMarkers(data) {
        markerGroup.clearLayers();
        markers = [];

        data.forEach(item => {
            if (!item.latitude || !item.longitude) return;
            const lat = parseFloat(item.latitude);
            const lng = parseFloat(item.longitude);
            const marker = L.marker([lat, lng], { icon: createIcon(item.brand) })
                .bindPopup(popupHTML(item), { maxWidth: 300 })
                .addTo(markerGroup);

            marker._scId = item.id;
            marker.on('click', () => highlightCard(item.id));
            markers.push({ id: item.id, marker });
        });

        const totalCountElement = document.getElementById('totalCount');
        if (totalCountElement) {
            totalCountElement.textContent = data.length;
        }

        const brandCountElement = document.getElementById('brandCount');
        if (brandCountElement) {
            const uniqueBrands = new Set(data.map(item => item.brand)).size;
            brandCountElement.textContent = uniqueBrands;
        }
    }

    // ── RENDER LIST ──
    function renderList(data) {
        const container = document.getElementById('listContainer');

        if (data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="icon">📍</div>
                    <div class="title">Tidak ada data</div>
                    <div class="desc">Coba ubah filter atau kata kunci pencarian</div>
                </div>`;
            return;
        }

        container.innerHTML = data.map(item => `
            <div class="sc-card" data-id="${item.id}" onclick="window.__zoomTo(${item.id})">
                <div class="sc-card-header">
                    <div class="sc-card-name">${item.nama_tempat}</div>
                    <span class="brand-badge brand-${item.brand}">${item.brand}</span>
                </div>
                <div class="sc-card-info">
                    <span>📍 ${item.alamat || '-'}</span>
                    <span>🕐 ${formatTime(item.jam_buka)} – ${formatTime(item.jam_tutup)} · ${item.hari_operasional || '-'}</span>
                    <span>📞 ${item.no_telepon || '-'}</span>
                </div>
            </div>
        `).join('');
    }

    // ── ZOOM TO MARKER ──
    window.__zoomTo = function(id) {
        const found = markers.find(m => m.id == id);
        if (!found) return;
        const ll = found.marker.getLatLng();
        map.flyTo(ll, 16, { duration: 0.8 });
        found.marker.openPopup();
        highlightCard(id);
    };

    function highlightCard(id) {
        document.querySelectorAll('.sc-card').forEach(c => c.classList.remove('active'));
        const card = document.querySelector(`.sc-card[data-id="${id}"]`);
        if (card) {
            card.classList.add('active');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ── FETCH DATA ──
    async function fetchData(url) {
        try {
            const res = await fetch(url);
            const json = await res.json();
            return json.data || [];
        } catch (err) {
            console.error('Fetch error:', err);
            return [];
        }
    }

    async function loadAll() {
        allData = await fetchData(API_BASE);
        renderMarkers(allData);
        renderList(allData);
    }

    // ── FILTER BY BRAND ──
    document.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', async () => {
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeBrand = chip.dataset.brand;

            // Clear search
            document.getElementById('searchInput').value = '';

            let data;
            if (activeBrand === 'all') {
                data = allData.length ? allData : await fetchData(API_BASE);
                if (!allData.length) allData = data;
            } else {
                data = await fetchData(`${API_BASE}/filter?brand=${activeBrand}`);
            }
            renderMarkers(data);
            renderList(data);

            // Fit bounds
            if (data.length) {
                const bounds = L.latLngBounds(data.map(d => [parseFloat(d.latitude), parseFloat(d.longitude)]));
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            }
        });
    });

    // ── SEARCH ──
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const q = this.value.trim();

        searchTimeout = setTimeout(async () => {
            // Reset brand filter
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            document.getElementById('chipAll').classList.add('active');
            activeBrand = 'all';

            let data;
            if (q.length < 2) {
                data = allData.length ? allData : await fetchData(API_BASE);
            } else {
                data = await fetchData(`${API_BASE}/search?alamat=${encodeURIComponent(q)}`);
            }
            renderMarkers(data);
            renderList(data);

            if (data.length) {
                const bounds = L.latLngBounds(data.map(d => [parseFloat(d.latitude), parseFloat(d.longitude)]));
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            }
        }, 400);
    });

    // ── SIDEBAR TOGGLE ──
    const sidebar = document.getElementById('sidebar');
    const toggle  = document.getElementById('sidebarToggle');
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
        setTimeout(() => map.invalidateSize(), 350);
    });

    // ── INIT ──
    loadAll().then(() => {
        setTimeout(() => {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.add('hidden');
            setTimeout(() => overlay.style.display = 'none', 500);
        }, 600);
    });

})();
</script>
</body>
</html>
