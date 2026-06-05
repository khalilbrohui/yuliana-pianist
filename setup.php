<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbName = 'music_academy';

$logs = [];

try {
    // 1. Connect to MySQL Server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $logs[] = "Conectado al servidor MySQL.";

    // 2. Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $logs[] = "Base de datos '$dbName' creada o verificada.";

    // 3. Connect to the created database
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 4. Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('student', 'admin') DEFAULT 'student',
        `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'users' verificada.";

    // 5. Create Courses Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `courses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(50) NOT NULL UNIQUE,
        `title` VARCHAR(100) NOT NULL,
        `description` TEXT NOT NULL,
        `details` TEXT NOT NULL,
        `benefits` TEXT NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `duration` VARCHAR(50) NOT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'courses' verificada.";

    // 6. Create Instructors Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `instructors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `instrument` VARCHAR(100) NOT NULL,
        `bio` TEXT NOT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'instructors' verificada.";

    // 7. Create Bookings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `bookings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT DEFAULT NULL,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(30) NOT NULL,
        `course_id` INT NOT NULL,
        `booking_date` DATE NOT NULL,
        `time_slot` VARCHAR(30) NOT NULL,
        `status` ENUM('pending', 'confirmed', 'cancelled', 'rescheduled') DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'bookings' verificada.";

    // 8. Create Blog Posts Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(150) NOT NULL UNIQUE,
        `title` VARCHAR(200) NOT NULL,
        `content` TEXT NOT NULL,
        `excerpt` TEXT NOT NULL,
        `category` VARCHAR(100) NOT NULL,
        `tags` VARCHAR(255) DEFAULT NULL,
        `author_id` INT NOT NULL,
        `status` ENUM('draft', 'published') DEFAULT 'draft',
        `image_url` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'blog_posts' verificada.";

    // 9. Create Site Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (
        `setting_key` VARCHAR(100) PRIMARY KEY,
        `setting_value` TEXT NOT NULL
    ) ENGINE=InnoDB");
    $logs[] = "Tabla 'site_settings' verificada.";

    // ==========================================
    // SEED SPANISH DATA
    // ==========================================

    // Seed Admin User
    $adminEmail = 'admin@yulianapianist.com';
    $stmt = $db = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $adminExists = $stmt->fetch();
    
    if (!$adminExists) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'approved')");
        $stmt->execute(['Administrador Yuliana', $adminEmail, $adminPass]);
        $adminId = $pdo->lastInsertId();
        $logs[] = "Usuario Admin creado: admin@yulianapianist.com / admin123";
    } else {
        $adminId = $adminExists['id'];
        $logs[] = "Usuario administrador ya existe.";
    }

    // Seed Instructors (Using uploaded image references!)
    $pdo->exec("TRUNCATE TABLE instructors");
    $instructors = [
        ['name' => 'Prof. Yuliana', 'instrument' => 'Piano', 'bio' => 'Yuliana es una pianista profesional y concertista internacional con más de 12 años de trayectoria pedagógica y artística.', 'image' => 'assets/images/violinist.jpg'], // Using violinist as coach photo
        ['name' => 'Prof. Carlos Gómez', 'instrument' => 'Voz y Canto', 'bio' => 'Carlos ayuda a los alumnos a liberar su voz, enseñando técnicas de respiración diafragmática, entonación y proyección escénica.', 'image' => 'assets/images/singer.jpg'], // Using singer
        ['name' => 'Prof. Sofía Ruiz', 'instrument' => 'Teoría Musical', 'bio' => 'Sofía se especializa en armonía clásica y lectura rítmica avanzada para estudiantes que buscan certificaciones profesionales.', 'image' => 'assets/images/certificate.jpg'] // Using certificate presentation image
    ];

    $stmt = $pdo->prepare("INSERT INTO instructors (name, instrument, bio, image_url) VALUES (?, ?, ?, ?)");
    foreach ($instructors as $inst) {
        $stmt->execute([$inst['name'], $inst['instrument'], $inst['bio'], $inst['image']]);
    }
    $logs[] = "Instructores registrados en español.";

    // Seed Courses
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE courses; SET FOREIGN_KEY_CHECKS = 1;");
    $courses = [
        [
            'slug' => 'piano',
            'title' => 'Clases de Piano Premium',
            'description' => 'Despierta al virtuoso que llevas dentro. Aprende desde acordes elementales hasta obras clásicas o contemporáneas avanzadas con instructores de primer nivel.',
            'details' => "Nuestro programa de piano está diseñado para todos los niveles de destreza. Aprenderás:\n- Lectura de partituras e interpretación a primera vista\n- Teoría musical aplicada y armonía\n- Ejercicios de independencia y velocidad digital\n- Preparación de repertorio y recitales en vivo.",
            'benefits' => 'Clases personalizadas y exclusivas;Horarios flexibles adaptados a ti;Recitales y presentaciones en auditorios;Uso de pianos acústicos y teclados de última generación',
            'price' => 120.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'guitar',
            'title' => 'Programa de Guitarra Élite',
            'description' => 'Domina la guitarra acústica, eléctrica o el bajo. Aprende acordes, arpegios y solos espectaculares con guías paso a paso.',
            'details' => "Aprende el estilo que más te guste: flamenco, jazz, rock o pop. Nos enfocamos en:\n- Posición de manos y técnicas de rasgueo\n- Escalas, acordes avanzados e improvisación\n- Mantenimiento y afinación del instrumento\n- Acompañamiento rítmico y ensambles.",
            'benefits' => 'Aprende acordes de forma interactiva;Talleres semanales de improvisación en vivo;Material digital de apoyo exclusivo;Afinación y mantenimiento de equipo',
            'price' => 110.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/certificate.jpg'
        ],
        [
            'slug' => 'drums',
            'title' => 'Batería y Percusión Rock',
            'description' => 'Encuentra tu ritmo ideal. Domina los tiempos, remates complejos y la sincronización total de tu set de batería con nuestras dinámicas clases.',
            'details' => "La batería es el corazón de la música. Nuestros profesores te enseñarán:\n- Coordinación e independencia de manos y pies\n- Ritmos de rock, jazz, funk y ritmos latinos\n- Lectura de partituras rítmicas\n- Improvisación y control de tempo con metrónomo.",
            'benefits' => 'Sets de baterías profesionales en estudio;Prácticas de velocidad y control de ritmo;Estudio de remates creativos;Entrenamiento auditivo para bandas',
            'price' => 130.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'vocal',
            'title' => 'Entrenamiento Vocal y Canto',
            'description' => 'Educa tu voz para cantar con absoluta confianza. Nuestros entrenadores te enseñarán soporte de aire, afinación y dominio escénico.',
            'details' => "Tu voz es tu instrumento principal. Aprende a protegerla y expandir tu rango:\n- Rutinas saludables de calentamiento vocal\n- Respiración diafragmática y afinación de notas\n- Proyección escénica y manejo del micrófono\n- Interpretación de diferentes géneros musicales.",
            'benefits' => 'Rutinas de calentamiento y salud vocal;Técnicas avanzadas de respiración;Corrección de tono y oído musical;Entrenamiento escénico para solistas',
            'price' => 115.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/singer.jpg'
        ],
        [
            'slug' => 'online',
            'title' => 'Clases Online Interactivas',
            'description' => 'Aprende música desde la comodidad de tu hogar. Transmisión en alta definición, pizarra interactiva y retroalimentación inmediata.',
            'details' => "¿Prefieres estudiar en casa? Nuestra plataforma online te conecta directamente con tu tutor. Incluye:\n- Transmisiones multicámara en alta definición\n- Acceso a archivos y partituras en tiempo real\n- Grabación de clases para repaso ilimitado\n- Evaluaciones personalizadas mensuales.",
            'benefits' => 'Estudia sin necesidad de viajar;Acceso a clases grabadas para repaso;Herramientas interactivas en pantalla;Match con profesores globales',
            'price' => 95.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/singer.jpg'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO courses (slug, title, description, details, benefits, price, duration, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($courses as $c) {
        $stmt->execute([$c['slug'], $c['title'], $c['description'], $c['details'], $c['benefits'], $c['price'], $c['duration'], $c['image']]);
    }
    $logs[] = "Cursos registrados en español.";

    // Seed Blog Posts
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE blog_posts; SET FOREIGN_KEY_CHECKS = 1;");
    $blogs = [
        [
            'slug' => 'como-iniciarse-en-las-clases-de-piano',
            'title' => 'Cómo Iniciarse en las Clases de Piano: Guía para Principiantes',
            'content' => '<p>Dar tus primeros pasos en el piano es una experiencia maravillosa. Sin embargo, muchos principiantes se sienten abrumados por la postura o la lectura de partituras.</p><h3>1. Enfócate en la Ergonomía</h3><p>Mantén tus dedos ligeramente curvados, como si sostuvieras una pelota pequeña. Esto te dará velocidad y prevendrá fatigas.</p><h3>2. Practica 15 Minutos Diarios</h3><p>La constancia supera a las largas sesiones acumuladas. Practicar un poco todos los días fija la memoria muscular de manera excepcional.</p>',
            'excerpt' => 'Descubre los consejos fundamentales, posturas de manos y rutinas diarias sencillas para acelerar tu aprendizaje inicial de piano.',
            'category' => 'Consejos y Guías',
            'tags' => 'Piano,Principiantes,Guía',
            'author_id' => $adminId,
            'status' => 'published',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'guitarra-acustica-vs-electrica-cual-es-mejor',
            'title' => 'Guitarra Acústica vs. Eléctrica: ¿Cuál es Mejor para Empezar?',
            'content' => '<p>Elegir tu primera guitarra depende de tus objetivos musicales y comodidad. Analicemos ambas opciones:</p><h3>Guitarra Acústica</h3><p>Pros: Es portátil, no requiere cables y fortalece rápidamente la punta de los dedos.</p><p>Cons: Las cuerdas de metal pueden ser duras al inicio.</p><h3>Guitarra Eléctrica</h3><p>Pros: Cuerdas más suaves de presionar y control de volumen ajustable con amplificador.</p><p>Cons: Requiere accesorios adicionales como cables y amplificadores.</p>',
            'excerpt' => 'Comparamos las ventajas y desventajas de las guitarras acústicas y eléctricas para ayudarte a tomar la decisión correcta.',
            'category' => 'Instrumentos',
            'tags' => 'Guitarra,Acústica,Eléctrica',
            'author_id' => $adminId,
            'status' => 'published',
            'image' => 'assets/images/certificate.jpg'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO blog_posts (slug, title, content, excerpt, category, tags, author_id, status, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($blogs as $b) {
        $stmt->execute([$b['slug'], $b['title'], $b['content'], $b['excerpt'], $b['category'], $b['tags'], $b['author_id'], $b['status'], $b['image']]);
    }
    $logs[] = "Artículos del blog registrados en español.";

    // Seed Site Settings (With Paraguay Phone & Spanish Content)
    $pdo->exec("TRUNCATE TABLE site_settings");
    $settings = [
        'hero_title' => 'Donde la Música Cobra Vida',
        'hero_subtitle' => 'YULIANA PIANIST & ACADEMIA DE MÚSICA',
        'hero_desc' => 'Descubre la magia del piano y otros instrumentos. Aprende con Yuliana Pianista y instructores de élite en clases personalizadas presenciales y online.',
        'contact_email' => 'info@yulianapianist.com',
        'contact_phone' => '+595 976 430263',
        'contact_address' => 'Asunción, Paraguay'
    ];

    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $key => $val) {
        $stmt->execute([$key, $val]);
    }
    $logs[] = "Configuraciones globales cargadas en español.";

} catch (PDOException $e) {
    $error = "Error al inicializar la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Base de Datos - Yuliana Pianist</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0a12;
            --card-bg: rgba(20, 18, 33, 0.7);
            --primary: #9d4edd;
            --teal: #00f5d4;
            --text-color: #e0e0e0;
        }
        body {
            margin: 0;
            padding: 40px 20px;
            background: linear-gradient(135deg, #09080e 0%, #15102a 100%);
            color: var(--text-color);
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary), var(--teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .logs-box {
            text-align: left;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin: 25px 0;
        }
        .log-item {
            color: #b0b0b0;
            margin-bottom: 8px;
        }
        .log-item::before {
            content: "➔ ";
            color: var(--teal);
        }
        .success-badge {
            background: rgba(0, 245, 212, 0.15);
            color: var(--teal);
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 245, 212, 0.3);
        }
        .error-badge {
            background: rgba(255, 99, 132, 0.15);
            color: #ff6384;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 99, 132, 0.3);
        }
        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, #7b2cbf 100%);
            color: #fff;
            padding: 14px 28px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 8px 20px rgba(157, 78, 221, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(157, 78, 221, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Instalación Yuliana Pianist</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-badge">Instalación Fallida</div>
            <p style="color: #ff6384;"><?= $error ?></p>
        <?php else: ?>
            <div class="success-badge">Base de Datos Inicializada</div>
            <p>Las tablas fueron creadas y pobladas con la información en español de Yuliana Pianist.</p>
        <?php endif; ?>

        <div class="logs-box">
            <?php foreach ($logs as $log): ?>
                <div class="log-item"><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
            <?php if (isset($error)): ?>
                <div class="log-item" style="color: #ff6384;">Error: <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!isset($error)): ?>
            <a href="index.php" class="btn">Ir al Sitio de Yuliana Pianist</a>
        <?php else: ?>
            <a href="setup.php" class="btn" style="background: #333; box-shadow: none;">Reintentar</a>
        <?php endif; ?>
    </div>
</body>
</html>
