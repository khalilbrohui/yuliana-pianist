<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Optimización SEO -->
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Yuliana Pianist - Clases de Música Premium' ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Desarrolla tu talento musical con instructores de primer nivel en Piano, Guitarra, Batería y Voz.' ?>">
    
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Iconos FontAwesome y Estilos Base -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Biblioteca jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Three.js y GSAP desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
</head>
<body>
    <!-- Orbes decorativos de fondo -->
    <div class="decor-orb glow-orb-1"></div>
    <div class="decor-orb glow-orb-2"></div>
    <div class="decor-orb glow-orb-3"></div>

    <!-- Encabezado de Navegación -->
    <header id="mainHeader">
        <div class="header-container">
            <a href="<?= BASE_URL ?>/" class="logo-link" id="logoBrand">
                <span class="logo-text">YULIANA</span>
                <span class="logo-subtext">PIANIST & ACADEMIA</span>
                <div class="logo-dot"></div>
            </a>
            
            <nav id="navbarMenu" class="nav-menu">
                <ul>
                    <li><a href="<?= BASE_URL ?>/" class="nav-link">Inicio</a></li>
                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Cursos <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= BASE_URL ?>/courses/piano">Clases de Piano</a></li>
                            <li><a href="<?= BASE_URL ?>/courses/guitar">Clases de Guitarra</a></li>
                            <li><a href="<?= BASE_URL ?>/courses/drums">Batería y Percusión</a></li>
                            <li><a href="<?= BASE_URL ?>/courses/vocal">Entrenamiento Vocal</a></li>
                            <li><a href="<?= BASE_URL ?>/courses/online">Clases Online</a></li>
                        </ul>
                    </li>
                    <li><a href="<?= BASE_URL ?>/blog" class="nav-link">Blog</a></li>
                    <li><a href="<?= BASE_URL ?>/contact" class="nav-link">Contacto</a></li>
                    
                    <?php if (isAdmin()): ?>
                        <li><a href="<?= BASE_URL ?>/admin" class="nav-link btn-admin"><i class="fas fa-user-shield"></i> Panel Admin</a></li>
                        <li><a href="<?= BASE_URL ?>/logout" class="nav-link logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                    <?php elseif (isStudent()): ?>
                        <li><a href="<?= BASE_URL ?>/dashboard" class="nav-link btn-dashboard"><i class="fas fa-graduation-cap"></i> Mi Panel</a></li>
                        <li><a href="<?= BASE_URL ?>/logout" class="nav-link logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/registration" class="nav-link btn-login"><i class="fas fa-user"></i> Portal Alumno</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header-actions">
                <a href="<?= BASE_URL ?>/booking" class="btn-cta" id="headerCtaBtn">Clase de Prueba Gratis</a>
                <button class="mobile-toggle" id="mobileNavToggle" aria-label="Alternar Navegación">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Contenedor dinámico principal AJAX -->
    <main id="ajaxPageContainer">
