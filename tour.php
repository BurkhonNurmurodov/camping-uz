<?php
require __DIR__ . '/app/bootstrap.php';

$lang = current_lang();
$slug = (string) input('slug', '');
$tour = $slug ? db_one("SELECT * FROM tours WHERE slug=? AND status<>'draft'", [$slug]) : null;

if (!$tour) {
    http_response_code(404);
    $head_title = ($lang === 'ru' ? 'Тур не найден' : 'Tour not found');
    require __DIR__ . '/partials/head.php';
    echo '<section class="cu-section" style="padding-top:200px"><div class="container text-center">'
        . '<h2>' . ($lang === 'ru' ? 'Тур не найден' : 'Tour not found') . '</h2>'
        . '<p><a class="main-btn primary-btn mt-20" href="' . url('tours') . '">' . e(t('sec_upcoming_tours')) . '</a></p>'
        . '</div></section>';
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
    exit;
}

$title  = lang_field($tour, 'title') ?: 'Tour';
$dates  = format_tour_dates($tour['start_date'], $tour['end_date'], $lang);
$desc   = $tour['description_' . $lang . '_html'] ?? $tour['description_en_html'] ?? null;
$points = db_all('SELECT * FROM tour_route_points WHERE tour_id=? ORDER BY sort_order', [$tour['id']]);
$categories = db_all('SELECT c.slug, c.title_en, c.title_ru FROM categories c JOIN tour_categories tc ON tc.category_id=c.id WHERE tc.tour_id=?', [$tour['id']]);
$guides = db_all(
    'SELECT g.* FROM guides g JOIN tour_guides tg ON tg.guide_id=g.id WHERE tg.tour_id=? ORDER BY tg.sort_order',
    [$tour['id']]
);
$googleKey = setting('google_maps_api_key', '');

/** Build a displayable social link for a guide social row. */
function guide_social(array $s): array
{
    $v = trim((string) $s['value']);
    $u = ltrim($v, '@');
    $map = [
        'whatsapp'  => ['https://wa.me/' . preg_replace('/\D+/', '', $v), 'fab fa-whatsapp', 'WhatsApp'],
        'instagram' => ['https://instagram.com/' . $u, 'fab fa-instagram', 'Instagram'],
        'telegram'  => ['https://t.me/' . $u, 'fab fa-telegram-plane', 'Telegram'],
        'facebook'  => ['https://facebook.com/' . $u, 'fab fa-facebook-f', 'Facebook'],
        'twitter'   => ['https://twitter.com/' . $u, 'fab fa-twitter', 'X'],
        'linkedin'  => [$v, 'fab fa-linkedin-in', 'LinkedIn'],
    ];
    if (isset($map[$s['type']])) {
        [$url, $fa, $label] = $map[$s['type']];
        return ['url' => $url, 'faclass' => $fa, 'icon' => null, 'label' => $label];
    }
    // other
    return [
        'url'     => $v,
        'faclass' => 'fas fa-link',
        'icon'    => $s['custom_icon'] ? upload_url($s['custom_icon']) : null,
        'label'   => $s['custom_name'] ?: 'Link',
    ];
}

$head_title = $title . ' — ' . setting('agency_name_' . $lang, 'Silk Naviora');
require __DIR__ . '/partials/head.php';
?>

<section class="cu-section" style="padding-top:150px">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($tour['poster']): ?>
                    <img class="cu-detail-hero wow fadeInUp" src="<?= e(upload_url($tour['poster'])) ?>" alt="<?= e($title) ?>">
                <?php endif; ?>

                <div class="text-center mt-40 mb-30">
                    <h1 class="wow fadeInUp h2"><?= e($title) ?></h1>
                    
                    <?php if ($categories): ?>
                        <div class="mt-2 mb-2 wow fadeInUp" style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                            <?php foreach ($categories as $c): ?>
                                <a href="<?= url('tours?cat=' . e($c['slug'])) ?>" class="badge bg-light text-dark border" style="text-decoration:none; font-weight:500; font-size:13px; padding:6px 12px; border-radius:50px; transition:all 0.2s;">
                                    <?= e(lang_field($c, 'title')) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($dates): ?>
                        <p class="cu-detail-meta mt-10"><i class="far fa-calendar-alt me-2"></i><?= e($dates) ?></p>
                    <?php endif; ?>
                    <?php if ($tour['status'] === 'upcoming'): ?>
                        <div class="mt-30 wow fadeInUp">
                            <a href="<?= url('register/' . urlencode($tour['slug'])) ?>" class="main-btn primary-btn"><?= e(t('register_cta')) ?><i class="fas fa-paper-plane"></i></a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($desc && trim(strip_tags($desc)) !== ''): ?>
                    <div class="cu-richtext wow fadeInUp"><?= render_html($desc) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Route -->
        <?php if ($points): ?>
            <div class="row justify-content-center mt-60">
                <div class="col-lg-10">
                    <div class="cu-sec-head text-center"><h2><?= e(t('sec_route')) ?></h2></div>
                    <?php if ($googleKey): ?>
                        <div id="cuRouteMap" class="cu-route-map wow fadeInUp"></div>
                        <div class="text-center mt-30 wow fadeInUp">
                            <?php
                            $pathSegments = array_map(static fn($p) => $p['lat'] . ',' . $p['lng'], $points);
                            $googleRouteUrl = 'https://www.google.com/maps/dir/' . implode('/', $pathSegments) . '/';
                            ?>
                            <a href="<?= $googleRouteUrl ?>" target="_blank" class="main-btn primary-btn">
                                <?= $lang === 'ru' ? 'Открыть маршрут в Google Maps' : 'Open route on Google Maps' ?>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <ol class="cu-route-list">
                            <?php foreach ($points as $p): ?>
                                <li><?= e(lang_field($p, 'label') ?: ($p['lat'] . ', ' . $p['lng'])) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Guides -->
        <?php if ($guides): ?>
            <div class="row justify-content-center mt-60">
                <div class="col-lg-10">
                    <div class="cu-sec-head text-center"><h2><?= e(t('sec_guides')) ?></h2></div>
                    <div class="row justify-content-center">
                        <?php foreach ($guides as $g):
                            $socials = db_all('SELECT * FROM guide_socials WHERE guide_id=? ORDER BY sort_order', [$g['id']]);
                            $socialData = array_map('guide_social', $socials);
                            $payload = [
                                'name'    => $g['full_name'],
                                'avatar'  => $g['image'] ? upload_url($g['image']) : 'assets/images/testimonial/author-1.jpg',
                                'bio'     => nl2br(e(lang_field($g, 'bio') ?: '')),
                                'socials' => $socialData,
                            ];
                            ?>
                            <div class="col-lg-3 col-md-4 col-6">
                                <div class="cu-guide wow fadeInUp"
                                     data-name="<?= e($g['full_name']) ?>"
                                     data-avatar="<?= e($payload['avatar']) ?>"
                                     data-bio="<?= e($payload['bio']) ?>"
                                     data-socials='<?= e(json_encode($socialData, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                    <img class="cu-guide__avatar" src="<?= e($payload['avatar']) ?>" alt="<?= e($g['full_name']) ?>">
                                    <p class="cu-guide__name"><?= e($g['full_name']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-50">
            <a href="<?= url('tours') ?>" class="main-btn"><i class="far fa-long-arrow-left me-2"></i><?= e(t('sec_upcoming_tours')) ?></a>
        </div>
    </div>
</section>

<!-- Guide modal -->
<div class="modal fade cu-guide-modal" id="cuGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="cu-guide-modal__top"></div>
            <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body px-4 pb-4">
                <img class="cu-guide-modal__avatar" src="" alt="">
                <h3 class="cu-guide-modal__name mt-2 mb-2"></h3>
                <div class="cu-guide-modal__socials mb-3"></div>
                <div class="cu-guide-modal__bio text-muted"></div>
            </div>
        </div>
    </div>
</div>

<?php
if ($points && $googleKey) {
    $pts = array_map(static fn($p) => [
        'lat' => (float) $p['lat'], 'lng' => (float) $p['lng'], 'label' => lang_field($p, 'label') ?: '',
    ], $points);
    $foot_js = 'window.CU_ROUTE = ' . json_encode($pts) . ';' . "\nvar GKEY = " . json_encode($googleKey) . ";" . <<<'JS'
    window.initMap = function() {
      var pts = window.CU_ROUTE || [];
      if (!pts.length) return;
      var map = new google.maps.Map(document.getElementById("cuRouteMap"), {
        center: {lat: pts[0].lat, lng: pts[0].lng}, zoom: 7,
        streetViewControl: false, mapTypeControl: false
      });
      var directionsService = new google.maps.DirectionsService();
      var bounds = new google.maps.LatLngBounds();
      var infoWindow = new google.maps.InfoWindow();

      pts.forEach(function(p, i) {
        var pos = {lat: p.lat, lng: p.lng};
        var marker = new google.maps.Marker({
          position: pos, map: map, label: (i+1).toString()
        });
        if (p.label) {
          marker.addListener("click", function() {
            infoWindow.setContent("<div style='padding:5px;font-family:sans-serif;font-weight:bold;color:#1d231f;'>" + p.label + "</div>");
            infoWindow.open(map, marker);
          });
        }
        bounds.extend(pos);
      });

      if (pts.length > 1) {
        for (let i = 0; i < pts.length - 1; i++) {
          (function(start, end) {
            function getDist(p1, p2) {
              var R = 6371e3, lat1 = p1.lat*Math.PI/180, lat2 = p2.lat*Math.PI/180;
              var dLat = (p2.lat-p1.lat)*Math.PI/180, dLng = (p2.lng-p1.lng)*Math.PI/180;
              var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1)*Math.cos(lat2)*Math.sin(dLng/2)*Math.sin(dLng/2);
              return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            }
            directionsService.route({
              origin: {lat: start.lat, lng: start.lng},
              destination: {lat: end.lat, lng: end.lng},
              travelMode: google.maps.TravelMode.DRIVING
            }, function(response, status) {
              var lineSymbol = { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 };
              function drawDashed(p1, p2) {
                new google.maps.Polyline({
                  path: [p1, p2], strokeOpacity: 0,
                  icons: [{icon: lineSymbol, offset: '0', repeat: '15px'}],
                  strokeColor: "#63ab45", strokeWeight: 4, map: map
                });
              }
              if (status === "OK" && response.routes && response.routes.length > 0) {
                var path = response.routes[0].overview_path;
                if (path.length > 0) {
                  var firstC = path[0];
                  var lastC = path[path.length - 1];
                  var dStartRoad = getDist(start, {lat: firstC.lat(), lng: firstC.lng()});
                  var dEndRoad = getDist(end, {lat: lastC.lat(), lng: lastC.lng()});
                  var dTotal = getDist(start, end);

                  if (dTotal > 0 && (dStartRoad + dEndRoad > dTotal * 0.8)) {
                    drawDashed(start, end);
                    return;
                  }

                  new google.maps.Polyline({
                    path: path,
                    strokeColor: "#63ab45", strokeOpacity: 0.9, strokeWeight: 4, map: map
                  });

                  if (dStartRoad > 10) drawDashed(start, firstC);
                  if (dEndRoad > 10) drawDashed(lastC, end);
                }
              } else {
                drawDashed(start, end);
              }
            });
          })(pts[i], pts[i+1]);
        }
        map.fitBounds(bounds);
      }
    };
    var s = document.createElement("script");
    s.src = "https://maps.googleapis.com/maps/api/js?key=" + encodeURIComponent(GKEY) + "&callback=initMap";
    s.async = true; s.defer = true;
    document.head.appendChild(s);
JS;
}
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
