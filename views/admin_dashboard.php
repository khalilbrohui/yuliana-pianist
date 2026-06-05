<?php
// Strict Access check
if (!isAdmin()) {
    header("Location: " . BASE_URL . "/registration");
    exit;
}

try {
    $db = getDBConnection();
    
    // Fetch bookings list
    $bookingsStmt = $db->query("
        SELECT b.*, c.title AS course_title 
        FROM bookings b 
        JOIN courses c ON b.course_id = c.id 
        ORDER BY b.booking_date DESC
    ");
    $bookings = $bookingsStmt->fetchAll();

    // Fetch students list
    $studentsStmt = $db->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
    $students = $studentsStmt->fetchAll();

    // Fetch courses list
    $coursesStmt = $db->query("SELECT * FROM courses ORDER BY title ASC");
    $courses = $coursesStmt->fetchAll();

    // Fetch site settings
    $settingsStmt = $db->query("SELECT * FROM site_settings");
    $settingsList = $settingsStmt->fetchAll();
    $settings = [];
    foreach ($settingsList as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $statusTranslations = [
        'pending' => 'pendiente',
        'approved' => 'aprobado',
        'rejected' => 'rechazado',
        'confirmed' => 'confirmado',
        'cancelled' => 'cancelado',
        'rescheduled' => 'reprogramado'
    ];

} catch (Exception $e) {
    die("Error al cargar el panel de administración: " . $e->getMessage());
}
?>

<div class="container" style="padding-top: 140px;">
    <div class="dashboard-layout">
        <!-- Sidebar Navigation Menu -->
        <div class="glass-card dashboard-sidebar">
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background: linear-gradient(135deg, var(--accent), #ff5400);">
                    A
                </div>
                <h3 style="font-size:1.2rem; margin-bottom:5px;">Administración</h3>
                <span class="status-badge approved">Consola</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-menu-item active">
                    <a href="#bookingsPanel" class="sidebar-link"><i class="fas fa-calendar-alt"></i> Reservas</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#studentsPanel" class="sidebar-link"><i class="fas fa-users"></i> Alumnos</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#coursesPanel" class="sidebar-link"><i class="fas fa-music"></i> Cursos CMS</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#blogPanel" class="sidebar-link"><i class="fas fa-edit"></i> Escribir Blog</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="#settingsPanel" class="sidebar-link"><i class="fas fa-cog"></i> Ajustes Globales</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= BASE_URL ?>/logout" class="sidebar-link" style="color:#ef233c;"><i class="fas fa-sign-out-alt"></i> Salir</a>
                </li>
            </ul>
        </div>

        <!-- Dashboard Content Panels -->
        <div>
            <!-- 1. Bookings Management -->
            <div id="bookingsPanel" class="dashboard-panel active">
                <div class="glass-card">
                    <h2 style="font-size:1.8rem; margin-bottom:20px;"><i class="fas fa-calendar-check" style="color:var(--teal); margin-right:10px;"></i> Gestión de Reservas</h2>
                    
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Nombre del Alumno</th>
                                    <th>Contacto</th>
                                    <th>Clase / Instrumento</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($b['name']) ?></strong></td>
                                        <td>
                                            <span style="display:block; font-size:0.85rem;"><?= htmlspecialchars($b['email']) ?></span>
                                            <span style="display:block; font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($b['phone']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($b['course_title']) ?></td>
                                        <td>
                                            <span style="display:block; font-weight:bold;"><?= date('d/m/Y', strtotime($b['booking_date'])) ?></span>
                                            <span style="display:block; font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($b['time_slot']) ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($b['status']) ?>">
                                                <?= htmlspecialchars($statusTranslations[$b['status']] ?? $b['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($b['status'] === 'pending'): ?>
                                                <button class="btn-action approve btn-approve-booking" data-id="<?= $b['id'] ?>" onclick="$.post('/api.php?action=admin_confirm_booking', {id: <?= $b['id'] ?>}, () => loadPage('/admin'))">Confirmar</button>
                                                <button class="btn-action reject btn-cancel-booking" data-id="<?= $b['id'] ?>">Cancelar</button>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted); font-size:0.8rem;">Sin acciones</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Student Approval Panel -->
            <div id="studentsPanel" class="dashboard-panel">
                <div class="glass-card">
                    <h2 style="font-size:1.8rem; margin-bottom:20px;"><i class="fas fa-user-shield" style="color:var(--teal); margin-right:10px;"></i> Aprobaciones de Alumnos</h2>
                    
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Correo</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($s['email']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($s['status']) ?>">
                                                <?= htmlspecialchars($statusTranslations[$s['status']] ?? $s['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($s['status'] === 'pending'): ?>
                                                <button class="btn-action approve btn-approve-student" data-id="<?= $s['id'] ?>">Aprobar</button>
                                                <button class="btn-action reject" onclick="$.post('/api.php?action=admin_reject_student', {id: <?= $s['id'] ?>}, () => loadPage('/admin'))">Rechazar</button>
                                            <?php else: ?>
                                                <button class="btn-action delete" onclick="$.post('/api.php?action=admin_delete_student', {id: <?= $s['id'] ?>}, () => loadPage('/admin'))">Eliminar</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. Course CMS Panel -->
            <div id="coursesPanel" class="dashboard-panel">
                <div class="glass-card">
                    <h2 style="font-size:1.8rem; margin-bottom:25px;"><i class="fas fa-edit" style="color:var(--teal); margin-right:10px;"></i> CMS de Cursos y Clases</h2>
                    
                    <form id="adminEditCourseForm">
                        <div class="form-alert"></div>
                        
                        <div class="form-group">
                            <label for="edit_course_select">Selecciona el curso a modificar</label>
                            <select id="edit_course_select" name="course_id" class="form-control" style="background:#000;" onchange="
                                const c = <?= htmlspecialchars(json_encode($courses)) ?>;
                                const selectedId = parseInt(this.value);
                                const item = c.find(val => val.id === selectedId);
                                if (item) {
                                    $('#edit_course_title').val(item.title);
                                    $('#edit_course_price').val(item.price);
                                    $('#edit_course_duration').val(item.duration);
                                    $('#edit_course_desc').val(item.description);
                                    $('#edit_course_details').val(item.details);
                                    $('#edit_course_benefits').val(item.benefits);
                                }
                            ">
                                <option value="">-- Selecciona el Curso --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_course_title">Título del Curso</label>
                            <input type="text" id="edit_course_title" name="title" class="form-control" required>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label for="edit_course_price">Precio Mensual ($)</label>
                                <input type="number" step="0.01" id="edit_course_price" name="price" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_course_duration">Frecuencia / Agenda</label>
                                <input type="text" id="edit_course_duration" name="duration" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_course_desc">Resumen Breve (Para tarjetas de inicio)</label>
                            <textarea id="edit_course_desc" name="description" class="form-control" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit_course_details">Detalle del Plan de Estudio (usa saltos de línea para separar párrafos)</label>
                            <textarea id="edit_course_details" name="details" class="form-control" required style="height:180px;"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="edit_course_benefits">Beneficios y Ventajas (Separar con punto y coma ';')</label>
                            <input type="text" id="edit_course_benefits" name="benefits" class="form-control" required placeholder="Ej. Clases de piano individuales; Pianos de alta gama; Soporte online">
                        </div>

                        <div class="form-group" style="margin-top:30px;">
                            <button type="submit" class="btn btn-teal" style="width:100%;">Guardar Cambios del Curso</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 4. Blog CMS Writer -->
            <div id="blogPanel" class="dashboard-panel">
                <div class="glass-card">
                    <h2 style="font-size:1.8rem; margin-bottom:25px;"><i class="fas fa-pen-nib" style="color:var(--teal); margin-right:10px;"></i> Publicar en el Diario</h2>
                    
                    <form id="adminAddBlogForm">
                        <div class="form-alert"></div>

                        <div class="form-group">
                            <label for="blog_title">Título del Artículo *</label>
                            <input type="text" id="blog_title" name="title" class="form-control" required placeholder="Ej. Cómo practicar piano en casa todos los días">
                        </div>

                        <div class="form-group">
                            <label for="blog_category">Categoría *</label>
                            <input type="text" id="blog_category" name="category" class="form-control" required placeholder="Ej. Consejos y Guías">
                        </div>

                        <div class="form-group">
                            <label for="blog_tags">Etiquetas (Separar con coma ',')</label>
                            <input type="text" id="blog_tags" name="tags" class="form-control" placeholder="Ej. Piano,Práctica,Diaria">
                        </div>

                        <div class="form-group">
                            <label for="blog_excerpt">Resumen Corto (Se muestra en el listado) *</label>
                            <textarea id="blog_excerpt" name="excerpt" class="form-control" required placeholder="Breve introducción al artículo..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="blog_content">Contenido HTML del Artículo *</label>
                            <textarea id="blog_content" name="content" class="form-control" required style="height:250px;" placeholder="<p>Escribe el contenido detallado de tu artículo aquí...</p>"></textarea>
                        </div>

                        <div class="form-group" style="margin-top:30px;">
                            <button type="submit" class="btn btn-primary" style="width:100%;">Publicar Artículo</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 5. General Site Settings CMS -->
            <div id="settingsPanel" class="dashboard-panel">
                <div class="glass-card">
                    <h2 style="font-size:1.8rem; margin-bottom:25px;"><i class="fas fa-sliders-h" style="color:var(--teal); margin-right:10px;"></i> Configuración del Sitio</h2>
                    
                    <form id="adminSettingsForm" onsubmit="
                        event.preventDefault();
                        const f = $(this);
                        const alertBox = f.find('.form-alert');
                        alertBox.fadeOut(100);
                        $.post('/api.php?action=admin_save_settings', f.serialize(), function(res) {
                            if (res.success) {
                                alertBox.removeClass('danger').addClass('success').text(res.message).fadeIn(250);
                            } else {
                                alertBox.removeClass('success').addClass('danger').text(res.message).fadeIn(250);
                            }
                        }, 'json');
                    ">
                        <div class="form-alert"></div>

                        <div class="form-group">
                            <label for="set_hero_title">Título Principal de Portada</label>
                            <input type="text" id="set_hero_title" name="hero_title" class="form-control" value="<?= htmlspecialchars($heroTitle ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="set_hero_subtitle">Subtítulo de Portada</label>
                            <input type="text" id="set_hero_subtitle" name="hero_subtitle" class="form-control" value="<?= htmlspecialchars($heroSubtitle ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="set_hero_desc">Descripción General de Portada</label>
                            <textarea id="set_hero_desc" name="hero_desc" class="form-control" required><?= htmlspecialchars($heroDesc ?? '') ?></textarea>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label for="set_email">Correo Electrónico de Contacto</label>
                                <input type="email" id="set_email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="set_phone">Teléfono / WhatsApp de Contacto</label>
                                <input type="text" id="set_phone" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="set_address">Dirección Física</label>
                            <input type="text" id="set_address" name="contact_address" class="form-control" value="<?= htmlspecialchars($settings['contact_address'] ?? '') ?>" required>
                        </div>

                        <div class="form-group" style="margin-top:30px;">
                            <button type="submit" class="btn btn-teal" style="width:100%;">Guardar Ajustes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
