<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About — ' . SITE_NAME;
$pageDesc  = 'Atlas Automotive Services is a Kigali-based car rental marketplace. We list the cars, you pick one, and you talk to us directly on WhatsApp.';
$bodyClass = 'page-about';

require __DIR__ . '/includes/header.php';
?>

<!-- ============ PAGE HEAD ============ -->
<section class="page-head">
  <div class="container">
    <span class="eyebrow">About</span>
    <h1>We keep it simple.</h1>
    <p class="page-head-sub">Atlas Automotive Services is a Kigali-based car rental business. We list and manage every vehicle on this site ourselves, so what you see here is what's actually on the lot.</p>
  </div>
</section>

<!-- ============ STORY ============ -->
<section class="section">
  <div class="container about-grid">
    <div class="about-intro reveal">
      <span class="eyebrow">Why we exist</span>
      <h2>Renting a car shouldn't be a guessing game.</h2>
      <div class="about-prose">
        <p>Most car rental listings online look the same: photos that may or may not be current, prices that turn out to be "from", availability you only find out about after you call. By the time you've made three phone calls you've spent more energy than the trip was worth.</p>
        <p>We built Atlas around a simpler idea. Show the real cars, show the real price, show whether it's actually available right now — and then let the customer talk to the person who owns it, directly, without a form in between.</p>
        <p>That's why every listing is managed by us, every availability status is set by us, and every WhatsApp conversation lands with the same person responsible for the fleet. No call centres. No middlemen. No surprises.</p>
      </div>
    </div>

    <aside class="about-aside reveal">
      <div class="about-card">
        <h3>At a glance</h3>
        <dl class="about-facts">
          <div><dt>Based</dt><dd>Kigali, Rwanda</dd></div>
          <div><dt>Fleet</dt><dd>SUVs, sedans, and 4x4s</dd></div>
          <div><dt>Contact</dt><dd>Direct on WhatsApp</dd></div>
          <div><dt>Booking</dt><dd>No forms, no accounts</dd></div>
        </dl>
      </div>
    </aside>
  </div>
</section>

<!-- ============ PRINCIPLES ============ -->
<section class="section section--paper">
  <div class="container">
    <header class="section-head reveal">
      <div>
        <span class="eyebrow">How we work</span>
        <h2>Three rules we don't break</h2>
      </div>
    </header>

    <div class="principles">
      <article class="principle reveal">
        <span class="principle-num">01</span>
        <h3>Real availability</h3>
        <p>If a car is out, it's marked Rented. If it's off the road, it's marked Unavailable. You'll never message us about a car that isn't there.</p>
      </article>
      <article class="principle reveal">
        <span class="principle-num">02</span>
        <h3>Prices on the page</h3>
        <p>The daily rate is on every card, in RWF, before you click. We don't hide it behind a "contact for pricing" wall.</p>
      </article>
      <article class="principle reveal">
        <span class="principle-num">03</span>
        <h3>Direct conversation</h3>
        <p>WhatsApp opens with the car already named and the question already asked. You start where most rentals make you finish.</p>
      </article>
    </div>
  </div>
</section>

<!-- ============ CLOSING ============ -->
<section class="closing">
  <div class="container closing-inner reveal">
    <span class="eyebrow">Ready when you are</span>
    <h2>See the cars.</h2>
    <p>Browse the fleet, pick one that fits, and reach out. The conversation is one message away.</p>
    <div class="closing-actions">
      <a class="btn btn--primary" href="<?= url('/cars') ?>">Explore Cars</a>
      <a class="btn btn--ghost" href="<?= url('/contact') ?>">Contact us</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>