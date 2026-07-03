<?php
/**
 * Reusable upcoming-tour card. Call tour_card($row).
 * Card = 4:3 poster, title, date(s), "view more" → trip details.
 */
if (!function_exists('tour_card')) {
    function tour_card(array $t, array $categories = []): void
    {
        $title  = lang_field($t, 'title') ?: 'Untitled tour';
        $dates  = format_tour_dates($t['start_date'] ?? null, $t['end_date'] ?? null, current_lang());
        $url    = 'tour/' . urlencode($t['slug']);
        $poster = !empty($t['poster']) ? upload_url($t['poster']) : null;
        $catSlugs = implode(' ', array_column($categories, 'slug'));
        ?>
        <div class="cu-tour-card wow fadeInUp tour-filter-item" data-categories="<?= e($catSlugs) ?>">
            <a href="<?= e($url) ?>" class="cu-tour-card__media" aria-label="<?= e($title) ?>">
                <?php if ($poster): ?>
                    <img src="<?= e($poster) ?>" alt="<?= e($title) ?>" loading="lazy">
                <?php else: ?>
                    <span class="cu-tour-card__placeholder"><i class="far fa-image"></i></span>
                <?php endif; ?>
            </a>
            <div class="cu-tour-card__body">
                <h4 class="cu-tour-card__title"><a href="<?= e($url) ?>"><?= e($title) ?></a></h4>
                <?php if ($dates): ?>
                    <p class="cu-tour-card__date"><i class="far fa-calendar-alt"></i> <?= e($dates) ?></p>
                <?php endif; ?>
                <a href="<?= e($url) ?>" class="cu-tour-card__more"><?= e(t('read_more')) ?> <i class="far fa-long-arrow-right"></i></a>
            </div>
        </div>
        <?php
    }
}
