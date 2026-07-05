<?php
/**
 * Public <head> + body open + preloader + header.
 * Expects $head_title. Public pages require app/bootstrap.php first.
 */
$head_title = $head_title ?? setting('agency_name_' . current_lang(), 'Silk Naviora');
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
        $moto = setting('moto_' . current_lang(), 'Tours across Central Asia');
        $desc = $meta_desc ?? $moto;
        // Strip tags and truncate description for meta tags
        $clean_desc = mb_strimwidth(trim(preg_replace('/\s+/', ' ', strip_tags($desc))), 0, 160, '...');
        
        // Build base URL for canonicals and schema
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'silknaviora.com';
        $site_url = $protocol . '://' . $host . BASE_PATH;
        $curr_url = $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
        $can_url = $canonical_url ?? strtok($curr_url, '?');
    ?>
    <meta name="description" content="<?= e($clean_desc) ?>">
    <meta name="keywords" content="Travel Uzbekistan, Central Asia Tours, Silk Road journeys, Uzbekistan tours, guided tours Central Asia, travel Central Asia, <?= e(setting('agency_name_' . current_lang(), 'Silk Naviora')) ?>">
    <link rel="canonical" href="<?= e($can_url) ?>">

    <!-- Regional Targeting (hreflang) -->
    <?php
        $base_req = strtok($_SERVER['REQUEST_URI'], '?');
        $en_url = $site_url . $base_req . '?lang=en';
        $ru_url = $site_url . $base_req . '?lang=ru';
    ?>
    <link rel="alternate" hreflang="en-US" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="en-GB" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="en-AU" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="en-CA" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="en-NZ" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="en" href="<?= e($en_url) ?>">
    <link rel="alternate" hreflang="ru" href="<?= e($ru_url) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e($en_url) ?>">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($head_title) ?>">
    <meta property="og:description" content="<?= e($clean_desc) ?>">
    <meta property="og:url" content="<?= e($can_url) ?>">
    <?php if ($ogImage = setting('hero_image')): ?>
        <meta property="og:image" content="<?= e($site_url . upload_url($ogImage)) ?>">
    <?php endif; ?>

    <?php if ($favicon = setting('favicon')): ?>
        <link rel="shortcut icon" href="<?= e(upload_url($favicon)) ?>">
    <?php else: ?>
        <link rel="shortcut icon" href="<?= BASE_PATH ?>/assets/images/favicon.ico" type="image/png">
    <?php endif; ?>

    <!-- Without JS, reveal animated content and hide the preloader. -->
    <noscript><style>.wow{visibility:visible!important}.preloader{display:none!important}</style></noscript>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/fonts/flaticon/flaticon_gowilds.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/vendor/magnific-popup/dist/magnific-popup.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/vendor/slick/slick.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/vendor/nice-select/css/nice-select.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/vendor/animate.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/default.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/site.css">
</head>
<body>
    <!--====== Preloader ======-->
    <div class="preloader">
        <div class="loader">
            <div class="pre-shadow"></div>
            <div class="pre-box"></div>
        </div>
    </div>

    <?php require __DIR__ . '/header.php'; ?>
