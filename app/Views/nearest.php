<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Cari Service Center Terdekat - GeoSC Medan',
        'description' => 'Gunakan lokasi Anda untuk mencari service center terdekat, memfilter radius, dan menampilkan rute langsung di peta Leaflet.',
    ]) ?>
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

        body.map-page .radius-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: start;
        }

        body.map-page .radius-toolbar .advanced-filter-toggle {
            min-height: 40px;
            padding-inline: 12px;
            font-size: 0.82rem;
            align-self: stretch;
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
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            background: linear-gradient(180deg, rgba(11, 26, 51, 0.76) 0%, rgba(11, 26, 51, 0.48) 58%, rgba(11, 26, 51, 0) 100%);
            backdrop-filter: blur(8px);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-10px);
            transition: opacity 180ms ease, transform 180ms ease;
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
            body.map-page .sidebar-searchbar,
            body.map-page .radius-toolbar {
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css">
</head>
<body class="map-page">
<div class="app-shell">
    <?= view('partials/gis_nav', ['current' => 'nearest']) ?>

    <main class="page-shell">
        <div class="explorer-shell explorer-shell--split">
            <aside class="panel explorer-sidebar">
                <div class="explorer-sidebar__scroll">
                    <div class="explorer-sidebar__sticky">
                        <div class="control-panel__header">
                            <div><h1 class="control-panel__title">Cari Service Center Terdekat</h1></div>
                        </div>

                        <div class="radius-toolbar">
                            <section class="radius-control">
                                <div class="radius-control__header">
                                    <div>
                                        <div class="radius-control__label">Radius pencarian</div>
                                    </div>
                                    <span class="radius-control__value" id="nearestRadiusValue">5 km</span>
                                </div>

                                <input id="nearestRadiusRange" type="range" min="1" max="10" step="1" value="5">

                            </section>

                            <button class="btn btn--ghost advanced-filter-toggle" id="brandToggle" type="button" aria-expanded="false" aria-controls="brandFilterPanel">Filter Brand</button>
                        </div>

                        <section class="advanced-filters" id="brandFilterPanel" hidden>
                            <div class="advanced-filters__header">
                                <div><h2 class="control-panel__title" style="font-size:1rem;">Filter Brand</h2></div>
                            </div>

                            <div class="filter-group">
                                <div class="chip-list" id="nearestBrandChips">
                                    <button class="chip-button is-active" type="button" data-brand="all">Semua Brand</button>
                                </div>
                            </div>
                        </section>

                    </div>

                    <div class="explorer-results__body">
                        <div class="explorer-results__summary">
                            <div><h2 class="control-panel__title" style="font-size:1rem;">Hasil Terdekat</h2></div>
                            <span class="pill" id="previewCount">0 hasil</span>
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
                            <span class="metric-pill__value" id="activeRadiusView">5 km</span>
                            <span class="metric-pill__label">Radius aktif</span>
                        </div>
                        <div class="metric-pill">
                            <span class="metric-pill__value" id="closestDistance">-</span>
                            <span class="metric-pill__label">Lokasi terdekat</span>
                        </div>
                        <div class="metric-pill">
                            <span class="metric-pill__value" id="routeMetricLabel">-</span>
                            <span class="metric-pill__label">Rute aktif</span>
                        </div>
                    </div>

                </div>

                <div id="nearestMap" class="map-stage__canvas"></div>

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
                        <p class="route-summary__text" id="routeSummaryLabel">Pilih tombol rute pada daftar lokasi untuk memulai navigasi.</p>
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
        nearestBase: <?= json_encode(base_url('api/service-center/nearest')) ?>,
        cityBoundaryApi: <?= json_encode(base_url('api/city-boundary')) ?>,
        detailBase: <?= json_encode(base_url('service-center')) ?>,
    };

    const map = GISApp.createMap('nearestMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 13,
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
    let markers = new Map();
    let originMarker = null;
    let radiusCircle = null;
    let routingControl = null;
    let nearestData = [];
    let renderedData = [];
    let activeRouteId = null;
    let activeDetailId = null;
    let cityBoundary = null;

    const state = {
        origin: { ...GISApp.DEFAULT_CENTER },
        radius: 5,
        brand: 'all',
        brandOptions: [],
        locationActive: false,
    };

    function updateLocationUI() {
        const nearestRadiusState = document.getElementById('nearestRadiusState');
        const nearestRadiusValue = document.getElementById('nearestRadiusValue');

        if (nearestRadiusState) {
            nearestRadiusState.textContent = state.locationActive ? `Radius aktif ${state.radius} km` : 'Aktif setelah lokasi dinyalakan';
        }

        if (nearestRadiusValue) {
            nearestRadiusValue.textContent = `${state.radius} km`;
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

    function resetRouteSummary(message = 'Pilih tombol rute pada daftar lokasi untuk memulai navigasi.') {
        document.getElementById('routeDistanceLabel').textContent = '-';
        document.getElementById('routeDurationLabel').textContent = '-';
        document.getElementById('routeMetricLabel').textContent = '-';
        document.getElementById('routeSummaryLabel').textContent = message;
    }

    function updateOriginLayer() {
        if (originMarker) {
            map.removeLayer(originMarker);
        }
        if (radiusCircle) {
            map.removeLayer(radiusCircle);
        }

        originMarker = L.circleMarker([state.origin.lat, state.origin.lng], {
            radius: 8,
            color: '#ffffff',
            weight: 3,
            fillColor: '#234ee2',
            fillOpacity: 1,
        }).addTo(map);

        radiusCircle = L.circle([state.origin.lat, state.origin.lng], {
            radius: state.radius * 1000,
            color: '#70cfff',
            weight: 2,
            fillColor: '#70cfff',
            fillOpacity: 0.08,
        }).addTo(map);
    }

    function buildFeatureList(item) {
        const source = (item.jenis_layanan || '')
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean);

        return source.length ? source.slice(0, 8) : ['Informasi layanan belum tersedia'];
    }

    function buildPreviewMeta(rows) {
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
                    <span>${GISApp.escapeHtml(item.kecamatan || 'Medan')} · ${GISApp.escapeHtml(GISApp.formatDistance(parseFloat(item.jarak_km || 0)))} dari posisi Anda</span>
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

    function renderBrandChips() {
        const container = document.getElementById('nearestBrandChips');
        const brands = ['all', ...state.brandOptions];

        container.innerHTML = brands.map((brand) => {
            const label = brand === 'all' ? 'Semua Brand' : brand;
            return `
                <button class="chip-button ${state.brand === brand ? 'is-active' : ''}" type="button" data-brand="${brand}">
                    ${GISApp.escapeHtml(label)}
                </button>
            `;
        }).join('');

        container.querySelectorAll('[data-brand]').forEach((button) => {
            button.addEventListener('click', () => {
                state.brand = button.dataset.brand;
                renderBrandChips();
                applyFilters();
            });
        });
    }

    function destroyRoute() {
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }
        activeRouteId = null;
        document.querySelector('.map-stage')?.classList.remove('is-route-open');
        document.getElementById('routeSummaryPanel').hidden = true;
        document.getElementById('cancelRoute').hidden = true;
        resetRouteSummary();
    }

    function drawRoute(item) {
        destroyRoute();
        hideDetailPanel();
        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            resetRouteSummary('Koordinat lokasi tujuan tidak valid untuk dibuatkan rute.');
            return;
        }

        activeRouteId = item.id;
        document.querySelector('.map-stage')?.classList.add('is-route-open');
        document.getElementById('routeSummaryPanel').hidden = false;
        document.getElementById('cancelRoute').hidden = false;
        document.getElementById('routeSummaryLabel').textContent = `Menyiapkan rute ke ${item.nama_tempat}...`;

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(state.origin.lat, state.origin.lng),
                L.latLng(lat, lng),
            ],
            lineOptions: {
                styles: [{ color: '#234ee2', opacity: 0.8, weight: 5 }],
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
            document.getElementById('routeDistanceLabel').textContent = GISApp.formatDistance(km);
            document.getElementById('routeDurationLabel').textContent = formatDuration(minutes);
            document.getElementById('routeMetricLabel').textContent = GISApp.formatDistance(km);
            document.getElementById('routeSummaryLabel').textContent = `Rute ke ${item.nama_tempat} sekitar ${GISApp.formatDistance(km)} dengan estimasi ${formatDuration(minutes)}.`;
        });

        routingControl.on('routingerror', () => {
            destroyRoute();
            resetRouteSummary(`Rute ke ${item.nama_tempat} tidak berhasil dihitung.`);
        });
    }

    function refreshActiveRoute() {
        if (!activeRouteId) {
            return;
        }

        const item = nearestData.find((entry) => entry.id === activeRouteId);
        if (item) {
            drawRoute(item);
        }
    }

    function renderResults() {
        const preview = document.getElementById('mapResults');
        const container = preview;
        document.getElementById('previewCount').textContent = `${renderedData.length} hasil`;
        document.getElementById('activeRadiusView').textContent = `${state.radius} km`;
        document.getElementById('closestDistance').textContent = renderedData.length ? GISApp.formatDistance(parseFloat(renderedData[0].jarak_km || 0)) : '-';

        if (!renderedData.length) {
            if (preview) preview.innerHTML = '<div class="empty-state">Tidak ada service center dalam radius aktif.</div>';
            destroyRoute();
            return;
        }

        // Full list in results stack (include data-id for focus when marker clicked)
        container.innerHTML = renderedData.map((item) => `
            <article class="nearby-card" data-id="${item.id}">
                <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                <h3 class="nearby-card__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                <div class="nearby-card__meta">
                    <span>${GISApp.escapeHtml(item.alamat || '-')}</span>
                    <span>${GISApp.escapeHtml(GISApp.formatDistance(parseFloat(item.jarak_km || 0)))} dari posisi Anda</span>
                    <span>${GISApp.escapeHtml(GISApp.locationTypeLabel(GISApp.locationTypeKey(item)))} · Rating ${GISApp.formatRating(item.rating)}</span>
                </div>
                <div class="nearby-card__actions">
                    <button class="mini-button is-primary" type="button" data-route="${item.id}">Rute di Peta</button>
                    <button class="mini-button" type="button" data-detail="${item.id}">Detail</button>
                </div>
            </article>
        `).join('');

        // Click on route buttons should start routing and not trigger card click
        container.querySelectorAll('[data-route]').forEach((button) => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const item = renderedData.find((entry) => entry.id === button.dataset.route);
                if (item) {
                    drawRoute(item);
                }
            });
        });

        container.querySelectorAll('.nearby-card').forEach((card) => {
            const item = renderedData.find((entry) => entry.id === card.dataset.id);
            const meta = card.querySelector('.nearby-card__meta');
            if (!item || !meta) {
                return;
            }

            meta.innerHTML = buildPreviewMeta([
                ['&#128205;', item.alamat || '-'],
                ['&#127970;', `${item.kecamatan || '-'} · ${GISApp.locationTypeLabel(GISApp.locationTypeKey(item))}`],
                ['&#9733;', `Rating ${GISApp.formatRating(item.rating)} · ${GISApp.formatDistance(parseFloat(item.jarak_km || 0))} dari posisi Anda`],
            ]);
        });

        // Clicking on a card focuses the map on its marker and opens popup
        container.querySelectorAll('.nearby-card').forEach((card) => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                const marker = markers.get(id);
                if (!marker) return;
                map.flyTo(marker.getLatLng(), 15, { duration: 0.8 });
                marker.openPopup();
            });
        });

        // Preview (top 3) in sidebar status area, styled like /peta
        if (preview) {
            const previews = renderedData;
            preview.innerHTML = previews.map((item) => {
                const distance = GISApp.formatDistance(parseFloat(item.jarak_km || 0));
                const typeLabel = GISApp.locationTypeLabel(GISApp.locationTypeKey(item));
                return `
                    <article class="result-card" data-id="${item.id}">
                        <span class="brand-badge" style="background:${GISApp.brandColor(item.brand)}">${GISApp.escapeHtml(item.brand)}</span>
                        <h3 class="result-card__title">${GISApp.escapeHtml(item.nama_tempat)}</h3>
                        <div class="result-card__meta">
                            <span>${GISApp.escapeHtml(item.alamat || '-')}</span>
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

            preview.querySelectorAll('[data-detail]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const item = renderedData.find((entry) => entry.id === button.dataset.detail);
                    if (item) {
                        showDetailPanel(item);
                    }
                });
            });

            preview.querySelectorAll('.result-card').forEach((card) => {
                const item = renderedData.find((entry) => entry.id === card.dataset.id);
                const meta = card.querySelector('.result-card__meta');
                if (!item || !meta) {
                    return;
                }

                const distance = GISApp.formatDistance(parseFloat(item.jarak_km || 0));
                const typeLabel = GISApp.locationTypeLabel(GISApp.locationTypeKey(item));
                meta.innerHTML = buildPreviewMeta([
                    ['&#128205;', item.alamat || '-'],
                    ['&#127970;', `${item.kecamatan || '-'} · ${typeLabel}`],
                    ['&#9733;', `Rating ${GISApp.formatRating(item.rating)} · ${distance}`],
                ]);
            });

            // Route buttons in preview should not trigger card click
            preview.querySelectorAll('[data-route]').forEach((button) => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const item = renderedData.find((entry) => entry.id === button.dataset.route);
                    if (item) drawRoute(item);
                });
            });

            // Clicking preview card focuses map on marker and opens popup
            preview.querySelectorAll('.result-card').forEach((card) => {
                card.addEventListener('click', () => {
                    const id = card.dataset.id;
                    const marker = markers.get(id);
                    if (!marker) return;
                    map.flyTo(marker.getLatLng(), 15, { duration: 0.8 });
                    marker.openPopup();
                });
            });
        }
    }

    function renderMap() {
        markerLayer.clearLayers();
        markers = GISApp.renderMarkers(markerLayer, renderedData, config.detailBase, {
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
        updateOriginLayer();
        map.setView([state.origin.lat, state.origin.lng], 13);
    }

    function applyFilters() {
        renderedData = nearestData
            .filter((item) => state.brand === 'all' || item.brand === state.brand)
            .filter((item) => parseFloat(item.jarak_km || 0) <= state.radius)
            .sort((a, b) => parseFloat(a.jarak_km || 0) - parseFloat(b.jarak_km || 0));

        if (activeDetailId) {
            const activeItem = renderedData.find((entry) => entry.id === activeDetailId);
            if (activeItem) {
                showDetailPanel(activeItem);
            } else {
                hideDetailPanel();
            }
        }

        // Render map first so marker registry is available for card focus handlers
        renderMap();
        renderResults();
    }

    async function loadNearest() {
        try {
            document.getElementById('mapResults').innerHTML = renderLoadingSkeletons();
            const query = new URLSearchParams({
                lat: state.origin.lat,
                lng: state.origin.lng,
                limit: '30',
            });
            const [response, boundaryResponse] = await Promise.all([
                GISApp.fetchJson(`${config.nearestBase}?${query.toString()}`),
                cityBoundary ? Promise.resolve({ data: cityBoundary }) : GISApp.fetchJson(config.cityBoundaryApi),
            ]);
            nearestData = response.data || [];
            cityBoundary = boundaryResponse.data || cityBoundary;
            renderCityBoundary();
            state.brandOptions = [...new Set(nearestData.map((item) => item.brand).filter(Boolean))].sort((a, b) => a.localeCompare(b));
            renderBrandChips();
            applyFilters();
        } catch (error) {
            document.getElementById('mapResults').innerHTML = '<div class="empty-state">Gagal memuat data terdekat dari server.</div>';
            console.error(error);
        }
    }

    const nearestRadiusRangeEl = document.getElementById('nearestRadiusRange');
    if (nearestRadiusRangeEl) {
        nearestRadiusRangeEl.addEventListener('input', (event) => {
            state.radius = parseInt(event.target.value, 10) || 5;
            const valEl = document.getElementById('nearestRadiusValue');
            if (valEl) valEl.textContent = `${state.radius} km`;
            const activeEl = document.getElementById('activeRadiusView');
            if (activeEl) activeEl.textContent = `${state.radius} km`;
            applyFilters();
            if (activeRouteId) {
                const item = nearestData.find((entry) => entry.id === activeRouteId);
                if (item) {
                    drawRoute(item);
                }
            }
        });
    }

    document.getElementById('brandToggle').addEventListener('click', () => {
        const panel = document.getElementById('brandFilterPanel');
        const trigger = document.getElementById('brandToggle');
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

    function requestCurrentLocation(auto = false) {
        if (!navigator.geolocation) {
            state.locationActive = false;
            updateLocationUI();
            document.getElementById('mapResults').innerHTML = '<div class="empty-state">Akses lokasi belum diaktifkan.</div>';
            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            state.locationActive = true;
            state.origin = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            updateLocationUI();
            loadNearest().then(() => {
                refreshActiveRoute();
            });
        }, () => {
            state.locationActive = false;
            state.origin = { ...GISApp.DEFAULT_CENTER };
            updateLocationUI();
            document.getElementById('mapResults').innerHTML = '<div class="empty-state">Akses lokasi belum diaktifkan.</div>';
        }, {
            enableHighAccuracy: true,
            timeout: 8000,
        });
    }

    // reset button removed from UI; reset behavior can be triggered via developer tools if needed

    updateLocationUI();
    resetRouteSummary();
    requestCurrentLocation(true);
})();
</script>
</body>
</html>
