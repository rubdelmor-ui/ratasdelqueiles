<?php
session_start();
include 'conexion.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        
        if (password_verify($password, $usuario['password'])) {
            if ($usuario['aprobado'] == 1) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "⏳ Tu cuenta está pendiente de aprobación por la Junta Directiva.";
            }
        } else {
            $error = "❌ Contraseña incorrecta.";
        }
    } else {
        $error = "❌ No existe un usuario con ese correo electrónico.";
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Ratas del Queiles</title>
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
            background-color: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e2e1;
            padding: 0.7rem 1rem;
            border-radius: 0.25rem;
            width: 100%;
            transition: border-color 0.2s;
        }
        .input-dark:focus {
            outline: none;
            border-color: #ffb59e;
            box-shadow: 0 0 0 2px rgba(255,181,158,0.2);
        }
        .input-dark::placeholder {
            color: #666;
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
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex items-center justify-center noise-bg p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img alt="Logo" class="h-20 w-20 object-contain rounded-full border-2 border-outline-variant mx-auto mb-4" src="images/logo2.jpg">
            <h1 class="font-headline-xl text-headline-xl text-primary uppercase tracking-tighter">Ratas del Queiles</h1>
            <p class="text-on-surface-variant font-label-md text-label-md uppercase mt-2">Accede a tu cuenta de socio</p>
        </div>

        <div class="bg-surface-container rounded-xl chrome-border p-8">
            <?php if (!empty($error)): ?>
                <div class="bg-error-container/20 border border-error/30 text-error rounded-lg px-4 py-3 mb-6 font-label-md text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Email -->
                <div class="mb-5">
                    <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="email">Correo electrónico</label>
                    <input type="email" name="email" id="email" class="input-dark" placeholder="tuemail@ejemplo.com" required>
                </div>

                <!-- Contraseña con toggle -->
                <div class="mb-6">
                    <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="input-dark" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <span class="material-symbols-outlined">visibility</span>
                            <span id="toggleText">Mostrar</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary-container text-black font-headline-md text-[18px] uppercase px-8 py-3 rounded-sm border-2 border-black shadow-[inset_0_0_0_2px_rgba(255,255,255,0.2)] hover:bg-primary transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">login</span> Entrar al Club
                </button>
            </form>

            <div class="mt-6 text-center space-y-3">
                <a href="registro.php" class="block text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm">
                    <span class="material-symbols-outlined text-[16px] align-middle mr-1">person_add</span> ¿No tienes cuenta? Regístrate aquí
                </a>
                <a href="index.php" class="block text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm">
                    <span class="material-symbols-outlined text-[16px] align-middle mr-1">arrow_back</span> Volver al inicio
                </a>
            </div>
        </div>

        <div class="text-center mt-6 text-secondary text-xs opacity-50">
            <span class="font-label-sm">🐀 Ratas del Queiles · Todos los derechos reservados</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>