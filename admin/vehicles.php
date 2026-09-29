<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/admin-ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['admin_message'] = $stmt->rowCount() ? 'Vehicle removed.' : 'Vehicle was not found.';
    }
    header('Location: ' . url('/admin/vehicles.php'));
    exit;
}

$message = $_SESSION['admin_message'] ?? '';
unset($_SESSION['admin_message']);
$vehicles = $pdo->query("SELECT v.*, (SELECT image_url FROM vehicle_images i WHERE i.vehicle_id = v.id ORDER BY i.display_order, i.id LIMIT 1) AS thumb FROM vehicles v ORDER BY v.created_at DESC")->fetchAll();

admin_header('Fleet');
?>
<div class="admin-page-head"><div><span class="admin-kicker">Inventory</span><h1>Your fleet</h1><p class="admin-muted">Update listings, pricing, photos, and public status.</p></div><a class="admin-button" href="<?= e(url('/admin/vehicle-edit.php')) ?>">Add a vehicle</a></div>
<?php if ($message !== '') admin_flash($message); ?>
<?php if (!$vehicles): ?><section class="admin-panel admin-empty"><h2>Your fleet starts here.</h2><p>Add your first vehicle to publish it on the site.</p><a class="admin-button" href="<?= e(url('/admin/vehicle-edit.php')) ?>">Add a vehicle</a></section><?php else: ?>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Photo</th><th>Vehicle</th><th>Daily price</th><th>Status</th><th>Location</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($vehicles as $vehicle): ?><tr>
  <td><img class="admin-thumb" src="<?= e(vehicle_image_url($vehicle['thumb'])) ?>" alt=""></td>
  <td><strong><?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?></strong><br><span class="admin-muted"><?= (int)$vehicle['year'] ?> · <?= e($vehicle['type']) ?></span></td>
  <td><?= e(money($vehicle['price_per_day'])) ?></td>
  <td><span class="admin-pill <?= $vehicle['availability_status'] === 'available' ? '' : 'admin-pill--blocked' ?>"><?= e(status_label($vehicle['availability_status'])) ?></span></td>
  <td><?= e($vehicle['location']) ?></td>
  <td><div class="admin-table-actions"><a class="admin-button admin-button--quiet admin-button--small" href="<?= e(url('/admin/vehicle-edit.php?id=' . (int)$vehicle['id'])) ?>">Edit</a><form class="admin-inline-form" method="post" onsubmit="return confirm('Remove this vehicle and its photos?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$vehicle['id'] ?>"><button class="admin-button admin-button--danger admin-button--small" type="submit">Remove</button></form></div></td>
</tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php admin_footer(); ?>
