<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/tour-card.php';

$lang = current_lang();
$tours = db_all("SELECT * FROM tours WHERE status='upcoming' ORDER BY sort_order, start_date IS NULL, start_date LIMIT 12");
$allCategories = db_all("SELECT * FROM categories ORDER BY sort_order, id");
$tourCats = [];
foreach (db_all("SELECT tc.tour_id, c.slug, c.title_en, c.title_ru FROM tour_categories tc JOIN categories c ON tc.category_id=c.id") as $r) {
    $tourCats[$r['tour_id']][] = $r;
}
$about = db_one("SELECT * FROM pages WHERE `key`='about'");
$reviews = db_all("SELECT * FROM testimonials WHERE is_visible=1 ORDER BY sort_order, created_at DESC");

$heroType  = setting('hero_type', 'image');
$heroImage = setting('hero_image');
$heroVideo = setting('hero_video');
$name = setting('agency_name_' . $lang, 'Camping Uzbekistan');
$moto = setting('moto_' . $lang, $lang === 'ru' ? 'Настоящие путешествия по Центральной Азии' : 'Real journeys across Central Asia');

$head_title = $name;
require __DIR__ . '/partials/head.php';
?>

<!--====== Hero ======-->
<section class="cu-hero">
    <div class="cu-hero__bg">
        <?php if ($heroType === 'video' && $heroVideo): ?>
            <video autoplay muted loop playsinline poster="<?= e($heroImage ? upload_url($heroImage) : '') ?>">
                <source src="<?= e(upload_url($heroVideo)) ?>" type="video/mp4">
            </video>
        <?php elseif ($heroImage): ?>
            <img src="<?= e(upload_url($heroImage)) ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="cu-hero__overlay"></div>
    <div class="cu-hero__content">
        <h1 class="cu-hero__title" data-animation="fadeInDown" data-delay=".2s"><?= e($name) ?></h1>
        <p class="cu-hero__moto" data-animation="fadeInUp" data-delay=".4s"><?= e($moto) ?></p>
        <a href="<?= url('tours') ?>" class="main-btn primary-btn" data-animation="fadeInUp" data-delay=".6s">
            <?= e(t('nav_tours')) ?><i class="fas fa-paper-plane"></i>
        </a>
    </div>
    <a href="#tours" class="cu-hero__scroll"><i class="far fa-angle-down"></i></a>
</section>

<!--====== Upcoming tours ======-->
<section class="cu-section" id="tours">
    <div class="container">
        <div class="cu-sec-head text-center wow fadeInDown">
            <span class="sub-title"><?= $lang === 'ru' ? 'Путешествия' : 'Journeys' ?></span>
            <h2><a href="<?= url('tours') ?>"><?= e(t('sec_upcoming_tours')) ?></a></h2>
        </div>
        <?php if (!$tours): ?>
            <p class="cu-empty"><?= $lang === 'ru' ? 'Скоро здесь появятся новые туры.' : 'New tours are coming soon.' ?></p>
        <?php else: ?>
            <?php if ($allCategories): ?>
                <div class="cu-tour-filters text-center mb-5 wow fadeInDown">
                    <button class="cu-filter-btn active" data-filter="all"><?= $lang === 'ru' ? 'Все' : 'All' ?></button>
                    <?php foreach ($allCategories as $c): ?>
                        <button class="cu-filter-btn" data-filter="<?= e($c['slug']) ?>"><?= e(lang_field($c, 'title')) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="cu-tours-carousel">
                <?php foreach ($tours as $tr): ?>
                    <div class="tour-carousel-item"><?php tour_card($tr, $tourCats[$tr['id']] ?? []); ?></div>
                <?php endforeach; ?>
            </div>
            <div class="cu-tours-arrows">
                <div class="prev"><i class="far fa-arrow-left"></i></div>
                <div class="next"><i class="far fa-arrow-right"></i></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!--====== About ======-->
<section class="cu-section cu-section--tint" id="about">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cu-sec-head text-center wow fadeInDown">
                    <span class="sub-title"><?= $lang === 'ru' ? 'Кто мы' : 'Who we are' ?></span>
                    <h2><?= e($about ? (lang_field($about, 'title') ?: t('sec_about')) : t('sec_about')) ?></h2>
                </div>
                <div class="cu-richtext wow fadeInUp">
                    <?php
                    $body = $about ? ($about['body_' . $lang . '_html'] ?? $about['body_en_html'] ?? null) : null;
                    if ($body && trim(strip_tags($body)) !== '') {
                        echo render_html($body);
                    } else {
                        echo '<p class="cu-empty">' . ($lang === 'ru' ? 'Раздел «О нас» скоро будет заполнен.' : 'The About section will be filled in soon.') . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!--====== Testimonials ======-->
<?php if ($reviews): ?>
<section class="cu-section" id="testimonials">
    <div class="container">
        <div class="cu-sec-head text-center wow fadeInDown">
            <span class="sub-title"><?= $lang === 'ru' ? 'Отзывы' : 'Testimonials' ?></span>
            <h2><?= e(t('sec_testimonials')) ?></h2>
        </div>
        <div class="slider-active-3-item-dot wow fadeInUp">
            <?php foreach ($reviews as $r): ?>
                <div class="cu-testimonial-card-wrap">
                    <div class="cu-testimonial-card">
                        <div class="cu-quote-icon">
                            <svg class="cu-quote-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 11L8 15H11V19H5V15L7 11H5V7H11V11H10ZM20 11L18 15H21V19H15V15L17 11H15V7H21V11H20Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="cu-testimonial-text">
                            <?= render_html($r['comment_' . $lang . '_html'] ?? $r['comment_en_html'] ?? '') ?>
                        </div>
                        <div class="cu-author-meta">
                            <img class="cu-author-avatar" src="<?= e($r['avatar'] ? upload_url($r['avatar']) : 'assets/images/testimonial/author-1.jpg') ?>" alt="<?= e($r['author_name']) ?>">
                            <div class="cu-author-details">
                                <h3 class="cu-author-name"><?= e($r['author_name']) ?></h3>
                                <p class="cu-author-role"><?= $lang === 'ru' ? 'Клиент' : 'Client' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!--====== Contact ======-->
<section class="cu-section cu-section--tint" id="contact">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="cu-sec-head text-center wow fadeInDown">
                    <span class="sub-title"><?= $lang === 'ru' ? 'Связь' : 'Get in touch' ?></span>
                    <h2><?= e(t('sec_contact')) ?></h2>
                </div>

                <?php if (input('sent') === '1'): ?>
                    <div class="alert alert-success text-center"><?= e(t('form_thanks')) ?></div>
                <?php elseif (input('err') === '1'): ?>
                    <div class="alert alert-danger text-center"><?= $lang === 'ru' ? 'Пожалуйста, заполните все обязательные поля.' : 'Please fill in all required fields.' ?></div>
                <?php endif; ?>

                <form class="cu-contact-form wow fadeInUp" method="post" action="contact#contact">
                    <?= csrf_field() ?>
                    <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="row">
                        <div class="col-md-6"><div class="form_group">
                            <input type="text" name="first_name" class="form_control" placeholder="<?= e(t('form_first_name')) ?> *" aria-label="<?= e(t('form_first_name')) ?>" required>
                        </div></div>
                        <div class="col-md-6"><div class="form_group">
                            <input type="text" name="last_name" class="form_control" placeholder="<?= e(t('form_last_name')) ?> *" aria-label="<?= e(t('form_last_name')) ?>" required>
                        </div></div>
                        <div class="col-md-6"><div class="form_group">
                            <input type="email" name="email" class="form_control" placeholder="<?= e(t('form_email')) ?> *" aria-label="<?= e(t('form_email')) ?>" required>
                        </div></div>
                        <div class="col-md-6"><div class="form_group">
                            <input type="text" name="topic" class="form_control" placeholder="<?= e(t('form_topic')) ?>" aria-label="<?= e(t('form_topic')) ?>">
                        </div></div>
                        <div class="col-12"><div class="form_group">
                            <textarea name="message" class="form_control" placeholder="<?= e(t('form_message')) ?> *" aria-label="<?= e(t('form_message')) ?>" required></textarea>
                        </div></div>
                        <div class="col-12 text-center">
                            <button type="submit" class="main-btn primary-btn"><?= e(t('send')) ?><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
