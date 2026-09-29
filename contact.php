<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/whatsapp.php';

$pageTitle = 'Contact — ' . SITE_NAME;
$pageDesc  = 'Reach Atlas Automotive Services directly. WhatsApp is the fastest way to get a reply.';
$bodyClass = 'page-contact';

/* ---------- Contact info from settings ---------- */
$waRaw  = setting($pdo, 'whatsapp_number', '');
$waNum  = wa_number($waRaw);
$phone  = setting($pdo, 'phone', '');
$email  = setting($pdo, 'email', '');
$loc    = setting($pdo, 'location', '');

$waLink = 'https://wa.me/' . $waNum . '?text=' . rawurlencode("Hi, I'd like to ask about renting a car from " . SITE_NAME . ".");

require __DIR__ . '/includes/header.php';
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <div class="container">
    <span class="eyebrow">Contact</span>
    <h1>Reach us directly.</h1>
    <p class="page-head-sub">WhatsApp is the fastest way to get a reply. If you'd rather not use WhatsApp, phone or email work too.</p>
  </div>
</section>

<!-- ============ CONTACT GRID ============ -->
<section class="section section--tight">
  <div class="container contact-grid">

    <!-- Primary: WhatsApp -->
    <div class="contact-primary reveal">
      <div class="contact-primary-inner">
        <span class="eyebrow">Fastest</span>
        <h2>Chat on WhatsApp</h2>
        <p>Message us about any car in the fleet — or a general question. We reply from the same number that owns and manages every vehicle on this site.</p>

        <a class="btn btn--whatsapp btn--block" href="<?= e($waLink) ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M20.52 3.48A11.9 11.9 0 0 0 12.05 0C5.5 0 .2 5.3.2 11.85c0 2.09.55 4.13 1.6 5.93L0 24l6.36-1.67a11.86 11.86 0 0 0 5.69 1.45h.01c6.55 0 11.85-5.3 11.85-11.85 0-3.17-1.23-6.15-3.39-8.45zM12.06 21.5h-.01a9.7 9.7 0 0 1-4.94-1.36l-.35-.21-3.77.99 1.01-3.68-.23-.38a9.67 9.67 0 0 1-1.48-5.16c0-5.35 4.35-9.7 9.7-9.7 2.59 0 5.03 1.01 6.86 2.85a9.63 9.63 0 0 1 2.84 6.86c0 5.36-4.35 9.7-9.63 9.7zm5.32-7.26c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.66.15s-.76.95-.93 1.14c-.17.19-.34.21-.63.07-.29-.15-1.23-.46-2.34-1.45-.86-.77-1.45-1.72-1.62-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.19-.29.29-.48.1-.19.05-.36-.02-.51-.07-.15-.66-1.58-.9-2.16-.24-.57-.48-.49-.66-.5l-.56-.01a1.08 1.08 0 0 0-.78.37c-.27.29-1.02.99-1.02 2.42s1.04 2.81 1.18 3c.15.19 2.05 3.13 4.96 4.39.69.3 1.23.48 1.66.61.69.22 1.32.19 1.82.12.56-.08 1.72-.7 1.96-1.38.24-.68.24-1.26.17-1.38-.07-.12-.27-.19-.56-.34z"/>
          </svg>
          Open WhatsApp
        </a>

        <p class="contact-hint">A short message is enough — tell us which car and your dates.</p>
      </div>
    </div>

    <!-- Secondary: details -->
    <div class="contact-secondary reveal">
      <ul class="contact-list">
        <?php if ($phone): ?>
          <li>
            <span class="contact-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <div>
              <span class="contact-label">Phone</span>
              <a class="contact-value" href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
            </div>
          </li>
        <?php endif; ?>

        <?php if ($email): ?>
          <li>
            <span class="contact-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,6 12,13 22,6"/></svg>
            </span>
            <div>
              <span class="contact-label">Email</span>
              <a class="contact-value" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            </div>
          </li>
        <?php endif; ?>

        <?php if ($loc): ?>
          <li>
            <span class="contact-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div>
              <span class="contact-label">Location</span>
              <span class="contact-value"><?= e($loc) ?></span>
            </div>
          </li>
        <?php endif; ?>

        <li>
          <span class="contact-icon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </span>
          <div>
            <span class="contact-label">Hours</span>
            <span class="contact-value">Every day · 7:00 – 21:00</span>
          </div>
        </li>
      </ul>

      <div class="contact-note">
        <h3>Before you message</h3>
        <p>For the fastest reply, tell us the car you're interested in and the dates you need it. If you're not sure yet, just say what you're planning — we'll help you pick.</p>
      </div>
    </div>

  </div>
</section>

<!-- ============ CLOSING ============ -->
<section class="closing">
  <div class="container closing-inner reveal">
    <span class="eyebrow">Not sure where to start?</span>
    <h2>Browse the fleet first.</h2>
    <p>Every listing shows the price and whether the car is available right now — so you know before you ask.</p>
    <a class="btn btn--primary" href="<?= url('/cars') ?>">Explore Cars</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>