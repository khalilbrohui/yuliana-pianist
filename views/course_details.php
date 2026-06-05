<?php
try {
    $db = getDBConnection();
    
    // Check if slug is provided
    $slug = isset($params['slug']) ? cleanInput($params['slug']) : '';
    if (empty($slug)) {
        header("Location: " . BASE_URL . "/");
        exit;
    }

    // Fetch specific course details
    $stmt = $db->prepare("SELECT * FROM courses WHERE slug = ?");
    $stmt->execute([$slug]);
    $course = $stmt->fetch();

    if (!$course) {
        echo "<div class='container' style='padding:140px 20px; text-align:center;'><h2>Curso no encontrado</h2><p>Lo sentimos, el curso solicitado no existe.</p><a href='".BASE_URL."/' class='btn btn-primary' style='margin-top:20px;'>Volver al Inicio</a></div>";
        return;
    }

    // Set page headers dynamically
    $pageTitle = $course['title'] . " - Yuliana Pianist";
    $pageDescription = $course['description'];

    // Parse benefits
    $benefitsList = explode(';', $course['benefits']);

    // Fetch instructors matching instrument keyword
    $instStmt = $db->prepare("SELECT * FROM instructors WHERE LOWER(instrument) = ? OR LOWER(instrument) LIKE ? ORDER BY name ASC LIMIT 2");
    $instStmt->execute([strtolower($course['slug']), '%' . strtolower($course['slug']) . '%']);
    $instructors = $instStmt->fetchAll();

    // Fallback: If no instructors matched, grab general instructors
    if (empty($instructors)) {
        $instStmt = $db->query("SELECT * FROM instructors ORDER BY id ASC LIMIT 2");
        $instructors = $instStmt->fetchAll();
    }

} catch (Exception $e) {
    die("Error loading course details: " . $e->getMessage());
}
?>

<!-- ==========================================================================
   BANNER DE HÉROE DEL CURSO
   ========================================================================== -->
<section class="course-hero">
    <div class="course-hero-container container">
        <div class="course-hero-grid">
            <div class="course-hero-content">
                <span class="course-hero-tag">Programa Yuliana Pianist</span>
                <h1><?= htmlspecialchars($course['title']) ?></h1>
                <p class="hero-desc" style="font-size:1.3rem;">
                    <?= htmlspecialchars($course['description']) ?>
                </p>
                <div style="display:flex; gap: 20px; align-items:center; margin-top:30px;">
                    <div style="border-right: 1px solid var(--glass-border); padding-right:20px;">
                        <small style="color:var(--text-muted); display:block; text-transform:uppercase;">Tarifa mensual</small>
                        <strong style="font-size:2rem; color:var(--teal); font-family:var(--font-heading);">$<?= number_format($course['price'], 2) ?><span style="font-size:0.9rem; color:var(--text-muted); font-weight:normal;">/mes</span></strong>
                    </div>
                    <div>
                        <small style="color:var(--text-muted); display:block; text-transform:uppercase;">Frecuencia</small>
                        <strong style="font-size:1.1rem; color:#fff;"><?= htmlspecialchars($course['duration']) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="course-hero-visual">
                <?php if (!empty($course['image_url']) && file_exists($course['image_url'])): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($course['image_url']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" style="width:100%; border-radius:24px; border:1px solid var(--glass-border); box-shadow:var(--shadow);">
                <?php else: ?>
                    <div style="background: linear-gradient(135deg, var(--accent), var(--secondary)); aspect-ratio:1.5; border-radius:24px; display:flex; align-items:center; justify-content:center; font-size:6rem; color:rgba(255,255,255,0.25); border:1px solid var(--glass-border); box-shadow:var(--shadow);">
                        <i class="fas fa-music"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
   DETALLES DEL CURSO Y BARRA LATERAL
   ========================================================================== -->
<section class="container reveal">
    <div class="details-split-grid">
        <!-- Contenido principal -->
        <div>
            <h2 style="font-size:2.2rem; margin-bottom:20px;">Plan de Estudios</h2>
            <div style="color:var(--text-muted); font-size:1.05rem; line-height:1.8;">
                <?php 
                $paragraphs = explode("\n", $course['details']);
                foreach ($paragraphs as $p) {
                    if (trim($p) !== '') {
                        echo "<p style='margin-bottom:15px;'>" . nl2br(htmlspecialchars(trim($p))) . "</p>";
                    }
                }
                ?>
            </div>

            <!-- Ventajas -->
            <div style="margin-top:50px;">
                <h3 style="font-size:1.8rem; margin-bottom:20px;">Beneficios del Programa</h3>
                <ul class="benefits-checklist">
                    <?php foreach ($benefitsList as $benefit): ?>
                        <?php if (trim($benefit) !== ''): ?>
                            <li><i class="fas fa-check-circle"></i> <span><?= htmlspecialchars(trim($benefit)) ?></span></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Instructores -->
            <div style="margin-top:60px; border-top: 1px solid var(--glass-border); padding-top:40px;">
                <h3 style="font-size:1.8rem; margin-bottom:30px;">Conoce a tus Profesores</h3>
                <div style="display:flex; flex-direction:column; gap:30px;">
                    <?php foreach ($instructors as $inst): ?>
                        <div class="instructor-card glass-card">
                            <?php if (!empty($inst['image_url']) && file_exists($inst['image_url'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($inst['image_url']) ?>" alt="<?= htmlspecialchars($inst['name']) ?>" class="instructor-img">
                            <?php else: ?>
                                <div style="width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--accent)); display:flex; align-items:center; justify-content:center; font-size:2.5rem; font-weight:bold; flex-shrink:0;">
                                    <?= substr($inst['name'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h4 style="font-size:1.3rem; color:var(--teal); margin-bottom:5px;"><?= htmlspecialchars($inst['name']) ?></h4>
                                <small style="display:block; text-transform:uppercase; color:var(--text-muted); font-weight:bold; font-size:0.75rem; margin-bottom:10px;">Especialista en <?= htmlspecialchars($inst['instrument']) ?></small>
                                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.5;"><?= htmlspecialchars($inst['bio']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Barra lateral widget de reservas -->
        <div class="glass-card" style="position: sticky; top:100px; z-index: 5;">
            <h3 style="font-size:1.5rem; margin-bottom:15px; text-align:center;">Clase de Prueba Gratis</h3>
            <p style="color:var(--text-muted); font-size:0.9rem; text-align:center; margin-bottom:25px;">Agenda una sesión introductoria de 30 minutos para <strong><?= htmlspecialchars($course['title']) ?></strong> totalmente gratis.</p>

            <form id="trialBookingForm">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                
                <div class="form-alert"></div>

                <div class="form-group">
                    <label for="booking_name">Tu Nombre Completo *</label>
                    <input type="text" id="booking_name" name="name" class="form-control" required placeholder="Ej. Juan Pérez">
                </div>

                <div class="form-group">
                    <label for="booking_email">Correo Electrónico *</label>
                    <input type="email" id="booking_email" name="email" class="form-control" required placeholder="Ej. juan@correo.com">
                </div>

                <div class="form-group">
                    <label for="booking_phone">Número de Teléfono *</label>
                    <input type="tel" id="booking_phone" name="phone" class="form-control" required placeholder="Ej. +595 976 430263">
                </div>

                <div class="form-group">
                    <a href="<?= BASE_URL ?>/booking" class="btn btn-primary" style="width:100%; border-radius:10px;">Elegir Fecha y Reservar <i class="fas fa-calendar-alt" style="margin-left:5px;"></i></a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ==========================================================================
   ACORDEÓN DE PREGUNTAS FRECUENTES (FAQ)
   ========================================================================== -->
<section style="background: rgba(0, 0, 0, 0.15); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); padding: 80px 0;">
    <div class="container reveal">
        <div class="section-title">
            <h2>Preguntas Frecuentes</h2>
            <p>Resolvemos tus dudas sobre horarios, cancelaciones y metodología de clases.</p>
        </div>

        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">¿A qué edades enseñan? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Ofrecemos programas adaptados para niños (desde 4 años), jóvenes y adultos de todos los niveles. Los instructores ajustan la metodología a cada edad.</div>
            </div>
            <div class="faq-item">
                <div class="faq-header">¿Necesito tener mi propio instrumento? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Para tu clase de prueba, te proporcionamos todos los instrumentos en el estudio. Sin embargo, para practicar en casa, te recomendamos contar con uno propio. ¡Te asesoramos en la compra!</div>
            </div>
            <div class="faq-item">
                <div class="faq-header">¿Cómo funcionan los pagos y cancelaciones? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Las clases se facturan mensualmente. Si necesitas cancelar o reprogramar, avísanos al menos 24 horas antes y te repondremos la clase.</div>
            </div>
            <div class="faq-item">
                <div class="faq-header">¿Son efectivas las clases online? <i class="fas fa-plus"></i></div>
                <div class="faq-body">¡Por supuesto! Nuestro estudio virtual utiliza configuraciones multicámara para ver las manos del profesor con absoluta nitidez y pizarras virtuales.</div>
            </div>
        </div>
    </div>
</section>
