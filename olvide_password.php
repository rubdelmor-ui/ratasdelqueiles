<?php
session_start();
include 'conexion.php';

$paso = 1; // 1: email, 2: pregunta, 3: nueva contraseña
$email = '';
$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && empty($_POST['respuesta']) && empty($_POST['nueva_password'])) {
        // PASO 1: Validar email
        $email = $_POST['email'];
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = $conexion->query($sql);
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            if (empty($usuario['pregunta_seguridad']) || empty($usuario['respuesta_seguridad'])) {
                $error = "❌ Este usuario no tiene configurada una pregunta de seguridad. Contacta con la administración.";
            } else {
                $_SESSION['reset_email'] = $email;
                $paso = 2;
            }
        } else {
            $error = "❌ No existe un usuario con ese correo electrónico.";
        }
    } elseif (isset($_POST['respuesta']) && isset($_SESSION['reset_email'])) {
        // PASO 2: Verificar respuesta
        $email = $_SESSION['reset_email'];
        $respuesta = $_POST['respuesta'];
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = $conexion->query($sql);
        $usuario = $resultado->fetch_assoc();
        if (password_verify($respuesta, $usuario['respuesta_seguridad'])) {
            $paso = 3;
        } else {
            $error = "❌ Respuesta incorrecta. Inténtalo de nuevo.";
        }
    } elseif (isset($_POST['nueva_password']) && isset($_SESSION['reset_email'])) {
        // PASO 3: Guardar nueva contraseña
        $nueva_password = $_POST['nueva_password'];
        $confirm_password = $_POST['confirm_password'];
        if ($nueva_password !== $confirm_password) {
            $error = "❌ Las contraseñas no coinciden.";
        } else {
            $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];
            $sql = "UPDATE usuarios SET password = '$hash' WHERE email = '$email'";
            if ($conexion->query($sql) === TRUE) {
                unset($_SESSION['reset_email']);
                $mensaje = "✅ Contraseña actualizada correctamente. <a href='login.php' class='text-primary hover:underline'>Inicia sesión aquí</a>";
                $paso = 0;
            } else {
                $error = "❌ Error al actualizar la contraseña.";
            }
        }
    }
}

if (($paso == 2 || $paso == 3) && !isset($_SESSION['reset_email'])) {
    $paso = 1;
    $error = "⏳ Sesión expirada. Vuelve a introducir tu correo.";
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Ratas del Queiles</title>
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
        .btn-primary {
            background: #ff5719;
            color: black;
            border: 2px solid black;
            padding: 0.75rem 2rem;
            border-radius: 0.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
            font-family: 'Anybody', sans-serif;
            text-transform: uppercase;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background: #ffb59e;
        }
        .label-dark {
            display: block;
            color: #b0b0b0 !important;
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 0.25rem !important;
        }
        .error-box {
            background: rgba(255,0,0,0.15);
            border: 1px solid rgba(255,0,0,0.3);
            color: #ffb4ab;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        .success-box {
            background: rgba(0,255,0,0.1);
            border: 1px solid rgba(0,255,0,0.2);
            color: #8bc34a;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        body {
            background-color: #131313 !important;
            color: #e5e2e1 !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .bg-surface-container {
            background-color: #201f1f !important;
        }
        .text-on-background {
            color: #e5e2e1 !important;
        }
        .text-secondary {
            color: #b8b8b8 !important;
        }
        .text-primary {
            color: #ffb59e !important;
        }
    </style>
</head>
<body>

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <img alt="Logo" class="h-20 w-20 object-contain rounded-full border-2 border-outline-variant mx-auto mb-4" src="images/logo2.jpg">
        <h1 class="font-headline-xl text-headline-xl text-primary uppercase tracking-tighter" style="font-family: 'Anybody', sans-serif; font-size: 2.5rem; font-weight: 800; color: #ffb59e;">Ratas del Queiles</h1>
        <p class="text-on-surface-variant font-label-md text-label-md uppercase mt-2" style="color: #e6beb2; font-family: 'JetBrains Mono', monospace; font-size: 0.875rem; font-weight: 500; text-transform: uppercase;">Recupera tu acceso</p>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-8">

        <?php if ($paso == 0 && !empty($mensaje)): ?>
            <div class="success-box"><?php echo $mensaje; ?></div>
        <?php else: ?>

            <?php if (!empty($error)): ?>
                <div class="error-box"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($paso == 1): ?>
                <form method="POST" action="">
                    <div>
                        <label class="label-dark" for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" class="input-dark" placeholder="tuemail@ejemplo.com" required>
                    </div>
                    <button type="submit" class="btn-primary mt-4">Continuar</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="login.php" class="text-secondary hover:text-primary transition-colors text-sm" style="color: #b8b8b8; text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 0.875rem;">⬅ Volver al inicio de sesión</a>
                </div>

            <?php elseif ($paso == 2): ?>
                <?php
                $email = $_SESSION['reset_email'];
                $sql = "SELECT pregunta_seguridad FROM usuarios WHERE email = '$email'";
                $resultado = $conexion->query($sql);
                $fila = $resultado->fetch_assoc();
                $pregunta = $fila['pregunta_seguridad'];
                ?>
                <p class="text-on-background mb-4" style="color: #e5e2e1;">Para verificar tu identidad, responde a la siguiente pregunta:</p>
                <div class="bg-surface-container-high p-4 rounded mb-4" style="background-color: #2a2a2a; border: 1px solid rgba(255,255,255,0.05); border-radius: 0.25rem;">
                    <p class="text-primary font-headline-md text-center" style="color: #ffb59e; font-family: 'Anybody', sans-serif; font-size: 1.25rem; font-weight: 600;"><?php echo $pregunta; ?></p>
                </div>
                <form method="POST" action="">
                    <div>
                        <label class="label-dark" for="respuesta">Tu respuesta</label>
                        <input type="text" name="respuesta" id="respuesta" class="input-dark" placeholder="Escribe tu respuesta" required>
                    </div>
                    <button type="submit" class="btn-primary mt-4">Verificar respuesta</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="olvide_password.php" class="text-secondary hover:text-primary transition-colors text-sm" style="color: #b8b8b8; text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 0.875rem;">⬅ Volver a introducir correo</a>
                </div>

            <?php elseif ($paso == 3): ?>
                <p class="text-on-background mb-4" style="color: #e5e2e1;">Crea una nueva contraseña para tu cuenta.</p>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="label-dark" for="nueva_password">Nueva contraseña</label>
                        <input type="password" name="nueva_password" id="nueva_password" class="input-dark" required>
                    </div>
                    <div class="mb-4">
                        <label class="label-dark" for="confirm_password">Confirmar contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="input-dark" required>
                    </div>
                    <button type="submit" class="btn-primary">Actualizar contraseña</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="olvide_password.php" class="text-secondary hover:text-primary transition-colors text-sm" style="color: #b8b8b8; text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 0.875rem;">⬅ Cancelar y volver</a>
                </div>

            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
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