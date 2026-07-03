<?php
/**
 * Scripts + body close. Optional $foot_js for page-specific inline JS,
 * and $foot_head_scripts for extra <script src> (e.g. Yandex Maps).
 */
?>
    <!--====== Back to top ======-->
    <a href="#" class="back-to-top"><i class="far fa-angle-up"></i></a>

    <script src="<?= BASE_PATH ?>/assets/vendor/jquery-3.7.1.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/popper/popper.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/slick/slick.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/nice-select/js/jquery.nice-select.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/vendor/wow.min.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>
    <?php foreach (($foot_scripts ?? []) as $src): ?>
        <script src="<?= e($src) ?>"></script>
    <?php endforeach; ?>
    <script src="<?= BASE_PATH ?>/assets/js/site.js"></script>
    <script src="<?= BASE_PATH ?>/assets/js/tour-filters.js"></script>
    <?php if (!empty($foot_js)): ?><script><?= $foot_js ?></script><?php endif; ?>
</body>
</html>
