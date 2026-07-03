<?php
// router.php for PHP built-in web server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $path = ltrim($path, '/');
    
    // Serve existing static files directly
    if (file_exists(__DIR__ . '/' . $path) && is_file(__DIR__ . '/' . $path)) {
        return false;
    }

    $routes = [
        '~^tour/([^/]+)$~' => ['tour.php', ['slug']],
        '~^register/([^/]+)$~' => ['register.php', ['tour']],
        '~^admin/tour-edit/([0-9]+)$~' => ['admin/tour-edit.php', ['id']],
        '~^admin/guide-edit/([0-9]+)$~' => ['admin/guide-edit.php', ['id']],
        '~^admin/testimonial-edit/([0-9]+)$~' => ['admin/testimonial-edit.php', ['id']],
    ];

    foreach ($routes as $pattern => list($file, $params)) {
        if (preg_match($pattern, $path, $matches)) {
            foreach ($params as $i => $key) {
                $_GET[$key] = $matches[$i + 1];
                $_REQUEST[$key] = $matches[$i + 1];
            }
            $_SERVER['SCRIPT_NAME'] = '/' . $file;
            $_SERVER['PHP_SELF'] = '/' . $file;
            require __DIR__ . '/' . $file;
            exit;
        }
    }

    if ($path === '') {
        $path = 'index';
    }

    // Implicit .php extension
    if (file_exists(__DIR__ . '/' . $path . '.php')) {
        $_SERVER['SCRIPT_NAME'] = '/' . $path . '.php';
        $_SERVER['PHP_SELF'] = '/' . $path . '.php';
        require __DIR__ . '/' . $path . '.php';
        exit;
    }
    
    // Directory index.php
    if (is_dir(__DIR__ . '/' . $path) && file_exists(__DIR__ . '/' . $path . '/index.php')) {
        $path = rtrim($path, '/');
        $_SERVER['SCRIPT_NAME'] = '/' . $path . '/index.php';
        $_SERVER['PHP_SELF'] = '/' . $path . '/index.php';
        require __DIR__ . '/' . $path . '/index.php';
        exit;
    }

    // 404
    http_response_code(404);
    echo "404 Not Found";
    exit;
}
