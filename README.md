# Yuliana Pianist & Academia de Música

Sitio web moderno e interactivo para la enseñanza de piano y clases de música, inspirado en la estética premium y dinamismo de Bach to Rock. La aplicación cuenta con un piano 3D interactivo que genera notas sonoras con sintetizadores Web Audio, sistema de agendamiento de clases de prueba por AJAX, registro de alumnos y un portal de administración CMS completo.

## 🛠️ Stack Tecnológico
- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, ES6 JavaScript
- **Librerías**: Three.js (Lienzo 3D), GSAP & ScrollTrigger (Animaciones), jQuery (Peticiones AJAX)
- **Efectos de Audio**: Web Audio API Synthesizer (Generación de frecuencias en tiempo real)

---

## 📂 Estructura del Proyecto
```
├───assets
│   ├───css
│   │       style.css         # Hoja de estilos premium con Glassmorphism
│   ├───images                # Fotos subidas (violinist.jpg, singer.jpg, certificate.jpg)
│   └───js
│           script.js         # Transición dinámica AJAX de páginas (SPA) y lógica de formularios
│           piano3d.js        # Configuración del piano 3D interactivo en Three.js
│
├───views
│       admin_dashboard.php   # Panel de administración CMS, reservas y alumnos
│       blog_list.php         # Listado de artículos filtrable
│       blog_post.php         # Vista de lectura de artículo individual
│       booking_page.php      # Formulario de agendamiento con calendario de ranuras horarias
│       contact_page.php      # Contacto con mapa integrado y chat WhatsApp
│       course_details.php    # Detalles dinámicos del curso seleccionado y profesores
│       footer.php            # Carga de scripts, Schema.org y pie de página
│       header.php            # Barra de navegación adaptable
│       home.php              # Portada con fondo de video y contenedor WebGL
│       registration_page.php # Formularios de inicio de sesión y registro
│       student_dashboard.php # Panel de control del alumno con el estado de aprobación
│
│   index.php                 # Enrutador principal y Front Controller
│   config.php                # Configuraciones de sesión y base de datos
│   setup.php                 # Instalador automatizado de tablas y datos semilla
│   api.php                   # Endpoints backend de peticiones AJAX
```

---

## ⚙️ Instalación y Configuración Local

1. **Requisitos Previos**:
   - Tener instalado un servidor local como **XAMPP** o **WampServer** con soporte para PHP 8 y MySQL.

2. **Configuración de Base de Datos**:
   - Inicia los servicios de Apache y MySQL en tu panel de control local.
   - Si usas credenciales personalizadas de base de datos, modifícalas en `config.php`. Por defecto está configurado para usuario `root` sin contraseña en `127.0.0.1`.

3. **Iniciar el Servidor de Desarrollo**:
   - Abre la terminal en la carpeta del proyecto y ejecuta:
     ```powershell
     C:\xampp\php\php.exe -S localhost:8000 index.php
     ```

4. **Inicializar Tablas**:
   - Entra en tu navegador a la ruta:
     `http://localhost:8000/setup.php`
   - El script creará automáticamente la base de datos `music_academy` con todas las tablas requeridas y cargará la información semilla en español (Cursos, profesores iniciales, artículos del blog y configuraciones).

---

## 🔑 Credenciales de Prueba

- **Acceso de Administrador**:
  - **Correo**: `admin@yulianapianist.com`
  - **Contraseña**: `admin123`
