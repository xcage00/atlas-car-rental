<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/admin-ui.php';

$error = '';
$message = $_SESSION['admin_message'] ?? '';
unset($_SESSION['admin_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    if (isset($_POST['delete_id'])) {
        $delete = $pdo->prepare('DELETE FROM vehicle_availability_blocks WHERE id = ?');
        $delete->execute([filter_var($_POST['delete_id'], FILTER_VALIDATE_INT) ?: 0]);
        $_SESSION['admin_message'] = 'Date block removed.';
        header('Location: ' . url('/admin/availability.php'));
        exit;
    }

    $vehicleId = filter_var($_POST['vehicle_id'] ?? null, FILTER_VALIDATE_INT);
    $start = trim($_POST['start_date'] ?? '');
    $end = trim($_POST['end_date'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $dateFormat = 'Y-m-d';
    $startDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $start);
    $endDate = DateTimeImmutable::createFromFormat('!' . $dateFormat, $end);
    $validStart = $startDate && $startDate->format($dateFormat) === $start && $startDate >= new DateTimeImmutable('today');
    $validEnd = $endDate && $endDate->format($dateFormat) === $end;

    if (!$vehicleId || !$validStart || !$validEnd || $endDate < $startDate || mb_strlen($note) > 255) {
        $error = 'Choose a vehicle, a start date today or later, an end date on or after it, and a note under 255 characters.';
    } else {
        $vehicleCheck = $pdo->prepare('SELECT id FROM vehicles WHERE id = ?');
        $vehicleCheck->execute([$vehicleId]);
        if (!$vehicleCheck->fetchColumn()) {
            $error = 'That vehicle could not be found.';
        } else {
            $overlap = $pdo->prepare('SELECT COUNT(*) FROM vehicle_availability_blocks WHERE vehicle_id = ? AND start_date <= ? AND end_date >= ?');
            $overlap->execute([$vehicleId, $end, $start]);
            if ((int)$overlap->fetchColumn() > 0) {
                $error = 'That date range overlaps an existing block. Edit the schedule before adding another.';
            } else {
                $insert = $pdo->prepare('INSERT INTO vehicle_availability_blocks (vehicle_id,start_date,end_date,note) VALUES (?,?,?,?)');
                $insert->execute([$vehicleId,$start,$end,$note !== '' ? $note : null]);
                $_SESSION['admin_message'] = 'Date range added. Matching searches will no longer show this vehicle.';
                header('Location: ' . url('/admin/availability.php'));
                exit;
            }
        }
    }
}

$vehicles = $pdo->query('SELECT id, brand, model, year FROM vehicles ORDER BY brand, model, year DESC')->fetchAll();
$blocks = $pdo->query('SELECT b.*, v.brand, v.model, v.year FROM vehicle_availability_blocks b JOIN vehicles v ON v.id = b.vehicle_id ORDER BY b.start_date DESC LIMIT 100')->fetchAll();
admin_header('Availability');
?>
<div class="admin-page-head"><div><span class="admin-kicker">Calendar</span><h1>Block dates</h1><p class="admin-muted">Record confirmed bookings or maintenance. Blocked cars will be excluded from matching date searches.</p></div></div>
<?php if ($message !== '') admin_flash($message); ?>
<?php if ($error !== ''): ?><p class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="admin-panel" style="margin-bottom:24px">
  <h2 style="font:600 24px Georgia,serif;margin:0 0 18px">Add an unavailable period</h2>
  <?php if (!$vehicles): ?><p class="admin-muted">Add a vehicle to your fleet first.</p><?php else: ?>
  <form class="admin-form" method="post">
    <?= csrf_field() ?>
    <div class="admin-field admin-field--wide"><label for="vehicle_id">Vehicle *</label><select id="vehicle_id" name="vehicle_id" required><?php foreach ($vehicles as $vehicle): ?><option value="<?= (int)$vehicle['id'] ?>"><?= e($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-field"><label for="start_date">First unavailable day *</label><input id="start_date" name="start_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['start_date'] ?? '') ?>" required></div>
    <div class="admin-field"><label for="end_date">Last unavailable day *</label><input id="end_date" name="end_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['end_date'] ?? '') ?>" required></div>
    <div class="admin-field admin-field--wide"><label for="note">Note</label><input id="note" name="note" maxlength="255" value="<?= e($_POST['note'] ?? '') ?>" placeholder="Confirmed booking or maintenance"></div>
    <div class="admin-form-actions"><button class="admin-button" type="submit">Save dates</button></div>
  </form>
  <?php endif; ?>
</section>
<div class="admin-page-head"><div><span class="admin-kicker">Schedule</span><h2>Recent date blocks</h2></div></div>
<?php if (!$blocks): ?><section class="admin-panel admin-empty"><h2>No date blocks yet</h2><p>Bookings and service periods you add will appear here.</p></section><?php else: ?>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Vehicle</th><th>From</th><th>Through</th><th>Note</th><th>Action</th></tr></thead><tbody>
<?php foreach ($blocks as $block): ?><tr><td><?= e($block['brand'] . ' ' . $block['model'] . ' ' . $block['year']) ?></td><td><?= e($block['start_date']) ?></td><td><?= e($block['end_date']) ?></td><td><?= e($block['note'] ?: '—') ?></td><td><form class="admin-inline-form" method="post" onsubmit="return confirm('Remove this date block?')"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= (int)$block['id'] ?>"><button class="admin-button admin-button--danger admin-button--small" type="submit">Remove</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php admin_footer(); ?>
