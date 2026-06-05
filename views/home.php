<?php
try {
    $db = getDBConnection();
    
    // Fetch site settings
    $settingsStmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
    $settingsList = $settingsStmt->fetchAll();
    $settings = [];
    foreach ($settingsList as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // Default settings fallback
    $heroTitle = $settings['hero_title'] ?? 'Donde la Música Cobra Vida';
    $heroSubtitle = $settings['hero_subtitle'] ?? 'YULIANA PIANIST & ACADEMIA DE MÚSICA';
    $heroDesc = $settings['hero_desc'] ?? 'Descubre la magia del piano y otros instrumentos. Aprende con Yuliana Pianista y instructores de élite en clases personalizadas presenciales y online.';

    // Fetch courses for the homepage grid
    $coursesStmt = $db->query("SELECT * FROM courses ORDER BY price ASC LIMIT 5");
    $courses = $coursesStmt->fetchAll();

} catch (Exception $e) {
    die("Error loading home data: " . $e->getMessage());
}
?>

<!-- ==========================================================================
   SECCIÓN HÉROE (Con Lienzo de Piano 3D y Video de Fondo)
   ========================================================================== -->
<section class="hero-section">
    <!-- Video de Fondo en Bucle -->
    <div class="video-bg-container">
        <video autoplay muted loop playsinline>
            <source src="https://assets.mixkit.co/videos/preview/mixkit-hand-of-a-pianist-playing-piano-keys-close-up-4820-large.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

    <!-- Contenedor del Lienzo del Piano 3D Interactivo -->
    <div class="hero-canvas-container">
        <canvas id="piano3dCanvas"></canvas>
    </div>

    <!-- Contenido del Héroe Izquierdo -->
    <div class="hero-grid">
        <div class="hero-content">
            <span class="hero-subtitle">
                <i class="fas fa-music"></i> <?= htmlspecialchars($heroSubtitle) ?>
            </span>
            <h1 class="hero-title">
                <?= $heroTitle ?>
            </h1>
            <p class="hero-desc">
                <?= htmlspecialchars($heroDesc) ?>
            </p>
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>/booking" class="btn btn-primary">Clase de Prueba</a>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-secondary">Contáctanos</a>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
   ¿POR QUÉ NUESTRA ACADEMIA? (CARACTERÍSTICAS)
   ========================================================================== -->
<section class="container reveal">
    <div class="section-title">
        <h2>Experiencia Musical Incomparable</h2>
        <p>Combinamos instrucción técnica de élite con prácticas dinámicas grupales para que el aprendizaje sea inspirador.</p>
    </div>
    
    <div class="features-grid">
        <div class="feature-item glass-card">
            <div class="feature-icon"><i class="fas fa-award"></i></div>
            <h3>Profesionales Certificados</h3>
            <p>Aprende de la mano de Yuliana Pianista y concertistas con amplia trayectoria internacional.</p>
        </div>
        <div class="feature-item glass-card">
            <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>Horarios Flexibles</h3>
            <p>Modifica el horario de tus clases hasta con 24 horas de anticipación desde tu panel personal.</p>
        </div>
        <div class="feature-item glass-card">
            <div class="feature-icon"><i class="fas fa-guitar"></i></div>
            <h3>Presentaciones y Recitales</h3>
            <p>Nuestros alumnos participan en prácticas conjuntas y tocan en escenarios y recitales reales en Asunción.</p>
        </div>
        <div class="feature-item glass-card">
            <div class="feature-icon"><i class="fas fa-cubes"></i></div>
            <h3>Tecnología Interactiva</h3>
            <p>Usa simuladores en 3D, sintetizadores de audio y herramientas visuales para aprender teoría musical.</p>
        </div>
    </div>
</section>

<!-- ==========================================================================
   SECCIÓN DE CURSOS POPULARES
   ========================================================================== -->
<section style="background: rgba(0, 0, 0, 0.2); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border);">
    <div class="container reveal">
        <div class="section-title">
            <h2>Nuestros Programas de Música</h2>
            <p>Elige tu instrumento ideal y opta por instrucción privada en nuestro estudio o clases virtuales.</p>
        </div>

        <div class="courses-grid">
            <?php foreach ($courses as $c): ?>
                <div class="course-card glass-card">
                    <!-- Render dynamic images (uses user-uploaded images violin, singer, cert!) -->
                    <?php if (!empty($c['image_url']) && file_exists($c['image_url'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($c['image_url']) ?>" alt="<?= htmlspecialchars($c['title']) ?>" class="course-card-img" style="object-position: center 20%;">
                    <?php else: ?>
                        <div style="background: linear-gradient(135deg, var(--primary), var(--secondary)); height:200px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; justify-content:center; font-size:4rem; color:rgba(255,255,255,0.2);">
                            <i class="fas fa-music"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3><?= htmlspecialchars($c['title']) ?></h3>
                    <p><?= htmlspecialchars($c['description']) ?></p>
                    <div class="course-card-footer">
                        <span class="course-card-price">$<?= number_format($c['price'], 2) ?> <small style="font-size:0.7rem; color:var(--text-muted);">/mes</small></span>
                        <a href="<?= BASE_URL ?>/courses/<?= $c['slug'] ?>" class="btn btn-secondary" style="padding: 8px 18px; font-size:0.85rem;">Explorar <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================================================
   OPINIONES DE ALUMNOS (TESTIMONIALS)
   ========================================================================== -->
<section class="container reveal">
    <div class="section-title">
        <h2>Opiniones de Nuestros Alumnos</h2>
        <p>Únete a más de 500 estudiantes activos que dominan su arte en nuestra academia.</p>
    </div>

    <div class="courses-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <div class="glass-card" style="padding: 40px; position: relative;">
            <span style="font-size: 5rem; color: rgba(138,43,226,0.1); position: absolute; top: 10px; left: 20px; font-family: Georgia, serif;">“</span>
            <p style="font-style: italic; position: relative; z-index: 2; margin-bottom: 20px;">
                "El simulador de piano 3D ayudó a mi hija de 8 años a visualizar acordes y escalas fácilmente. ¡Los recitales en vivo han aumentado enormemente su confianza!"
            </p>
            <div style="display:flex; align-items:center; gap: 15px;">
                <div style="width: 50px; height: 50px; border-radius:50%; background: #9d4edd; display:flex; align-items:center; justify-content:center; font-weight:bold;">SM</div>
                <div>
                    <h4 style="font-size:1rem; margin-bottom: 2px;">Sarah Miller</h4>
                    <small style="color:var(--text-muted);">Madre de alumna de piano</small>
                </div>
            </div>
        </div>

        <div class="glass-card" style="padding: 40px; position: relative;">
            <span style="font-size: 5rem; color: rgba(138,43,226,0.1); position: absolute; top: 10px; left: 20px; font-family: Georgia, serif;">“</span>
            <p style="font-style: italic; position: relative; z-index: 2; margin-bottom: 20px;">
                "Tener control absoluto de mis horarios es fantástico. Mi tutora Yuliana es increíble, estructurando las prácticas rítmicas según mi propio ritmo."
            </p>
            <div style="display:flex; align-items:center; gap: 15px;">
                <div style="width: 50px; height: 50px; border-radius:50%; background: #00f5d4; color:#000; display:flex; align-items:center; justify-content:center; font-weight:bold;">JL</div>
                <div>
                    <h4 style="font-size:1rem; margin-bottom: 2px;">Juan Luis</h4>
                    <small style="color:var(--text-muted);">Alumno de piano adulto</small>
                </div>
            </div>
        </div>
    </div>
</section>
