<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

/* ---------- Filter inputs ---------- */
$loc    = trim($_GET['location'] ?? '');
$type   = trim($_GET['type'] ?? '');
$min    = trim($_GET['min_price'] ?? '');
$max    = trim($_GET['max_price'] ?? '');
$pick   = trim($_GET['pickup'] ?? '');
$ret    = trim($_GET['return'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort   = trim($_GET['sort'] ?? 'featured');

/* Dates are an inquiry preference, not a live availability calendar. */
$dateFormat = 'Y-m-d';
$pickupDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $pick);
$returnDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $ret);
$dateRangeValid = $pickupDate && $pickupDate->format($dateFormat) === $pick
    && $returnDate && $returnDate->format($dateFormat) === $ret
    && $pickupDate >= new DateTimeImmutable('today')
    && $returnDate > $pickupDate;

/* ---------- Build query ---------- */
$where  = [];
$params = [];

if ($loc !== '') {
    $where[] = 'v.location = :loc';
    $params[':loc'] = $loc;
}
if ($type !== '') {
    $where[] = 'v.type = :type';
    $params[':type'] = $type;
}
if ($min !== '' && is_numeric($min)) {
    $where[] = 'v.price_per_day >= :min';
    $params[':min'] = (float)$min;
}
if ($max !== '' && is_numeric($max)) {
    $where[] = 'v.price_per_day <= :max';
    $params[':max'] = (float)$max;
}
if (in_array($status, ['available', 'rented', 'unavailable'], true)) {
    $where[] = 'v.availability_status = :status';
    $params[':status'] = $status;
}

$sql = "SELECT v.*, (
            SELECT image_url FROM vehicle_images
            WHERE vehicle_id = v.id
            ORDER BY display_order ASC, id ASC LIMIT 1
        ) AS thumb
        FROM vehicles v";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);

$sql .= match ($sort) {
    'price_asc'  => ' ORDER BY v.price_per_day ASC',
    'price_desc' => ' ORDER BY v.price_per_day DESC',
    'newest'     => ' ORDER BY v.year DESC, v.created_at DESC',
    'name'       => ' ORDER BY v.brand ASC, v.model ASC',
    default      => " ORDER BY (v.availability_status = 'available') DESC, v.created_at DESC",
};

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

/* ---------- Filter option sources ---------- */
$locations = $pdo->query("SELECT DISTINCT location FROM vehicles WHERE location <> '' ORDER BY location")
                 ->fetchAll(PDO::FETCH_COLUMN);
$types     = $pdo->query("SELECT DISTINCT type FROM vehicles ORDER BY type")
                 ->fetchAll(PDO::FETCH_COLUMN);

/* ---------- Active filter count (for the mobile "Filters" button) ---------- */
$activeCount = 0;
foreach (['location','type','min_price','max_price','status','pickup','return'] as $k) {
    if (trim($_GET[$k] ?? '') !== '') $activeCount++;
}

/* ---------- Helpers ---------- */
function qs_with(array $overrides): string {
    $base = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') unset($base[$k]);
        else $base[$k] = $v;
    }
    return $base ? '?' . http_build_query($base) : '';
}

$pageTitle = 'Cars — ' . SITE_NAME;
$pageDesc  = 'Browse every vehicle in the Atlas fleet. Filter by location, type, price, and availability.';
$bodyClass = 'page-cars';

require __DIR__ . '/includes/header.php';
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <div class="container">
    <span class="eyebrow">The fleet</span>
    <h1>Browse available cars</h1>
    <p class="page-head-sub">Every vehicle on this page is listed and managed by Atlas. Filter to narrow it down, then message us directly on WhatsApp.</p>
  </div>
</section>

<!-- ============ LAYOUT ============ -->
<section class="section section--tight cars-layout-wrap">
  <div class="container cars-layout">

    <!-- ---------- Sidebar filters ---------- -->
    <aside class="filters" id="filters" aria-label="Filters">
      <div class="filters-head">
        <h2>Filters</h2>
        <button class="filters-close" id="filters-close" aria-label="Close filters">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
        </button>
      </div>

      <form method="get" action="<?= url('/cars') ?>" id="filter-form">
        <div class="filter-group">
          <label for="f-location">Location</label>
          <select id="f-location" name="location">
            <option value="">Anywhere</option>
            <?php foreach ($locations as $l): ?>
              <option value="<?= e($l) ?>" <?= $loc === $l ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label for="f-type">Vehicle type</label>
          <select id="f-type" name="type">
            <option value="">Any type</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Price / day (RWF)</label>
          <div class="filter-price">
            <input type="number" name="min_price" placeholder="Min" min="0" step="1000" value="<?= e($min) ?>">
            <span>–</span>
            <input type="number" name="max_price" placeholder="Max" min="0" step="1000" value="<?= e($max) ?>">
          </div>
        </div>

        <div class="filter-group">
          <label for="f-pickup">Pickup date</label>
          <input type="date" id="f-pickup" name="pickup" value="<?= e($pick) ?>" min="<?= date('Y-m-d') ?>">
        </div>

        <div class="filter-group">
          <label for="f-return">Return date</label>
          <input type="date" id="f-return" name="return" value="<?= e($ret) ?>" min="<?= date('Y-m-d') ?>">
        </div>

        <div class="filter-group">
          <label>Availability</label>
          <div class="filter-chips">
            <?php
              $statuses = ['' => 'All', 'available' => 'Available', 'rented' => 'Rented', 'unavailable' => 'Unavailable'];
              foreach ($statuses as $val => $label):
                $isOn = ($status === $val);
            ?>
              <label class="chip <?= $isOn ? 'is-on' : '' ?>">
                <input type="radio" name="status" value="<?= e($val) ?>" <?= $isOn ? 'checked' : '' ?>>
                <span><?= e($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <input type="hidden" name="sort" value="<?= e($sort) ?>">

        <div class="filter-actions">
          <button class="btn btn--primary btn--block" type="submit">Apply filters</button>
          <a class="filter-reset" href="<?= url('/cars') ?>">Clear all</a>
        </div>
      </form>
    </aside>

    <!-- ---------- Results ---------- -->
    <div class="results">
      <div class="results-bar">
        <div class="results-count">
          <strong><?= count($cars) ?></strong>
          <?= count($cars) === 1 ? 'car' : 'cars' ?>
          <?php if ($activeCount > 0): ?>
            <span class="results-active">· <?= $activeCount ?> filter<?= $activeCount === 1 ? '' : 's' ?> active</span>
          <?php endif; ?>
        </div>

        <div class="results-controls">
          <button class="btn btn--ghost filters-open" id="filters-open" type="button">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <line x1="4" y1="6" x2="20" y2="6"/>
              <line x1="4" y1="12" x2="20" y2="12"/>
              <line x1="4" y1="18" x2="20" y2="18"/>
            </svg>
            Filters<?= $activeCount ? " ($activeCount)" : '' ?>
          </button>

          <label class="sort-control">
            <span>Sort</span>
            <select name="sort" form="filter-form" onchange="this.form.submit()">
              <option value="featured"  <?= $sort === 'featured'   ? 'selected' : '' ?>>Featured</option>
              <option value="price_asc" <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low → High</option>
              <option value="price_desc"<?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
              <option value="newest"    <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest year</option>
              <option value="name"      <?= $sort === 'name'       ? 'selected' : '' ?>>Name A–Z</option>
            </select>
          </label>
        </div>
      </div>

      <?php if (!$cars): ?>
        <div class="empty">
          <h3>No cars match those filters.</h3>
          <p>Try widening the price range or clearing a filter.</p>
          <a class="btn btn--primary" href="<?= url('/cars') ?>">Reset search</a>
        </div>
      <?php else: ?>
        <?php if ($dateRangeValid): ?>
          <p class="text-muted">Dates are included in your WhatsApp inquiry. Availability for those dates will be confirmed directly with Atlas.</p>
        <?php endif; ?>
        <div class="car-grid">
          <?php foreach ($cars as $v): ?>
            <?php $v['detail_query'] = $dateRangeValid ? '?' . http_build_query(['pickup' => $pick, 'return' => $ret]) : ''; ?>
            <?php require __DIR__ . '/includes/car-card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<div class="filters-backdrop" id="filters-backdrop" hidden></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
