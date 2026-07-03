<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/tour-card.php';

$lang = current_lang();
$tours = db_all("SELECT * FROM tours WHERE status='upcoming' ORDER BY sort_order, start_date IS NULL, start_date");
$allCategories = db_all("SELECT * FROM categories ORDER BY sort_order, id");
$tourCats = [];
foreach (db_all("SELECT tc.tour_id, c.slug, c.title_en, c.title_ru FROM tour_categories tc JOIN categories c ON tc.category_id=c.id") as $r) {
    $tourCats[$r['tour_id']][] = $r;
}

$head_title = t('sec_upcoming_tours') . ' — ' . setting('agency_name_' . $lang, 'Silk Naviora');
require __DIR__ . '/partials/head.php';
?>

<section class="page-banner overlay pt-170 pb-170 cu-hero-decor">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="page-banner-content text-center text-white">
                    <h1 class="page-title"><?= e(t('sec_upcoming_tours')) ?></h1>
                    <ul class="breadcrumb-link text-white">
                        <li><a href="<?= url('index') ?>"><?= $lang === 'ru' ? 'Главная' : 'Home' ?></a></li>
                        <li class="active"><?= e(t('sec_upcoming_tours')) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cu-section">
    <div class="container">
        <?php if (!$tours): ?>
            <p class="cu-empty"><?= $lang === 'ru' ? 'Скоро здесь появятся новые туры.' : 'New tours are coming soon.' ?></p>
        <?php else: ?>
            <?php if ($allCategories): ?>
                <div class="cu-tour-filters text-center mb-5 wow fadeInUp">
                    <button class="cu-filter-btn active" data-filter="all"><?= $lang === 'ru' ? 'Все' : 'All' ?></button>
                    <?php foreach ($allCategories as $c): ?>
                        <button class="cu-filter-btn" data-filter="<?= e($c['slug']) ?>"><?= e(lang_field($c, 'title')) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="row cu-tours-grid" id="toursGrid">
                <?php foreach ($tours as $tr): ?>
                    <div class="col-lg-4 col-md-6 col-12 tour-grid-item">
                        <?php tour_card($tr, $tourCats[$tr['id']] ?? []); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
