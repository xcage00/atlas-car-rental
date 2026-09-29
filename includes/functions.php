<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/** Prefix a path with the app's base URL. */
function url(string $path = '/'): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/** Escape for HTML output. */
function e(?string $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Slugify a string for URLs. */
function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'vehicle';
}

/** Format a price in RWF. */
function money(float|int|string $amount): string {
    return CURRENCY . ' ' . number_format((float)$amount, 0);
}

/** Human label for availability status. */
function status_label(string $status): string {
    return match ($status) {
        'available'   => 'Available',
        'rented'      => 'Rented',
        'unavailable' => 'Unavailable',
        default       => ucfirst($status),
    };
}

/** First image URL for a vehicle, or a branded placeholder. */
function vehicle_image_url(?string $url): string {
    if (!$url) return PLACEHOLDER_IMG;
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || $path === '') return PLACEHOLDER_IMG;
    $rel = '/' . ltrim($path, '/');
    $base = rtrim(BASE_URL, '/');
    $filePath = $base !== '' && str_starts_with($rel, $base . '/') ? substr($rel, strlen($base)) : $rel;
    if (!is_file(BASE_PATH . $filePath)) return PLACEHOLDER_IMG;
    return $base !== '' && str_starts_with($rel, $base . '/') ? $rel : url($rel);
}

/** Fetch all images for a vehicle, ordered. */
function vehicle_images(PDO $pdo, int $vehicleId): array {
    $stmt = $pdo->prepare("SELECT image_url FROM vehicle_images WHERE vehicle_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$vehicleId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Fetch a single setting value (cached per request). */
function setting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $v = $stmt->fetchColumn();
    return $cache[$key] = ($v !== false ? (string)$v : $default);
}

/** Normalise a phone number for wa.me (digits only). */
function wa_number(string $raw): string {
    return preg_replace('/\D+/', '', $raw);
}

/** Current path relative to BASE_URL, e.g. "/cars". */
function current_path(): string {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (BASE_URL !== '' && str_starts_with($p, BASE_URL)) {
        $p = substr($p, strlen(BASE_URL));
    }
    return $p === '' ? '/' : $p;
}
