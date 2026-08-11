<?php
session_start();
include 'conexion.php';

// 🔴 DETECTAR NUEVAS ACTAS (para la junta)
$hay_actas_nuevas = false;
if (isset($_SESSION['usuario_id'])) {
    $sql_ultima = "SELECT valor FROM configuracion WHERE clave = 'ultima_acta'";
    $res_ultima = $conexion->query($sql_ultima);
    if ($res_ultima && $res_ultima->num_rows > 0) {
        $fila_ultima = $res_ultima->fetch_assoc();
        $ultima_acta = strtotime($fila_ultima['valor']);
        $ultima_visita = isset($_SESSION['ultima_visita_actas']) ? $_SESSION['ultima_visita_actas'] : 0;
        if ($ultima_acta > $ultima_visita) {
            $hay_actas_nuevas = true;
        }
    }
}

$es_junta = (isset($_SESSION['rol']) && $_SESSION['rol'] == 'junta');
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');

$pendientes_total = 0;
if ($es_junta) {
    $count_sql = "SELECT COUNT(*) as total FROM usuarios WHERE aprobado = 0";
    $count_result = $conexion->query($count_sql);
    $pendientes_total = $count_result->fetch_assoc()['total'];
}

$sql = "SELECT valor FROM configuracion WHERE clave = 'estatutos_pdf'";
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
$pdf_estatutos = $fila['valor'] ?? null;
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatutos - Ratas del Queiles</title>
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
        .btn-pdf-estatutos { background: #dc3545; color: white; padding: 10px 24px; border-radius: 4px; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-pdf-estatutos:hover { background: #b02a37; }
        .btn-subir { background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; }
        .btn-subir:hover { background: #218838; }
        .superadmin-tag { background: #ffaa00; color: #000; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.7rem; margin-left: 5px; }
        .punto-notificacion {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 12px;
            height: 12px;
            background-color: #dc3545;
            border-radius: 50%;
            border: 2px solid #131313;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-margin-mobile py-unit h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
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
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span> Salidas</a>
        <!-- Enlace a Actas (visible solo para junta) con punto rojo -->
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors relative" href="actas.php">
                <span class="material-symbols-outlined">description</span>
                <span class="font-label-md uppercase">Actas</span>
                <?php if($hay_actas_nuevas): ?>
                    <span class="punto-notificacion"></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3" href="estatutos.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">gavel</span> Estatutos</a>
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 relative" href="admin_usuarios.php">
                <span class="material-symbols-outlined">groups</span> Socios
                <?php if($pendientes_total > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?php echo $pendientes_total; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </nav>
</aside>

<main class="flex-grow p-margin-mobile flex flex-col gap-gutter pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">Estatutos del Club</h2>
            <p class="text-on-surface-variant max-w-xl font-label-md text-label-md uppercase">Normas fundamentales y código de conducta.</p>
        </div>
        <?php if($es_superadmin): ?>
            <div class="mt-4 md:mt-0">
                <span class="superadmin-tag">👑 SUPERADMIN</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 text-center">
        <?php if (!empty($pdf_estatutos) && file_exists('pdf_estatutos/' . $pdf_estatutos)): ?>
            <span class="material-symbols-outlined text-6xl text-primary">picture_as_pdf</span>
            <h3 class="font-headline-md text-on-background mt-4">Estatutos disponibles</h3>
            <p class="text-secondary text-sm mt-2">Última actualización: <?php echo date('d/m/Y', filemtime('pdf_estatutos/' . $pdf_estatutos)); ?></p>
            <a href="pdf_estatutos/<?php echo $pdf_estatutos; ?>" target="_blank" class="btn-pdf-estatutos mt-4">📄 Ver Estatutos</a>
        <?php else: ?>
            <span class="material-symbols-outlined text-6xl text-secondary">description</span>
            <h3 class="font-headline-md text-on-background mt-4">No hay estatutos disponibles</h3>
            <p class="text-secondary text-sm">El documento aún no ha sido subido por la administración.</p>
        <?php endif; ?>
    </div>

    <?php if ($es_superadmin): ?>
        <div class="bg-surface-container rounded-xl chrome-border p-6">
            <h3 class="font-headline-md text-primary uppercase mb-4">🛠️ Gestión de Estatutos</h3>
            <form action="guardar_estatutos.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                <div>
                    <label class="block text-on-surface-variant font-label-md text-label-md uppercase mb-1" for="archivo_pdf">Selecciona un archivo PDF:</label>
                    <input type="file" name="archivo_pdf" accept=".pdf" required class="input-dark">
                </div>
                <button type="submit" class="btn-subir">📤 Subir / Actualizar Estatutos</button>
            </form>
            <?php if (!empty($pdf_estatutos)): ?>
                <p class="text-secondary text-sm mt-4">Archivo actual: <?php echo $pdf_estatutos; ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="index.php"><span class="material-symbols-outlined">home_app_logo</span><span class="font-label-sm uppercase text-xs">Home</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span><span class="font-label-sm uppercase text-xs">Salidas</span></a>
    <!-- Enlace a Actas (visible solo para junta) con punto rojo -->
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors relative" href="actas.php">
            <span class="material-symbols-outlined">description</span>
            <span class="font-label-sm uppercase mt-1">Actas</span>
            <?php if($hay_actas_nuevas): ?>
                <span class="punto-notificacion"></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
    <a class="flex flex-col items-center justify-center text-primary-container bg-on-primary-container/10 rounded-xl p-1" href="estatutos.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">gavel</span><span class="font-label-sm uppercase text-xs">Estatutos</span></a>
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 relative" href="admin_usuarios.php">
            <span class="material-symbols-outlined">groups</span><span class="font-label-sm uppercase text-xs">Socios</span>
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