<?php
session_start();
include 'conexion.php';

// Solo superadmin puede editar
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

// Obtener contenido actual
$sql = "SELECT * FROM contenido_home WHERE seccion = 'bienvenida'";
$resultado = $conexion->query($sql);
if ($resultado && $resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $contenido_actual = $fila['contenido'] ?? '';
    $texto_imagen_actual = $fila['texto_imagen'] ?? '';
    $imagen_actual = $fila['imagen'] ?? null;
} else {
    $contenido_actual = '';
    $texto_imagen_actual = '';
    $imagen_actual = null;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Home - Ratas del Queiles</title>
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
        @media (min-width: 768px) { main { margin-left: 16rem; max-width: calc(100% - 16rem); } }

        .input-dark {
            background-color: #0d0d0d !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            padding: 0.6rem 0.8rem !important;
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
        textarea.input-dark {
            resize: vertical;
            min-height: 120px;
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
        .btn-guardar {
            background: #28a745;
            color: white;
            font-weight: bold;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-guardar:hover {
            background: #218838;
        }
        .btn-cancelar {
            background: #dc3545;
            color: white;
            font-weight: bold;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-cancelar:hover {
            background: #c82333;
        }
        .imagen-actual {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-top: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.1);
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .user-avatar:hover {
            border-color: #ffb59e;
        }
        .header-titulo {
            font-family: 'Anybody', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #e5e2e1;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }
        .header-subtitulo {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<!-- ===== HEADER ===== -->
<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div class="flex items-center gap-3">
        <?php if (isset($_SESSION['usuario_nombre'])): ?>
            <?php
            $foto_usuario = null;
            if (isset($_SESSION['usuario_id'])) {
                $sql_foto = "SELECT foto FROM usuarios WHERE id = " . intval($_SESSION['usuario_id']);
                $res_foto = $conexion->query($sql_foto);
                if ($res_foto && $res_foto->num_rows > 0) {
                    $row_foto = $res_foto->fetch_assoc();
                    $foto_usuario = $row_foto['foto'];
                }
            }
            ?>
            <?php if (!empty($foto_usuario) && file_exists('uploads/perfiles/' . $foto_usuario)): ?>
                <img src="uploads/perfiles/<?php echo $foto_usuario; ?>" alt="Foto" class="user-avatar" id="avatar-button">
            <?php else: ?>
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='18' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" alt="Sin foto" class="user-avatar" id="avatar-button">
            <?php endif; ?>
        <?php endif; ?>
        <div class="relative" id="settings-menu">
            <button id="settings-button" class="text-on-surface-variant hover:bg-surface-container-high transition-colors duration-200 ease-in-out p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary" aria-label="Ajustes">
                <span class="material-symbols-outlined">settings</span>
            </button>
            <div id="settings-dropdown" class="absolute right-0 mt-2 w-48 bg-surface-container border border-outline-variant rounded-lg shadow-lg py-1 hidden z-50">
                <?php if(isset($_SESSION['usuario_nombre'])): ?>
                    <div class="px-4 py-2 border-b border-outline-variant">
                        <span class="block text-on-background font-label-md"><?php echo $_SESSION['usuario_nombre']; ?></span>
                        <span class="block text-secondary font-label-sm text-xs"><?php echo $_SESSION['rol']; ?></span>
                    </div>
                    <a href="logout.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors font-label-md">
                        <span class="material-symbols-outlined text-[18px] align-middle mr-2">logout</span> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="login.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors font-label-md">
                        <span class="material-symbols-outlined text-[18px] align-middle mr-2">login</span> Iniciar Sesión
                    </a>
                    <a href="registro.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors font-label-md">
                        <span class="material-symbols-outlined text-[18px] align-middle mr-2">person_add</span> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const settingsButton = document.getElementById('settings-button');
        const avatarButton = document.getElementById('avatar-button');
        const settingsDropdown = document.getElementById('settings-dropdown');

        function toggleDropdown(e) {
            e.stopPropagation();
            settingsDropdown.classList.toggle('hidden');
        }
        if (settingsButton) settingsButton.addEventListener('click', toggleDropdown);
        if (avatarButton) avatarButton.addEventListener('click', toggleDropdown);
        document.addEventListener('click', function(e) {
            const container = document.getElementById('settings-menu');
            if (!container.contains(e.target)) {
                settingsDropdown.classList.add('hidden');
            }
        });
        settingsDropdown.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                settingsDropdown.classList.add('hidden');
            });
        });
    });
</script>

<!-- ===== SIDEBAR ===== -->
<aside class="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface-container border-r border-outline-variant p-4 z-40">
    <nav class="flex flex-col gap-2 mt-4">
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="index.php">
            <span class="material-symbols-outlined">home_app_logo</span>
            <span class="font-label-md uppercase">Home</span>
        </a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="salidas.php">
            <span class="material-symbols-outlined">motorcycle</span>
            <span class="font-label-md uppercase">Salidas</span>
        </a>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="actas.php">
                <span class="material-symbols-outlined">description</span>
                <span class="font-label-md uppercase">Actas</span>
            </a>
        <?php endif; ?>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="estatutos.php">
                <span class="material-symbols-outlined">gavel</span>
                <span class="font-label-md uppercase">Estatutos</span>
            </a>
        <?php endif; ?>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <?php 
                $count_sql = "SELECT COUNT(*) as total FROM usuarios WHERE aprobado = 0";
                $count_result = $conexion->query($count_sql);
                $pendientes_total = $count_result->fetch_assoc()['total'];
            ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors relative" href="admin_usuarios.php">
                <span class="material-symbols-outlined">groups</span>
                <span class="font-label-md uppercase">Socios</span>
                <?php if($pendientes_total > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?php echo $pendientes_total; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </nav>
    <div class="mt-auto pb-8 pt-4 border-t border-outline-variant/30">
        <div class="bg-surface-container-high p-4 rounded border border-outline-variant/50">
            <span class="block text-primary font-label-md text-label-md uppercase mb-2">Próxima Reunión</span>
            <span class="block text-on-background font-headline-md text-[16px] uppercase">Viernes, 20:00</span>
            <span class="block text-on-surface-variant font-label-sm text-label-sm">Sede del Club</span>
        </div>
    </div>
</aside>

<!-- ===== MAIN ===== -->
<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="header-titulo text-2xl md:text-3xl">✏️ Editar contenido de la Home</h2>
            <p class="header-subtitulo mt-1">Modifica el texto de bienvenida, el texto encima de la imagen y la imagen.</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="index.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <?php if (isset($_GET['ok'])): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-400 rounded-lg px-4 py-3 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span> Contenido actualizado correctamente.
            </div>
        <?php endif; ?>
        <form action="guardar_home.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- Campo 1: Texto de bienvenida (textarea) -->
            <div>
                <label class="label-dark" for="contenido">Texto de bienvenida</label>
                <textarea name="contenido" id="contenido" class="input-dark" rows="6" placeholder="Escribe aquí el texto que aparecerá debajo del saludo..."><?php echo htmlspecialchars($contenido_actual); ?></textarea>
                <p class="text-secondary text-xs mt-1">Puedes usar saltos de línea para separar párrafos.</p>
            </div>

            <!-- Campo 2: Texto encima de la imagen (textarea, idéntico al anterior) -->
            <div>
                <label class="label-dark" for="texto_imagen">Texto encima de la imagen</label>
                <textarea name="texto_imagen" id="texto_imagen" class="input-dark" rows="6" placeholder="Escribe aquí el texto que aparecerá encima de la imagen..."><?php echo htmlspecialchars($texto_imagen_actual); ?></textarea>
                <p class="text-secondary text-xs mt-1">Este texto aparecerá justo encima de la imagen, en un recuadro independiente.</p>
            </div>

            <!-- Imagen actual -->
            <div>
                <label class="label-dark">Imagen actual</label>
                <?php if (!empty($imagen_actual) && file_exists('images/home/' . $imagen_actual)): ?>
                    <img src="images/home/<?php echo $imagen_actual; ?>" class="imagen-actual" alt="Imagen actual">
                    <p class="text-secondary text-sm mt-1"><?php echo $imagen_actual; ?></p>
                <?php else: ?>
                    <p class="text-secondary text-sm">No hay imagen configurada.</p>
                <?php endif; ?>
            </div>

            <!-- Subir imagen -->
            <div>
                <label class="label-dark" for="imagen">Subir nueva imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño recomendado: 1200x400px.</p>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 btn-guardar flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span> Guardar Cambios
                </button>
                <a href="index.php" class="flex-1 btn-cancelar flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">close</span> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<!-- ===== BOTTOM NAVBAR ===== -->
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="index.php">
        <span class="material-symbols-outlined">home_app_logo</span>
        <span class="font-label-sm uppercase mt-1">Home</span>
    </a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="salidas.php">
        <span class="material-symbols-outlined">motorcycle</span>
        <span class="font-label-sm uppercase mt-1">Salidas</span>
    </a>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="actas.php">
            <span class="material-symbols-outlined">description</span>
            <span class="font-label-sm uppercase mt-1">Actas</span>
        </a>
    <?php endif; ?>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="estatutos.php">
            <span class="material-symbols-outlined">gavel</span>
            <span class="font-label-sm uppercase mt-1">Estatutos</span>
        </a>
    <?php endif; ?>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors relative" href="admin_usuarios.php">
            <span class="material-symbols-outlined">groups</span>
            <span class="font-label-sm uppercase mt-1">Socios</span>
            <?php if($pendientes_total > 0): ?>
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-background"><?php echo $pendientes_total; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</nav>
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