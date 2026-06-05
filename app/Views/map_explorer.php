<?php $searchValue = service('request')->getGet('search') ?? ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Peta Service Center - GeoSC Medan',
        'description' => 'Jelajahi persebaran service center resmi smartphone di Medan dengan filter brand, kecamatan, rating, dan tipe lokasi.',
    ]) ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css">
    <style>
        body.map-page .page-shell {
            padding-top: 20px;
            padding-bottom: 28px;
        }

        body.map-page .explorer-shell.explorer-shell--split {
            display: grid;
            grid-template-columns: minmax(360px, 430px) minmax(0, 1fr);
            align-items: stretch;
            gap: 24px;
        }

        body.map-page .explorer-sidebar {
            min-height: calc(100vh - 132px);
            max-height: calc(100vh - 132px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0;
        }

        body.map-page .explorer-sidebar__scroll {
            min-height: 0;
            height: 100%;
            overflow: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        body.map-page .explorer-sidebar__sticky {
            position: sticky;
            top: -16px;
            z-index: 3;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin: -16px -16px 0;
            padding: 16px 16px 0;
            background: linear-gradient(180deg, rgba(246, 248, 252, 0.98) 0%, rgba(246, 248, 252, 0.96) 84%, rgba(246, 248, 252, 0) 100%);
            backdrop-filter: blur(12px);
        }

        body.map-page .sidebar-searchbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
        }

        body.map-page .sidebar-searchbar .input-field,
        body.map-page .sidebar-searchbar .btn {
            min-height: 44px;
        }

        body.map-page .advanced-filter-toggle {
            white-space: nowrap;
            padding-inline: 14px;
        }

        body.map-page .advanced-filters {
            display: grid;
            gap: 12px;
            padding: 14px;
            border: 1px solid rgba(35, 78, 226, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.78);
        }

        body.map-page .advanced-filters[hidden] {
            display: none;
        }

        body.map-page .advanced-filters__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        body.map-page .explorer-filter-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        body.map-page .explorer-filter-grid .filter-group--full,
        body.map-page .explorer-filter-grid .location-summary,
        body.map-page .explorer-filter-grid .action-row {
            grid-column: 1 / -1;
        }

        body.map-page .filter-group {
            gap: 8px;
        }

        body.map-page .filter-group label {
            font-size: 0.75rem;
            letter-spacing: 0.04em;
        }

        body.map-page .explorer-sidebar .input-field,
        body.map-page .explorer-sidebar .select-field {
            min-height: 42px;
            padding: 0 12px;
            font-size: 0.92rem;
        }

        body.map-page .explorer-sidebar .chip-list {
            gap: 8px;
            row-gap: 8px;
        }

        body.map-page .explorer-sidebar .chip-button {
            padding: 8px 12px;
            font-size: 0.8rem;
        }

        body.map-page .explorer-sidebar .stats-grid {
            gap: 10px;
        }

        body.map-page .explorer-sidebar .stat-card {
            padding: 14px;
        }

        body.map-page .explorer-sidebar .stat-card__value {
            font-size: 1.45rem;
        }

        body.map-page .explorer-sidebar .stat-card__hint {
            font-size: 0.8rem;
        }

        body.map-page .radius-control {
            display: grid;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 16px;
            background: rgba(35, 78, 226, 0.06);
            border: 1px solid rgba(35, 78, 226, 0.12);
        }

        body.map-page .radius-control__header,
        body.map-page .radius-control__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        body.map-page .radius-control__label {
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #53617d;
        }

        body.map-page .radius-control__value {
            font-size: 0.88rem;
            font-weight: 700;
            color: #16325c;
        }

        body.map-page .radius-control input[type="range"] {
            width: 100%;
            accent-color: #234ee2;
        }

        body.map-page .location-summary {
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(35, 78, 226, 0.08);
        }

        body.map-page .location-summary__value {
            margin-top: 6px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        body.map-page .action-row {
            gap: 8px;
            justify-content: flex-start;
        }

        body.map-page .action-row .btn {
            min-height: 40px;
            padding: 0 14px;
            font-size: 0.88rem;
        }

        body.map-page .explorer-results__body {
            min-height: 0;
            display: grid;
            gap: 14px;
            flex: 1 1 auto;
        }

        body.map-page .explorer-results__summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        body.map-page #mapResults {
            display: grid;
            gap: 10px;
        }

        body.map-page #mapResults .result-card {
            padding: 14px;
            border-radius: 16px;
        }

        body.map-page #mapResults .brand-badge {
            padding: 5px 10px;
            font-size: 0.72rem;
        }

        body.map-page #mapResults .result-card__title {
            font-size: 0.95rem;
        }

        body.map-page #mapResults .result-card__meta {
            margin-top: 8px;
            gap: 6px;
            font-size: 0.82rem;
        }

        body.map-page #mapResults .result-card__actions {
            margin-top: 12px;
            gap: 8px;
        }

        body.map-page #mapResults .mini-button {
            min-height: 34px;
            padding: 0 12px;
            font-size: 0.8rem;
        }

        body.map-page .result-skeleton {
            display: grid;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(35, 78, 226, 0.08);
        }

        body.map-page .result-skeleton__line {
            height: 12px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(220, 228, 241, 0.72) 25%, rgba(244, 247, 252, 0.96) 50%, rgba(220, 228, 241, 0.72) 75%);
            background-size: 200% 100%;
            animation: sidebarShimmer 1.25s linear infinite;
        }

        body.map-page .result-skeleton__line--short {
            width: 34%;
        }

        body.map-page .result-skeleton__line--title {
            width: 78%;
            height: 16px;
        }

        body.map-page .result-skeleton__line--medium {
            width: 62%;
        }

        body.map-page .result-skeleton__actions {
            display: flex;
            gap: 8px;
            margin-top: 2px;
        }

        body.map-page .result-skeleton__button {
            flex: 1 1 0;
            height: 34px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(220, 228, 241, 0.72) 25%, rgba(244, 247, 252, 0.96) 50%, rgba(220, 228, 241, 0.72) 75%);
            background-size: 200% 100%;
            animation: sidebarShimmer 1.25s linear infinite;
        }

        @keyframes sidebarShimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        body.map-page .explorer-results .pill,
        body.map-page .explorer-sidebar .pill {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        body.map-page .radius-control .pill {
            padding: 5px 9px;
            font-size: 0.75rem;
        }

        body.map-page .map-stage {
            --detail-panel-width: clamp(320px, 44%, 520px);
            position: relative;
            overflow: hidden;
            min-height: calc(100vh - 132px);
            max-height: calc(100vh - 132px);
            padding: 16px;
        }

        body.map-page .map-stage__canvas {
            height: 100%;
            min-height: calc(100vh - 164px);
            max-height: calc(100vh - 164px);
        }

        body.map-page .route-summary {
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 24px;
            z-index: 450;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto auto;
            gap: 12px;
            align-items: center;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(35, 78, 226, 0.1);
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(12px);
            box-shadow: 0 16px 32px rgba(22, 50, 92, 0.14);
        }

        body.map-page .route-summary__copy {
            min-width: 0;
        }

        body.map-page .route-summary__title {
            margin: 0;
            font-size: 0.95rem;
            color: #16325c;
        }

        body.map-page .route-summary__text {
            margin: 6px 0 0;
            font-size: 0.84rem;
            line-height: 1.5;
            color: #60708d;
        }

        body.map-page .route-metric {
            min-width: 104px;
            text-align: center;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(35, 78, 226, 0.06);
        }

        body.map-page .route-metric__value {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            color: #16325c;
        }

        body.map-page .route-metric__label {
            display: block;
            margin-top: 4px;
            font-size: 0.74rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #60708d;
        }

        body.map-page .route-summary__close {
            width: 38px;
            min-width: 38px;
            height: 38px;
            border: 0;
            border-radius: 12px;
            background: rgba(22, 50, 92, 0.08);
            color: #16325c;
            font-size: 1.15rem;
            line-height: 1;
        }

        body.map-page .route-summary__close[hidden] {
            display: none;
        }

        body.map-page .route-summary[hidden] {
            display: none;
        }

        body.map-page .map-stage.is-detail-open .route-summary {
            left: calc(24px + var(--detail-panel-width) + 20px);
        }

        body.map-page .map-detail-panel {
            position: absolute;
            top: 22px;
            bottom: 22px;
            left: 22px;
            z-index: 430;
            width: var(--detail-panel-width);
            border-radius: 26px;
            overflow: hidden;
            border: 1px solid rgba(35, 78, 226, 0.14);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            box-shadow: 0 22px 48px rgba(17, 39, 78, 0.18);
        }

        body.map-page .map-detail-panel[hidden] {
            display: none;
        }

        body.map-page .map-detail-panel__scroll {
            position: relative;
            height: 100%;
            overflow: auto;
        }

        body.map-page .map-detail-panel__stickybar {
            position: sticky;
            top: 0;
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            margin-bottom: -72px;
            background: linear-gradient(180deg, rgba(11, 26, 51, 0.76) 0%, rgba(11, 26, 51, 0.48) 58%, rgba(11, 26, 51, 0) 100%);
            backdrop-filter: blur(8px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-10px);
            transition: opacity 180ms ease, transform 180ms ease, visibility 180ms ease;
        }

        body.map-page .map-detail-panel__stickybar.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        body.map-page .map-detail-panel__sticky-title {
            min-width: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.28);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.map-page .map-detail-panel__sticky-close {
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            font-size: 1.2rem;
            line-height: 1;
            box-shadow: 0 10px 22px rgba(8, 18, 39, 0.22);
        }

        body.map-page .map-detail-panel__media {
            position: relative;
            min-height: 240px;
            background:
                linear-gradient(180deg, rgba(11, 26, 51, 0.08) 0%, rgba(11, 26, 51, 0.48) 100%),
                linear-gradient(135deg, #2249d8 0%, #66c6ff 100%);
        }

        body.map-page .map-detail-panel__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        body.map-page .map-detail-panel__placeholder {
            height: 100%;
            display: grid;
            place-items: center;
            padding: 24px;
            text-align: center;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        body.map-page .map-detail-panel__close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 2;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 14px;
            background: rgba(11, 26, 51, 0.62);
            color: #ffffff;
            font-size: 1.35rem;
            line-height: 1;
            box-shadow: 0 12px 26px rgba(8, 18, 39, 0.24);
        }

        body.map-page .map-detail-panel__body {
            padding: 20px 20px 18px;
            display: grid;
            gap: 18px;
        }

        body.map-page .map-detail-panel__intro {
            display: grid;
            gap: 12px;
        }

        body.map-page .map-detail-panel__eyebrow {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        body.map-page .map-detail-panel__eyebrow-main {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        body.map-page .map-detail-panel__eyebrow-action .btn {
            min-height: 38px;
            padding-inline: 14px;
            font-size: 0.82rem;
        }

        body.map-page .map-detail-route-icon {
            margin-right: 6px;
            font-size: 0.92rem;
            line-height: 1;
        }

        body.map-page .detail-rating-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 196, 79, 0.16);
            color: #8a5a00;
            font-size: 0.82rem;
            font-weight: 700;
        }

        body.map-page .detail-rating-pill__star {
            color: #f3a000;
            font-size: 0.92rem;
            line-height: 1;
        }

        body.map-page .map-detail-panel__title {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.2;
            color: #16325c;
        }

        body.map-page .map-detail-panel__address {
            margin: 0;
            font-size: 0.94rem;
            line-height: 1.6;
            color: #5e6d8a;
        }

        body.map-page .map-detail-panel__section {
            display: grid;
            gap: 12px;
        }

        body.map-page .map-detail-panel__section h3 {
            margin: 0;
            font-size: 0.92rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #4f6080;
        }

        body.map-page .map-detail-panel__grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        body.map-page .map-detail-panel__fact {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(35, 78, 226, 0.06);
            border: 1px solid rgba(35, 78, 226, 0.08);
        }

        body.map-page .map-detail-panel__fact-icon {
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(35, 78, 226, 0.1);
            color: #234ee2;
            font-size: 1rem;
            line-height: 1;
        }

        body.map-page .map-detail-panel__fact-copy {
            min-width: 0;
        }

        body.map-page .map-detail-panel__fact-label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #60708d;
        }

        body.map-page .map-detail-panel__fact-value {
            display: block;
            margin-top: 6px;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #16325c;
            font-weight: 600;
        }

        body.map-page .map-detail-panel__services {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        body.map-page .map-detail-panel__services .feature-pill {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(22, 50, 92, 0.08);
            color: #16325c;
            font-size: 0.82rem;
            line-height: 1.4;
        }

        @media (max-width: 1280px) {
            body.map-page .explorer-shell.explorer-shell--split {
                grid-template-columns: minmax(330px, 400px) minmax(0, 1fr);
                gap: 20px;
            }

            body.map-page .explorer-sidebar {
                min-height: calc(100vh - 140px);
                max-height: calc(100vh - 140px);
            }

            body.map-page .map-stage {
                min-height: calc(100vh - 140px);
                max-height: calc(100vh - 140px);
            }

            body.map-page .map-stage__canvas {
                min-height: calc(100vh - 172px);
                max-height: calc(100vh - 172px);
            }
        }

        @media (max-width: 1120px) {
            body.map-page .explorer-shell.explorer-shell--split {
                grid-template-columns: minmax(310px, 360px) minmax(0, 1fr);
                gap: 18px;
            }

            body.map-page .explorer-sidebar {
                min-height: calc(100vh - 148px);
                max-height: calc(100vh - 148px);
            }

            body.map-page .map-stage {
                min-height: calc(100vh - 148px);
                max-height: calc(100vh - 148px);
            }

            body.map-page .map-stage__canvas {
                min-height: calc(100vh - 180px);
                max-height: calc(100vh - 180px);
            }

            body.map-page .map-stage {
                --detail-panel-width: clamp(300px, 48%, 420px);
            }

            body.map-page .explorer-filter-grid,
            body.map-page .sidebar-searchbar {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 960px) {
            body.map-page .explorer-shell.explorer-shell--split {
                grid-template-columns: 1fr;
            }

            body.map-page .explorer-sidebar,
            body.map-page .map-stage {
                min-height: auto;
                max-height: none;
            }

            body.map-page .explorer-sidebar__scroll {
                overflow: visible;
            }

            body.map-page .explorer-sidebar__sticky {
                position: static;
                top: auto;
                margin: 0;
                padding: 0;
                background: transparent;
                backdrop-filter: none;
            }

            body.map-page .map-stage__canvas {
                min-height: 420px;
                max-height: none;
            }

            body.map-page .map-detail-panel {
                top: 16px;
                right: 16px;
                bottom: 16px;
                left: 16px;
                width: auto;
            }

            body.map-page .route-summary {
                left: 16px;
                right: 16px;
                bottom: 16px;
                grid-template-columns: 1fr;
            }

            body.map-page .map-stage.is-detail-open .route-summary {
                left: 16px;
            }

            body.map-page .route-metric {
                min-width: 0;
                text-align: left;
            }

            body.map-page .map-detail-panel__grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="map-page">
<div class="app-shell">
    <?= view('partials/gis_nav', ['current' => 'map', 'searchValue' => $searchValue]) ?>

    <main class="page-shell">
        <div class="explorer-shell explorer-shell--split">
            <aside class="panel explorer-sidebar">
                <div class="explorer-sidebar__scroll">
                    <div class="explorer-sidebar__sticky">
                        <div class="sidebar-searchbar">
                            <input class="input-field" id="keywordInput" type="search" placeholder="Cari nama service center atau alamat..." value="<?= esc($searchValue) ?>">
                            <button class="btn btn--ghost advanced-filter-toggle" id="advancedFilterToggle" type="button" aria-expanded="false" aria-controls="advancedFilterPanel">Filter Lanjutan</button>
                        </div>

                        <section class="advanced-filters" id="advancedFilterPanel" hidden>
                            <div class="advanced-filters__header">
                                <div><h2 class="control-panel__title" style="font-size:1rem;">Filter Lanjutan</h2></div>
                                <button class="btn btn--ghost" id="resetFilters" type="button">Reset</button>
                            </div>

                            <div class="explorer-filter-grid">
                                <div class="filter-group" id="brand-filter">
                                    <label for="brandSelect">Brand smartphone</label>
                                    <select class="select-field" id="brandSelect">
                                        <option value="all">Semua Brand</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="districtSelect">Kecamatan</label>
                                    <select class="select-field" id="districtSelect">
                                        <option value="all">Semua Kecamatan</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="ratingSelect">Minimum rating</label>
                                    <select class="select-field" id="ratingSelect">
                                        <option value="0">Semua rating</option>
                                        <option value="4">4.0 ke atas</option>
                                        <option value="4.5">4.5 ke atas</option>
                                        <option value="5">5.0 saja</option>
                                    </select>
                                </div>

                                <div class="filter-group filter-group--full">
                                    <label>Tipe lokasi</label>
                                    <div class="chip-list" id="typeFilterList">
                                        <button class="chip-button is-active" type="button" data-type="all">Semua</button>
                                        <button class="chip-button" type="button" data-type="mall">Mall</button>
                                        <button class="chip-button" type="button" data-type="ruko">Ruko</button>
                                        <button class="chip-button" type="button" data-type="gerai-mandiri">Gerai Mandiri</button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Radius control removed from /peta sidebar per request -->
                    </div>

                    <div class="explorer-results__body">
                        <div class="explorer-results__summary">
                            <div><h2 class="control-panel__title" style="font-size:1rem;">Lokasi Service Center</h2></div>
                            <span class="pill" id="listResultCount">0 hasil</span>
                        </div>
                        <div class="results-stack" id="mapResults">
                            <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                            <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                            <div class="result-skeleton"><div class="result-skeleton__line result-skeleton__line--short"></div><div class="result-skeleton__line result-skeleton__line--title"></div><div class="result-skeleton__line"></div><div class="result-skeleton__line result-skeleton__line--medium"></div><div class="result-skeleton__actions"><div class="result-skeleton__button"></div><div class="result-skeleton__button"></div></div></div>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="panel map-stage">
                <div class="map-stage__overlay">
                    <div class="metric-ribbon">
                        <div class="metric-pill">
                            <span class="metric-pill__value" id="overlayCount">0</span>
                            <span class="metric-pill__label">Lokasi terlihat</span>
                        </div>
                        <div class="metric-pill">
                            <span class="metric-pill__value" id="overlayDistricts">0</span>
                            <span class="metric-pill__label">Kecamatan aktif</span>
                        </div>
                        <div class="metric-pill">
                            <span class="metric-pill__value" id="overlayNearest">-</span>
                            <span class="metric-pill__label">Terdekat dari Anda</span>
                        </div>
                    </div>

                </div>

                <div id="explorerMapPage" class="map-stage__canvas"></div>

                <aside class="panel legend-panel" id="legendPanel">
                    <div class="legend-panel__header">
                        <strong>Legenda</strong>
                        <button class="legend-panel__toggle" id="legendToggle" type="button" aria-expanded="true" aria-controls="legendPanelBody" aria-label="Tutup legenda">−</button>
                    </div>
                    <div class="legend-panel__body" id="legendPanelBody">
                        <div class="legend-list legend-list--map">
                            <span><span class="legend-line legend-line--route"></span> Rute navigasi</span>
                            <span><span class="legend-line legend-line--city"></span> Polygon Kota Medan</span>
                            <span><span class="legend-line legend-line--district"></span> Polygon kecamatan</span>
                            <span><span class="legend-marker legend-marker--location"></span> Marker service center</span>
                            <span><span class="legend-marker legend-marker--position"></span> Posisi pengguna</span>
                        </div>
                    </div>
                </aside>

                <section class="map-detail-panel" id="mapDetailPanel" hidden>
                    <div class="map-detail-panel__scroll" id="mapDetailScroll">
                        <div class="map-detail-panel__stickybar" id="mapDetailStickybar">
                            <div class="map-detail-panel__sticky-title" id="mapDetailStickyTitle">Detail Service Center</div>
                            <button class="map-detail-panel__sticky-close" id="closeDetailPanelSticky" type="button" aria-label="Tutup detail">&times;</button>
                        </div>
                        <div class="map-detail-panel__media" id="mapDetailMedia">
                            <div class="map-detail-panel__placeholder"></div>
                            <button class="map-detail-panel__close" id="closeDetailPanel" type="button" aria-label="Tutup detail">&times;</button>
                        </div>
                        <div class="map-detail-panel__body">
                            <div class="map-detail-panel__intro">
                                <div class="map-detail-panel__eyebrow" id="mapDetailEyebrow"></div>
                                <div>
                                    <h2 class="map-detail-panel__title" id="mapDetailTitle">Detail Service Center</h2>
                                    <p class="map-detail-panel__address" id="mapDetailAddress">Pilih lokasi dari daftar atau marker di peta.</p>
                                </div>
                            </div>

                            <section class="map-detail-panel__section">
                                <h3>Informasi Utama</h3>
                                <div class="map-detail-panel__grid" id="mapDetailFacts"></div>
                            </section>

                            <section class="map-detail-panel__section">
                                <h3>Layanan Tersedia</h3>
                                <div class="map-detail-panel__services" id="mapDetailServices"></div>
                            </section>

                        </div>
                    </div>
                </section>

                <section class="route-summary" id="routeSummaryPanel" hidden>
                    <div class="route-summary__copy">
                        <h3 class="route-summary__title">Informasi Rute</h3>
                        <p class="route-summary__text" id="routeSummaryLabel">Pilih tombol rute pada card lokasi untuk memulai perutean dari posisi Anda.</p>
                    </div>
                    <div class="route-metric">
                        <span class="route-metric__value" id="routeDistanceLabel">-</span>
                        <span class="route-metric__label">Jarak</span>
                    </div>
                    <div class="route-metric">
                        <span class="route-metric__value" id="routeDurationLabel">-</span>
                        <span class="route-metric__label">Estimasi</span>
                    </div>
                    <button class="route-summary__close" id="cancelRoute" type="button" hidden aria-label="Batalkan navigasi">&times;</button>
                </section>
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
        apiBase: <?= json_encode(base_url('api/service-center')) ?>,
        brandsApi: <?= json_encode(base_url('api/brands')) ?>,
        districtsApi: <?= json_encode(base_url('api/districts')) ?>,
        cityBoundaryApi: <?= json_encode(base_url('api/city-boundary')) ?>,
        detailBase: <?= json_encode(base_url('service-center')) ?>,
        initialBrand: <?= json_encode(service('request')->getGet('brand') ?? 'all') ?>,
        initialFocusId: <?= json_encode(service('request')->getGet('focus') ?? '') ?>,
        initialRouteId: <?= json_encode(service('request')->getGet('route') ?? '') ?>,
    };

    const map = GISApp.createMap('explorerMapPage', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: GISApp.DEFAULT_ZOOM,
    });
    map.zoomControl.setPosition('topright');

    const markerLayer = L.layerGroup().addTo(map);
    const cityBoundaryLayer = L.geoJSON(null, {
        style: {
            color: '#1c50e0',
            weight: 2,
            opacity: 0.85,
            fillColor: '#6fc5ff',
            fillOpacity: 0.05,
            dashArray: '8 6',
        },
    }).addTo(map);
    const districtBoundaryLayer = L.geoJSON(null, {
        style: {
            color: '#0d6d77',
            weight: 3,
            opacity: 0.95,
            fillColor: '#2dd4bf',
            fillOpacity: 0.14,
            dashArray: '10 6',
        },
    }).addTo(map);
    let routingControl = null;
    let userMarker = null;
    let userCircle = null;
    let markers = new Map();
    let allData = [];
    let filtered = [];
    let brands = [];
    let cityBoundary = null;
    let districts = [];

    const state = {
        brand: config.initialBrand,
        district: 'all',
        keyword: <?= json_encode($searchValue) ?>,
        minRating: 0,
        locationType: 'all',
        radius: 5,
        locationActive: false,
        origin: null,
    };
    let activeRouteTargetId = null;
    let activeDetailId = null;
    let hasHandledInitialIntent = false;

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function formatDuration(minutes) {
        if (!Number.isFinite(minutes) || minutes < 1) {
            return '< 1 menit';
        }

        if (minutes < 60) {
            return `${minutes} menit`;
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return remainingMinutes > 0 ? `${hours}j ${remainingMinutes}m` : `${hours} jam`;
    }

    function resetRouteSummary(message = 'Pilih tombol rute pada card lokasi untuk memulai perutean dari posisi Anda.') {
        setText('routeDistanceLabel', '-');
        setText('routeDurationLabel', '-');
        setText('routeSummaryLabel', message);
    }

    function updateRadiusDisplay() {
        setText('radiusValue', `${state.radius} km`);
        setText('radiusState', state.locationActive ? `Radius aktif ${state.radius} km` : 'Aktif setelah lokasi dinyalakan');
    }

    function refreshLegend() {
        updateRadiusDisplay();
    }

    function updateUserLayer() {
        if (userMarker) {
            map.removeLayer(userMarker);
            userMarker = null;
        }

        if (userCircle) {
            map.removeLayer(userCircle);
            userCircle = null;
        }

        if (!state.locationActive || !state.origin) {
            return;
        }

        userMarker = L.circleMarker([state.origin.lat, state.origin.lng], {
            radius: 8,
            color: '#ffffff',
            weight: 3,
            fillColor: '#234ee2',
            fillOpacity: 1,
        }).addTo(map);
    }

    function buildFeatureList(item) {
        const source = (item.jenis_layanan || '')
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean);

        return source.length ? source.slice(0, 8) : ['Informasi layanan belum tersedia'];
    }

    function buildPreviewMeta(item, rows) {
        return rows.map(([icon, text]) => `
            <div class="info-item">
                <span class="info-item__icon" aria-hidden="true">${icon}</span>
                <span class="info-item__text">${GISApp.escapeHtml(text)}</span>
            </div>
        `).join('');
    }

    function hideDetailPanel() {
        activeDetailId = null;
        const panel = document.getElementById('mapDetailPanel');
        const stage = document.querySelector('.map-stage');
        if (panel) {
            panel.hidden = true;
        }
        document.getElementById('mapDetailStickybar')?.classList.remove('is-visible');
        stage?.classList.remove('is-detail-open');
    }

    function syncDetailScrollState() {
        const scroller = document.getElementById('mapDetailScroll');
        const stickybar = document.getElementById('mapDetailStickybar');
        if (!scroller || !stickybar) {
            return;
        }

        stickybar.classList.toggle('is-visible', scroller.scrollTop > 18);
    }

    function setDetailMedia(item) {
        const media = document.getElementById('mapDetailMedia');
        const photo = GISApp.photoUrl(item.foto_lokasi);
        const closeButton = '<button class="map-detail-panel__close" id="closeDetailPanel" type="button" aria-label="Tutup detail">&times;</button>';

        if (!photo) {
            media.innerHTML = `<div class="map-detail-panel__placeholder">Foto lokasi belum tersedia untuk ${GISApp.escapeHtml(item.nama_tempat)}.</div>${closeButton}`;
        } else {
            media.innerHTML = `<img src="${photo}" alt="${GISApp.escapeHtml(item.nama_tempat)}">${closeButton}`;
        }

        document.getElementById('closeDetailPanel').addEventListener('click', hideDetailPanel);
    }

    function showDetailPanel(item) {
        if (!item) {
            return;
        }

        destroyRoute();
        activeDetailId = item.id;
        const panel = document.getElementById('mapDetailPanel');
        const stage = document.querySelector('.map-stage');
        const typeLabel = GISApp.locationTypeLabel(GISApp.locationTypeKey(item));

        setDetailMedia(item);
        document.getElementById('mapDetailEyebrow').innerHTML = `
            <div class="map-detail-panel__eyebrow-main">
                <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand || 'Brand')}</span>
                <span class="pill">${GISApp.escapeHtml(typeLabel)}</span>
                <span class="detail-rating-pill"><span class="detail-rating-pill__star">&#9733;</span>${GISApp.formatRating(item.rating)}</span>
            </div>
            <div class="map-detail-panel__eyebrow-action">
                <button class="btn btn--primary" id="mapDetailRouteButton" type="button"><span class="map-detail-route-icon">&#10148;</span>Rute</button>
            </div>
        `;
        document.getElementById('mapDetailTitle').textContent = item.nama_tempat || 'Service Center';
        document.getElementById('mapDetailStickyTitle').textContent = item.nama_tempat || 'Service Center';
        document.getElementById('mapDetailAddress').textContent = item.alamat || '-';
        document.getElementById('mapDetailFacts').innerHTML = [
            ['&#128337;', 'Jam Operasional', `${GISApp.formatTime(item.jam_buka)} - ${GISApp.formatTime(item.jam_tutup)}`],
            ['&#128197;', 'Hari Aktif', item.hari_operasional || 'Menyesuaikan'],
            ['&#9742;', 'Telepon', item.no_telepon || '-'],
            ['&#127970;', 'Tipe Lokasi', typeLabel],
            ['&#128205;', 'Kecamatan', item.kecamatan || '-'],
        ].map(([icon, label, value]) => `
            <div class="map-detail-panel__fact">
                <span class="map-detail-panel__fact-icon">${icon}</span>
                <div class="map-detail-panel__fact-copy">
                    <span class="map-detail-panel__fact-label">${GISApp.escapeHtml(label)}</span>
                    <span class="map-detail-panel__fact-value">${GISApp.escapeHtml(value)}</span>
                </div>
            </div>
        `).join('');
        document.getElementById('mapDetailServices').innerHTML = buildFeatureList(item)
            .map((service) => `<span class="feature-pill">${GISApp.escapeHtml(service)}</span>`)
            .join('');
        document.getElementById('mapDetailRouteButton').onclick = () => drawRoute(item);

        panel.hidden = false;
        const scroller = document.getElementById('mapDetailScroll');
        if (scroller) {
            scroller.scrollTop = 0;
        }
        syncDetailScrollState();
        stage?.classList.add('is-detail-open');
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
                    <button class="mini-button is-primary" type="button" data-popup-detail="${item.id}">Detail</button>
                    <button class="mini-button" type="button" data-popup-route="${item.id}">Rute</button>
                </div>
            </div>
        `;
    }

    function renderCityBoundary() {
        cityBoundaryLayer.clearLayers();
        const geojson = cityBoundary?.geojson;
        if (!geojson) {
            return;
        }

        try {
            cityBoundaryLayer.addData(JSON.parse(geojson));
        } catch (error) {
            console.error('Gagal memuat polygon Kota Medan.', error);
        }
    }

    function getSelectedDistrict() {
        if (state.district === 'all') {
            return null;
        }

        return districts.find((district) => district.name === state.district) || null;
    }

    function getSelectedDistrictGeoJson() {
        const selectedDistrict = getSelectedDistrict();
        if (!selectedDistrict?.geojson) {
            return null;
        }

        try {
            return JSON.parse(selectedDistrict.geojson);
        } catch (error) {
            console.error('GeoJSON kecamatan tidak valid.', error);
            return null;
        }
    }

    function renderSelectedDistrictBoundary() {
        districtBoundaryLayer.clearLayers();
        const geojson = getSelectedDistrictGeoJson();
        if (!geojson) {
            return null;
        }

        districtBoundaryLayer.addData(geojson);
        if (typeof districtBoundaryLayer.bringToFront === 'function') {
            districtBoundaryLayer.bringToFront();
        }

        return districtBoundaryLayer.getBounds().isValid() ? districtBoundaryLayer.getBounds() : null;
    }

    function renderLoadingSkeletons(count = 3) {
        return Array.from({ length: count }, () => `
            <div class="result-skeleton">
                <div class="result-skeleton__line result-skeleton__line--short"></div>
                <div class="result-skeleton__line result-skeleton__line--title"></div>
                <div class="result-skeleton__line"></div>
                <div class="result-skeleton__line result-skeleton__line--medium"></div>
                <div class="result-skeleton__actions">
                    <div class="result-skeleton__button"></div>
                    <div class="result-skeleton__button"></div>
                </div>
            </div>
        `).join('');
    }

    function destroyRoute(message) {
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }

        activeRouteTargetId = null;
        document.querySelector('.map-stage')?.classList.remove('is-route-open');
        const routeSummaryPanel = document.getElementById('routeSummaryPanel');
        if (routeSummaryPanel) {
            routeSummaryPanel.hidden = true;
        }
        const cancelRouteButton = document.getElementById('cancelRoute');
        if (cancelRouteButton) {
            cancelRouteButton.hidden = true;
        }
        resetRouteSummary(message);
    }

    function syncRouteAfterLocationUpdate() {
        if (!activeRouteTargetId) {
            return;
        }

        const item = allData.find((entry) => entry.id === activeRouteTargetId);
        if (item) {
            drawRoute(item, true);
        }
    }

    function ensureOriginForRouting() {
        if (state.locationActive && state.origin) {
            return Promise.resolve(state.origin);
        }

        if (!navigator.geolocation) {
            state.locationActive = false;
            state.origin = null;
            applyFilters();
            return Promise.resolve(null);
        }

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition((position) => {
                state.locationActive = true;
                state.origin = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                applyFilters();
                resolve(state.origin);
            }, () => {
                state.locationActive = false;
                state.origin = null;
                applyFilters();
                resolve(null);
            }, {
                enableHighAccuracy: true,
                timeout: 8000,
            });
        });
    }

    async function drawRoute(item, fromSync = false) {
        const lat = GISApp.parseCoordinate(item.latitude);
        const lng = GISApp.parseCoordinate(item.longitude);
        if (lat == null || lng == null) {
            destroyRoute('Koordinat lokasi tujuan tidak valid untuk dibuatkan rute.');
            return;
        }

        hideDetailPanel();

        if (!fromSync) {
            resetRouteSummary(`Menyiapkan rute ke ${item.nama_tempat}...`);
        }

        const origin = await ensureOriginForRouting();
        if (!origin) {
            destroyRoute('Lokasi asal belum tersedia untuk memulai rute.');
            return;
        }

        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }

        activeRouteTargetId = item.id;
        const routeSummaryPanel = document.getElementById('routeSummaryPanel');
        if (routeSummaryPanel) {
            routeSummaryPanel.hidden = false;
        }
        document.querySelector('.map-stage')?.classList.add('is-route-open');
        const cancelRouteButton = document.getElementById('cancelRoute');
        if (cancelRouteButton) {
            cancelRouteButton.hidden = false;
        }
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(origin.lat, origin.lng),
                L.latLng(lat, lng),
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
            setText('routeDistanceLabel', GISApp.formatDistance(km));
            setText('routeDurationLabel', formatDuration(minutes));
            setText('routeSummaryLabel', `Rute ke ${item.nama_tempat} sekitar ${GISApp.formatDistance(km)} dengan estimasi ${formatDuration(minutes)}.`);
        });

        routingControl.on('routingerror', () => {
            destroyRoute(`Rute ke ${item.nama_tempat} tidak berhasil dihitung.`);
        });
    }

    function renderResults() {
        const container = document.getElementById('mapResults');
        const closest = filtered.find((item) => item.distanceKm != null);
        setText('listResultCount', `${filtered.length} hasil`);
        setText('overlayCount', filtered.length);
        setText('overlayDistricts', new Set(filtered.map((item) => item.kecamatan).filter(Boolean)).size);
        setText('overlayNearest', closest ? GISApp.formatDistance(closest.distanceKm) : '-');

        if (!filtered.length) {
            container.innerHTML = '<div class="empty-state">Tidak ada lokasi yang cocok dengan filter saat ini.</div>';
            return;
        }

        container.innerHTML = filtered.map((item) => {
            const distance = item.distanceKm != null ? GISApp.formatDistance(item.distanceKm) : 'Jelajahi di peta';
            const typeLabel = GISApp.locationTypeLabel(GISApp.locationTypeKey(item));
            return `
                <article class="result-card" data-id="${item.id}">
                    <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                    <h3 class="result-card__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                    <div class="result-card__meta">
                        <div class="info-item">
                            <span class="info-item__icon" aria-hidden="true">&#128205;</span>
                            <span class="info-item__text">${GISApp.escapeHtml(item.alamat || '-')}</span>
                        </div>
                        <span>${GISApp.escapeHtml(item.kecamatan || '-')} · ${GISApp.escapeHtml(typeLabel)}</span>
                        <span>Rating ${GISApp.formatRating(item.rating)} · ${GISApp.escapeHtml(distance)}</span>
                    </div>
                    <div class="result-card__actions">
                        <button class="mini-button is-primary" type="button" data-detail="${item.id}">Detail</button>
                        <button class="mini-button" type="button" data-route="${item.id}">Rute</button>
                    </div>
                </article>
            `;
        }).join('');

        container.querySelectorAll('[data-detail]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const item = filtered.find((entry) => entry.id === button.dataset.detail);
                if (item) {
                    showDetailPanel(item);
                }
            });
        });

        container.querySelectorAll('[data-route]').forEach((button) => {
            button.addEventListener('click', async (e) => {
                e.stopPropagation();
                const item = filtered.find((entry) => entry.id === button.dataset.route);
                if (!item) {
                    return;
                }

                await drawRoute(item);
            });
        });

        container.querySelectorAll('.result-card').forEach((card) => {
            const item = filtered.find((entry) => entry.id === card.dataset.id);
            const meta = card.querySelector('.result-card__meta');
            if (!item || !meta) {
                return;
            }

            const distance = item.distanceKm != null ? GISApp.formatDistance(item.distanceKm) : 'Jelajahi di peta';
            const typeLabel = GISApp.locationTypeLabel(GISApp.locationTypeKey(item));
            meta.innerHTML = buildPreviewMeta(item, [
                ['&#128205;', item.alamat || '-'],
                ['&#127970;', `${item.kecamatan || '-'} · ${typeLabel}`],
                ['&#9733;', `Rating ${GISApp.formatRating(item.rating)} · ${distance}`],
            ]);
        });

        // Clicking the whole card focuses the map and opens popup
        container.querySelectorAll('.result-card').forEach((card) => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                const marker = markers.get(id);
                if (!marker) return;
                map.flyTo(marker.getLatLng(), 15, { duration: 0.8 });
                marker.openPopup();
            });
        });
    }

    function renderMap() {
        markers = GISApp.renderMarkers(markerLayer, filtered, config.detailBase, {
            onSelect: (item) => {
                document.querySelector(`[data-id="${item.id}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            },
            popupRenderer,
            onPopupOpen: (marker, item) => {
                const popupElement = marker.getPopup()?.getElement();
                const detailButton = popupElement?.querySelector('[data-popup-detail]');
                const routeButton = popupElement?.querySelector('[data-popup-route]');
                if (detailButton) {
                    detailButton.addEventListener('click', () => {
                        showDetailPanel(item);
                    });
                }
                if (routeButton) {
                    routeButton.addEventListener('click', () => {
                        drawRoute(item);
                    });
                }
            },
            popupMaxWidth: 260,
        });
        updateUserLayer();
        const selectedDistrictBounds = renderSelectedDistrictBoundary();

        if (selectedDistrictBounds) {
            map.fitBounds(selectedDistrictBounds, { padding: [32, 32], maxZoom: 14 });
            return;
        }

        if (state.locationActive && state.origin) {
            const points = filtered.map(GISApp.latLngFromItem).filter(Boolean);
            points.push([state.origin.lat, state.origin.lng]);
            if (points.length > 1) {
                map.fitBounds(L.latLngBounds(points), { padding: [40, 40], maxZoom: 14 });
            } else {
                map.setView([state.origin.lat, state.origin.lng], 13);
            }
            return;
        }

        GISApp.fitBounds(map, filtered, [40, 40], 14);
    }

    async function handleInitialIntent() {
        if (hasHandledInitialIntent) {
            return;
        }

        const targetId = config.initialRouteId || config.initialFocusId;
        if (!targetId) {
            hasHandledInitialIntent = true;
            return;
        }

        const item = allData.find((entry) => entry.id === targetId);
        if (!item) {
            hasHandledInitialIntent = true;
            return;
        }

        hasHandledInitialIntent = true;
        const latLng = GISApp.latLngFromItem(item);
        if (latLng) {
            map.flyTo(latLng, 15, { duration: 0.85 });
        }

        if (config.initialRouteId) {
            await drawRoute(item);
            return;
        }

        showDetailPanel(item);
    }

    function applyFilters() {
        const selectedDistrictGeoJson = getSelectedDistrictGeoJson();
        filtered = allData
            .map((item) => {
                const lat = GISApp.parseCoordinate(item.latitude);
                const lng = GISApp.parseCoordinate(item.longitude);
                const distance = state.locationActive && state.origin && lat != null && lng != null
                    ? GISApp.distanceKm(state.origin.lat, state.origin.lng, lat, lng)
                    : null;
                return { ...item, distanceKm: distance };
            })
            .filter((item) => state.brand === 'all' || item.brand === state.brand)
            .filter((item) => {
                if (state.district === 'all') {
                    return true;
                }

                const lat = GISApp.parseCoordinate(item.latitude);
                const lng = GISApp.parseCoordinate(item.longitude);

                if (selectedDistrictGeoJson && lat != null && lng != null) {
                    return GISApp.geoJsonContainsPoint(selectedDistrictGeoJson, lat, lng);
                }

                return item.kecamatan === state.district;
            })
            .filter((item) => parseFloat(item.rating || 0) >= state.minRating)
            .filter((item) => state.locationType === 'all' || GISApp.locationTypeKey(item) === state.locationType)
            .filter((item) => {
                if (!state.keyword) {
                    return true;
                }

                const haystack = `${item.nama_tempat} ${item.alamat} ${item.kecamatan}`.toLowerCase();
                return haystack.includes(state.keyword.toLowerCase());
            })
            .sort((a, b) => {
                if (state.locationActive && a.distanceKm != null && b.distanceKm != null) {
                    return a.distanceKm - b.distanceKm;
                }

                return a.nama_tempat.localeCompare(b.nama_tempat);
            });

        if (activeDetailId) {
            const activeItem = filtered.find((entry) => entry.id === activeDetailId);
            if (activeItem) {
                showDetailPanel(activeItem);
            } else {
                hideDetailPanel();
            }
        }

        refreshLegend();
        // Render map first so markers registry is available for result card handlers
        renderMap();
        renderResults();
    }

    function populateFilters() {
        const brandSelect = document.getElementById('brandSelect');
        const districtSelect = document.getElementById('districtSelect');

        brandSelect.innerHTML = '<option value="all">Semua Brand</option>' +
            brands.map((brand) => `<option value="${GISApp.escapeHtml(brand.name)}" ${state.brand === brand.name ? 'selected' : ''}>${GISApp.escapeHtml(brand.name)}</option>`).join('');
        districtSelect.innerHTML = '<option value="all">Semua Kecamatan</option>' +
            districts.map((district) => `<option value="${GISApp.escapeHtml(district.name)}" ${state.district === district.name ? 'selected' : ''}>${GISApp.escapeHtml(district.name)}</option>`).join('');
    }

    async function init() {
        try {
            document.getElementById('mapResults').innerHTML = renderLoadingSkeletons();
            const [dataResponse, brandResponse, districtResponse, boundaryResponse] = await Promise.all([
                GISApp.fetchJson(config.apiBase),
                GISApp.fetchJson(config.brandsApi),
                GISApp.fetchJson(config.districtsApi),
                GISApp.fetchJson(config.cityBoundaryApi),
            ]);

            allData = dataResponse.data || [];
            brands = brandResponse.data || [];
            districts = districtResponse.data || [];
            populateFilters();
            cityBoundary = boundaryResponse.data || null;
            renderCityBoundary();
            applyFilters();
            await handleInitialIntent();
        } catch (error) {
            document.getElementById('mapResults').innerHTML = '<div class="empty-state">Gagal memuat data service center dari server.</div>';
            console.error(error);
        }
    }

    document.getElementById('keywordInput').addEventListener('input', (event) => {
        state.keyword = event.target.value.trim();
        applyFilters();
    });

    document.getElementById('brandSelect').addEventListener('change', (event) => {
        state.brand = event.target.value;
        applyFilters();
    });

    document.getElementById('districtSelect').addEventListener('change', (event) => {
        state.district = event.target.value;
        applyFilters();
    });

    document.getElementById('ratingSelect').addEventListener('change', (event) => {
        state.minRating = parseFloat(event.target.value || '0');
        applyFilters();
    });

    document.getElementById('typeFilterList').addEventListener('click', (event) => {
        const button = event.target.closest('[data-type]');
        if (!button) {
            return;
        }

        state.locationType = button.dataset.type;
        document.querySelectorAll('#typeFilterList [data-type]').forEach((item) => {
            item.classList.toggle('is-active', item.dataset.type === state.locationType);
        });
        applyFilters();
    });

    const radiusRangeEl = document.getElementById('radiusRange');
    if (radiusRangeEl) {
        radiusRangeEl.addEventListener('input', (event) => {
            state.radius = parseInt(event.target.value, 10) || 5;
            if (state.locationActive) {
                applyFilters();
            } else {
                updateRadiusDisplay();
            }
        });
    }

    document.getElementById('advancedFilterToggle').addEventListener('click', () => {
        const panel = document.getElementById('advancedFilterPanel');
        const trigger = document.getElementById('advancedFilterToggle');
        const isHidden = panel.hasAttribute('hidden');
        if (isHidden) {
            panel.removeAttribute('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            return;
        }

        panel.setAttribute('hidden', '');
        trigger.setAttribute('aria-expanded', 'false');
    });

    document.getElementById('cancelRoute').addEventListener('click', () => {
        destroyRoute();
    });

    document.getElementById('legendToggle')?.addEventListener('click', () => {
        const panel = document.getElementById('legendPanel');
        const body = document.getElementById('legendPanelBody');
        const button = document.getElementById('legendToggle');
        if (!panel || !body || !button) {
            return;
        }

        const isCollapsed = panel.classList.toggle('is-collapsed');
        body.hidden = isCollapsed;
        button.textContent = isCollapsed ? '+' : '−';
        button.setAttribute('aria-label', isCollapsed ? 'Buka legenda' : 'Tutup legenda');
        button.setAttribute('aria-expanded', String(!isCollapsed));
    });

    document.getElementById('closeDetailPanelSticky').addEventListener('click', hideDetailPanel);
    document.getElementById('mapDetailScroll').addEventListener('scroll', syncDetailScrollState);

    document.getElementById('resetFilters').addEventListener('click', () => {
        state.brand = 'all';
        state.district = 'all';
        state.keyword = '';
        state.minRating = 0;
        state.locationType = 'all';
        state.radius = 5;
        state.locationActive = false;
        state.origin = null;

        document.getElementById('keywordInput').value = '';
        document.getElementById('brandSelect').value = 'all';
        document.getElementById('districtSelect').value = 'all';
        document.getElementById('ratingSelect').value = '0';
        if (typeof radiusRangeEl !== 'undefined' && radiusRangeEl) {
            radiusRangeEl.value = '5';
        }
        document.querySelectorAll('#typeFilterList [data-type]').forEach((item) => {
            item.classList.toggle('is-active', item.dataset.type === 'all');
        });
        destroyRoute();
        applyFilters();
    });

    resetRouteSummary();
    updateRadiusDisplay();
    refreshLegend();
    init().then(() => {
        ensureOriginForRouting().then((origin) => {
            if (origin) {
                syncRouteAfterLocationUpdate();
            }
        });
    });
})();
</script>
</body>
</html>
