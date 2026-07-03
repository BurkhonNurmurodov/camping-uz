<?php
/**
 * Public footer.
 */
$lang = current_lang();
$name = setting('agency_name_' . $lang, 'Camping Uzbekistan');
$moto = setting('moto_' . $lang, '');
$logoImage = setting('logo_image');
$logoLight = setting('logo_image_light') ?: $logoImage;
$socials = [
    'social_instagram' => ['fab fa-instagram', 'Instagram'],
    'social_telegram'  => ['fab fa-telegram-plane', 'Telegram'],
    'social_facebook'  => ['fab fa-facebook-f', 'Facebook'],
    'social_whatsapp'  => ['fab fa-whatsapp', 'WhatsApp'],
];
?>
<footer class="main-footer black-bg pt-100">
    <div class="container">
        <div class="footer-widget-area pb-30">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget about-company-widget mb-40 wow fadeInUp">
                        <a href="<?= url('index') ?>" class="brand-logo <?= $logoLight ? '' : 'cu-wordmark cu-wordmark--light' ?>">
                            <?php if ($logoLight): ?>
                                <img src="<?= e(upload_url($logoLight)) ?>" alt="<?= e($name) ?>" style="max-height: 60px;">
                            <?php else: ?>
                                <?= e($name) ?>
                            <?php endif; ?>
                        </a>
                        <div class="footer-content mt-20">
                            <p><?= e($moto) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget service-nav-widget mb-40 pl-lg-50 wow fadeInUp">
                        <h4 class="widget-title"><?= $lang === 'ru' ? 'Навигация' : 'Explore' ?></h4>
                        <div class="footer-content">
                            <ul class="footer-widget-nav">
                                <li><a href="<?= url('tours') ?>"><?= e(t('nav_tours')) ?></a></li>
                                <li><a href="<?= url('index#about') ?>"><?= e(t('nav_about')) ?></a></li>
                                <li><a href="<?= url('index#testimonials') ?>"><?= e(t('nav_testimonials')) ?></a></li>
                                <li><a href="<?= url('index#contact') ?>"><?= e(t('nav_contact')) ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="footer-widget mb-40 wow fadeInUp">
                        <h4 class="widget-title"><?= e(t('nav_contact')) ?></h4>
                        <div class="social-box mt-20">
                            <ul class="social-link">
                                <?php foreach ($socials as $key => [$icon, $label]):
                                    $val = setting($key); if (!$val) continue;
                                    if ($key === 'social_whatsapp') {
                                        $href = 'https://wa.me/' . preg_replace('/\D+/', '', $val);
                                    } else {
                                        $val = ltrim(trim($val), '@');
                                        if (preg_match('~^https?://~', $val)) {
                                            $href = $val;
                                        } elseif (str_contains($val, '.') || str_contains($val, '/')) {
                                            $href = 'https://' . $val;
                                        } else {
                                            $domains = [
                                                'social_instagram' => 'https://instagram.com/',
                                                'social_telegram'  => 'https://t.me/',
                                                'social_facebook'  => 'https://facebook.com/'
                                            ];
                                            $href = ($domains[$key] ?? 'https://') . $val;
                                        }
                                    }
                                    ?>
                                    <li><a href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e($label) ?>"><i class="<?= $icon ?>"></i></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-copyright pt-30 pb-30">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <p class="copyright-text">&copy; <?= date('Y') ?> <?= e($name) ?>. <?= $lang === 'ru' ? 'Все права защищены.' : 'All rights reserved.' ?></p>
                </div>
            </div>
        </div>
    </div>
</footer>
