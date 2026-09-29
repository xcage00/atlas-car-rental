<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/whatsapp.php';

/* ---------- Resolve the vehicle ---------- */
$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: ' . url('/cars'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$car = $stmt->fetch();

if (!$car) {
    http_response_code(404);
    $pageTitle = 'Car not found — ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty">'
       . '<h3>We couldn\'t find that car.</h3>'
       . '<p>It may have been removed or the link is wrong.</p>'
       . '<a class="btn btn--primary" href="' . url('/cars') . '">Back to all cars</a>'
       . '</div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* ---------- Images, WhatsApp, related ---------- */
$images = vehicle_images($pdo, (int)$car['id']);
if (!$images) $images = [PLACEHOLDER_IMG];

$dateFormat = 'Y-m-d';
$pickup = trim($_GET['pickup'] ?? '');
$return = trim($_GET['return'] ?? '');
$pickupDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $pickup);
$returnDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $return);
$dateRangeValid = $pickupDate && $pickupDate->format($dateFormat) === $pickup
    && $returnDate && $returnDate->format($dateFormat) === $return
    && $pickupDate >= new DateTimeImmutable('today')
    && $returnDate > $pickupDate;
$wa = whatsapp_url($pdo, $car, $dateRangeValid ? $pickup : null, $dateRangeValid ? $return : null);

$related = $pdo->prepare("
    SELECT v.*, (
        SELECT image_url FROM vehicle_images
        WHERE vehicle_id = v.id
        ORDER BY display_order ASC, id ASC LIMIT 1
    ) AS thumb
    FROM vehicles v
    WHERE v.id <> ?
    ORDER BY (v.type = ?) DESC, (v.availability_status = 'available') DESC, v.created_at DESC
    LIMIT 3
");
$related->execute([$car['id'], $car['type']]);
$relatedCars = $related->fetchAll();

$title  = trim($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']);
$isAvail = $car['availability_status'] === 'available';

$pageTitle = $title . ' — ' . SITE_NAME;
$pageDesc  = mb_substr(trim(strip_tags((string)$car['description'])), 0, 155);
$bodyClass = 'page-car';

require __DIR__ . '/includes/header.php';
?>

<!-- ============ BREADCRUMB ============ -->
<nav class="breadcrumb container" aria-label="Breadcrumb">
  <a href="<?= url('/') ?>">Home</a>
  <span aria-hidden="true">/</span>
  <a href="<?= url('/cars') ?>">Cars</a>
  <span aria-hidden="true">/</span>
  <span aria-current="page"><?= e($title) ?></span>
</nav>

<!-- ============ MAIN ============ -->
<section class="car-detail">
  <div class="container">

    <header class="car-detail-head">
      <div>
        <span class="eyebrow"><?= e($car['type']) ?> · <?= e($car['location']) ?></span>
        <h1><?= e($title) ?></h1>
      </div>
      <span class="status status--<?= e($car['availability_status']) ?> status--lg">
        <?= e(status_label($car['availability_status'])) ?>
      </span>
    </header>

    <!-- ---------- Gallery ---------- -->
    <div class="gallery" id="gallery">
      <div class="gallery-main">
        <img id="gallery-main-img"
             src="<?= e(vehicle_image_url($images[0])) ?>"
             alt="<?= e($title) ?> — main photo"
             fetchpriority="high">
        <?php if (count($images) > 1): ?>
          <button class="gallery-nav gallery-prev" type="button" aria-label="Previous photo">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <button class="gallery-nav gallery-next" type="button" aria-label="Next photo">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        <?php endif; ?>
      </div>

      <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs" role="tablist" aria-label="Vehicle photos">
          <?php foreach ($images as $i => $img): ?>
            <button class="gallery-thumb <?= $i === 0 ? 'is-active' : '' ?>"
                    type="button"
                    role="tab"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                    data-index="<?= $i ?>"
                    data-src="<?= e(vehicle_image_url($img)) ?>">
              <img src="<?= e(vehicle_image_url($img)) ?>" alt="<?= e($title) ?> — photo <?= $i + 1 ?>" loading="lazy">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <script type="application/json" id="gallery-data">
        <?= json_encode(array_map('vehicle_image_url', $images), JSON_UNESCAPED_SLASHES) ?>
      </script>
    </div>

    <!-- ---------- Two-column body ---------- -->
    <div class="car-detail-grid">

      <!-- ---------- Left: content ---------- -->
      <div class="car-detail-main">

        <section class="detail-block">
          <h2>Specifications</h2>
          <dl class="spec-grid">
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Year
              </dt>
              <dd><?= (int)$car['year'] ?></dd>
            </div>
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2"/></svg>
                Transmission
              </dt>
              <dd><?= e($car['transmission']) ?></dd>
            </div>
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 22V8a2 2 0 0 1 2-2h6v16"/><path d="M13 22V4a2 2 0 0 1 2-2h6v20"/><line x1="7" y1="10" x2="7" y2="10"/><line x1="7" y1="14" x2="7" y2="14"/></svg>
                Fuel
              </dt>
              <dd><?= e($car['fuel_type']) ?></dd>
            </div>
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                Seats
              </dt>
              <dd><?= (int)$car['seats'] ?></dd>
            </div>
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M15 3v18"/></svg>
                Doors
              </dt>
              <dd><?= (int)$car['doors'] ?></dd>
            </div>
            <div>
              <dt>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Location
              </dt>
              <dd><?= e($car['location']) ?></dd>
            </div>
          </dl>
        </section>

        <?php if (!empty($car['description'])): ?>
          <section class="detail-block">
            <h2>About this car</h2>
            <p class="detail-desc"><?= nl2br(e($car['description'])) ?></p>
          </section>
        <?php endif; ?>

        <section class="detail-block detail-listed-by">
          <p>
            <span class="listed-label">Listed and managed by</span>
            <strong><?= e(SITE_NAME) ?></strong>
          </p>
        </section>

      </div>

      <!-- ---------- Right: sticky booking card ---------- -->
      <aside class="car-detail-aside">
        <div class="booking-card">
          <div class="booking-price">
            <span class="booking-price-num"><?= e(money($car['price_per_day'])) ?></span>
            <span class="booking-price-unit">/ day</span>
          </div>

          <span class="status status--<?= e($car['availability_status']) ?> status--block">
            <?= e(status_label($car['availability_status'])) ?>
          </span>

          <?php if ($isAvail): ?>
            <a class="btn btn--whatsapp btn--block" href="<?= e($wa) ?>" target="_blank" rel="noopener">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.52 3.48A11.9 11.9 0 0 0 12.05 0C5.5 0 .2 5.3.2 11.85c0 2.09.55 4.13 1.6 5.93L0 24l6.36-1.67a11.86 11.86 0 0 0 5.69 1.45h.01c6.55 0 11.85-5.3 11.85-11.85 0-3.17-1.23-6.15-3.39-8.45zM12.06 21.5h-.01a9.7 9.7 0 0 1-4.94-1.36l-.35-.21-3.77.99 1.01-3.68-.23-.38a9.67 9.67 0 0 1-1.48-5.16c0-5.35 4.35-9.7 9.7-9.7 2.59 0 5.03 1.01 6.86 2.85a9.63 9.63 0 0 1 2.84 6.86c0 5.36-4.35 9.7-9.63 9.7zm5.32-7.26c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.66.15s-.76.95-.93 1.14c-.17.19-.34.21-.63.07-.29-.15-1.23-.46-2.34-1.45-.86-.77-1.45-1.72-1.62-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.19-.29.29-.48.1-.19.05-.36-.02-.51-.07-.15-.66-1.58-.9-2.16-.24-.57-.48-.49-.66-.5l-.56-.01a1.08 1.08 0 0 0-.78.37c-.27.29-1.02.99-1.02 2.42s1.04 2.81 1.18 3c.15.19 2.05 3.13 4.96 4.39.69.3 1.23.48 1.66.61.69.22 1.32.19 1.82.12.56-.08 1.72-.7 1.96-1.38.24-.68.24-1.26.17-1.38-.07-.12-.27-.19-.56-.34z"/>
              </svg>
              Chat with Owner on WhatsApp
            </a>
            <p class="booking-note">Opens WhatsApp with a message about this car already written.</p>
          <?php else: ?>
            <button class="btn btn--block" disabled aria-disabled="true">
              <?= $car['availability_status'] === 'rented' ? 'Currently rented' : 'Currently unavailable' ?>
            </button>
            <p class="booking-note">This car can't be requested right now. Browse other available vehicles below.</p>
            <a class="btn btn--ghost btn--block" href="<?= url('/cars?status=available') ?>">See available cars</a>
          <?php endif; ?>

          <ul class="booking-meta">
            <li>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= e($car['location']) ?>
            </li>
            <li>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Priced per day, no hidden fees
            </li>
            <li>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Availability updated by us, not guessed
            </li>
          </ul>
        </div>
      </aside>

    </div>
  </div>
</section>

<!-- ============ MOBILE STICKY CTA ============ -->
<?php if ($isAvail): ?>
  <div class="mobile-cta">
    <div class="mobile-cta-price">
      <strong><?= e(money($car['price_per_day'])) ?></strong>
      <span>/ day</span>
    </div>
    <a class="btn btn--whatsapp" href="<?= e($wa) ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M20.52 3.48A11.9 11.9 0 0 0 12.05 0C5.5 0 .2 5.3.2 11.85c0 2.09.55 4.13 1.6 5.93L0 24l6.36-1.67a11.86 11.86 0 0 0 5.69 1.45h.01c6.55 0 11.85-5.3 11.85-11.85 0-3.17-1.23-6.15-3.39-8.45zM12.06 21.5h-.01a9.7 9.7 0 0 1-4.94-1.36l-.35-.21-3.77.99 1.01-3.68-.23-.38a9.67 9.67 0 0 1-1.48-5.16c0-5.35 4.35-9.7 9.7-9.7 2.59 0 5.03 1.01 6.86 2.85a9.63 9.63 0 0 1 2.84 6.86c0 5.36-4.35 9.7-9.63 9.7zm5.32-7.26c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.66.15s-.76.95-.93 1.14c-.17.19-.34.21-.63.07-.29-.15-1.23-.46-2.34-1.45-.86-.77-1.45-1.72-1.62-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.19-.29.29-.48.1-.19.05-.36-.02-.51-.07-.15-.66-1.58-.9-2.16-.24-.57-.48-.49-.66-.5l-.56-.01a1.08 1.08 0 0 0-.78.37c-.27.29-1.02.99-1.02 2.42s1.04 2.81 1.18 3c.15.19 2.05 3.13 4.96 4.39.69.3 1.23.48 1.66.61.69.22 1.32.19 1.82.12.56-.08 1.72-.7 1.96-1.38.24-.68.24-1.26.17-1.38-.07-.12-.27-.19-.56-.34z"/>
      </svg>
      Chat on WhatsApp
    </a>
  </div>
<?php endif; ?>

<!-- ============ RELATED ============ -->
<?php if ($relatedCars): ?>
  <section class="section section--tight related">
    <div class="container">
      <header class="section-head reveal">
        <div>
          <span class="eyebrow">You might also like</span>
          <h2>Other cars in the fleet</h2>
        </div>
        <a class="section-link" href="<?= url('/cars') ?>">View all cars →</a>
      </header>

      <div class="car-grid">
        <?php foreach ($relatedCars as $v): ?>
          <?php require __DIR__ . '/includes/car-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
