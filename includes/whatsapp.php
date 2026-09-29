<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Build the wa.me URL with a pre-filled, vehicle-specific message.
 */
function whatsapp_url(PDO $pdo, array $vehicle, ?string $pickup = null, ?string $return = null, bool $dateBlocked = false): string {
    $number  = wa_number(setting($pdo, 'whatsapp_number', ''));
    $title   = trim(($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '') . ' ' . ($vehicle['year'] ?? ''));
    $message = "Hi, I'm interested in renting the {$title} listed on " . SITE_NAME . '.';
    if ($pickup !== null && $return !== null) {
        $message .= $dateBlocked
            ? " I saw those dates are blocked ({$pickup} to {$return}). Could you suggest other dates?"
            : " Could you confirm availability from {$pickup} to {$return}?";
    } else {
        $message .= ' Is it available for my requested dates?';
    }
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}
