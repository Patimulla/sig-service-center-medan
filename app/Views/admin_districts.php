<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Kelola Kecamatan - Admin Panel',
        'description' => 'Kelola master kecamatan dan polygon batas wilayah untuk visualisasi GIS pada web service center Medan.',
    ]) ?>
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= view('partials/admin_sidebar', ['current' => 'district']) ?>

    <main class="admin-main">
        <div class="admin-page">
            <section class="admin-page__header">
                <div>
                    <h1>Kelola Kecamatan</h1>
                </div>
            </section>

            <section class="admin-manage-layout">
                <article class="panel admin-card">
                    <div class="admin-card__header">
                        <div>
                            <h2 class="admin-card__title" id="districtFormTitle">Tambah Kecamatan</h2>
                        </div>
                        <span class="status-tag is-success" id="districtFormMode">Mode tambah</span>
                    </div>

                    <form id="districtForm">
                        <input type="hidden" id="districtId" name="id">
                        <div class="admin-form-grid">
                            <div class="field-stack">
                                <label class="field-label" for="districtName">Nama Kecamatan</label>
                                <input class="input-field" id="districtName" name="name" type="text" required>
                            </div>
                            <div class="field-stack">
                                <label class="field-label" for="districtSlug">Slug</label>
                                <input class="input-field" id="districtSlug" name="slug" type="text" required>
                            </div>
                            <div class="field-stack">
                                <label class="field-label" for="districtActive">Status</label>
                                <select class="select-field" id="districtActive" name="is_active">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div class="field-stack field-span-2">
                                <label class="field-label" for="districtNotes">Catatan</label>
                                <textarea class="textarea-field" id="districtNotes" name="notes"></textarea>
                            </div>
                            <div class="field-stack field-span-2">
                                <label class="field-label" for="districtGeojson">GeoJSON Polygon</label>
                                <textarea class="textarea-field" id="districtGeojson" name="geojson" placeholder='{"type":"Polygon","coordinates":[...]}'></textarea>
                            </div>
                            <div class="field-span-2 admin-form-actions">
                                <button class="btn btn--primary" id="districtSubmitButton" type="submit">Tambah Kecamatan</button>
                                <button class="btn btn--ghost" id="districtReset" type="button">Reset</button>
                            </div>
                        </div>
                    </form>
                </article>

                <aside class="admin-manage-side">
                    <article class="panel admin-card">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">Preview Polygon</h2>
                            <span class="pill" id="districtCount">0 kecamatan</span>
                        </div>
                        <div id="districtPreviewMap" class="mini-map"></div>
                    </article>

                    <article class="panel admin-card admin-table-shell">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">Daftar Kecamatan</h2>
                        </div>
                        <div class="admin-table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Kecamatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="districtTableBody">
                                    <tr><td colspan="3">Memuat data kecamatan...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </aside>
            </section>
        </div>
    </main>
</div>

<div class="status-panel" id="districtStatusPanel" aria-live="polite" aria-atomic="true"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const readApi = <?= json_encode(base_url('api/districts')) ?>;
    const writeApi = <?= json_encode(base_url('admin-api/districts')) ?>;
    const map = GISApp.createMap('districtPreviewMap', {
        center: [GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng],
        zoom: 11,
    });
    const layer = L.geoJSON().addTo(map);
    const form = document.getElementById('districtForm');
    const districtNameInput = document.getElementById('districtName');
    const districtSlugInput = document.getElementById('districtSlug');
    const districtSubmitButton = document.getElementById('districtSubmitButton');
    const districtStatusPanel = document.getElementById('districtStatusPanel');
    let rows = [];
    let statusPanelTimer = null;
    let pendingDeleteId = null;

    function updateSubmitButtonLabel() {
        districtSubmitButton.textContent = document.getElementById('districtId').value ? 'Update Kecamatan' : 'Tambah Kecamatan';
    }

    function statusIconMarkup(type) {
        if (type === 'loading') {
            return '<span class="status-panel__spinner" aria-hidden="true"></span>';
        }

        if (type === 'success') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.55 16.6 5.4 12.45l-1.4 1.4 5.55 5.55L20 8.95l-1.4-1.4-9.05 9.05Z" fill="currentColor"/></svg>';
        }

        if (type === 'confirm') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 1.7 20h20.6L12 3Zm1 13h-2v-2h2v2Zm0-4h-2V8h2v4Z" fill="currentColor"/></svg>';
        }

        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m13.41 12 4.3-4.29-1.42-1.42-4.29 4.3-4.29-4.3-1.42 1.42 4.3 4.29-4.3 4.29 1.42 1.42 4.29-4.3 4.29 4.3 1.42-1.42L13.41 12Z" fill="currentColor"/></svg>';
    }

    function showStatusPanel(type, message, details = {}) {
        const detailItems = Object.values(details || {});
        window.clearTimeout(statusPanelTimer);
        districtStatusPanel.className = `status-panel is-visible is-${type}`;
        districtStatusPanel.style.display = 'flex';
        districtStatusPanel.style.opacity = '1';
        districtStatusPanel.style.pointerEvents = 'auto';
        districtStatusPanel.innerHTML = `
            <div class="status-panel__icon">${statusIconMarkup(type)}</div>
            <div class="status-panel__body">
                <strong>${GISApp.escapeHtml(message)}</strong>
                ${detailItems.length ? `<ul>${detailItems.map((item) => `<li>${GISApp.escapeHtml(item)}</li>`).join('')}</ul>` : ''}
            </div>
        `;

        if (type !== 'loading') {
            statusPanelTimer = window.setTimeout(() => hideStatusPanel(), 3200);
        }
    }

    function showDeleteConfirm(row) {
        pendingDeleteId = row.id;
        window.clearTimeout(statusPanelTimer);
        districtStatusPanel.className = 'status-panel is-visible is-confirm';
        districtStatusPanel.style.display = 'flex';
        districtStatusPanel.style.opacity = '1';
        districtStatusPanel.style.pointerEvents = 'auto';
        districtStatusPanel.innerHTML = `
            <div class="status-panel__icon">${statusIconMarkup('confirm')}</div>
            <div class="status-panel__body">
                <strong>Hapus kecamatan "${GISApp.escapeHtml(row.name)}"?</strong>
                <div class="status-panel__actions">
                    <button class="mini-button" type="button" data-confirm-cancel>Batal</button>
                    <button class="mini-button is-danger" type="button" data-confirm-delete>Hapus</button>
                </div>
            </div>
        `;
    }

    function hideStatusPanel() {
        window.clearTimeout(statusPanelTimer);
        pendingDeleteId = null;
        districtStatusPanel.className = 'status-panel';
        districtStatusPanel.style.display = 'none';
        districtStatusPanel.style.opacity = '0';
        districtStatusPanel.style.pointerEvents = 'none';
        districtStatusPanel.innerHTML = '';
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            ...options,
        });
        const data = await response.json();
        if (!response.ok) {
            const error = new Error(data.message || 'Request gagal.');
            error.details = data.errors || {};
            throw error;
        }
        return data;
    }

    function normalizeSlug(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function drawGeojson(rawGeojson) {
        layer.clearLayers();
        if (!rawGeojson) {
            map.setView([GISApp.DEFAULT_CENTER.lat, GISApp.DEFAULT_CENTER.lng], 11);
            return;
        }

        try {
            const parsed = JSON.parse(rawGeojson);
            layer.addData(parsed);
            const bounds = layer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [24, 24] });
            }
        } catch (error) {
            console.error(error);
        }
    }

    function resetForm() {
        form.reset();
        document.getElementById('districtId').value = '';
        document.getElementById('districtFormTitle').textContent = 'Tambah Kecamatan';
        document.getElementById('districtFormMode').textContent = 'Mode tambah';
        document.getElementById('districtActive').value = '1';
        drawGeojson('');
        updateSubmitButtonLabel();
        hideStatusPanel();
    }

    function fillForm(row) {
        document.getElementById('districtId').value = row.id;
        document.getElementById('districtName').value = row.name || '';
        document.getElementById('districtSlug').value = row.slug || '';
        document.getElementById('districtActive').value = row.is_active ? '1' : '0';
        document.getElementById('districtNotes').value = row.notes || '';
        document.getElementById('districtGeojson').value = row.geojson || '';
        document.getElementById('districtFormTitle').textContent = 'Edit Kecamatan';
        document.getElementById('districtFormMode').textContent = 'Mode edit';
        drawGeojson(row.geojson || '');
        updateSubmitButtonLabel();
        hideStatusPanel();
    }

    function render() {
        document.getElementById('districtCount').textContent = `${rows.length} kecamatan`;
        document.getElementById('districtTableBody').innerHTML = rows.map((row) => `
            <tr>
                <td>
                    <div class="table-brand">
                        <span class="table-brand__name">${GISApp.escapeHtml(row.name)}</span>
                        <span class="table-subtle">${row.geojson ? 'Polygon tersedia' : 'Belum ada polygon'}</span>
                    </div>
                </td>
                <td><span class="status-tag ${row.is_active ? 'is-success' : 'is-warning'}">${row.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                <td>
                    <div class="table-actions">
                        <button class="mini-button is-primary" type="button" data-edit="${row.id}">Edit</button>
                        <button class="mini-button is-danger" type="button" data-delete="${row.id}">Hapus</button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="3">Belum ada data kecamatan.</td></tr>';
    }

    async function load() {
        const response = await GISApp.fetchJson(readApi);
        rows = response.data || [];
        render();
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        districtSlugInput.value = normalizeSlug(districtSlugInput.value);
        const payload = Object.fromEntries(new FormData(form).entries());
        const id = payload.id;
        delete payload.id;
        const isUpdate = Boolean(id);
        districtSubmitButton.disabled = true;
        showStatusPanel('loading', isUpdate ? 'Memperbarui kecamatan...' : 'Menambahkan kecamatan...');

        try {
            if (id) {
                await request(`${writeApi}/${id}`, { method: 'POST', body: JSON.stringify(payload) });
                showStatusPanel('success', 'Kecamatan berhasil diperbarui.');
            } else {
                await request(writeApi, { method: 'POST', body: JSON.stringify(payload) });
                showStatusPanel('success', 'Kecamatan berhasil ditambahkan.');
            }
            await load();
            resetForm();
        } catch (error) {
            showStatusPanel('error', error.message, error.details);
        } finally {
            districtSubmitButton.disabled = false;
            updateSubmitButtonLabel();
        }
    });

    document.getElementById('districtReset').addEventListener('click', resetForm);
    districtStatusPanel.addEventListener('click', async (event) => {
        if (event.target.closest('[data-confirm-cancel]')) {
            hideStatusPanel();
            return;
        }

        if (event.target.closest('[data-confirm-delete]') && pendingDeleteId) {
            const deleteId = pendingDeleteId;
            const row = rows.find((item) => String(item.id) === String(deleteId));
            pendingDeleteId = null;
            if (!row) {
                hideStatusPanel();
                return;
            }

            showStatusPanel('loading', `Menghapus kecamatan "${row.name}"...`);
            try {
                await request(`${writeApi}/${deleteId}`, { method: 'DELETE' });
                showStatusPanel('success', `Kecamatan "${row.name}" berhasil dihapus.`);
                await load();
                resetForm();
            } catch (error) {
                showStatusPanel('error', error.message, error.details);
            }
        }
    });
    districtNameInput.addEventListener('input', () => {
        districtSlugInput.value = normalizeSlug(districtNameInput.value);
    });
    document.getElementById('districtGeojson').addEventListener('input', (event) => {
        drawGeojson(event.target.value);
    });

    document.getElementById('districtTableBody').addEventListener('click', async (event) => {
        const editId = event.target.dataset.edit;
        const deleteId = event.target.dataset.delete;

        if (editId) {
            const row = rows.find((item) => String(item.id) === editId);
            if (row) {
                fillForm(row);
            }
            return;
        }

        if (deleteId) {
            const row = rows.find((item) => String(item.id) === deleteId);
            if (!row) {
                return;
            }
            showDeleteConfirm(row);
        }
    });

    resetForm();
    load().catch((error) => showStatusPanel('error', error.message));
})();
</script>
</body>
</html>
