<?php
session_start();
include 'conexion.php';

// 🔐 Solo el admin (superadmin) puede editar salidas
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM salidas WHERE id = $id";
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
if (!$fila) {
    header("Location: salidas.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Salida - Ratas del Queiles</title>
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
            background-color: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e5e2e1;
            padding: 0.6rem 0.8rem;
            border-radius: 0.25rem;
            width: 100%;
            transition: border-color 0.2s;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 1rem;
        }
        .input-dark:focus { outline: none; border-color: #ffb59e; box-shadow: 0 0 0 2px rgba(255,181,158,0.2); }
        .input-dark::placeholder { color: #666; }
        textarea.input-dark { resize: vertical; }
        .imagen-actual { max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-margin-mobile py-unit h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div class="flex items-center gap-4">
        <div class="relative" id="settings-menu">
            <button id="settings-button" class="text-on-surface-variant hover:bg-surface-container-high p-2 rounded-full">
                <span class="material-symbols-outlined">settings</span>
            </button>
            <div id="settings-dropdown" class="absolute right-0 mt-2 w-48 bg-surface-container border border-outline-variant rounded-lg shadow-lg py-1 hidden z-50">
                <?php if(isset($_SESSION['usuario_nombre'])): ?>
                    <div class="px-4 py-2 border-b border-outline-variant">
                        <span class="block text-on-background"><?php echo $_SESSION['usuario_nombre']; ?></span>
                        <span class="block text-secondary text-xs"><?php echo $_SESSION['rol']; ?></span>
                    </div>
                    <a href="logout.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary">Iniciar Sesión</a>
                    <a href="registro.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('settings-button');
        const dropdown = document.getElementById('settings-dropdown');
        btn.addEventListener('click', function(e) { e.stopPropagation(); dropdown.classList.toggle('hidden'); });
        document.addEventListener('click', function() { dropdown.classList.add('hidden'); });
    });
</script>

<aside class="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface-container border-r border-outline-variant p-4 z-40">
    <nav class="flex flex-col gap-2 mt-4">
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="index.php"><span class="material-symbols-outlined">home_app_logo</span> Home</a>
        <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3" href="salidas.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">motorcycle</span> Salidas</a>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="actas.php"><span class="material-symbols-outlined">description</span> Actas</a>
        <?php endif; ?>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="estatutos.php"><span class="material-symbols-outlined">gavel</span> Estatutos</a>
        <?php endif; ?>
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
            <?php $count = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE aprobado=0")->fetch_assoc()['total']; ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 relative" href="admin_usuarios.php">
                <span class="material-symbols-outlined">groups</span> Socios
                <?php if($count > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?php echo $count; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </nav>
</aside>

<main class="flex-grow p-margin-mobile flex flex-col gap-gutter pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">✏️ Editar Salida</h2>
            <p class="text-on-surface-variant max-w-xl font-label-md text-label-md uppercase">Modifica los datos de la salida.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="salidas.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <form action="actualizar_salida.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
            <input type="hidden" name="imagen_antigua" value="<?php echo $fila['imagen']; ?>">

            <div>
                <label class="label-dark" for="destino">Destino *</label>
                <input type="text" name="destino" id="destino" value="<?php echo $fila['destino']; ?>" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark" for="fecha_salida">Fecha *</label>
                <input type="date" name="fecha_salida" id="fecha_salida" value="<?php echo $fila['fecha_salida']; ?>" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark" for="hora_quedada">Hora *</label>
                <input type="time" name="hora_quedada" id="hora_quedada" value="<?php echo $fila['hora_quedada']; ?>" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark" for="punto_encuentro">Punto de encuentro</label>
                <input type="text" name="punto_encuentro" id="punto_encuentro" value="<?php echo $fila['punto_encuentro']; ?>" class="input-dark">
            </div>
            <div>
                <label class="label-dark" for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="5" class="input-dark"><?php echo $fila['descripcion']; ?></textarea>
            </div>
            <div>
                <label class="label-dark" for="responsable">Responsable de la salida</label>
                <input type="text" name="responsable" id="responsable" value="<?php echo $fila['responsable']; ?>" class="input-dark" placeholder="Ej: Juan Pérez">
            </div>
            <div>
                <label class="label-dark">Imagen actual</label>
                <?php if (!empty($fila['imagen']) && file_exists('images/salidas/' . $fila['imagen'])): ?>
                    <img src="images/salidas/<?php echo $fila['imagen']; ?>" class="imagen-actual" alt="Imagen actual">
                    <p class="text-secondary text-sm mt-1"><?php echo $fila['imagen']; ?></p>
                <?php else: ?>
                    <p class="text-secondary text-sm">No hay imagen asociada.</p>
                <?php endif; ?>
            </div>
            <div>
                <label class="label-dark" for="imagen">Cambiar imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos: JPG, PNG, WEBP.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 bg-primary-container text-black font-headline-md text-[16px] uppercase px-8 py-3 rounded-sm border-2 border-black shadow-[inset_0_0_0_2px_rgba(255,255,255,0.2)] hover:bg-primary transition-colors flex items-center justify-center gap-2">
                    💾 Guardar Cambios
                </button>
                <a href="salidas.php" class="flex-1 bg-surface-container-high text-on-surface-variant font-headline-md text-[16px] uppercase px-8 py-3 rounded-sm border border-outline-variant hover:border-primary transition-colors flex items-center justify-center gap-2 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="index.php"><span class="material-symbols-outlined">home_app_logo</span><span class="font-label-sm uppercase text-xs">Home</span></a>
    <a class="flex flex-col items-center justify-center text-primary-container bg-on-primary-container/10 rounded-xl p-1" href="salidas.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">motorcycle</span><span class="font-label-sm uppercase text-xs">Salidas</span></a>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="actas.php"><span class="material-symbols-outlined">description</span><span class="font-label-sm uppercase text-xs">Actas</span></a>
    <?php endif; ?>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="estatutos.php"><span class="material-symbols-outlined">gavel</span><span class="font-label-sm uppercase text-xs">Estatutos</span></a>
    <?php endif; ?>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta'): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 relative" href="admin_usuarios.php">
            <span class="material-symbols-outlined">groups</span><span class="font-label-sm uppercase text-xs">Socios</span>
            <?php if($count > 0): ?>
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-background"><?php echo $count; ?></span>
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