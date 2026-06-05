<section class="container" style="padding-top: 140px;">
    <div class="section-title">
        <h2>Portal del Alumno</h2>
        <p>Inicia sesión para gestionar tus clases o crea una nueva cuenta de estudiante para unirte a la academia.</p>
    </div>

    <div class="booking-container">
        <!-- Login Form -->
        <div class="glass-card">
            <h3 style="font-size:1.5rem; margin-bottom:25px;"><i class="fas fa-sign-in-alt" style="color:var(--teal); margin-right:10px;"></i> Iniciar Sesión</h3>
            
            <form id="studentLoginForm">
                <div class="form-alert"></div>

                <div class="form-group">
                    <label for="login_email">Correo Electrónico *</label>
                    <input type="email" id="login_email" name="email" class="form-control" required placeholder="Ej. correo@correo.com">
                </div>

                <div class="form-group">
                    <label for="login_password">Contraseña *</label>
                    <input type="password" id="login_password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <div class="form-group" style="margin-top:30px;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Ingresar</button>
                </div>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="glass-card">
            <h3 style="font-size:1.5rem; margin-bottom:25px;"><i class="fas fa-user-plus" style="color:var(--teal); margin-right:10px;"></i> Registro de Alumnos</h3>
            
            <form id="studentRegisterForm">
                <div class="form-alert"></div>

                <div class="form-group">
                    <label for="reg_name">Nombre Completo *</label>
                    <input type="text" id="reg_name" name="name" class="form-control" required placeholder="Ej. Alice Cooper">
                </div>

                <div class="form-group">
                    <label for="reg_email">Correo Electrónico *</label>
                    <input type="email" id="reg_email" name="email" class="form-control" required placeholder="Ej. alice@correo.com">
                </div>

                <div class="form-group">
                    <label for="reg_password">Contraseña *</label>
                    <input type="password" id="reg_password" name="password" class="form-control" required placeholder="Elige tu contraseña">
                </div>

                <p style="font-size:0.8rem; color:var(--text-muted); line-height:1.4; margin-top:20px;">
                    * Nota: Las nuevas cuentas de alumnos ingresan en estado de <strong>Aprobación Pendiente</strong>. La administración verifica los registros diariamente.
                </p>

                <div class="form-group" style="margin-top:25px;">
                    <button type="submit" class="btn btn-teal" style="width:100%;">Crear Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</section>
