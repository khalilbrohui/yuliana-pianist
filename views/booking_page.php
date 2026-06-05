<?php
try {
    $db = getDBConnection();
    
    // Fetch courses for dropdown select list
    $coursesStmt = $db->query("SELECT id, title FROM courses ORDER BY title ASC");
    $courses = $coursesStmt->fetchAll();

} catch (Exception $e) {
    die("Error al cargar la configuración de reservas: " . $e->getMessage());
}

// Generate calendar variables
$timezone = new DateTimeZone('America/Asuncion');
$now = new DateTime('now', $timezone);
$currentYear = (int)$now->format('Y');
$currentMonth = (int)$now->format('m');
$daysInMonth = (int)$now->format('t');
$firstDayOfWeek = (int)(new DateTime("$currentYear-$currentMonth-01"))->format('w');
$today = (int)$now->format('d');

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$nombreMes = $meses[$currentMonth];
?>

<section class="container" style="padding-top: 140px;">
    <div class="section-title">
        <h2>Agenda tu Clase de Prueba Gratis</h2>
        <p>Elige tu programa de violín, selecciona una fecha y reserva la hora que te convenga. Sin compromisos ni tarjetas de crédito.</p>
    </div>

    <div class="booking-container">
        <!-- Calendar Selection -->
        <div class="glass-card">
            <h3 style="font-size:1.5rem; margin-bottom:25px;"><i class="fas fa-calendar-alt" style="color:var(--teal); margin-right:10px;"></i> 1. Selecciona una Fecha</h3>
            
            <div class="calendar-widget">
                <div class="calendar-header">
                    <h4 style="color:var(--teal); font-size:1.2rem;"><?= $nombreMes . ' ' . $currentYear ?></h4>
                </div>
                
                <div class="calendar-grid">
                    <!-- Week headers -->
                    <div class="calendar-day-label">Dom</div>
                    <div class="calendar-day-label">Lun</div>
                    <div class="calendar-day-label">Mar</div>
                    <div class="calendar-day-label">Mié</div>
                    <div class="calendar-day-label">Jue</div>
                    <div class="calendar-day-label">Vie</div>
                    <div class="calendar-day-label">Sáb</div>
                    
                    <!-- Blank days before first of month -->
                    <?php for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                        <div></div>
                    <?php endfor; ?>
                    
                    <!-- Calendar days -->
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                        <?php
                        $dayDate = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $day);
                        $isDisabled = ($day < $today) ? 'disabled' : '';
                        ?>
                        <div class="calendar-day <?= $isDisabled ?>" 
                             data-date="<?= $dayDate ?>"
                             <?= ($day < $today) ? '' : 'style="background: rgba(255,255,255,0.03);"' ?>>
                            <?= $day ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Time slot selectors -->
            <div class="slots-container" style="display:none;">
                <h3 style="font-size:1.5rem; margin-bottom:20px; border-top:1px solid var(--glass-border); padding-top:30px;"><i class="fas fa-clock" style="color:var(--teal); margin-right:10px;"></i> 2. Selecciona una Hora</h3>
                <div class="slots-grid">
                    <div class="slot-pill" data-slot="10:00 AM">10:00 AM</div>
                    <div class="slot-pill" data-slot="11:00 AM">11:00 AM</div>
                    <div class="slot-pill" data-slot="12:00 PM">12:00 PM</div>
                    <div class="slot-pill" data-slot="01:00 PM">01:00 PM</div>
                    <div class="slot-pill" data-slot="02:00 PM">02:00 PM</div>
                    <div class="slot-pill" data-slot="03:00 PM">03:00 PM</div>
                    <div class="slot-pill" data-slot="04:00 PM">04:00 PM</div>
                    <div class="slot-pill" data-slot="05:00 PM">05:00 PM</div>
                    <div class="slot-pill" data-slot="06:00 PM">06:00 PM</div>
                </div>
            </div>
        </div>

        <!-- Lead Form Information -->
        <div class="glass-card">
            <h3 style="font-size:1.5rem; margin-bottom:25px;"><i class="fas fa-user-edit" style="color:var(--teal); margin-right:10px;"></i> 3. Información de Contacto</h3>
            
            <form id="trialBookingForm">
                <!-- Hidden fields for calendar selection -->
                <input type="hidden" id="selected_date_input" name="booking_date" required>
                <input type="hidden" id="selected_slot_input" name="time_slot" required>

                <div class="form-alert"></div>

                <div class="form-group">
                    <label for="booking_course">Selecciona Instrumento / Clase *</label>
                    <select id="booking_course" name="course_id" class="form-control" required style="background:#000;">
                        <option value="">-- Elige el Programa de Violín --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lead_name">Tu Nombre Completo *</label>
                    <input type="text" id="lead_name" name="name" class="form-control" required placeholder="Ej. Juan Pérez">
                </div>

                <div class="form-group">
                    <label for="lead_email">Correo Electrónico *</label>
                    <input type="email" id="lead_email" name="email" class="form-control" required placeholder="Ej. juan@correo.com">
                </div>

                <div class="form-group">
                    <label for="lead_phone">Número de Teléfono / WhatsApp *</label>
                    <input type="tel" id="lead_phone" name="phone" class="form-control" required placeholder="Ej. +595 976 430263">
                </div>

                <div class="form-group" style="margin-top:30px;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Confirmar Clase de Prueba</button>
                </div>
            </form>
        </div>
    </div>
</section>
