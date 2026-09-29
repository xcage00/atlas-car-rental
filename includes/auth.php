<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function admin_user(): ?array {
    return $_SESSION['admin'] ?? null;
}

function is_admin(): bool {
    return admin_user() !== null;
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . url('/admin/login.php'));
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): void {
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}