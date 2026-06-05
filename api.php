<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

$response = [
    'success' => false,
    'message' => 'Solicitud de acción no válida.'
];

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if (empty($action)) {
    echo json_encode($response);
    exit;
}

try {
    $db = getDBConnection();

    function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }

    // ==========================================
    // ACCIÓN: REGISTRO DE ALUMNO
    // ==========================================
    if ($action === 'register') {
        $name = cleanInput($_POST['name'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("Todos los campos del formulario son obligatorios.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Por favor, ingresa un correo electrónico válido.");
        }

        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            throw new Exception("Ya existe una cuenta registrada con este correo electrónico.");
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'pending')");
        $stmt->execute([$name, $email, $hashedPassword]);

        // Simular envío de correo
        $logMsg = date('[Y-m-d H:i:s]') . " Registro Alumno: '$name' ($email) se ha registrado y está pendiente de aprobación.\n";
        file_put_contents('email_notification_logs.txt', $logMsg, FILE_APPEND);

        $response['success'] = true;
        $response['message'] = "¡Cuenta creada con éxito! Tu cuenta de estudiante está pendiente de aprobación administrativa.";
    }

    // ==========================================
    // ACCIÓN: INICIAR SESIÓN
    // ==========================================
    elseif ($action === 'login') {
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception("Por favor, completa el correo y la contraseña.");
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("El correo electrónico o la contraseña son incorrectos.");
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $response['success'] = true;
        $response['message'] = "¡Ingreso exitoso! Redireccionando...";
        
        if ($user['role'] === 'admin') {
            $response['redirect_url'] = BASE_URL . "/admin";
        } else {
            $response['redirect_url'] = BASE_URL . "/dashboard";
        }
    }

    // ==========================================
    // ACCIÓN: RESERVAR CLASE DE PRUEBA
    // ==========================================
    elseif ($action === 'book_slot') {
        $name = cleanInput($_POST['name'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $phone = cleanInput($_POST['phone'] ?? '');
        $courseId = (int)($_POST['course_id'] ?? 0);
        $bookingDate = cleanInput($_POST['booking_date'] ?? '');
        $timeSlot = cleanInput($_POST['time_slot'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || $courseId <= 0 || empty($bookingDate) || empty($timeSlot)) {
            throw new Exception("Por favor, selecciona una fecha, horario y completa tus datos de contacto.");
        }

        if (strtotime($bookingDate) < strtotime(date('Y-m-d'))) {
            throw new Exception("No puedes programar una reserva en el pasado.");
        }

        $userCheck = $db->prepare("SELECT id FROM users WHERE email = ? AND role = 'student'");
        $userCheck->execute([$email]);
        $userData = $userCheck->fetch();
        $studentId = $userData ? $userData['id'] : null;

        $conflictCheck = $db->prepare("SELECT id FROM bookings WHERE course_id = ? AND booking_date = ? AND time_slot = ? AND status != 'cancelled'");
        $conflictCheck->execute([$courseId, $bookingDate, $timeSlot]);
        if ($conflictCheck->fetch()) {
            throw new Exception("Este horario ya está reservado para esta clase. Por favor, elige otro horario.");
        }

        $stmt = $db->prepare("INSERT INTO bookings (student_id, name, email, phone, course_id, booking_date, time_slot, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$studentId, $name, $email, $phone, $courseId, $bookingDate, $timeSlot]);

        // Simular correo de confirmación
        $courseStmt = $db->prepare("SELECT title FROM courses WHERE id = ?");
        $courseStmt->execute([$courseId]);
        $courseName = $courseStmt->fetchColumn();

        $logMsg = date('[Y-m-d H:i:s]') . " Correo Confirmación enviado a: $email. Subj: Clase de Prueba Agendada - $courseName el $bookingDate a las $timeSlot.\n";
        file_put_contents('email_notification_logs.txt', $logMsg, FILE_APPEND);

        $response['success'] = true;
        $response['message'] = "¡Clase de prueba agendada! Hemos enviado un correo a " . htmlspecialchars($email) . " con los detalles.";
    }

    // ==========================================
    // ACCIÓN: FORMULARIO DE CONTACTO
    // ==========================================
    elseif ($action === 'contact_submit') {
        $name = cleanInput($_POST['name'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $subject = cleanInput($_POST['subject'] ?? '');
        $message = cleanInput($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            throw new Exception("Todos los campos del formulario de contacto son obligatorios.");
        }

        $logEntry = "Fecha: " . date('Y-m-d H:i:s') . "\nNombre: $name\nEmail: $email\nAsunto: $subject\nMensaje: $message\n-----------------------\n";
        file_put_contents('contact_form_submissions.txt', $logEntry, FILE_APPEND);

        $response['success'] = true;
        $response['message'] = "¡Gracias! Tu mensaje ha sido recibido. Nos pondremos en contacto contigo a la brevedad.";
    }

    // ==========================================
    // ACCIONES ADMINISTRATIVAS
    // ==========================================
    elseif (strpos($action, 'admin_') === 0) {
        if (!isAdmin()) {
            throw new Exception("Acceso no autorizado. Se requieren privilegios de administrador.");
        }

        // Aprobar registro de alumno
        if ($action === 'admin_approve_student') {
            $studentId = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'student'");
            $stmt->execute([$studentId]);
            $response['success'] = true;
            $response['message'] = "Alumno aprobado con éxito.";
        }

        // Rechazar registro de alumno
        elseif ($action === 'admin_reject_student') {
            $studentId = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'student'");
            $stmt->execute([$studentId]);
            $response['success'] = true;
            $response['message'] = "Alumno rechazado con éxito.";
        }

        // Eliminar cuenta de alumno
        elseif ($action === 'admin_delete_student') {
            $studentId = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
            $stmt->execute([$studentId]);
            $response['success'] = true;
            $response['message'] = "Alumno eliminado con éxito.";
        }

        // Confirmar reserva
        elseif ($action === 'admin_confirm_booking') {
            $bookingId = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$bookingId]);
            
            $bookDetails = $db->query("SELECT email, booking_date, time_slot FROM bookings WHERE id = $bookingId")->fetch();
            $logMsg = date('[Y-m-d H:i:s]') . " Correo Confirmación enviado a: {$bookDetails['email']}. Subj: Clase de Prueba Confirmada para el {$bookDetails['booking_date']} a las {$bookDetails['time_slot']}.\n";
            file_put_contents('email_notification_logs.txt', $logMsg, FILE_APPEND);

            $response['success'] = true;
            $response['message'] = "Reserva confirmada con éxito.";
        }

        // Cancelar reserva
        elseif ($action === 'admin_cancel_booking') {
            $bookingId = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$bookingId]);
            $response['success'] = true;
            $response['message'] = "Reserva cancelada con éxito.";
        }

        // CMS: Editar curso
        elseif ($action === 'admin_edit_course') {
            $courseId = (int)($_POST['course_id'] ?? 0);
            $title = cleanInput($_POST['title'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $duration = cleanInput($_POST['duration'] ?? '');
            $desc = cleanInput($_POST['description'] ?? '');
            $details = cleanInput($_POST['details'] ?? '');
            $benefits = cleanInput($_POST['benefits'] ?? '');

            if ($courseId <= 0 || empty($title) || $price <= 0 || empty($duration) || empty($desc) || empty($details) || empty($benefits)) {
                throw new Exception("Todos los campos de modificación del curso son obligatorios.");
            }

            $stmt = $db->prepare("
                UPDATE courses 
                SET title = ?, price = ?, duration = ?, description = ?, details = ?, benefits = ? 
                WHERE id = ?
            ");
            $stmt->execute([$title, $price, $duration, $desc, $details, $benefits, $courseId]);

            $response['success'] = true;
            $response['message'] = "¡Información del curso actualizada en el CMS!";
        }

        // CMS: Agregar artículo de blog
        elseif ($action === 'admin_add_blog') {
            $title = cleanInput($_POST['title'] ?? '');
            $category = cleanInput($_POST['category'] ?? '');
            $tags = cleanInput($_POST['tags'] ?? '');
            $excerpt = cleanInput($_POST['excerpt'] ?? '');
            $content = $_POST['content'] ?? '';

            if (empty($title) || empty($category) || empty($excerpt) || empty($content)) {
                throw new Exception("Completa el título, categoría, resumen y contenido del artículo.");
            }

            $slug = slugify($title);

            $checkStmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if ($checkStmt->fetch()) {
                $slug .= '-' . rand(100, 999);
            }

            $authorId = $_SESSION['user_id'];
            $stmt = $db->prepare("
                INSERT INTO blog_posts (slug, title, content, excerpt, category, tags, author_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'published')
            ");
            $stmt->execute([$slug, $title, $content, $excerpt, $category, $tags, $authorId]);

            $response['success'] = true;
            $response['message'] = "¡Artículo publicado con éxito!";
        }

        // CMS: Guardar configuraciones del sitio
        elseif ($action === 'admin_save_settings') {
            $heroTitle = cleanInput($_POST['hero_title'] ?? '');
            $heroSubtitle = cleanInput($_POST['hero_subtitle'] ?? '');
            $heroDesc = cleanInput($_POST['hero_desc'] ?? '');
            $contactEmail = cleanInput($_POST['contact_email'] ?? '');
            $contactPhone = cleanInput($_POST['contact_phone'] ?? '');
            $contactAddress = cleanInput($_POST['contact_address'] ?? '');

            if (empty($heroTitle) || empty($heroSubtitle) || empty($heroDesc) || empty($contactEmail) || empty($contactPhone) || empty($contactAddress)) {
                throw new Exception("Todos los campos de configuración general son obligatorios.");
            }

            $settings = [
                'hero_title' => $heroTitle,
                'hero_subtitle' => $heroSubtitle,
                'hero_desc' => $heroDesc,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'contact_address' => $contactAddress
            ];

            $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settings as $key => $val) {
                $stmt->execute([$key, $val]);
            }

            $response['success'] = true;
            $response['message'] = "¡Configuraciones generales actualizadas con éxito!";
        }
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
