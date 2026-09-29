<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/whatsapp.php';

/* ---------- Filters from search bar ---------- */
$loc  = trim($_GET['location'] ?? '');
$type = trim($_GET['type'] ?? '');
$min  = trim($_GET['min_price'] ?? '');
$max  = trim($_GET['max_price'] ?? '');
$pick = trim($_GET['pickup'] ?? '');
$ret  = trim($_GET['return'] ?? '');

if (isset($_GET['search'])) {
    $qs = http_build_query(array_filter([
        'location'  => $loc,
        'type'      => $type,
        'min_price' => $min,
        'max_price' => $max,
        'pickup'    => $pick,
        'return'    => $ret,
    ], fn($v) => $v !== ''));
    header('Location: ' . url('/cars' . ($qs ? "?$qs" : '')));
    exit;
}

$featured = $pdo->query("
    SELECT v.*, (
        SELECT image_url FROM vehicle_images
        WHERE vehicle_id = v.id
        ORDER BY display_order ASC, id ASC LIMIT 1
    ) AS thumb
    FROM vehicles v
    ORDER BY (v.availability_status = 'available') DESC, v.created_at DESC
    LIMIT 6
")->fetchAll();

$locations = $pdo->query("SELECT DISTINCT location FROM vehicles WHERE location <> '' ORDER BY location")
                 ->fetchAll(PDO::FETCH_COLUMN);
$types     = $pdo->query("SELECT DISTINCT type FROM vehicles ORDER BY type")
                 ->fetchAll(PDO::FETCH_COLUMN);

$counts = $pdo->query("
    SELECT
      COUNT(*) AS total,
      SUM(availability_status = 'available') AS available
    FROM vehicles
")->fetch();

$pageTitle = SITE_NAME . ' — Find the right car for your journey';
$pageDesc  = 'Browse trusted vehicles, check availability, and connect directly with the owner on WhatsApp.';
$bodyClass = 'page-home';

require __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy reveal">
      <span class="eyebrow">Kigali · Rwanda</span>
      <h1>Atlas Automotive Services</h1>
      <p class="hero-lede">Find the right car for your journey.</p>
      <p class="hero-sub">Browse trusted vehicles, check availability, and connect directly with the owner.</p>
    </div>

    <?php if ((int)$counts['available'] > 0): ?>
      <div class="hero-stat reveal">
        <span class="hero-stat-num"><?= (int)$counts['available'] ?></span>
        <span class="hero-stat-label">cars available now</span>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============ SEARCH ============ -->
<section class="search-band">
  <div class="container">
    <form class="search-bar" method="get" action="<?= url('/cars') ?>" novalidate>
      <div class="search-field">
        <label for="s-location">Location</label>
        <select id="s-location" name="location">
          <option value="">Anywhere</option>
          <?php foreach ($locations as $l): ?>
            <option value="<?= e($l) ?>" <?= $loc === $l ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="search-field">
        <label for="s-pickup">Pickup</label>
        <input type="date" id="s-pickup" name="pickup" value="<?= e($pick) ?>" min="<?= date('Y-m-d') ?>">
      </div>

      <div class="search-field">
        <label for="s-return">Return</label>
        <input type="date" id="s-return" name="return" value="<?= e($ret) ?>" min="<?= date('Y-m-d') ?>">
      </div>

      <div class="search-field">
        <label for="s-type">Vehicle type</label>
        <select id="s-type" name="type">
          <option value="">Any type</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="search-field search-field--price">
        <label>Price / day (RWF)</label>
        <div class="price-pair">
          <input type="number" name="min_price" placeholder="Min" min="0" step="1000" value="<?= e($min) ?>">
          <span>–</span>
          <input type="number" name="max_price" placeholder="Max" min="0" step="1000" value="<?= e($max) ?>">
        </div>
      </div>

      <button class="btn btn--primary search-submit" type="submit" name="search" value="1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="7"/>
          <line x1="20" y1="20" x2="16.65" y2="16.65"/>
        </svg>
        Search
      </button>
    </form>
  </div>
</section>

<!-- ============ FEATURED CARS ============ -->
<section class="section">
  <div class="container">
    <header class="section-head reveal">
      <div>
        <span class="eyebrow">Featured</span>
        <h2>Vehicles ready for the road</h2>
      </div>
      <a class="section-link" href="<?= url('/cars') ?>">View all cars →</a>
    </header>

    <?php if (!$featured): ?>
      <p class="text-muted">No vehicles listed yet.</p>
    <?php else: ?>
      <div class="car-grid">
        <?php foreach ($featured as $v): ?>
          <?php require __DIR__ . '/includes/car-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="section section--paper how">
  <div class="container">
    <div class="how-grid">
      <div class="how-intro reveal">
        <span class="eyebrow">How Atlas works</span>
        <h2>No booking forms. No waiting.</h2>
        <p class="how-lede">We list the cars. You pick one. WhatsApp does the rest — the conversation goes straight to the person who owns and manages every vehicle on this site.</p>
        <a class="btn btn--onpaper" href="<?= url('/cars') ?>">Browse available cars</a>
      </div>

      <ol class="how-steps reveal">
        <li>
          <span class="step-num">01</span>
          <h3>Browse the fleet</h3>
          <p>Filter by location, dates, type, and price. Availability is updated by us, not guessed.</p>
        </li>
        <li>
          <span class="step-num">02</span>
          <h3>Open the listing</h3>
          <p>Photos, full specifications, daily price, and a clear availability status — before you commit to anything.</p>
        </li>
        <li>
          <span class="step-num">03</span>
          <h3>Message on WhatsApp</h3>
          <p>One tap opens WhatsApp with a message already written, referencing the exact car you're viewing.</p>
        </li>
      </ol>
    </div>
  </div>
</section>

<!-- ============ WHY ATLAS ============ -->
<section class="section why">
  <div class="container">
    <header class="section-head reveal">
      <div>
        <span class="eyebrow">Why Atlas</span>
        <h2>Straightforward by design</h2>
      </div>
    </header>

    <div class="why-grid">
      <article class="why-item reveal">
        <h3>A growing, real fleet</h3>
        <p>Every car on Atlas is listed, priced, and kept up to date by us. What you see is what's actually on the lot — no dead listings, no ghost inventory.</p>
      </article>
      <article class="why-item reveal">
        <h3>Prices before conversations</h3>
        <p>The daily rate is on the card. You know what a car costs before you reach out — no "DM for price" games, no surprises.</p>
      </article>
      <article class="why-item reveal">
        <h3>Direct line to the owner</h3>
        <p>WhatsApp opens with a message that already names the car. The reply comes from the person responsible for it — not a call centre.</p>
      </article>
      <article class="why-item reveal">
        <h3>Availability you can trust</h3>
        <p>Each vehicle is marked Available, Rented, or Unavailable. When a car's out, it's marked out — so you don't waste a message on it.</p>
      </article>
    </div>
  </div>
</section>

<!-- ============ CLOSING CTA ============ -->
<section class="closing">
  <div class="container closing-inner reveal">
    <span class="eyebrow">Ready when you are</span>
    <h2>Pick a car. Send a message. Go.</h2>
    <p>Browse the fleet and reach out on WhatsApp — it takes less than a minute.</p>
    <a class="btn btn--primary" href="<?= url('/cars') ?>">Explore Cars</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>