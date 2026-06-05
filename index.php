<?php
// Include database configuration & utility functions
require_once 'config.php';

// Serve static assets directly if running via PHP built-in web server
$requestUri = $_SERVER["REQUEST_URI"];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

if (php_sapi_name() === 'cli-server' && preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|mp3|mp4|svg|docx|woff|woff2|ttf)$/', $path)) {
    return false;
}

// Clean and normalize the request path
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Extract path relative to base path
if (strpos($path, $basePath) === 0) {
    $route = substr($path, strlen($basePath));
} else {
    $route = $path;
}
$route = '/' . ltrim($route, '/');

// Routing Table
$viewFile = 'views/home.php';
$params = [];
$pageTitle = 'Yuliana Violinist - Clases de Violín Premium y Lecciones';
$pageDescription = 'Aprende violín con instructores de primer nivel. Reserva tu clase de prueba gratuita presencial u online.';

// Parse SEO-friendly dynamic routes
if ($route === '/' || $route === '/home') {
    $viewFile = 'views/home.php';
} elseif ($route === '/booking') {
    $viewFile = 'views/booking_page.php';
    $pageTitle = 'Reservar Clase de Prueba Gratis - Yuliana Violinist';
} elseif ($route === '/registration') {
    $viewFile = 'views/registration_page.php';
    $pageTitle = 'Portal de Estudiantes - Yuliana Violinist';
} elseif ($route === '/dashboard') {
    $viewFile = 'views/student_dashboard.php';
    $pageTitle = 'Panel de Estudiantes - Yuliana Violinist';
} elseif ($route === '/admin') {
    $viewFile = 'views/admin_dashboard.php';
    $pageTitle = 'Panel de Administración - Yuliana Violinist';
} elseif ($route === '/contact') {
    $viewFile = 'views/contact_page.php';
    $pageTitle = 'Contacto - Yuliana Violinist';
} elseif ($route === '/blog') {
    $viewFile = 'views/blog_list.php';
    $pageTitle = 'Diario de la Academia - Noticias y Consejos';
} elseif (preg_match('#^/blog/([a-zA-Z0-9\-]+)$#', $route, $matches)) {
    $viewFile = 'views/blog_post.php';
    $params['slug'] = $matches[1];
} elseif (preg_match('#^/courses/([a-zA-Z0-9\-]+)$#', $route, $matches)) {
    $viewFile = 'views/course_details.php';
    $params['slug'] = $matches[1];
} elseif ($route === '/logout') {
    session_destroy();
    header("Location: " . BASE_URL . "/");
    exit;
} else {
    header("HTTP/1.0 404 Not Found");
    $viewFile = 'views/home.php';
    $pageTitle = 'Página No Encontrada - Yuliana Violinist';
}

// Buffer page rendering to inject headers/footers elegantly
ob_start();
if (file_exists($viewFile)) {
    include $viewFile;
} else {
    echo "<div class='container' style='padding:100px 20px; text-align:center;'><h2>404 - Vista no encontrada</h2><p>La plantilla de la página solicitada no existe.</p></div>";
}
$pageContent = ob_get_clean();

// Assemble header and footer around the view content
include 'views/header.php';
echo $pageContent;
include 'views/footer.php';
?>
