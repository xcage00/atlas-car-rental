<?php
declare(strict_types=1);
/** @var array $v */
$detail = url('/cars/' . $v['slug']) . ($v['detail_query'] ?? '');
?>
<article class="car-card reveal">
  <a class="car-card-media" href="<?= $detail ?>" aria-label="<?= e($v['brand'] . ' ' . $v['model']) ?>">
    <img src="<?= e(vehicle_image_url($v['thumb'] ?? null)) ?>"
         alt="<?= e($v['brand'] . ' ' . $v['model'] . ' ' . $v['year']) ?>"
         loading="lazy">
    <span class="status status--<?= e($v['availability_status']) ?>">
      <?= e(status_label($v['availability_status'])) ?>
    </span>
  </a>
  <div class="car-card-body">
    <header>
      <h3><a href="<?= $detail ?>"><?= e($v['brand'] . ' ' . $v['model']) ?></a></h3>
      <p class="car-card-meta">
        <?= (int)$v['year'] ?> · <?= e($v['transmission']) ?> · <?= (int)$v['seats'] ?> Seats
      </p>
    </header>
    <div class="car-card-foot">
      <span class="car-card-price"><?= e(money($v['price_per_day'])) ?><small>/day</small></span>
      <a class="car-card-link" href="<?= $detail ?>">View Details</a>
    </div>
  </div>
</article>
