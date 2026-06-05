</main>

    <!-- ==========================================================================
       SECCIÓN DEL PIE DE PÁGINA (FOOTER)
       ========================================================================== -->
    <footer id="mainFooter">
        <div class="footer-top">
            <div class="footer-container">
                <div class="footer-info">
                    <a href="<?= BASE_URL ?>/" class="logo-link">
                        <span class="logo-text">YULIANA</span>
                        <span class="logo-subtext">PIANIST & ACADEMIA</span>
                        <div class="logo-dot"></div>
                    </a>
                    <p class="footer-about">
                        Academia de música de primer nivel que ofrece enseñanza personalizada en piano, guitarra, batería y técnicas vocales. Descubre tu talento con nuestros instructores de élite.
                    </p>
                    <div class="footer-socials">
                        <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <div class="footer-nav-col">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/">Inicio</a></li>
                        <li><a href="<?= BASE_URL ?>/booking">Reservar Clase</a></li>
                        <li><a href="<?= BASE_URL ?>/blog">Nuestro Blog</a></li>
                        <li><a href="<?= BASE_URL ?>/contact">Contáctanos</a></li>
                        <li><a href="<?= BASE_URL ?>/registration">Portal Alumno</a></li>
                    </ul>
                </div>

                <div class="footer-nav-col">
                    <h4>Nuestros Cursos</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/courses/piano">Clases de Piano</a></li>
                        <li><a href="<?= BASE_URL ?>/courses/guitar">Clases de Guitarra</a></li>
                        <li><a href="<?= BASE_URL ?>/courses/drums">Batería y Percusión</a></li>
                        <li><a href="<?= BASE_URL ?>/courses/vocal">Entrenamiento Vocal</a></li>
                        <li><a href="<?= BASE_URL ?>/courses/online">Clases Online</a></li>
                    </ul>
                </div>

                <div class="footer-nav-col">
                    <h4>Contacto</h4>
                    <p class="contact-item"><i class="fas fa-envelope"></i> info@yulianapianist.com</p>
                    <p class="contact-item"><i class="fas fa-phone"></i> +595 976 430263</p>
                    <p class="contact-item"><i class="fas fa-map-marker-alt"></i> Av. Mariscal López, Asunción, Paraguay</p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-container">
                <p>&copy; <?= date('Y') ?> Yuliana Pianist & Academia de Música. Todos los derechos reservados.</p>
                <div class="footer-meta-links">
                    <a href="#">Política de Privacidad</a>
                    <a href="#">Términos de Servicio</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón Volver Arriba -->
    <div class="scroll-top" id="btnScrollTop" aria-label="Volver arriba">
        <i class="fas fa-chevron-up"></i>
    </div>

    <!-- Widget Flotante de WhatsApp -->
    <a href="https://wa.me/595976430263" class="whatsapp-float" target="_blank" aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="tooltip-text">¡Escríbenos hoy!</span>
    </a>

    <!-- Marcado de Datos Estructurados Schema para SEO Local -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "MusicSchool",
      "name": "Yuliana Pianist & Academia de Música",
      "image": "<?= BASE_URL ?>/assets/images/singer.jpg",
      "@id": "<?= BASE_URL ?>/#school",
      "url": "<?= BASE_URL ?>",
      "telephone": "+595-976-430263",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Av. Mariscal López",
        "addressLocality": "Asunción",
        "addressRegion": "Distrito Capital",
        "postalCode": "1209",
        "addressCountry": "PY"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": -25.2867,
        "longitude": -57.6470
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday"
        ],
        "opens": "09:00",
        "closes": "21:00"
      },
      "sameAs": [
        "https://www.facebook.com/yulianapianist",
        "https://www.instagram.com/yulianapianist"
      ]
    }
    </script>

    <!-- Módulos de JavaScript Principales -->
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    
    <!-- Carga condicional del script de piano 3D en la página de inicio -->
    <?php if (isset($viewFile) && $viewFile === 'views/home.php'): ?>
        <script src="<?= BASE_URL ?>/assets/js/piano3d.js"></script>
    <?php endif; ?>
</body>
</html>
