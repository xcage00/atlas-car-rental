</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <div class="brand-mark">ATLAS</div>
      <p>Automotive Services · Find the right car for your journey.</p>
    </div>

    <div class="footer-col">
      <h4>Explore</h4>
      <ul>
        <li><a href="<?= url('/cars') ?>">All Cars</a></li>
        <li><a href="<?= url('/cars?status=available') ?>">Available Now</a></li>
        <li><a href="<?= url('/about') ?>">About</a></li>
        <li><a href="<?= url('/contact') ?>">Contact</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Reach Us</h4>
      <ul>
        <li><?= e(setting($pdo, 'phone', '')) ?></li>
        <li><?= e(setting($pdo, 'email', '')) ?></li>
        <li><?= e(setting($pdo, 'location', '')) ?></li>
      </ul>
    </div>
  </div>

  <div class="container footer-bottom">
    <span>© <?= date('Y') ?> <?= e(setting($pdo, 'business_name', SITE_NAME)) ?></span>
    <span>Kigali · Rwanda</span>
  </div>
</footer>

<script src="<?= url('/assets/js/main.js') ?>" defer></script>
</body>
</html>