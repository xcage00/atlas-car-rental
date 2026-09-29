<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/admin-ui.php';

$types = ['SUV', 'Sedan', 'Hatchback', 'Pickup', 'Van', 'Coupe', 'Convertible', 'Wagon', 'Minibus'];
$transmissions = ['Automatic', 'Manual'];
$fuels = ['Petrol', 'Diesel', 'Hybrid', 'Electric'];
$statuses = ['available', 'rented', 'unavailable'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = $pdo->prepare('SELECT * FROM vehicles WHERE id = ?');
$stmt->execute([$id]);
$vehicle = $stmt->fetch();
if ($id && !$vehicle) {
    http_response_code(404);
    exit('Vehicle not found.');
}
$vehicle = $vehicle ?: [
    'brand' => '', 'model' => '', 'year' => date('Y'), 'type' => 'SUV', 'price_per_day' => '',
    'transmission' => 'Automatic', 'fuel_type' => 'Petrol', 'seats' => 5, 'doors' => 4,
    'location' => 'Kigali', 'description' => '', 'availability_status' => 'available',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = filter_var($_POST['year'] ?? null, FILTER_VALIDATE_INT);
    $type = $_POST['type'] ?? '';
    $price = filter_var($_POST['price_per_day'] ?? null, FILTER_VALIDATE_FLOAT);
    $transmission = $_POST['transmission'] ?? '';
    $fuel = $_POST['fuel_type'] ?? '';
    $seats = filter_var($_POST['seats'] ?? null, FILTER_VALIDATE_INT);
    $doors = filter_var($_POST['doors'] ?? null, FILTER_VALIDATE_INT);
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['availability_status'] ?? '';

    if ($brand === '' || $model === '' || !$year || $year < 1950 || $year > (int)date('Y') + 1
        || !in_array($type, $types, true) || $price === false || $price <= 0
        || !in_array($transmission, $transmissions, true) || !in_array($fuel, $fuels, true)
        || !$seats || $seats < 1 || $seats > 20 || !$doors || $doors < 2 || $doors > 6
        || $location === '' || !in_array($status, $statuses, true)) {
        $error = 'Check the required fields and choose valid vehicle details.';
    }

    $photoNames = [];
    $files = $_FILES['photos'] ?? null;
    if ($error === '' && $files && is_array($files['name'])) {
        $count = count(array_filter($files['name'], static fn($name) => $name !== ''));
        if ($count > 8) {
            $error = 'Upload no more than 8 photos at a time.';
        } else {
            $mimeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                if ($files['error'][$i] !== UPLOAD_ERR_OK || $files['size'][$i] > 8 * 1024 * 1024) {
                    $error = 'Each photo must be a valid upload under 8 MB.';
                    break;
                }
                $image = @getimagesize($files['tmp_name'][$i]);
                $mime = $image['mime'] ?? '';
                if (!isset($mimeExtensions[$mime])) {
                    $error = 'Photos must be JPG, PNG, or WebP images.';
                    break;
                }
                $photoNames[] = [
                    'tmp' => $files['tmp_name'][$i],
                    'name' => bin2hex(random_bytes(16)) . '.' . $mimeExtensions[$mime],
                ];
            }
        }
    }

    if ($error === '') {
        $baseSlug = slugify($brand . '-' . $model . '-' . $year);
        $slug = $baseSlug;
        $suffix = 2;
        $slugCheck = $pdo->prepare('SELECT id FROM vehicles WHERE slug = ? AND id <> ? LIMIT 1');
        do {
            $slugCheck->execute([$slug, $id]);
            $slugExists = (bool)$slugCheck->fetchColumn();
            if ($slugExists) $slug = $baseSlug . '-' . $suffix++;
        } while ($slugExists);

        try {
            if ($id) {
                $save = $pdo->prepare('UPDATE vehicles SET slug=?,brand=?,model=?,year=?,type=?,price_per_day=?,transmission=?,fuel_type=?,seats=?,doors=?,location=?,description=?,availability_status=? WHERE id=?');
                $save->execute([$slug,$brand,$model,$year,$type,$price,$transmission,$fuel,$seats,$doors,$location,$description,$status,$id]);
            } else {
                $save = $pdo->prepare('INSERT INTO vehicles (slug,brand,model,year,type,price_per_day,transmission,fuel_type,seats,doors,location,description,availability_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $save->execute([$slug,$brand,$model,$year,$type,$price,$transmission,$fuel,$seats,$doors,$location,$description,$status]);
                $id = (int)$pdo->lastInsertId();
            }

            if ($photoNames) {
                $uploadDir = UPLOAD_PATH . '/vehicles';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    throw new RuntimeException('Upload folder could not be created.');
                }
                $orderQuery = $pdo->prepare('SELECT COALESCE(MAX(display_order), 0) FROM vehicle_images WHERE vehicle_id = ?');
                $orderQuery->execute([$id]);
                $order = (int)$orderQuery->fetchColumn();
                $imageInsert = $pdo->prepare('INSERT INTO vehicle_images (vehicle_id,image_url,display_order) VALUES (?,?,?)');
                foreach ($photoNames as $photo) {
                    if (!move_uploaded_file($photo['tmp'], $uploadDir . '/' . $photo['name'])) {
                        throw new RuntimeException('A photo could not be saved.');
                    }
                    $imageInsert->execute([$id, UPLOAD_URL . '/vehicles/' . $photo['name'], ++$order]);
                }
            }
            $_SESSION['admin_message'] = 'Vehicle saved.';
            header('Location: ' . url('/admin/vehicles.php'));
            exit;
        } catch (Throwable $e) {
            $error = 'Vehicle could not be saved. Check the database and upload-folder permissions.';
        }
    }

    $vehicle = array_merge($vehicle, [
        'brand' => $brand, 'model' => $model, 'year' => $year ?: '', 'type' => $type,
        'price_per_day' => $_POST['price_per_day'] ?? '', 'transmission' => $transmission,
        'fuel_type' => $fuel, 'seats' => $seats ?: '', 'doors' => $doors ?: '',
        'location' => $location, 'description' => $description, 'availability_status' => $status,
    ]);
}

$images = $id ? vehicle_images($pdo, $id) : [];
admin_header($id ? 'Edit vehicle' : 'Add vehicle');
?>
<div class="admin-page-head"><div><span class="admin-kicker">Fleet</span><h1><?= $id ? 'Edit vehicle' : 'Add a vehicle' ?></h1><p class="admin-muted">Keep the listing details accurate and easy to compare.</p></div><a class="admin-button admin-button--quiet" href="<?= e(url('/admin/vehicles.php')) ?>">Back to fleet</a></div>
<?php if ($error !== ''): ?><p class="admin-alert admin-alert--error" role="alert"><?= e($error) ?></p><?php endif; ?>
<section class="admin-panel">
<form class="admin-form" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="admin-field"><label for="brand">Make *</label><input id="brand" name="brand" maxlength="80" value="<?= e($vehicle['brand']) ?>" required></div>
  <div class="admin-field"><label for="model">Model *</label><input id="model" name="model" maxlength="80" value="<?= e($vehicle['model']) ?>" required></div>
  <div class="admin-field"><label for="year">Year *</label><input id="year" name="year" type="number" min="1950" max="<?= (int)date('Y') + 1 ?>" value="<?= e((string)$vehicle['year']) ?>" required></div>
  <div class="admin-field"><label for="type">Vehicle type *</label><select id="type" name="type"><?php foreach ($types as $option): ?><option value="<?= e($option) ?>" <?= $vehicle['type'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
  <div class="admin-field"><label for="price_per_day">Price per day (RWF) *</label><input id="price_per_day" name="price_per_day" type="number" min="1" step="1000" value="<?= e((string)$vehicle['price_per_day']) ?>" required></div>
  <div class="admin-field"><label for="availability_status">Status *</label><select id="availability_status" name="availability_status"><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $vehicle['availability_status'] === $option ? 'selected' : '' ?>><?= e(status_label($option)) ?></option><?php endforeach; ?></select></div>
  <div class="admin-field"><label for="transmission">Transmission *</label><select id="transmission" name="transmission"><?php foreach ($transmissions as $option): ?><option value="<?= e($option) ?>" <?= $vehicle['transmission'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
  <div class="admin-field"><label for="fuel_type">Fuel *</label><select id="fuel_type" name="fuel_type"><?php foreach ($fuels as $option): ?><option value="<?= e($option) ?>" <?= $vehicle['fuel_type'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
  <div class="admin-field"><label for="seats">Seats *</label><input id="seats" name="seats" type="number" min="1" max="20" value="<?= e((string)$vehicle['seats']) ?>" required></div>
  <div class="admin-field"><label for="doors">Doors *</label><input id="doors" name="doors" type="number" min="2" max="6" value="<?= e((string)$vehicle['doors']) ?>" required></div>
  <div class="admin-field admin-field--wide"><label for="location">Pickup location *</label><input id="location" name="location" maxlength="120" value="<?= e($vehicle['location']) ?>" required></div>
  <div class="admin-field admin-field--wide"><label for="description">Description</label><textarea id="description" name="description" maxlength="5000"><?= e($vehicle['description']) ?></textarea></div>
  <div class="admin-field admin-field--wide"><label for="photos">Add photos</label><input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><p class="admin-note">JPG, PNG, or WebP · up to 8 MB each · select up to 8 at once. Existing photos stay on the listing.</p></div>
  <?php if ($images): ?><div class="admin-field admin-field--wide"><label>Current photos</label><div class="admin-photo-list"><?php foreach ($images as $image): ?><img src="<?= e(vehicle_image_url($image)) ?>" alt="Current vehicle photo"><?php endforeach; ?></div></div><?php endif; ?>
  <div class="admin-form-actions"><button class="admin-button" type="submit">Save vehicle</button><a class="admin-button admin-button--quiet" href="<?= e(url('/admin/vehicles.php')) ?>">Cancel</a></div>
</form>
</section>
<?php admin_footer(); ?>
