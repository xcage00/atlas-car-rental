<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/admin-ui.php';

$vehicleCount = (int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$availableCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE availability_status = 'available'")->fetchColumn();
$upcomingBlocks = (int)$pdo->query('SELECT COUNT(*) FROM vehicle_availability_blocks WHERE end_date >= CURDATE()')->fetchColumn();
$recentBlocks = $pdo->query("SELECT b.*, v.brand, v.model, v.year FROM vehicle_availability_blocks b JOIN vehicles v ON v.id = b.vehicle_id WHERE b.end_date >= CURDATE() ORDER BY b.start_date ASC LIMIT 6")->fetchAll();

admin_header('Dashboard');
?>
<div class="admin-page-head"><div><span class="admin-kicker">Overview</span><h1>Good day, <?= e(admin_user()['name']) ?>.</h1><p class="admin-muted">Here’s what’s happening with your fleet.</p></div><a class="admin-button" href="<?= e(url('/admin/vehicle-edit.php')) ?>">Add a vehicle</a></div>
<section class="admin-stats" aria-label="Fleet summary">
  <article class="admin-card admin-stat"><span>Vehicles listed</span><strong><?= $vehicleCount ?></strong></article>
  <article class="admin-card admin-stat"><span>Marked available</span><strong><?= $availableCount ?></strong></article>
  <article class="admin-card admin-stat"><span>Upcoming date blocks</span><strong><?= $upcomingBlocks ?></strong></article>
</section>
<section class="admin-panel">
  <div class="admin-page-head"><div><span class="admin-kicker">Schedule</span><h2>Upcoming blocked dates</h2></div><a class="admin-button admin-button--quiet" href="<?= e(url('/admin/availability.php')) ?>">Manage dates</a></div>
  <?php if (!$recentBlocks): ?><div class="admin-empty"><h2>No upcoming blocks</h2><p>Add a booking or maintenance period to update customer search results.</p></div><?php else: ?>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Vehicle</th><th>From</th><th>Through</th><th>Note</th></tr></thead><tbody>
      <?php foreach ($recentBlocks as $block): ?><tr><td><?= e($block['brand'] . ' ' . $block['model'] . ' ' . $block['year']) ?></td><td><?= e($block['start_date']) ?></td><td><?= e($block['end_date']) ?></td><td><?= e($block['note'] ?: '—') ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</section>
<?php admin_footer(); ?>
