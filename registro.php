<?php
session_start();
include 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $pregunta = $_POST['pregunta'];
    $respuesta = $_POST['respuesta'];

    if ($password !== $password_confirm) {
        $mensaje = '<div style="color:red;">❌ Las contraseñas no coinciden.</div>';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $respuesta_hash = password_hash($respuesta, PASSWORD_DEFAULT);

        $check_sql = "SELECT id FROM usuarios WHERE email = '$email'";
        $check_result = $conexion->query($check_sql);
        if ($check_result->num_rows > 0) {
            $mensaje = '<div style="color:red;">❌ Este correo electrónico ya está registrado.</div>';
        } else {
            $nombre_foto = NULL;
            if ($_FILES['foto']['error'] == 0) {
                $archivo = $_FILES['foto'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($extension, $tipos_permitidos)) {
                    $nombre_foto = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
                    $ruta_destino = 'uploads/perfiles/' . $nombre_foto;
                    move_uploaded_file($archivo['tmp_name'], $ruta_destino);
                }
            }

            $sql = "INSERT INTO usuarios (nombre, email, password, rol, aprobado, foto, pregunta_seguridad, respuesta_seguridad) 
                    VALUES ('$nombre', '$email', '$password_hash', 'socio', 0, '$nombre_foto', '$pregunta', '$respuesta_hash')";
            
            if ($conexion->query($sql) === TRUE) {
                $mensaje = '<div style="color:green; font-weight:bold;">✅ ¡Registro exitoso! Tu solicitud está pendiente de aprobación. <a href="login.php">Ir a Iniciar Sesión</a></div>';
            } else {
                $mensaje = '<div style="color:red;">❌ Error en el registro: ' . $conexion->error . '</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Ratas del Queiles</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-container": "#521300",
                        "primary": "#ffb59e",
                        "surface-container-highest": "#353534",
                        "error": "#ffb4ab",
                        "on-primary": "#5e1700",
                        "on-error": "#690005",
                        "tertiary-container": "#ff5542",
                        "inverse-on-surface": "#313030",
                        "surface-container": "#201f1f",
                        "surface-tint": "#ffb59e",
                        "on-secondary": "#2f3131",
                        "primary-fixed": "#ffdbd0",
                        "on-secondary-container": "#b8b8b8",
                        "secondary-fixed-dim": "#c6c6c6",
                        "secondary": "#c6c6c6",
                        "surface-dim": "#131313",
                        "primary-container": "#ff5719",
                        "primary-fixed-dim": "#ffb59e",
                        "surface-container-low": "#1c1b1b",
                        "on-tertiary-fixed": "#410000",
                        "outline": "#ac897e",
                        "on-secondary-fixed-variant": "#464747",
                        "error-container": "#93000a",
                        "on-secondary-fixed": "#1a1c1c",
                        "on-background": "#e5e2e1",
                        "tertiary-fixed-dim": "#ffb4a8",
                        "on-tertiary-container": "#5c0001",
                        "surface": "#131313",
                        "secondary-fixed": "#e3e2e2",
                        "inverse-primary": "#ad3200",
                        "on-primary-fixed": "#3a0b00",
                        "on-primary-fixed-variant": "#852400",
                        "on-surface-variant": "#e6beb2",
                        "on-error-container": "#ffdad6",
                        "tertiary-fixed": "#ffdad5",
                        "on-tertiary-fixed-variant": "#930002",
                        "surface-container-lowest": "#0e0e0e",
                        "outline-variant": "#5c4037",
                        "surface-container-high": "#2a2a2a",
                        "surface-variant": "#353534",
                        "background": "#131313",
                        "tertiary": "#ffb4a8",
                        "on-tertiary": "#690001",
                        "inverse-surface": "#e5e2e1",
                        "surface-bright": "#393939",
                        "secondary-container": "#484949",
                        "on-surface": "#e5e2e1"
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    spacing: { "unit": "4px", "gutter": "16px", "container-max": "1200px", "margin-mobile": "20px", "margin-desktop": "40px" },
                    fontFamily: {
                        "headline-xl": ["Anybody"], "headline-lg": ["Anybody"], "headline-md": ["Anybody"],
                        "label-sm": ["JetBrains Mono"], "body-lg": ["Hanken Grotesk"], "label-md": ["JetBrains Mono"],
                        "body-md": ["Hanken Grotesk"], "headline-lg-mobile": ["Anybody"]
                    },
                    fontSize: {
                        "headline-xl": ["48px", { "lineHeight": "52px", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "headline-lg": ["32px", { "lineHeight": "38px", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "30px", "fontWeight": "600" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "label-md": ["14px", { "lineHeight": "20px", "fontWeight": "500" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "34px", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>
    <style>
        .noise-bg { position: relative; }
        .noise-bg::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0.05;
            z-index: -1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            pointer-events: none;
        }
        .chrome-border { border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-dark {
            background-color: #0d0d0d !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            padding: 0.7rem 1rem !important;
            border-radius: 0.25rem !important;
            width: 100% !important;
            transition: border-color 0.2s;
            font-family: 'Hanken Grotesk', sans-serif !important;
            font-size: 1rem !important;
        }
        .input-dark:focus {
            outline: none !important;
            border-color: #ffb59e !important;
            box-shadow: 0 0 0 2px rgba(255,181,158,0.2) !important;
        }
        .input-dark::placeholder {
            color: #666 !important;
        }
        select.input-dark {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23888' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
            padding-right: 2.5rem;
        }
        select.input-dark option {
            background-color: #0d0d0d;
            color: #ffffff;
        }
        .btn-registro {
            background: #007bff;
            color: white;
            font-weight: bold;
            border: none;
            padding: 12px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 18px;
            width: 100%;
        }
        .btn-registro:hover {
            background: #0056b3;
        }
        .mensaje {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper .input-dark {
            padding-right: 3.5rem;
        }
        .toggle-password {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #888;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: color 0.2s, background 0.2s;
        }
        .toggle-password:hover {
            color: #ffb59e;
            background: rgba(255,255,255,0.05);
        }
        .toggle-password .material-symbols-outlined {
            font-size: 20px;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .contenedor-registro {
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md noise-bg">

<div class="contenedor-registro">
    <div class="text-center mb-8">
        <img alt="Logo" class="h-20 w-20 object-contain rounded-full border-2 border-outline-variant mx-auto mb-4" src="images/logo2.jpg">
        <h1 class="font-headline-xl text-headline-xl text-primary uppercase tracking-tighter">Ratas del Queiles</h1>
        <p class="text-on-surface-variant font-label-md text-label-md uppercase mt-2">Registro de nuevo socio</p>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-8">
        <?php echo $mensaje; ?>
        <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="nombre">Nombre y Apellidos *</label>
                <input type="text" name="nombre" id="nombre" class="input-dark" placeholder="Ej: Juan Pérez" value="" required>
            </div>
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="email">Correo electrónico *</label>
                <input type="email" name="email" id="email" class="input-dark" placeholder="tuemail@ejemplo.com" value="" required>
            </div>
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="password">Contraseña *</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="input-dark" value="" required>
                    <button type="button" class="toggle-password" id="togglePassword">
                        <span class="material-symbols-outlined">visibility</span>
                        <span id="toggleText">Mostrar</span>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="password_confirm">Repetir Contraseña *</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirm" id="password_confirm" class="input-dark" value="" required>
                    <button type="button" class="toggle-password" id="togglePasswordConfirm">
                        <span class="material-symbols-outlined">visibility</span>
                        <span id="toggleTextConfirm">Mostrar</span>
                    </button>
                </div>
            </div>

            <!-- Pregunta de seguridad -->
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="pregunta">Pregunta de seguridad *</label>
                <select name="pregunta" id="pregunta" class="input-dark" required>
                    <option value="">Selecciona una pregunta...</option>
                    <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                    <option value="¿Cuál es tu ciudad natal?">¿Cuál es tu ciudad natal?</option>
                    <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                    <option value="¿Cuál es tu comida favorita?">¿Cuál es tu comida favorita?</option>
                    <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="respuesta">Respuesta *</label>
                <input type="text" name="respuesta" id="respuesta" class="input-dark" placeholder="Escribe tu respuesta" required>
            </div>

            <div class="mb-6">
                <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="foto">Foto de perfil (opcional)</label>
                <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
            </div>
            <button type="submit" class="btn-registro">📨 Enviar Solicitud</button>
        </form>
        <div class="mt-6 text-center">
            <a href="login.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm">⬅ ¿Ya tienes cuenta? Inicia sesión</a>
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