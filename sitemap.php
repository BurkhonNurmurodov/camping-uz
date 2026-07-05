<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'silknaviora.com';
$baseUrl = $protocol . '://' . $host . BASE_PATH;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static Pages
$staticPages = [
    '',
    '/tours',
    '/contact'
];

foreach ($staticPages as $path) {
    echo "  <url>\n";
    echo "    <loc>" . e($baseUrl . $path) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>" . ($path === '' ? '1.0' : '0.8') . "</priority>\n";
    echo "  </url>\n";
}

// Active Tours
$tours = db_all("SELECT slug, updated_at FROM tours WHERE status='upcoming' ORDER BY sort_order, start_date");
foreach ($tours as $t) {
    $date = $t['updated_at'] ? date('Y-m-d', strtotime($t['updated_at'])) : date('Y-m-d');
    echo "  <url>\n";
    echo "    <loc>" . e($baseUrl . '/tour/' . $t['slug']) . "</loc>\n";
    echo "    <lastmod>" . e($date) . "</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.9</priority>\n";
    echo "  </url>\n";
}

// Active Categories
$cats = db_all("SELECT slug FROM categories ORDER BY sort_order");
foreach ($cats as $c) {
    echo "  <url>\n";
    echo "    <loc>" . e($baseUrl . '/tours?cat=' . $c['slug']) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
