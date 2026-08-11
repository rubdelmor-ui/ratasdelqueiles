<?php
session_start();
include 'conexion.php';

$mensaje = "";
$token_valido = false;
$usuario_id = null;
$email = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // Buscar usuario con ese token y que no haya expirado
    $sql = "SELECT id, email, fecha_token FROM usuarios WHERE token_recuperacion = '$token'";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $fecha_token = new DateTime($fila['fecha_token']);
        $ahora = new DateTime();
        if ($fecha_token > $ahora) {
            $token_valido = true;
            $usuario_id = $fila['id'];
            $email = $fila['email'];
        } else {
            $mensaje = '<div style="color:#ffb4ab; background:#2a1a1a; border:1px solid #93000a; padding:12px; border-radius:8px;">⏳ El enlace de restablecimiento ha expirado. Solicita uno nuevo.</div>';
        }
    } else {
        $mensaje = '<div style="color:#ffb4ab; background:#2a1a1a; border:1px solid #93000a; padding:12px; border-radius:8px;">❌ El enlace no es válido. Por favor, solicita un nuevo restablecimiento.</div>';
    }
} else {
    header("Location: olvide_password.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if ($password !== $password_confirm) {
        $mensaje = '<div style="color:#ffb4ab; background:#2a1a1a; border:1px solid #93000a; padding:12px; border-radius:8px;">❌ Las contraseñas no coinciden.</div>';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Actualizar contraseña y eliminar token
        $update_sql = "UPDATE usuarios SET password = '$password_hash', token_recuperacion = NULL, fecha_token = NULL WHERE id = $usuario_id";
        if ($conexion->query($update_sql) === TRUE) {
            $mensaje = '<div style="color:#b5e6b5; background:#1a2a1a; border:1px solid #28a745; padding:12px; border-radius:8px;">✅ Contraseña actualizada correctamente. Ahora puedes <a href="login.php" style="color:#ffb59e; font-weight:bold;">iniciar sesión</a> con tu nueva contraseña.</div>';
            $token_valido = false; // Ocultar el formulario
        } else {
            $mensaje = '<div style="color:#ffb4ab; background:#2a1a1a; border:1px solid #93000a; padding:12px; border-radius:8px;">❌ Error al actualizar la contraseña. Intenta nuevamente.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - Ratas del Queiles</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body { background: #131313; color: #e5e2e1; font-family: 'Hanken Grotesk', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; margin: 0; }
        .bg-surface { background: #201f1f; }
        .chrome-border { border: 1px solid rgba(255,255,255,0.1); }
        .input-dark { background-color: #0d0d0d; border: 1px solid rgba(255,255,255,0.15); color: #ffffff; padding: 0.7rem 1rem; border-radius: 0.25rem; width: 100%; font-family: 'Hanken Grotesk', sans-serif; font-size: 1rem; transition: border-color 0.2s; }
        .input-dark:focus { outline: none; border-color: #ffb59e; box-shadow: 0 0 0 2px rgba(255,181,158,0.2); }
        .input-dark::placeholder { color: #666; }
        .btn-primary { background: #ff5719; color: black; border: 2px solid black; padding: 0.75rem 2rem; border-radius: 0.25rem; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-family: 'Anybody', sans-serif; font-size: 1rem; text-transform: uppercase; }
        .btn-primary:hover { background: #ffb59e; }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .text-secondary { color: #b8b8b8; }
        .text-primary { color: #ffb59e; }
        .text-on-background { color: #e5e2e1; }
        .font-headline-lg { font-family: 'Anybody', sans-serif; font-size: 1.5rem; font-weight: 700; }
        .max-w-md { max-width: 450px; width: 100%; }
        .mt-8 { margin-top: 2rem; }
        .mb-8 { margin-bottom: 2rem; }
        .text-center { text-align: center; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .block { display: block; }
        .w-full { width: 100%; }
        .p-4 { padding: 1rem; }
        .p-8 { padding: 2rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .gap-1 { gap: 0.25rem; }
        .inline-flex { display: inline-flex; align-items: center; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .noise-bg { position: relative; }
        .noise-bg::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.05; z-index: -1; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); pointer-events: none; }
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper .input-dark { padding-right: 3.5rem; }
        .toggle-password { position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #888; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; font-size: 0.8rem; font-weight: 500; padding: 0.25rem 0.5rem; border-radius: 0.25rem; transition: color 0.2s, background 0.2s; }
        .toggle-password:hover { color: #ffb59e; background: rgba(255,255,255,0.05); }
        .toggle-password .material-symbols-outlined { font-size: 20px; }
    </style>
</head>
<body class="noise-bg">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img alt="Logo" class="h-20 w-20 object-contain rounded-full border-2 border-outline-variant mx-auto mb-4" src="images/logo2.jpg">
            <h1 class="font-headline-lg text-headline-lg text-primary uppercase tracking-tighter">Ratas del Queiles</h1>
            <p class="text-secondary font-label-md text-label-md uppercase mt-2">Nueva contraseña</p>
        </div>

        <div class="bg-surface rounded-xl chrome-border p-8">
            <?php echo $mensaje; ?>
            <?php if ($token_valido): ?>
                <p class="text-secondary text-sm mb-4">Introduce tu nueva contraseña para <?php echo htmlspecialchars($email); ?>.</p>
                <form method="POST" action="" autocomplete="off">
                    <div class="mb-4">
                        <label class="label-dark" for="password">Nueva contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="input-dark" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <span class="material-symbols-outlined">visibility</span>
                                <span id="toggleText">Mostrar</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="label-dark" for="password_confirm">Repetir contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirm" id="password_confirm" class="input-dark" required>
                            <button type="button" class="toggle-password" id="togglePasswordConfirm">
                                <span class="material-symbols-outlined">visibility</span>
                                <span id="toggleTextConfirm">Mostrar</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined">check_circle</span> Restablecer contraseña
                    </button>
                </form>
            <?php endif; ?>
            <div class="mt-6 text-center">
                <a href="login.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle para la primera contraseña
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleText = document.getElementById('toggleText');
            const icon = toggleBtn.querySelector('.material-symbols-outlined');

            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.textContent = 'visibility_off';
                    toggleText.textContent = 'Ocultar';
                } else {
                    passwordInput.type = 'password';
                    icon.textContent = 'visibility';
                    toggleText.textContent = 'Mostrar';
                }
            });

            // Toggle para la confirmación de contraseña
            const toggleBtnConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmInput = document.getElementById('password_confirm');
            const toggleTextConfirm = document.getElementById('toggleTextConfirm');
            const iconConfirm = toggleBtnConfirm.querySelector('.material-symbols-outlined');

            toggleBtnConfirm.addEventListener('click', function() {
                if (passwordConfirmInput.type === 'password') {
                    passwordConfirmInput.type = 'text';
                    iconConfirm.textContent = 'visibility_off';
                    toggleTextConfirm.textContent = 'Ocultar';
                } else {
                    passwordConfirmInput.type = 'password';
                    iconConfirm.textContent = 'visibility';
                    toggleTextConfirm.textContent = 'Mostrar';
                }
            });
        });
    </script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('ServiceWorker registrado con éxito', registration.scope);
                })
                .catch(error => {
                    console.log('Fallo al registrar ServiceWorker', error);
                });
        });
    }
</script>
</body>
</html>