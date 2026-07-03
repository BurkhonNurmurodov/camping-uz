<?php
require __DIR__ . '/app/bootstrap.php';

$lang = current_lang();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim((string) input('name'));
    $email = trim((string) input('email'));
    $whatsapp = trim((string) input('whatsapp'));
    $destinations = input('destinations'); // array
    $other_dest = trim((string) input('other_destination'));
    $dates_info = trim((string) input('dates_info'));
    $group_size = trim((string) input('group_size'));
    $notes = trim((string) input('notes'));

    if (is_array($destinations)) {
        if (($key = array_search('Other', $destinations)) !== false) {
            unset($destinations[$key]);
            if ($other_dest) {
                $destinations[] = 'Other: ' . $other_dest;
            }
        }
        $destinations = array_values($destinations);
    }

    if (!$name || !$email) {
        flash('error', $lang === 'ru' ? 'Имя и Email обязательны.' : 'Name and Email are required.');
    } else {
        $destJson = is_array($destinations) ? json_encode($destinations, JSON_UNESCAPED_UNICODE) : null;
        db_run("INSERT INTO private_tour_requests (name, email, whatsapp, destinations, dates_info, group_size, notes) VALUES (?,?,?,?,?,?,?)",
            [$name, $email, $whatsapp, $destJson, $dates_info, $group_size, $notes]);
        redirect('private-tours?sent=1');
    }
}

$head_title = ($lang === 'ru' ? 'Индивидуальные туры' : 'Private Tours') . ' — ' . setting('agency_name_' . $lang, 'Camping Uzbekistan');
require __DIR__ . '/partials/head.php';
?>

<section class="page-banner overlay pt-170 pb-170 cu-hero-decor">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="page-banner-content text-center text-white">
                    <h1 class="page-title"><?= $lang === 'ru' ? 'Индивидуальные туры' : 'Private Tours' ?></h1>
                    <ul class="breadcrumb-link text-white">
                        <li><a href="<?= url('index') ?>"><?= $lang === 'ru' ? 'Главная' : 'Home' ?></a></li>
                        <li class="active"><?= $lang === 'ru' ? 'Индивидуальный тур' : 'Private Tour' ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cu-section cu-section--tint">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="cu-sec-head text-center wow fadeInDown">
                    <span class="sub-title"><?= $lang === 'ru' ? 'Создайте свой идеальный маршрут' : 'Build Your Perfect Trip' ?></span>
                    <h2><?= $lang === 'ru' ? 'Запрос на индивидуальный тур' : 'Request a Custom Tour' ?></h2>
                    <p class="mt-3 text-muted"><?= $lang === 'ru' ? 'Расскажите нам о своих предпочтениях, и мы организуем незабываемое путешествие специально для вас.' : 'Tell us what you are looking for, and we will craft a memorable, tailor-made journey just for you.' ?></p>
                </div>

                <?php if (input('sent') === '1'): ?>
                    <div class="alert alert-success text-center py-4 rounded-4 shadow-sm wow fadeInUp">
                        <h4><i class="ri-checkbox-circle-line text-success"></i></h4>
                        <h5 class="mb-2"><?= $lang === 'ru' ? 'Запрос отправлен!' : 'Request Sent Successfully!' ?></h5>
                        <p class="mb-0 text-muted"><?= $lang === 'ru' ? 'Мы свяжемся с вами в ближайшее время, чтобы обсудить детали.' : 'Our travel experts will contact you shortly to start planning your adventure.' ?></p>
                    </div>
                <?php else: ?>
                    <form class="cu-contact-form bg-white p-4 p-md-5 rounded-4 shadow-sm wow fadeInUp" method="post" action="private-tours">
                        <?= csrf_field() ?>

                        <!-- Destination Vibes -->
                        <h5 class="mb-3"><?= $lang === 'ru' ? '1. Какие места вас интересуют?' : '1. What kind of destinations do you prefer?' ?></h5>
                        <div class="row g-3 mb-4">
                            <?php 
                            $vibes = [
                                'Mountains & Nature' => 'Горы и природа',
                                'Historical Cities' => 'Исторические города',
                                'Desert & Lakes' => 'Пустыни и озера',
                                'Surprise me!' => 'Положиться на ваш вкус!'
                            ];
                            foreach ($vibes as $en => $ru): 
                            ?>
                                <div class="col-sm-6">
                                    <label class="d-block bg-light rounded p-3 cursor-pointer h-100 border cu-vibe-check" style="cursor:pointer; transition: all 0.2s;">
                                        <input type="checkbox" name="destinations[]" value="<?= e($en) ?>" class="me-2"> 
                                        <?= $lang === 'ru' ? $ru : $en ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-12">
                                <label class="d-block bg-light rounded p-3 cursor-pointer border cu-vibe-check" style="cursor:pointer; transition: all 0.2s;">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" name="destinations[]" value="Other" class="me-2 cu-other-vibe-check"> 
                                        <span><?= $lang === 'ru' ? 'Другое (укажите):' : 'Other (Please specify):' ?></span>
                                    </div>
                                    <div class="mt-2 cu-other-vibe-input" style="display:none;">
                                        <input type="text" name="other_destination" class="form_control bg-white" placeholder="<?= $lang === 'ru' ? 'Напишите свой вариант...' : 'Type your destination here...' ?>" style="padding: 10px 20px; height: 45px; border-radius: 8px; border: 1px solid #ddd; width: 100%;">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Travel Window -->
                        <h5 class="mb-3"><?= $lang === 'ru' ? '2. Когда вы планируете поездку?' : '2. When are you planning to travel?' ?></h5>
                        <div class="form_group mb-4">
                            <input type="text" name="dates_info" class="form_control bg-light" placeholder="<?= $lang === 'ru' ? 'Например: Сентябрь 2026, или точные даты' : 'e.g. September 2026, or exact dates' ?>">
                        </div>

                        <!-- Group Size -->
                        <h5 class="mb-3"><?= $lang === 'ru' ? '3. Сколько человек едет?' : '3. How many people are traveling?' ?></h5>
                        <div class="row g-3 mb-4">
                            <?php 
                            $sizes = [
                                'Just me' => 'Только я',
                                'Couple' => 'Пара',
                                'Small Group (3-5)' => 'Малая группа (3-5)',
                                'Large Group (6+)' => 'Большая группа (6+)',
                                'Not sure yet' => 'Пока не уверен'
                            ];
                            foreach ($sizes as $en => $ru): 
                            ?>
                                <div class="col-sm-6 col-md-4">
                                    <label class="d-block bg-light rounded p-2 text-center border cu-vibe-check" style="cursor:pointer; transition: all 0.2s;">
                                        <input type="radio" name="group_size" value="<?= e($en) ?>" class="d-none"> 
                                        <?= $lang === 'ru' ? $ru : $en ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Contact Info -->
                        <h5 class="mb-3"><?= $lang === 'ru' ? '4. Контактная информация' : '4. Contact Details' ?></h5>
                        <div class="row">
                            <div class="col-md-6"><div class="form_group">
                                <input type="text" name="name" class="form_control bg-light" placeholder="<?= $lang === 'ru' ? 'Имя *' : 'Name *' ?>" required>
                            </div></div>
                            <div class="col-md-6"><div class="form_group">
                                <input type="email" name="email" class="form_control bg-light" placeholder="<?= $lang === 'ru' ? 'Email *' : 'Email *' ?>" required>
                            </div></div>
                            <div class="col-md-12"><div class="form_group mb-4">
                                <input type="text" name="whatsapp" class="form_control bg-light" placeholder="<?= $lang === 'ru' ? 'WhatsApp номер (опционально)' : 'WhatsApp Number (optional)' ?>">
                            </div></div>
                            
                            <div class="col-12"><div class="form_group mb-4">
                                <textarea name="notes" class="form_control bg-light" placeholder="<?= $lang === 'ru' ? 'Дополнительные пожелания...' : 'Any special requests or details...' ?>" rows="4"></textarea>
                            </div></div>
                            
                            <div class="col-12 text-center mt-2">
                                <button type="submit" class="main-btn primary-btn">
                                    <?= $lang === 'ru' ? 'Отправить запрос' : 'Submit Request' ?> <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Custom radio/checkbox styles */
.cu-vibe-check {
    user-select: none;
}
.cu-vibe-check:hover {
    border-color: var(--cu-green) !important;
}
input[type="radio"]:checked + label, .cu-vibe-check:has(input:checked) {
    background-color: rgba(99, 171, 69, 0.1) !important;
    border-color: var(--cu-green) !important;
    color: var(--cu-green);
    font-weight: 500;
}
.cu-vibe-check input[type="radio"] {
    display: none;
}
</style>

<script>
document.querySelectorAll('.cu-vibe-check input[type="radio"]').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('input[name="group_size"]').forEach(i => {
            i.closest('label').style.backgroundColor = '';
            i.closest('label').style.borderColor = '';
            i.closest('label').style.color = '';
        });
        if(this.checked) {
            this.closest('label').style.backgroundColor = 'rgba(99, 171, 69, 0.1)';
            this.closest('c').style.borderColor = 'var(--cu-green)';
            this.closest('label').style.color = 'var(--cu-green)';
        }
    });
});

document.querySelector('.cu-other-vibe-check')?.addEventListener('change', function() {
    const inputDiv = document.querySelector('.cu-other-vibe-input');
    const inputField = inputDiv.querySelector('input');
    if (this.checked) {
        inputDiv.style.display = 'block';
        inputField.focus();
    } else {
        inputDiv.style.display = 'none';
        inputField.value = '';
    }
});

// Shift + Click and Shift + Arrow Keys for destination checkboxes
const destCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"][name="destinations[]"]'));
let lastCheckedIndex = -1;

destCheckboxes.forEach((checkbox, index) => {
    checkbox.addEventListener('click', function(e) {
        if (e.shiftKey && lastCheckedIndex !== -1) {
            const start = Math.min(lastCheckedIndex, index);
            const end = Math.max(lastCheckedIndex, index);
            const isChecked = this.checked;
            for (let i = start; i <= end; i++) {
                if (destCheckboxes[i].checked !== isChecked) {
                    destCheckboxes[i].checked = isChecked;
                    destCheckboxes[i].dispatchEvent(new Event('change'));
                }
            }
        }
        lastCheckedIndex = index;
    });

    checkbox.addEventListener('keydown', function(e) {
        if (e.shiftKey) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                e.preventDefault();
                if (index < destCheckboxes.length - 1) {
                    const nextCb = destCheckboxes[index + 1];
                    nextCb.checked = this.checked;
                    nextCb.focus();
                    nextCb.dispatchEvent(new Event('change'));
                    lastCheckedIndex = index + 1;
                }
            } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                e.preventDefault();
                if (index > 0) {
                    const prevCb = destCheckboxes[index - 1];
                    prevCb.checked = this.checked;
                    prevCb.focus();
                    prevCb.dispatchEvent(new Event('change'));
                    lastCheckedIndex = index - 1;
                }
            }
        }
    });
});
</script>

<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
