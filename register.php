<?php
require __DIR__ . '/app/bootstrap.php';

$lang = current_lang();
$slug = (string) input('tour', '');
$tour = $slug ? db_one("SELECT id, slug, title_en, title_ru FROM tours WHERE slug=? AND status<>'draft'", [$slug]) : null;
$tourTitle = $tour ? (lang_field($tour, 'title') ?: 'Tour') : null;

$done = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (trim((string) input('website', '')) !== '') {       // honeypot
        redirect('register' . ($slug ? '/' . urlencode($slug) : '') . '?registered=1');
    }

    $names  = (array) input('person_name', []);
    $emails = (array) input('person_email', []);
    $phones = (array) input('person_phone', []);

    $people = [];
    foreach ($names as $i => $n) {
        $n = trim((string) $n);
        $em = trim((string) ($emails[$i] ?? ''));
        $ph = trim((string) ($phones[$i] ?? ''));
        if ($n === '' && $em === '') {
            continue; // skip empty rows
        }
        if ($n === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $error = t('register_error');
            break;
        }
        $people[] = ['name' => $n, 'email' => $em, 'phone' => $ph !== '' ? $ph : null];
    }

    if (!$error && !$people) {
        $error = t('register_error');
    }

    if (!$error) {
        db_run('INSERT INTO registration_groups (tour_id, status) VALUES (?, ?)', [$tour['id'] ?? null, 'new']);
        $gid = (int) db_insert_id();
        foreach ($people as $idx => $p) {
            db_run(
                'INSERT INTO registration_people (group_id, full_name, email, whatsapp_phone, is_primary) VALUES (?,?,?,?,?)',
                [$gid, $p['name'], $p['email'], $p['phone'], $idx === 0 ? 1 : 0]
            );
        }

        // Telegram notification
        $lines = ["🏕 <b>New registration</b>"];
        $lines[] = 'Tour: ' . tg_escape($tourTitle ?: 'General registration');
        $lines[] = 'People: ' . count($people);
        foreach ($people as $idx => $p) {
            $lines[] = ($idx + 1) . '. ' . tg_escape($p['name']) . ' — ' . tg_escape($p['email'])
                . ($p['phone'] ? ' — ' . tg_escape($p['phone']) : '') . ($idx === 0 ? ' (lead)' : '');
        }
        telegram_notify(implode("\n", $lines));

        redirect('register' . ($slug ? '/' . urlencode($slug) : '') . '?registered=1');
    }
}

if (input('registered') === '1') {
    $done = true;
}

$head_title = t('register_title') . ' — ' . setting('agency_name_' . $lang, 'Camping Uzbekistan');
require __DIR__ . '/partials/head.php';
?>

<section class="page-banner overlay pt-170 pb-170 cu-hero-decor">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="page-banner-content text-center text-white">
                    <h1 class="page-title"><?= e(t('register_title')) ?></h1>
                    <ul class="breadcrumb-link text-white">
                        <li><a href="<?= url('index') ?>"><?= $lang === 'ru' ? 'Главная' : 'Home' ?></a></li>
                        <?php if ($tour): ?><li><a href="<?= url('tour/' . urlencode($tour['slug'])) ?>"><?= e($tourTitle) ?></a></li><?php endif; ?>
                        <li class="active"><?= e(t('nav_register')) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cu-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($done): ?>
                    <div class="text-center">
                        <div class="alert alert-success"><?= e(t('register_success')) ?></div>
                        <a href="<?= url('tours') ?>" class="main-btn primary-btn mt-20"><?= e(t('sec_upcoming_tours')) ?><i class="fas fa-paper-plane"></i></a>
                    </div>
                <?php else: ?>
                    <div class="cu-sec-head text-center wow fadeInDown">
                        <?php if ($tour): ?>
                            <span class="sub-title"><?= e(t('register_for')) ?></span>
                            <h2><?= e($tourTitle) ?></h2>
                        <?php else: ?>
                            <h2><?= e(t('register_title')) ?></h2>
                        <?php endif; ?>
                        <p class="text-muted mt-2"><?= e(t('register_intro')) ?></p>
                    </div>

                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

                    <form class="cu-contact-form wow fadeInUp" method="post"
                          action="register<?= $slug ? '?tour=' . urlencode($slug) : '' ?>">
                        <?= csrf_field() ?>
                        <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <div id="cuPeople">
                            <!-- Lead person -->
                            <div class="cu-person">
                                <h5 class="mb-3"><?= e(t('register_lead')) ?></h5>
                                <div class="row">
                                    <div class="col-md-6"><div class="form_group">
                                        <input type="text" name="person_name[]" class="form_control" placeholder="<?= e(t('form_full_name')) ?> *" aria-label="<?= e(t('form_full_name')) ?>" required>
                                    </div></div>
                                    <div class="col-md-6"><div class="form_group">
                                        <input type="email" name="person_email[]" class="form_control" placeholder="<?= e(t('form_email')) ?> *" aria-label="<?= e(t('form_email')) ?>" required>
                                    </div></div>
                                    <div class="col-md-6"><div class="form_group">
                                        <input type="text" name="person_phone[]" class="form_control" placeholder="<?= e(t('form_whatsapp')) ?>" aria-label="<?= e(t('form_whatsapp')) ?>">
                                    </div></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-30">
                            <button type="button" class="main-btn" id="cuAddPerson"><?= e(t('form_add_person')) ?><i class="far fa-plus"></i></button>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="main-btn primary-btn"><?= e(t('register_btn')) ?><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<template id="cuPersonTpl">
    <div class="cu-person cu-person--extra mt-30">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0 cu-person__label"></h5>
            <button type="button" class="btn-link text-danger ms-auto cu-remove-person"><?= e(t('register_remove')) ?></button>
        </div>
        <div class="row">
            <div class="col-md-6"><div class="form_group">
                <input type="text" name="person_name[]" class="form_control" placeholder="<?= e(t('form_full_name')) ?> *" aria-label="<?= e(t('form_full_name')) ?>">
            </div></div>
            <div class="col-md-6"><div class="form_group">
                <input type="email" name="person_email[]" class="form_control" placeholder="<?= e(t('form_email')) ?> *" aria-label="<?= e(t('form_email')) ?>">
            </div></div>
            <div class="col-md-6"><div class="form_group">
                <input type="text" name="person_phone[]" class="form_control" placeholder="<?= e(t('form_whatsapp')) ?>" aria-label="<?= e(t('form_whatsapp')) ?>">
            </div></div>
        </div>
    </div>
</template>

<?php
$personWord = t('register_person');
$foot_js = <<<JS
(function(){
  var wrap=document.getElementById('cuPeople');
  var tpl=document.getElementById('cuPersonTpl');
  var addBtn=document.getElementById('cuAddPerson');
  function renumber(){ wrap.querySelectorAll('.cu-person--extra .cu-person__label').forEach(function(el,i){ el.textContent='$personWord '+(i+2); }); }
  addBtn && addBtn.addEventListener('click', function(){
    var node=tpl.content.firstElementChild.cloneNode(true);
    wrap.appendChild(node);
    node.querySelector('.cu-remove-person').addEventListener('click', function(){ node.remove(); renumber(); });
    renumber();
  });
})();
JS;
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
