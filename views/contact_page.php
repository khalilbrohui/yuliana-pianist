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

    $contactEmail = $settings['contact_email'] ?? 'info@yulianapianist.com';
    $contactPhone = $settings['contact_phone'] ?? '+595 976 430263';
    $contactAddress = $settings['contact_address'] ?? 'Asunción, Paraguay';

} catch (Exception $e) {
    die("Error al cargar la configuración de contacto: " . $e->getMessage());
}
?>

<section class="container" style="padding-top: 140px;">
    <div class="section-title">
        <h2>Ponte en Contacto</h2>
        <p>¿Tienes preguntas sobre nuestras clases de piano, metodologías o inscripciones? Envíanos un mensaje y te responderemos en la brevedad.</p>
    </div>

    <div class="contact-grid-split">
        <!-- Contact Form -->
        <div class="glass-card">
            <h3 style="font-size:1.5rem; margin-bottom:25px;"><i class="fas fa-paper-plane" style="color:var(--teal); margin-right:10px;"></i> Enviar Mensaje</h3>
            
            <form id="academyContactForm">
                <div class="form-alert"></div>

                <div class="form-group">
                    <label for="contact_name">Tu Nombre Completo *</label>
                    <input type="text" id="contact_name" name="name" class="form-control" required placeholder="Ej. Juan Pérez">
                </div>

                <div class="form-group">
                    <label for="contact_email">Correo Electrónico *</label>
                    <input type="email" id="contact_email" name="email" class="form-control" required placeholder="Ej. juan@correo.com">
                </div>

                <div class="form-group">
                    <label for="contact_subject">Asunto *</label>
                    <input type="text" id="contact_subject" name="subject" class="form-control" required placeholder="¿En qué te podemos ayudar?">
                </div>

                <div class="form-group">
                    <label for="contact_message">Mensaje / Consulta *</label>
                    <textarea id="contact_message" name="message" class="form-control" required placeholder="Escribe los detalles aquí..."></textarea>
                </div>

                <div class="form-group" style="margin-top:25px;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Enviar Mensaje</button>
                </div>
            </form>
        </div>

        <!-- Location details & Map -->
        <div>
            <div class="glass-card" style="margin-bottom:30px;">
                <h3 style="font-size:1.5rem; margin-bottom:20px;">Datos de Contacto</h3>
                
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($contactAddress) ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?= htmlspecialchars($contactPhone) ?>"><?= htmlspecialchars($contactPhone) ?></a>
                    </div>
                </div>

                <div style="margin-top:25px; display:flex; gap:15px;">
                    <a href="https://wa.me/595976430263" class="btn btn-teal" target="_blank" style="padding: 10px 20px; font-size:0.9rem;">
                        <i class="fab fa-whatsapp"></i> Escribir al WhatsApp
                    </a>
                </div>
            </div>

            <!-- Google Map Iframe (Asuncion, Paraguay) -->
            <div class="map-embed-wrapper">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d57717.38139586145!2d-57.6321287!3d-25.2867!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x940c3c6ef1ba27d3%3A0xa1969ef07d727b3d!2sAsunci%C3%B3n%2C%20Paraguay!5e0!3m2!1ses-419!2spy!4v1655000000000!5m2!1ses-419!2spy" 
                    width="100%" 
                    height="100%" 
                    style="border:0; filter: invert(90%) hue-rotate(180deg);" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>
