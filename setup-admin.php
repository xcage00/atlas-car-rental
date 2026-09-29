<?php
declare(strict_types=1);

/*
 * Create the first Atlas administrator from the command line only.
 * Set ATLAS_ADMIN_NAME, ATLAS_ADMIN_EMAIL, and ATLAS_ADMIN_PASSWORD before running.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/includes/db.php';

$name = trim((string)getenv('ATLAS_ADMIN_NAME'));
$email = trim((string)getenv('ATLAS_ADMIN_EMAIL'));
$password = (string)getenv('ATLAS_ADMIN_PASSWORD');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Set ATLAS_ADMIN_NAME, a valid ATLAS_ADMIN_EMAIL, and an ATLAS_ADMIN_PASSWORD of at least 12 characters.\n");
    exit(1);
}

try {
    $existing = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($existing > 0) {
        fwrite(STDERR, "An administrator already exists. This command only creates the first account.\n");
        exit(1);
    }

    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, 'superadmin')");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Administrator created. Clear ATLAS_ADMIN_PASSWORD from the environment now.\n");
} catch (Throwable $e) {
    fwrite(STDERR, "Could not create administrator: " . $e->getMessage() . "\n");
    exit(1);
}
