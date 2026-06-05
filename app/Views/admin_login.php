<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/gis_head', [
        'title' => 'Login - GeoSC Medan',
        'description' => 'Masuk ke panel GeoSC Medan menggunakan akun admin Supabase.',
    ]) ?>
</head>
<body class="admin-body">
<div class="auth-shell auth-shell--geosc">
    <section class="panel auth-card auth-card--geosc">
        <div class="auth-brand">
            <img class="auth-brand__logo" src="<?= base_url('images/geosc-medan-logo.svg') ?>" alt="GeoSC Medan">
            <div>
                <span class="pill">GeoSC Medan</span>
                <h1 class="section-title">Login Admin</h1>
            </div>
        </div>

        <div class="auth-hero-note">
            <p class="section-copy">Akses panel pengelolaan menggunakan akun email dan password yang terdaftar di Supabase Auth.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="admin-alert is-visible is-danger"><?= esc($error) ?></div>
        <?php endif; ?>

        <form class="auth-form" action="<?= base_url('login') ?>" method="post">
            <div class="field-stack">
                <label class="field-label" for="email">Email Admin</label>
                <input class="input-field" id="email" name="email" type="email" value="<?= esc($email ?? '') ?>" required autocomplete="email">
            </div>

            <div class="field-stack">
                <label class="field-label" for="password">Password</label>
                <input class="input-field" id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <div class="auth-actions auth-actions--stack">
                <button class="btn btn--primary" type="submit">Masuk ke Dashboard</button>
                <a class="btn btn--ghost" href="<?= base_url('/') ?>">Kembali ke Situs</a>
            </div>
        </form>
    </section>
</div>
</body>
</html>
