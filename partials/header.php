<?php
/**
 * Public header — transparent at the top, white + shrunk logo once scrolled
 * (sticky handled by theme.js toggling .sticky on .header-navigation).
 */
$lang = current_lang();
$name = setting('agency_name_' . $lang, 'Camping Uzbekistan');
$logoDark = setting('logo_image');
$logoLight = setting('logo_image_light') ?: $logoDark;
// Nav targets: section anchors live on the home page, so prefix with index.php.
$home = url('index');
$is_transparent = in_array(basename($_SERVER['PHP_SELF']), ['index.php', 'tours.php', 'register.php', 'private-tours.php'], true);
?>
<header class="header-area header-one <?= $is_transparent ? 'transparent-header' : '' ?>">
    <div class="header-navigation <?= $is_transparent ? 'navigation-white' : '' ?>">
        <div class="nav-overlay"></div>
        <div class="container-fluid">
            <div class="primary-menu">
                <div class="site-branding">
                    <a href="<?= $home ?>" class="brand-logo <?= $logoDark ? '' : 'cu-wordmark' ?>">
                        <?php if ($logoDark): ?>
                            <img src="<?= e(upload_url($logoDark)) ?>" alt="<?= e($name) ?>" class="logo-dark" style="max-height: 60px;">
                            <img src="<?= e(upload_url($logoLight)) ?>" alt="<?= e($name) ?>" class="logo-light" style="max-height: 60px;">
                        <?php else: ?>
                            <?= e($name) ?>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-menu">
                    <div class="mobile-logo mb-30 d-block d-xl-none">
                        <a href="<?= $home ?>" class="brand-logo <?= $logoDark ? '' : 'cu-wordmark cu-wordmark--dark' ?>">
                            <?php if ($logoDark): ?>
                                <img src="<?= e(upload_url($logoDark)) ?>" alt="<?= e($name) ?>" style="max-height: 60px;">
                            <?php else: ?>
                                <?= e($name) ?>
                            <?php endif; ?>
                        </a>
                    </div>
                    <nav class="main-menu">
                        <ul>
                            <li><a href="<?= $home ?>"><?= $lang === 'ru' ? 'Главная' : 'Home' ?></a></li>
                            <li><a href="<?= url('tours') ?>"><?= e(t('nav_tours')) ?></a></li>
                            <li><a href="<?= url('private-tours') ?>"><?= $lang === 'ru' ? 'Свой тур' : 'Private Tours' ?></a></li>
                            <li><a href="<?= $home ?>#about"><?= e(t('nav_about')) ?></a></li>
                            <li><a href="<?= $home ?>#testimonials"><?= e(t('nav_testimonials')) ?></a></li>
                            <li><a href="<?= $home ?>#contact"><?= e(t('nav_contact')) ?></a></li>
                        </ul>
                    </nav>
                    <div class="cu-lang mt-40 d-block d-xl-none">
                        <a href="<?= e(lang_switch_url('en')) ?>" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
                        <a href="<?= e(lang_switch_url('ru')) ?>" class="<?= $lang === 'ru' ? 'active' : '' ?>">RU</a>
                    </div>
                </div>

                <div class="nav-right-item">
                    <div class="cu-lang d-none d-xl-flex">
                        <a href="<?= e(lang_switch_url('en')) ?>" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
                        <span class="cu-lang-sep">/</span>
                        <a href="<?= e(lang_switch_url('ru')) ?>" class="<?= $lang === 'ru' ? 'active' : '' ?>">RU</a>
                    </div>
                    <div class="menu-button d-none d-xl-block">
                        <a href="<?= url('tours') ?>" class="main-btn primary-btn"><?= e(t('nav_tours')) ?><i class="fas fa-paper-plane"></i></a>
                    </div>
                    <div class="navbar-toggler">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
