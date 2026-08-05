<?php
session_start();
include 'conexion.php';

// Obtener contenido editable de la home
$sql_contenido = "SELECT * FROM contenido_home WHERE seccion = 'bienvenida'";
$result_contenido = $conexion->query($sql_contenido);
if ($result_contenido && $result_contenido->num_rows > 0) {
    $fila = $result_contenido->fetch_assoc();
    $contenido_bienvenida = $fila['contenido'] ?? 'La carretera espera. Únete a la próxima ruta o revisa las últimas novedades del club.';
    $imagen_home = $fila['imagen'] ?? null;
} else {
    $contenido_bienvenida = 'La carretera espera. Únete a la próxima ruta o revisa las últimas novedades del club.';
    $imagen_home = null;
}

$superadmin_email = 'admin@club.com';
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == $superadmin_email);
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratas del Queiles - Home</title>
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
        .imagen-home {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 8px;
        }
        /* Botón de edición: círculo rojo fijo, subido para no tapar el botón "Socios" */
        .btn-editar-home {
            position: fixed;
            bottom: 100px;   /* Subido para evitar superposición con la barra de navegación móvil */
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: #dc3545;
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            z-index: 999;
            text-decoration: none;
            font-size: 28px;
        }
        .btn-editar-home:hover {
            background-color: #b02a37;
            transform: scale(1.08);
        }
        .btn-editar-home .material-symbols-outlined {
            font-size: 28px;
        }
        /* Recuadro de imagen */
        .recuadro-imagen {
            background-color: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .placeholder-imagen {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<!-- ===== HEADER ===== -->
<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-margin-mobile py-unit h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Ratas del Queiles Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div class="flex items-center gap-4">
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
        const settingsDropdown = document.getElementById('settings-dropdown');
        settingsButton.addEventListener('click', function(e) {
            e.stopPropagation();
            settingsDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function(e) {
            if (!settingsButton.contains(e.target) && !settingsDropdown.contains(e.target)) {
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
        <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3 transition-colors" href="index.php">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">home_app_logo</span>
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
<main class="flex-grow p-margin-mobile flex flex-col gap-gutter pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <!-- Recuadro 1: Bienvenida (HERO) -->
    <section class="relative rounded-xl overflow-hidden chrome-border h-64 md:h-96 flex items-center justify-center text-center">
        <div class="absolute inset-0 bg-cover bg-center z-0 opacity-40 mix-blend-overlay" style="background-image: url('assets/images/fondo_moto.jpg');"></div>
        <div class="relative z-10 p-6">
            <img alt="Club Logo" class="h-32 mx-auto mb-4 drop-shadow-2xl" src="images/logo2.jpg">
            <h2 class="font-headline-xl text-primary uppercase tracking-tighter drop-shadow-lg">
                <?php 
                if(isset($_SESSION['usuario_nombre'])) {
                    echo 'Bienvenido     ' . $_SESSION['usuario_nombre'];
                } else {
                    echo 'Bienvenidos, Hermanos';
                }
                ?>
            </h2>
            <p class="font-body-lg text-on-surface mt-2 max-w-md mx-auto">
                <?php echo nl2br(htmlspecialchars($contenido_bienvenida)); ?>
            </p>
        </div>
    </section>

    <!-- Recuadro 2: Imagen -->
    <div class="recuadro-imagen">
        <?php if (!empty($imagen_home) && file_exists('images/home/' . $imagen_home)): ?>
            <img src="images/home/<?php echo $imagen_home; ?>" alt="Imagen de la home" class="imagen-home">
        <?php else: ?>
            <div class="placeholder-imagen">
                <span class="material-symbols-outlined text-4xl">image</span>
                <span>No hay imagen configurada. El administrador puede subir una.</span>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ===== BOTÓN DE EDICIÓN (círculo rojo fijo, subido) ===== -->
<?php if ($es_superadmin): ?>
    <a href="editar_home.php" class="btn-editar-home" title="Editar contenido de la home">
        <span class="material-symbols-outlined">edit</span>
    </a>
<?php endif; ?>

<!-- ===== BOTTOM NAVBAR ===== -->
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-primary-container bg-on-primary-container/10 rounded-xl p-1 transition-transform duration-100 scale-95" href="index.php">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">home_app_logo</span>
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

</body>
</html>