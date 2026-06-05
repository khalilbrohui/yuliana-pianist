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
(1, 'Administrador Yuliana', 'admin@yulianapianist.com', '$2y$10$wUHTpT.e/3yLh4iYQ6F/A.hC9lW3G1y817tqD0K.P2x10tQ19rV2y', 'admin', 'approved');

-- Seed Instructors
INSERT INTO `instructors` (`name`, `instrument`, `bio`, `image_url`) VALUES
('Prof. Yuliana', 'Piano', 'Yuliana es una pianista profesional y concertista internacional con más de 12 años de trayectoria pedagógica y artística.', 'assets/images/violinist.jpg'),
('Prof. Carlos Gómez', 'Voz y Canto', 'Carlos ayuda a los alumnos a liberar su voz, enseñando técnicas de respiración diafragmática, entonación y proyección escénica.', 'assets/images/singer.jpg'),
('Prof. Sofía Ruiz', 'Teoría Musical', 'Sofía se especializa en armonía clásica y lectura rítmica avanzada para estudiantes que buscan certificaciones profesionales.', 'assets/images/certificate.jpg');

-- Seed Courses
INSERT INTO `courses` (`slug`, `title`, `description`, `details`, `benefits`, `price`, `duration`, `image_url`) VALUES
('piano', 'Clases de Piano Premium', 'Despierta al virtuoso que llevas dentro. Aprende desde acordes elementales hasta obras clásicas o contemporáneas avanzadas con instructores de primer nivel.', 'Nuestro programa de piano está diseñado para todos los niveles de destreza. Aprenderás:\n- Lectura de partituras e interpretación a primera vista\n- Teoría musical aplicada y armonía\n- Ejercicios de independencia y velocidad digital\n- Preparación de repertorio y recitales en vivo.', 'Clases de piano individuales; Pianos de alta gama; Soporte online', 120.00, '4 Clases al Mes (45 min cada una)', 'assets/images/violinist.jpg'),
('guitar', 'Programa de Guitarra Élite', 'Domina la guitarra acústica, eléctrica o el bajo. Aprende acordes, arpegios y solos espectaculares con guías paso a paso.', 'Aprende el estilo que más te guste: flamenco, jazz, rock o pop. Nos enfocamos en:\n- Posición de manos y técnicas de rasgueo\n- Escalas, acordes avanzados e improvisación\n- Mantenimiento y afinación del instrumento\n- Acompañamiento rítmico y ensambles.', 'Aprende acordes de forma interactiva;Talleres semanales de improvisación en vivo;Material digital de apoyo exclusivo;Afinación y mantenimiento de equipo', 110.00, '4 Clases al Mes (45 min cada una)', 'assets/images/certificate.jpg'),
('drums', 'Batería y Percusión Rock', 'Encuentra tu ritmo ideal. Domina los tiempos, remates complejos y la sincronización total de tu set de batería con nuestras dinámicas clases.', 'La batería es el corazón de la música. Nuestros profesores te enseñarán:\n- Coordinación e independencia de manos y pies\n- Ritmos de rock, jazz, funk y ritmos latinos\n- Lectura de partituras rítmicas\n- Improvisación y control de tempo con metrónomo.', 'Sets de baterías profesionales en estudio;Prácticas de velocidad y control de ritmo;Estudio de remates creativos;Entrenamiento auditivo para bandas', 130.00, '4 Clases al Mes (45 min cada una)', 'assets/images/violinist.jpg'),
('vocal', 'Entrenamiento Vocal y Canto', 'Educa tu voz para cantar con absoluta confianza. Nuestros entrenadores te enseñarán soporte de aire, afinación y dominio escénico.', 'Tu voz es tu instrumento principal. Aprende a protegerla y expandir tu rango:\n- Rutinas saludables de calentamiento vocal\n- Respiración diafragmática y afinación de notas\n- Proyección escénica y manejo del micrófono\n- Interpretación de diferentes géneros musicales.', 'Rutinas de calentamiento y salud vocal;Técnicas avanzadas de respiración;Corrección de tono y oído musical;Entrenamiento escénico para solistas', 115.00, '4 Clases al Mes (45 min cada una)', 'assets/images/singer.jpg'),
('online', 'Clases Online Interactivas', 'Aprende música desde la comodidad de tu hogar. Transmisión en alta definición, pizarra interactiva y retroalimentación inmediata.', '¿Prefieres estudiar en casa? Nuestra plataforma online te conecta directamente con tu tutor. Incluye:\n- Transmisiones multicámara en alta definición\n- Acceso a archivos y partituras en tiempo real\n- Grabación de clases para repaso ilimitado\n- Evaluaciones personalizadas mensuales.', 'Estudia sin necesidad de viajar;Acceso a clases grabadas para repaso;Herramientas interactivas en pantalla;Match con profesores globales', 95.00, '4 Clases al Mes (45 min cada una)', 'assets/images/singer.jpg');

-- Seed Blog Posts
INSERT INTO `blog_posts` (`slug`, `title`, `content`, `excerpt`, `category`, `tags`, `author_id`, `status`, `image_url`) VALUES
('como-iniciarse-en-las-clases-de-piano', 'Cómo Iniciarse en las Clases de Piano: Guía para Principiantes', '<p>Dar tus primeros pasos en el piano es una experiencia maravillosa. Sin embargo, muchos principiantes se sienten abrumados por la postura o la lectura de partituras.</p><h3>1. Enfócate en la Ergonomía</h3><p>Mantén tus dedos ligeramente curvados, como si sostuvieras una pelota pequeña. Esto te dará velocidad y prevendrá fatigas.</p><h3>2. Practica 15 Minutos Diarios</h3><p>La constancia supera a las largas sesiones acumuladas. Practicar un poco todos los días fija la memoria muscular de manera excepcional.</p>', 'Descubre los consejos fundamentales, posturas de manos y rutinas diarias sencillas para acelerar tu aprendizaje inicial de piano.', 'Consejos y Guías', 'Piano,Principiantes,Guía', 1, 'published', 'assets/images/violinist.jpg'),
('guitarra-acustica-vs-electrica-cual-es-mejor', 'Guitarra Acústica vs. Eléctrica: ¿Cuál es Mejor para Empezar?', '<p>Elegir tu primera guitarra depende de tus objetivos musicales y comodidad. Analicemos ambas opciones:</p><h3>Guitarra Acústica</h3><p>Pros: Es portátil, no requiere cables y fortalece rápidamente la punta de los dedos.</p><p>Cons: Las cuerdas de metal pueden ser duras al inicio.</p><h3>Guitarra Eléctrica</h3><p>Pros: Cuerdas más suaves de presionar y control de volumen ajustable con amplificador.</p><p>Cons: Requiere accesorios adicionales como cables y amplificadores.</p>', 'Comparamos las ventajas y desventajas de las guitarras acústicas y eléctricas para ayudarte a tomar la decisión correcta.', 'Instrumentos', 'Guitarra,Acústica,Eléctrica', 1, 'published', 'assets/images/certificate.jpg');

-- Seed Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_title', 'Donde la Música Cobra Vida'),
('hero_subtitle', 'YULIANA PIANIST & ACADEMIA DE MÚSICA'),
('hero_desc', 'Descubre la magia del piano y otros instrumentos. Aprende con Yuliana Pianista y instructores de élite en clases personalizadas presenciales y online.'),
('contact_email', 'info@yulianapianist.com'),
('contact_phone', '+595 976 430263'),
('contact_address', 'Asunción, Paraguay');
