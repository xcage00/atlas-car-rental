<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin-ui.php';

if (is_admin()) {
    header('Location: ' . url('/admin/'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $now = time();
    if (($_SESSION['login_window'] ?? 0) + 900 < $now) {
        $_SESSION['login_window'] = $now;
        $_SESSION['login_failures'] = 0;
    }
    if (($_SESSION['login_failures'] ?? 0) >= 5) {
        $error = 'Too many sign-in attempts. Wait 15 minutes, then try again.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([trim($_POST['email'] ?? '')]);
        $admin = $stmt->fetch();
        if ($admin && password_verify((string)($_POST['password'] ?? ''), $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = [
                'id' => (int)$admin['id'],
                'name' => $admin['name'],
                'email' => $admin['email'],
                'role' => $admin['role'],
            ];
            unset($_SESSION['login_failures'], $_SESSION['login_window'], $_SESSION['csrf']);
            header('Location: ' . url('/admin/'));
            exit;
        }
        $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;
        $error = 'Email or password is incorrect.';
    }
}

admin_header('Sign in');
?>
<section class="admin-login">
  <span class="admin-kicker">Atlas operations</span>
  <h1>Welcome back.</h1>
  <p class="admin-muted">Sign in to manage your fleet and date availability.</p>
  <div class="admin-card">
    <?php if ($error !== ''): ?><p class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form class="admin-form" method="post" action="<?= e(url('/admin/login.php')) ?>">
      <?= csrf_field() ?>
      <div class="admin-field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" required></div>
      <div class="admin-field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
      <div class="admin-form-actions"><button class="admin-button" type="submit">Sign in</button><a class="admin-button admin-button--quiet" href="<?= e(url('/')) ?>">Back to site</a></div>
    </form>
  </div>
</section>
<?php admin_footer(); ?>
