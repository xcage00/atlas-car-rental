<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? SITE_NAME;
$bodyClass = $bodyClass ?? '';
$navPath   = current_path();
$isActive  = fn(string $p) => str_starts_with($navPath, $p) ? 'is-active' : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc ?? 'Browse trusted vehicles, check availability, and connect directly with the owner.') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter+Tight:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('/assets/css/main.css') ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= url('/') ?>" aria-label="<?= e(SITE_NAME) ?> — home">
      <span class="brand-mark">ATLAS</span>
      <span class="brand-sub">Automotive Services</span>
    </a>

    <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <nav class="primary-nav" id="primary-nav" aria-label="Primary">
      <ul>
        <li><a class="<?= $isActive('/cars') ?>" href="<?= url('/cars') ?>">Cars</a></li>
        <li><a class="<?= $isActive('/about') ?>" href="<?= url('/about') ?>">About</a></li>
        <li><a class="<?= $isActive('/contact') ?>" href="<?= url('/contact') ?>">Contact</a></li>
        <li class="nav-cta"><a href="<?= url('/cars') ?>">Explore Cars</a></li>
      </ul>
    </nav>
  </div>
</header>

<main id="main">