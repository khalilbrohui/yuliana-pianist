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
    $adminEmail = 'admin@yulianaviolinist.com';
    $stmt = $db = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $adminExists = $stmt->fetch();
    
    if (!$adminExists) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'approved')");
        $stmt->execute(['Administrador Yuliana', $adminEmail, $adminPass]);
        $adminId = $pdo->lastInsertId();
        $logs[] = "Usuario Admin creado: admin@yulianaviolinist.com / admin123";
    } else {
        $adminId = $adminExists['id'];
        $logs[] = "Usuario administrador ya existe.";
    }

    // Seed Instructors (Using uploaded image references!)
    $pdo->exec("TRUNCATE TABLE instructors");
    $instructors = [
        ['name' => 'Prof. Yuliana', 'instrument' => 'Violín', 'bio' => 'Yuliana es una violinista profesional y concertista internacional con más de 12 años de trayectoria pedagógica y artística.', 'image' => 'assets/images/violinist.jpg'], // Using violinist as coach photo
        ['name' => 'Prof. Carlos Gómez', 'instrument' => 'Violín Acústico', 'bio' => 'Carlos ayuda a los alumnos a dominar la técnica de arco, enseñando lectura de partituras, afinación precisa y teoría aplicada.', 'image' => 'assets/images/singer.jpg'], // Using singer
        ['name' => 'Prof. Sofía Ruiz', 'instrument' => 'Violín y Teoría Musical', 'bio' => 'Sofía se especializa en armonía clásica, lectura de partituras y técnica de violín para estudiantes que buscan de forma profesional su certificación.', 'image' => 'assets/images/certificate.jpg'] // Using certificate presentation image
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
            'slug' => 'violin-premium',
            'title' => 'Clases de Violín Premium',
            'description' => 'Despierta al virtuoso que llevas dentro. Aprende desde técnicas fundamentales de arco hasta obras clásicas o contemporáneas avanzadas con instructores de primer nivel.',
            'details' => "Nuestro programa de violín está diseñado para todos los niveles de destreza. Aprenderás:\n- Lectura de partituras e interpretación a primera vista\n- Teoría musical aplicada y armonía para cuerdas\n- Ejercicios de postura, afinación e independencia de manos\n- Preparación de repertorio clásico y contemporáneo.",
            'benefits' => 'Clases personalizadas y exclusivas;Horarios flexibles adaptados a ti;Recitales y presentaciones en auditorios;Uso de violines acústicos y eléctricos de gama alta',
            'price' => 120.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'violin-elite',
            'title' => 'Programa de Violín Élite',
            'description' => 'Domina el violín acústico y eléctrico. Aprende escalas complejas, vibrato avanzado y solos espectaculares con guías paso a paso.',
            'details' => "Aprende el estilo que más te guste: clásico, barroco, pop o jazz. Nos enfocamos en:\n- Posición correcta de manos, mentonera y arco\n- Escalas, arpegios avanzados e improvisación\n- Mantenimiento y afinación precisa del instrumento\n- Acompañamiento rítmico y ensambles de cuerdas.",
            'benefits' => 'Aprende digitación de forma interactiva;Talleres semanales de improvisación en vivo;Material digital de apoyo exclusivo;Ajuste y mantenimiento de violín',
            'price' => 110.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/certificate.jpg'
        ],
        [
            'slug' => 'violin-kids',
            'title' => 'Violín Suzuki para Niños',
            'description' => 'Iniciación musical para los más pequeños. Fomenta la memoria musical, coordinación y el amor por el violín desde temprana edad.',
            'details' => "El método Suzuki y la pedagogía infantil son la base de este curso. Los niños aprenderán:\n- Coordinación motora e independencia auditiva\n- Juegos musicales adaptados al tamaño de violín infantil\n- Lectura de partituras rítmicas simplificadas\n- Práctica divertida en un entorno grupal y de apoyo.",
            'benefits' => 'Profesores especializados en pedagogía infantil;Violines pequeños de cortesía en estudio;Método Suzuki y juegos interactivos;Desarrollo de oído musical temprano',
            'price' => 130.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'violin-arco',
            'title' => 'Técnica de Arco y Expresión',
            'description' => 'Domina el uso del arco para lograr un sonido limpio, potente y lleno de matices emocionales en cada interpretación.',
            'details' => "La mano derecha es la voz del violín. Aprende a controlar el sonido y los golpes de arco:\n- Técnicas de legato, staccato, spiccato y detache\n- Dinámicas de volumen, modulación y afinación expresiva\n- Proyección escénica y relajación corporal\n- Interpretación de diferentes géneros y estilos musicales.",
            'benefits' => 'Perfeccionamiento técnico personalizado;Técnicas avanzadas de articulación física;Corrección de postura y tensión corporal;Prácticas de expresión y dinámicas',
            'price' => 115.00,
            'duration' => '4 Clases al Mes (45 min cada una)',
            'image' => 'assets/images/singer.jpg'
        ],
        [
            'slug' => 'violin-online',
            'title' => 'Clases de Violín Online',
            'description' => 'Aprende violín desde la comodidad de tu hogar. Transmisión en alta definición, pizarra interactiva y retroalimentación inmediata.',
            'details' => "¿Prefieres estudiar en casa? Nuestra plataforma online te conecta directamente con tu tutor. Incluye:\n- Transmisiones multicámara en alta definición enfocadas en tus manos\n- Acceso a archivos y partituras en tiempo real\n- Grabación de clases para repaso ilimitado\n- Evaluaciones personalizadas mensuales y soporte.",
            'benefits' => 'Estudia sin necesidad de viajar;Acceso a clases grabadas para repaso;Herramientas interactivas en pantalla;Match con profesores de violín globales',
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
            'slug' => 'como-iniciarse-en-las-clases-de-violin',
            'title' => 'Cómo Iniciarse en las Clases de Violín: Guía para Principiantes',
            'content' => '<p>Dar tus primeros pasos en el violín es una experiencia maravillosa. Sin embargo, muchos principiantes se sienten abrumados por la postura o el agarre del arco.</p><h3>1. Enfócate en la Postura</h3><p>Mantén la espalda recta y el violín paralelo al suelo. El agarre del arco debe ser relajado pero firme, evitando tensiones en la muñeca.</p><h3>2. Practica 15 Minutos Diarios</h3><p>La constancia supera a las largas sesiones acumuladas. Practicar un poco todos los días fija la memoria muscular de manera excepcional.</p>',
            'excerpt' => 'Descubre los consejos fundamentales, posturas de manos y rutinas de arco sencillas para acelerar tu aprendizaje inicial de violín.',
            'category' => 'Consejos y Guías',
            'tags' => 'Violín,Principiantes,Guía',
            'author_id' => $adminId,
            'status' => 'published',
            'image' => 'assets/images/violinist.jpg'
        ],
        [
            'slug' => 'violin-acustico-vs-electrico-cual-es-mejor',
            'title' => 'Violín Acústico vs. Eléctrico: ¿Cuál es Mejor para Empezar?',
            'content' => '<p>Elegir tu primer violín depende de tus objetivos musicales y comodidad. Analicemos ambas opciones:</p><h3>Violín Acústico</h3><p>Pros: Es tradicional, no requiere cables ni amplificadores y produce un tono cálido y natural de forma nativa.</p><p>Cons: No se puede regular el volumen, lo que puede ser ruidoso al practicar en casa.</p><h3>Violín Eléctrico</h3><p>Pros: Permite practicar con auriculares, ajustar el volumen y experimentar con efectos electrónicos.</p><p>Cons: Requiere accesorios adicionales como amplificador, cables y pilas.</p>',
            'excerpt' => 'Comparamos las ventajas y desventajas de los violines acústicos y eléctricos para ayudarte a tomar la decisión correcta.',
            'category' => 'Instrumentos',
            'tags' => 'Violín,Acústico,Eléctrico',
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
        'hero_title' => 'Donde el Violín Cobra Vida',
        'hero_subtitle' => 'YULIANA VIOLINIST & ACADEMIA DE VIOLÍN',
        'hero_desc' => 'Descubre la magia del violín. Aprende con Yuliana Violinista e instructores de élite en clases personalizadas presenciales y online.',
        'contact_email' => 'info@yulianaviolinist.com',
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
