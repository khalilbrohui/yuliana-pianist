<?php
if (!isStudent()) {
    header("Location: " . BASE_URL . "/registration");
    exit;
}

try {
    $db = getDBConnection();
    
    // Fetch logged-in user profile details
    $email = $_SESSION['user_email'];
    $userStmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $userStmt->execute([$email]);
    $user = $userStmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: " . BASE_URL . "/registration");
        exit;
    }

    // Fetch user bookings
    $bookingStmt = $db->prepare("
        SELECT b.*, c.title AS course_title 
        FROM bookings b 
        JOIN courses c ON b.course_id = c.id 
        WHERE b.email = ? 
        ORDER BY b.booking_date DESC
    ");
    $bookingStmt->execute([$email]);
    $myBookings = $bookingStmt->fetchAll();

    $statusTranslations = [
        'pending' => 'pendiente',
        'approved' => 'aprobado',
        'rejected' => 'rechazado',
        'confirmed' => 'confirmado',
        'cancelled' => 'cancelado',
        'rescheduled' => 'reprogramado'
    ];

} catch (Exception $e) {
    die("Error al cargar el panel de estudiante: " . $e->getMessage());
}
?>

<div class="container" style="padding-top: 140px;">
    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <div class="glass-card dashboard-sidebar">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h3 style="font-size:1.2rem; margin-bottom:5px;"><?= htmlspecialchars($user['name']) ?></h3>
                <span class="status-badge <?= htmlspecialchars($user['status']) ?>">
                    <?= htmlspecialchars($statusTranslations[$user['status']] ?? $user['status']) ?>
                </span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-menu-item active">
                    <a href="#overviewPanel" class="sidebar-link"><i class="fas fa-columns"></i> Panel de Control</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= BASE_URL ?>/booking" class="sidebar-link"><i class="fas fa-calendar-plus"></i> Reservar Clase</a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="<?= BASE_URL ?>/logout" class="sidebar-link" style="color:#ef233c;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </li>
            </ul>
        </div>

        <!-- Main Dashboard View Panel -->
        <div>
            <!-- Overview Panel -->
            <div id="overviewPanel" class="dashboard-panel active">
                <div class="glass-card" style="margin-bottom:30px;">
                    <h2 style="font-size:1.8rem; margin-bottom:15px; color:var(--teal);">¡Bienvenido/a de nuevo, <?= htmlspecialchars($user['name']) ?>!</h2>
                    
                    <?php if ($user['status'] === 'pending'): ?>
                        <div style="background:rgba(255, 179, 0, 0.1); border:1px solid #ffb300; border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="color:#ffb300; margin-bottom:5px;"><i class="fas fa-exclamation-triangle"></i> Estado de cuenta: Aprobación Pendiente</h4>
                            <p style="font-size:0.95rem; color:var(--text-muted); line-height:1.5;">
                                Tu registro de cuenta está siendo revisado por nuestro equipo administrativo. Puedes verificar tus reservas programadas abajo, pero no podrás asistir a clases formales hasta que se apruebe el estado.
                            </p>
                        </div>
                    <?php elseif ($user['status'] === 'approved'): ?>
                        <div style="background:rgba(0, 245, 212, 0.08); border:1px solid var(--teal); border-radius:12px; padding:20px; margin-bottom:20px;">
                            <h4 style="color:var(--teal); margin-bottom:5px;"><i class="fas fa-check-circle"></i> Cuenta Estudiante Aprobada</h4>
                            <p style="font-size:0.95rem; color:var(--text-muted); line-height:1.5;">
                                ¡Felicidades! Tus credenciales de alumno han sido verificadas. Ya posees acceso total a los materiales y las clases programadas en el estudio.
                            </p>
                        </div>
                    <?php endif; ?>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-top:30px;">
                        <div style="background:rgba(255,255,255,0.02); border:1px solid var(--glass-border); padding:20px; border-radius:12px; text-align:center;">
                            <span style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:bold; margin-bottom:5px;">Clases Reservadas</span>
                            <strong style="font-size:2rem; font-family:var(--font-heading); color:#fff;"><?= count($myBookings) ?></strong>
                        </div>
                        <div style="background:rgba(255,255,255,0.02); border:1px solid var(--glass-border); padding:20px; border-radius:12px; text-align:center;">
                            <span style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:bold; margin-bottom:5px;">Miembro Desde</span>
                            <strong style="font-size:1.2rem; font-family:var(--font-heading); color:#fff;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Bookings listing -->
                <div class="glass-card">
                    <h3 style="font-size:1.5rem; margin-bottom:20px;">Mis Clases de Prueba Agendadas</h3>
                    
                    <?php if (empty($myBookings)): ?>
                        <div style="text-align:center; padding:40px 0; color:var(--text-muted);">
                            <i class="far fa-calendar-times" style="font-size:3rem; margin-bottom:15px; color:var(--accent);"></i>
                            <p>No tienes clases de prueba agendadas todavía.</p>
                            <a href="<?= BASE_URL ?>/booking" class="btn btn-teal" style="margin-top:20px; padding:8px 18px; font-size:0.85rem;">Agendar Ahora</a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Clase / Instrumento</th>
                                        <th>Fecha Programada</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myBookings as $b): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($b['course_title']) ?></strong></td>
                                            <td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td>
                                            <td><?= htmlspecialchars($b['time_slot']) ?></td>
                                            <td>
                                                <span class="status-badge <?= htmlspecialchars($b['status']) ?>">
                                                    <?= htmlspecialchars($statusTranslations[$b['status']] ?? $b['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
