<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Kelola Brand - Admin Panel',
        'description' => 'Kelola master data brand smartphone yang digunakan oleh service center pada web GIS.',
    ]) ?>
</head>
<body class="admin-body">
<div class="admin-shell">
    <?= view('partials/admin_sidebar', ['current' => 'brand']) ?>

    <main class="admin-main">
        <div class="admin-page">
            <section class="admin-page__header">
                <div>
                    <h1>Kelola Brand</h1>
                </div>
            </section>

            <section class="admin-manage-layout">
                <article class="panel admin-card">
                    <div class="admin-card__header">
                        <div>
                            <h2 class="admin-card__title" id="brandFormTitle">Tambah Brand</h2>
                        </div>
                        <span class="status-tag is-success" id="brandFormMode">Mode tambah</span>
                    </div>

                    <form id="brandForm">
                        <input type="hidden" id="brandId" name="id">
                        <div class="admin-form-grid">
                            <div class="field-stack">
                                <label class="field-label" for="brandName">Nama Brand</label>
                                <input class="input-field" id="brandName" name="name" type="text" required>
                            </div>
                            <div class="field-stack">
                                <label class="field-label" for="brandSlug">Slug</label>
                                <input class="input-field" id="brandSlug" name="slug" type="text" required>
                            </div>
                            <div class="field-stack field-span-2">
                                <label class="field-label" for="brandColor">Accent Color</label>
                                <div class="color-picker-row">
                                    <input class="color-picker-input" id="brandColor" name="accent_color" type="color" value="#234ee2">
                                    <input class="input-field" id="brandColorValue" type="text" value="#234ee2" inputmode="text" spellcheck="false">
                                </div>
                            </div>
                            <div class="field-stack">
                                <label class="field-label" for="brandActive">Status</label>
                                <select class="select-field" id="brandActive" name="is_active">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>
                            <div class="field-stack field-span-2">
                                <label class="field-label" for="brandDescription">Deskripsi</label>
                                <textarea class="textarea-field" id="brandDescription" name="description"></textarea>
                            </div>
                            <div class="field-span-2 admin-form-actions">
                                <button class="btn btn--primary" id="brandSubmitButton" type="submit">Tambah Brand</button>
                                <button class="btn btn--ghost" id="brandReset" type="button">Reset</button>
                            </div>
                        </div>
                    </form>
                </article>

                <aside class="admin-manage-side">
                    <article class="panel admin-card admin-table-shell">
                        <div class="admin-card__header">
                            <h2 class="admin-card__title">Daftar Brand</h2>
                            <span class="pill" id="brandCount">0 brand</span>
                        </div>
                        <div class="admin-table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Brand</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="brandTableBody">
                                    <tr><td colspan="4">Memuat data brand...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </aside>
            </section>
        </div>
    </main>
</div>

<div class="status-panel" id="brandStatusPanel" aria-live="polite" aria-atomic="true"></div>

<script src="<?= base_url('js/gis-app.js') ?>"></script>
<script>
(() => {
    const readApi = <?= json_encode(base_url('api/brands')) ?>;
    const writeApi = <?= json_encode(base_url('admin-api/brands')) ?>;
    const form = document.getElementById('brandForm');
    const brandNameInput = document.getElementById('brandName');
    const brandSlugInput = document.getElementById('brandSlug');
    const brandColorInput = document.getElementById('brandColor');
    const brandColorValueInput = document.getElementById('brandColorValue');
    const brandSubmitButton = document.getElementById('brandSubmitButton');
    const brandStatusPanel = document.getElementById('brandStatusPanel');
    let rows = [];
    let statusPanelTimer = null;
    let pendingDeleteId = null;

    function updateSubmitButtonLabel() {
        brandSubmitButton.textContent = document.getElementById('brandId').value ? 'Update Brand' : 'Tambah Brand';
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
        brandStatusPanel.className = `status-panel is-visible is-${type}`;
        brandStatusPanel.style.display = 'flex';
        brandStatusPanel.style.opacity = '1';
        brandStatusPanel.style.pointerEvents = 'auto';
        brandStatusPanel.innerHTML = `
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
        brandStatusPanel.className = 'status-panel is-visible is-confirm';
        brandStatusPanel.style.display = 'flex';
        brandStatusPanel.style.opacity = '1';
        brandStatusPanel.style.pointerEvents = 'auto';
        brandStatusPanel.innerHTML = `
            <div class="status-panel__icon">${statusIconMarkup('confirm')}</div>
            <div class="status-panel__body">
                <strong>Hapus brand "${GISApp.escapeHtml(row.name)}"?</strong>
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
        brandStatusPanel.className = 'status-panel';
        brandStatusPanel.style.display = 'none';
        brandStatusPanel.style.opacity = '0';
        brandStatusPanel.style.pointerEvents = 'none';
        brandStatusPanel.innerHTML = '';
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

    function normalizeHex(value) {
        const raw = String(value || '').trim();
        if (/^#[0-9a-fA-F]{6}$/.test(raw)) {
            return raw.toLowerCase();
        }

        return '#234ee2';
    }

    function syncColorInputs(value) {
        const normalized = normalizeHex(value);
        brandColorInput.value = normalized;
        brandColorValueInput.value = normalized;
    }

    function resetForm() {
        form.reset();
        document.getElementById('brandId').value = '';
        document.getElementById('brandFormTitle').textContent = 'Tambah Brand';
        document.getElementById('brandFormMode').textContent = 'Mode tambah';
        document.getElementById('brandActive').value = '1';
        syncColorInputs('#234ee2');
        updateSubmitButtonLabel();
        hideStatusPanel();
    }

    function fillForm(row) {
        document.getElementById('brandId').value = row.id;
        document.getElementById('brandName').value = row.name || '';
        document.getElementById('brandSlug').value = row.slug || '';
        syncColorInputs(row.accent_color || '#234ee2');
        document.getElementById('brandDescription').value = row.description || '';
        document.getElementById('brandActive').value = row.is_active ? '1' : '0';
        document.getElementById('brandFormTitle').textContent = 'Edit Brand';
        document.getElementById('brandFormMode').textContent = 'Mode edit';
        updateSubmitButtonLabel();
        hideStatusPanel();
    }

    function render() {
        document.getElementById('brandCount').textContent = `${rows.length} brand`;
        document.getElementById('brandTableBody').innerHTML = rows.map((row) => `
            <tr>
                <td>
                    <div class="table-brand">
                        <span class="table-brand__name">${GISApp.escapeHtml(row.name)}</span>
                        <span class="table-subtle">${GISApp.escapeHtml(row.description || '-')}</span>
                    </div>
                </td>
                <td>${GISApp.escapeHtml(row.slug || '-')}</td>
                <td><span class="status-tag ${row.is_active ? 'is-success' : 'is-warning'}">${row.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                <td>
                    <div class="table-actions">
                        <button class="mini-button is-primary" type="button" data-edit="${row.id}">Edit</button>
                        <button class="mini-button is-danger" type="button" data-delete="${row.id}">Hapus</button>
                    </div>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="4">Belum ada data brand.</td></tr>';
    }

    async function load() {
        const response = await GISApp.fetchJson(readApi);
        rows = response.data || [];
        render();
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        brandSlugInput.value = normalizeSlug(brandSlugInput.value);
        syncColorInputs(brandColorValueInput.value);
        const payload = Object.fromEntries(new FormData(form).entries());
        const id = payload.id;
        delete payload.id;
        const isUpdate = Boolean(id);
        brandSubmitButton.disabled = true;
        showStatusPanel('loading', isUpdate ? 'Memperbarui brand...' : 'Menambahkan brand...');

        try {
            if (id) {
                await request(`${writeApi}/${id}`, { method: 'POST', body: JSON.stringify(payload) });
                showStatusPanel('success', 'Brand berhasil diperbarui.');
            } else {
                await request(writeApi, { method: 'POST', body: JSON.stringify(payload) });
                showStatusPanel('success', 'Brand berhasil ditambahkan.');
            }
            await load();
            resetForm();
        } catch (error) {
            showStatusPanel('error', error.message, error.details);
        } finally {
            brandSubmitButton.disabled = false;
            updateSubmitButtonLabel();
        }
    });

    brandNameInput.addEventListener('input', () => {
        brandSlugInput.value = normalizeSlug(brandNameInput.value);
    });

    brandColorInput.addEventListener('input', () => {
        syncColorInputs(brandColorInput.value);
    });

    brandColorValueInput.addEventListener('input', () => {
        if (/^#?[0-9a-fA-F]{0,6}$/.test(brandColorValueInput.value.trim())) {
            const normalized = brandColorValueInput.value.startsWith('#')
                ? brandColorValueInput.value
                : `#${brandColorValueInput.value}`;

            if (/^#[0-9a-fA-F]{6}$/.test(normalized)) {
                syncColorInputs(normalized);
            }
        }
    });

    brandColorValueInput.addEventListener('blur', () => {
        syncColorInputs(brandColorValueInput.value);
    });

    document.getElementById('brandReset').addEventListener('click', resetForm);
    brandStatusPanel.addEventListener('click', async (event) => {
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

            showStatusPanel('loading', `Menghapus brand "${row.name}"...`);
            try {
                await request(`${writeApi}/${deleteId}`, { method: 'DELETE' });
                showStatusPanel('success', `Brand "${row.name}" berhasil dihapus.`);
                await load();
                resetForm();
            } catch (error) {
                showStatusPanel('error', error.message, error.details);
            }
        }
    });

    document.getElementById('brandTableBody').addEventListener('click', async (event) => {
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
