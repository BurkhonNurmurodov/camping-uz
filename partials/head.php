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
    <meta name="description" content="<?= e(setting('moto_' . current_lang(), 'Tours across Central Asia')) ?>">
    <title><?= e($head_title) ?></title>

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($head_title) ?>">
    <meta property="og:description" content="<?= e(setting('moto_' . current_lang(), 'Tours across Central Asia')) ?>">
    <?php if ($ogImage = setting('hero_image')): ?>
        <meta property="og:image" content="<?= e(upload_url($ogImage)) ?>">
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
