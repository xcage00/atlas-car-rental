<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function admin_header(string $title): void {
    $user = admin_user();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> · <?= e(SITE_SHORT) ?> Admin</title>
      <link rel="stylesheet" href="<?= e(url('/admin/admin.css')) ?>">
    </head>
    <body class="admin-body">
      <header class="admin-topbar">
        <a class="admin-brand" href="<?= e(url('/admin/')) ?>">ATLAS <span>ADMIN</span></a>
        <nav aria-label="Admin navigation">
          <a href="<?= e(url('/admin/vehicles.php')) ?>">Fleet</a>
          <a href="<?= e(url('/admin/availability.php')) ?>">Availability</a>
          <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">View site</a>
        </nav>
        <?php if ($user): ?>
          <form method="post" action="<?= e(url('/admin/logout.php')) ?>">
            <?= csrf_field() ?>
            <button class="admin-logout" type="submit">Sign out</button>
          </form>
        <?php endif; ?>
      </header>
      <main class="admin-main">
    <?php
}

function admin_footer(): void {
    ?>
      </main>
    </body>
    </html>
    <?php
}

function admin_flash(string $message, string $kind = 'success'): void {
    echo '<p class="admin-alert admin-alert--' . e($kind) . '" role="status">' . e($message) . '</p>';
}
