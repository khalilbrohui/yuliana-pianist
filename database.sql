-- phpMyAdmin SQL Dump
-- Database: if0_42108863_music_academy

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `instructors`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Create Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') DEFAULT 'student',
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create Courses Table
CREATE TABLE `courses` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create Instructors Table
CREATE TABLE `instructors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `instrument` VARCHAR(100) NOT NULL,
  `bio` TEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create Bookings Table
CREATE TABLE `bookings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create Blog Posts Table
CREATE TABLE `blog_posts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create Site Settings Table
CREATE TABLE `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SEED DATA
-- ==========================================

-- Seed Administrator (password: admin123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, 'Administrador Yuliana', 'admin@yulianaviolinist.com', '$2y$10$wUHTpT.e/3yLh4iYQ6F/A.hC9lW3G1y817tqD0K.P2x10tQ19rV2y', 'admin', 'approved');

-- Seed Instructors
INSERT INTO `instructors` (`name`, `instrument`, `bio`, `image_url`) VALUES
('Prof. Yuliana', 'Violín', 'Yuliana es una violinista profesional y concertista internacional con más de 12 años de trayectoria pedagógica y artística.', 'assets/images/violinist.jpg'),
('Prof. Carlos Gómez', 'Violín Acústico', 'Carlos ayuda a los alumnos a dominar la técnica de arco, enseñando lectura de partituras, afinación precisa y teoría aplicada.', 'assets/images/singer.jpg'),
('Prof. Sofía Ruiz', 'Violín y Teoría Musical', 'Sofía se especializa en armonía clásica, lectura de partituras y técnica de violín para estudiantes que buscan de forma profesional su certificación.', 'assets/images/certificate.jpg');

-- Seed Courses
INSERT INTO `courses` (`slug`, `title`, `description`, `details`, `benefits`, `price`, `duration`, `image_url`) VALUES
('violin-premium', 'Clases de Violín Premium', 'Despierta al virtuoso que llevas dentro. Aprende desde técnicas fundamentales de arco hasta obras clásicas o contemporáneas avanzadas con instructores de primer nivel.', 'Nuestro programa de violín está diseñado para todos los niveles de destreza. Aprenderás:\n- Lectura de partituras e interpretación a primera vista\n- Teoría musical aplicada y armonía para cuerdas\n- Ejercicios de postura, afinación e independencia de manos\n- Preparación de repertorio clásico y contemporáneo.', 'Clases personalizadas y exclusivas;Horarios flexibles adaptados a ti;Recitales y presentaciones en auditorios;Uso de violines acústicos y eléctricos de gama alta', 120.00, '4 Clases al Mes (45 min cada una)', 'assets/images/violinist.jpg'),
('violin-elite', 'Programa de Violín Élite', 'Domina el violín acústico y eléctrico. Aprende escalas complejas, vibrato avanzado y solos espectaculares con guías paso a paso.', 'Aprende el estilo que más te guste: clásico, barroco, pop o jazz. Nos enfocamos en:\n- Posición correcta de manos, mentonera y arco\n- Escalas, arpegios avanzados e improvisación\n- Mantenimiento y afinación precisa del instrumento\n- Acompañamiento rítmico y ensambles de cuerdas.', 'Aprende digitación de forma interactiva;Talleres semanales de improvisación en vivo;Material digital de apoyo exclusivo;Ajuste y mantenimiento de violín', 110.00, '4 Clases al Mes (45 min cada una)', 'assets/images/certificate.jpg'),
('violin-kids', 'Violín Suzuki para Niños', 'Iniciación musical para los más pequeños. Fomenta la memoria musical, coordinación y el amor por el violín desde temprana edad.', 'El método Suzuki y la pedagogía infantil son la base de este curso. Los niños aprenderán:\n- Coordinación motora e independencia auditiva\n- Juegos musicales adaptados al tamaño de violín infantil\n- Lectura de partituras rítmicas simplificadas\n- Práctica divertida en un entorno grupal y de apoyo.', 'Profesores especializados en pedagogía infantil;Violines pequeños de cortesía en estudio;Método Suzuki y juegos interactivos;Desarrollo de oído musical temprano', 130.00, '4 Clases al Mes (45 min cada una)', 'assets/images/violinist.jpg'),
('violin-arco', 'Técnica de Arco y Expresión', 'Domina el uso del arco para lograr un sonido limpio, potente y lleno de matices emocionales en cada interpretación.', 'La mano derecha es la voz del violín. Aprende a controlar el sonido y los golpes de arco:\n- Técnicas de legato, staccato, spiccato y detache\n- Dinámicas de volumen, modulación y afinación expresiva\n- Proyección escénica y relajación corporal\n- Interpretación de diferentes géneros y estilos musicales.', 'Perfeccionamiento técnico personalizado;Técnicas avanzadas de articulación física;Corrección de postura y tensión corporal;Prácticas de expresión y dinámicas', 115.00, '4 Clases al Mes (45 min cada una)', 'assets/images/singer.jpg'),
('violin-online', 'Clases de Violín Online', 'Aprende violín desde la comodidad de tu hogar. Transmisión en alta definición, pizarra interactiva y retroalimentación inmediata.', '¿Prefieres estudiar en casa? Nuestra plataforma online te conecta directamente con tu tutor. Incluye:\n- Transmisiones multicámara en alta definición enfocadas en tus manos\n- Acceso a archivos y partituras en tiempo real\n- Grabación de clases para repaso ilimitado\n- Evaluaciones personalizadas mensuales y soporte.', 'Estudia sin necesidad de viajar;Acceso a clases grabadas para repaso;Herramientas interactivas en pantalla;Match con profesores de violín globales', 95.00, '4 Clases al Mes (45 min cada una)', 'assets/images/singer.jpg');

-- Seed Blog Posts
INSERT INTO `blog_posts` (`slug`, `title`, `content`, `excerpt`, `category`, `tags`, `author_id`, `status`, `image_url`) VALUES
('como-iniciarse-en-las-clases-de-violin', 'Cómo Iniciarse en las Clases de Violín: Guía para Principiantes', '<p>Dar tus primeros pasos en el violín es una experiencia maravillosa. Sin embargo, muchos principiantes se sienten abrumados por la postura o el agarre del arco.</p><h3>1. Enfócate en la Postura</h3><p>Mantén la espalda recta y el violín paralelo al suelo. El agarre del arco debe ser relajado pero firme, evitando tensiones en la muñeca.</p><h3>2. Practica 15 Minutos Diarios</h3><p>La constancia supera a las largas sesiones acumuladas. Practicar un poco todos los días fija la memoria muscular de manera excepcional.</p>', 'Descubre los consejos fundamentales, posturas de manos y rutinas de arco sencillas para acelerar tu aprendizaje inicial de violín.', 'Consejos y Guías', 'Violín,Principiantes,Guía', 1, 'published', 'assets/images/violinist.jpg'),
('violin-acustico-vs-electrico-cual-es-mejor', 'Violín Acústico vs. Eléctrico: ¿Cuál es Mejor para Empezar?', '<p>Elegir tu primer violín depende de tus objetivos musicales y comodidad. Analicemos ambas opciones:</p><h3>Violín Acústico</h3><p>Pros: Es tradicional, no requiere cables ni amplificadores y produce un tono cálido y natural de forma nativa.</p><p>Cons: No se puede regular el volumen, lo que puede ser ruidoso al practicar en casa.</p><h3>Violín Eléctrico</h3><p>Pros: Permite practicar con auriculares, ajustar el volumen y experimentar con efectos electrónicos.</p><p>Cons: Requiere accesorios adicionales como amplificador, cables y pilas.</p>', 'Comparamos las ventajas y desventajas de los violines acústicos y eléctricos para ayudarte a tomar la decisión correcta.', 'Instrumentos', 'Violín,Acústico,Eléctrico', 1, 'published', 'assets/images/certificate.jpg');

-- Seed Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_title', 'Donde el Violín Cobra Vida'),
('hero_subtitle', 'YULIANA VIOLINIST & ACADEMIA DE VIOLÍN'),
('hero_desc', 'Descubre la magia del violín. Aprende con Yuliana Violinista e instructores de élite en clases personalizadas presenciales y online.'),
('contact_email', 'info@yulianaviolinist.com'),
('contact_phone', '+595 976 430263'),
('contact_address', 'Asunción, Paraguay');

