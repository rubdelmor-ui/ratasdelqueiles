<?php
session_start();
include 'conexion.php';

// Seguridad: Solo la JUNTA puede entrar aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta') {
    header("Location: login.php");
    exit;
}

$es_junta = ($_SESSION['rol'] == 'junta');
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');

// --- ACCIONES ---
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    $conexion->query("UPDATE usuarios SET aprobado = 1 WHERE id = $id");
    header("Location: admin_usuarios.php");
    exit;
}

if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    $conexion->query("DELETE FROM usuarios WHERE id = $id");
    header("Location: admin_usuarios.php");
    exit;
}

// --- ACCIONES DE CAMBIO DE ROL (SOLO SUPERADMIN) ---
if ($es_superadmin) {
    if (isset($_GET['hacer_junta'])) {
        $id = intval($_GET['hacer_junta']);
        if ($id != $_SESSION['usuario_id']) {
            $conexion->query("UPDATE usuarios SET rol='junta' WHERE id=$id");
        }
        header("Location: admin_usuarios.php");
        exit;
    }
    if (isset($_GET['quitar_junta'])) {
        $id = intval($_GET['quitar_junta']);
        if ($id != $_SESSION['usuario_id']) {
            $conexion->query("UPDATE usuarios SET rol='socio' WHERE id=$id");
        }
        header("Location: admin_usuarios.php");
        exit;
    }
    if (isset($_GET['eliminar'])) {
        $id = intval($_GET['eliminar']);
        if ($id != $_SESSION['usuario_id']) {
            $sql_foto = "SELECT foto FROM usuarios WHERE id = $id";
            $res_foto = $conexion->query($sql_foto);
            $fila_foto = $res_foto->fetch_assoc();
            $foto = $fila_foto['foto'] ?? null;
            $conexion->query("DELETE FROM usuarios WHERE id = $id");
            if (!empty($foto) && file_exists('uploads/perfiles/' . $foto)) {
                unlink('uploads/perfiles/' . $foto);
            }
        }
        header("Location: admin_usuarios.php");
        exit;
    }
}

// Exportar CSV (solo superadmin)
if (isset($_GET['exportar_csv']) && $es_superadmin) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="socios_' . date('Y-m-d') . '.xls"');
    $salida = fopen('php://output', 'w');
    fputs($salida, "\xEF\xBB\xBF");
    fputcsv($salida, ['ID', 'Nombre', 'Email', 'Rol', 'Cargo', 'Estado', 'Fecha Registro'], ';');
    $sql_csv = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
    $res_csv = $conexion->query($sql_csv);
    while ($row = $res_csv->fetch_assoc()) {
        $estado = ($row['aprobado'] == 1) ? 'Aprobado' : 'Pendiente';
        fputcsv($salida, [
            $row['id'],
            $row['nombre'],
            $row['email'],
            strtoupper($row['rol']),
            $row['cargo'] ?? '',
            $estado,
            $row['fecha_registro']
        ], ';');
    }
    fclose($salida);
    exit;
}

// Obtener datos
$sql_pendientes = "SELECT * FROM usuarios WHERE aprobado = 0 ORDER BY fecha_registro ASC";
$pendientes = $conexion->query($sql_pendientes);

$sql_aprobados = "SELECT * FROM usuarios WHERE aprobado = 1 ORDER BY nombre ASC";
$aprobados = $conexion->query($sql_aprobados);

$sql_todos = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
$todos = $conexion->query($sql_todos);

$count = $pendientes->num_rows;
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socios - Ratas del Queiles</title>
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
        .foto-socio { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid rgba(255,255,255,0.1); background: #1a1a1a; }
        .badge-junta { background: #b30000; color: white; padding: 2px 12px; border-radius: 12px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; }
        .badge-socio { background: #28a745; color: white; padding: 2px 12px; border-radius: 12px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; }
        .badge-pendiente { background: #ffc107; color: black; padding: 2px 12px; border-radius: 12px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; }
        .badge-cargo { background: #17a2b8; color: white; padding: 2px 10px; border-radius: 12px; font-size: 0.6rem; font-weight: bold; text-transform: uppercase; margin-left: 4px; }
        .btn-aprobar { background: #28a745; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-aprobar:hover { background: #218838; }
        .btn-rechazar { background: #dc3545; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-rechazar:hover { background: #c82333; }
        .btn-junta { background: #ffc107; color: black; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-junta:hover { background: #e0a800; }
        .btn-socio { background: #6c757d; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-socio:hover { background: #5a6268; }
        .btn-eliminar { background: #dc3545; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; border: 1px solid rgba(255,255,255,0.1); }
        .btn-eliminar:hover { background: #a71d2a; }
        .btn-editar { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-editar:hover { background: #138496; }
        .btn-listado { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-listado:hover { background: #0056b3; }
        .btn-csv { background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block; transition: 0.2s; }
        .btn-csv:hover { background: #218838; }
        .listado-completo { margin-top: 20px; display: none; }
        .listado-completo.visible { display: block; }
        .superadmin-tag { background: #ffaa00; color: #000; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.7rem; margin-left: 5px; }
        .grid-socios { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .card-socio { background: #1a1a1a; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 16px; transition: border-color 0.2s; }
        .card-socio:hover { border-color: rgba(255,255,255,0.15); }
        .card-socio .nombre { font-family: 'Anybody', sans-serif; font-size: 1rem; font-weight: 700; color: #e5e2e1; }
        .card-socio .email { font-size: 0.8rem; color: #888; }
        .card-socio .acciones { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
        .card-socio .acciones a { font-size: 0.65rem; padding: 2px 8px; }
        .tabla-socios { width: 100%; border-collapse: collapse; background: #1a1a1a; border-radius: 8px; overflow: hidden; }
        .tabla-socios th { background: #2a2a2a; color: #e5e2e1; padding: 10px 12px; text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid #353534; }
        .tabla-socios td { padding: 10px 12px; border-bottom: 1px solid #2a2a2a; vertical-align: middle; }
        .tabla-socios tr:hover td { background: #252525; }
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

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div class="flex items-center gap-3">
        <!-- Se ha eliminado la foto del usuario -->
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

        function toggleDropdown(e) {
            e.stopPropagation();
            settingsDropdown.classList.toggle('hidden');
        }
        if (settingsButton) settingsButton.addEventListener('click', toggleDropdown);
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
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="actas.php">
                <span class="material-symbols-outlined">description</span>
                <span class="font-label-md uppercase">Actas</span>
            </a>
        <?php endif; ?>
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3 transition-colors" href="estatutos.php">
                <span class="material-symbols-outlined">gavel</span>
                <span class="font-label-md uppercase">Estatutos</span>
            </a>
        <?php endif; ?>
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3 transition-colors relative" href="admin_usuarios.php">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">groups</span>
                <span class="font-label-md uppercase">Socios</span>
                <?php if($count > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-2 py-0.5"><?php echo $count; ?></span>
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

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="header-titulo text-2xl md:text-3xl">Gestión de Socios</h2>
            <p class="header-subtitulo mt-1">Administra los miembros del club, aprueba solicitudes y gestiona roles.</p>
        </div>
        <div class="mt-3 md:mt-0 flex gap-2">
            <?php if($es_superadmin): ?>
                <span class="px-3 py-1 bg-surface-container-high border border-outline-variant rounded text-on-background font-label-sm text-label-sm uppercase flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">shield</span>
                    <span>Superadmin</span>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if($es_superadmin): ?>
        <div class="bg-surface-container border border-outline-variant/50 rounded-lg p-4 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">info</span>
            <span class="text-on-surface-variant font-label-md">👑 <strong>Superadmin:</strong> Tienes control total para ascender/descender, editar, eliminar socios y ver/exportar el listado completo.</span>
        </div>
    <?php else: ?>
        <div class="bg-surface-container border border-outline-variant/50 rounded-lg p-4 flex items-center gap-3">
            <span class="material-symbols-outlined text-secondary">lock</span>
            <span class="text-on-surface-variant font-label-md">🔒 <strong>Miembro de la Junta:</strong> Puedes aprobar/rechazar solicitudes, pero no cambiar roles, editar, eliminar ni ver el listado completo.</span>
        </div>
    <?php endif; ?>

    <!-- Solicitudes Pendientes -->
    <section class="bg-surface-container rounded-xl chrome-border overflow-hidden">
        <div class="bg-surface-container-high px-6 py-4 border-b border-outline-variant flex justify-between items-center">
            <h3 class="font-headline-md text-headline-md text-on-background uppercase flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">pending</span>
                Solicitudes Pendientes
                <?php if($count > 0): ?>
                    <span class="bg-red-600 text-white text-xs rounded-full px-3 py-1 ml-2"><?php echo $count; ?></span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="p-6">
            <?php if ($pendientes->num_rows > 0): ?>
                <div class="grid-socios">
                    <?php while($socio = $pendientes->fetch_assoc()): ?>
                        <div class="card-socio">
                            <div class="flex items-center gap-4">
                                <?php if (!empty($socio['foto']) && file_exists('uploads/perfiles/' . $socio['foto'])): ?>
                                    <img src="uploads/perfiles/<?php echo $socio['foto']; ?>" class="foto-socio" alt="Foto">
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Ccircle cx='30' cy='30' r='30' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='30' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" class="foto-socio" alt="Sin foto">
                                <?php endif; ?>
                                <div>
                                    <div class="nombre"><?php echo $socio['nombre']; ?></div>
                                    <div class="email"><?php echo $socio['email']; ?></div>
                                    <div class="text-secondary text-xs">Registrado: <?php echo date('d/m/Y', strtotime($socio['fecha_registro'])); ?></div>
                                    <span class="badge-pendiente">Pendiente</span>
                                </div>
                            </div>
                            <div class="acciones">
                                <a href="admin_usuarios.php?aprobar=<?php echo $socio['id']; ?>" class="btn-aprobar">✅ Aprobar</a>
                                <a href="admin_usuarios.php?rechazar=<?php echo $socio['id']; ?>" class="btn-rechazar" onclick="return confirm('¿Rechazar esta solicitud?')">❌ Rechazar</a>
                                <?php if ($es_superadmin): ?>
                                    <a href="editar_socio.php?id=<?php echo $socio['id']; ?>" class="btn-editar">✏️ Editar</a>
                                    <a href="admin_usuarios.php?eliminar=<?php echo $socio['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar definitivamente a este socio?')">🗑️ Eliminar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-secondary text-center py-4">✅ No hay solicitudes pendientes.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Socios Activos -->
    <section class="bg-surface-container rounded-xl chrome-border overflow-hidden">
        <div class="bg-surface-container-high px-6 py-4 border-b border-outline-variant">
            <h3 class="font-headline-md text-headline-md text-on-background uppercase flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">groups</span>
                Socios Activos
            </h3>
        </div>
        <div class="p-6">
            <?php if ($aprobados->num_rows > 0): ?>
                <div class="grid-socios">
                    <?php while($socio = $aprobados->fetch_assoc()): ?>
                        <div class="card-socio">
                            <div class="flex items-center gap-4">
                                <?php if (!empty($socio['foto']) && file_exists('uploads/perfiles/' . $socio['foto'])): ?>
                                    <img src="uploads/perfiles/<?php echo $socio['foto']; ?>" class="foto-socio" alt="Foto">
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Ccircle cx='30' cy='30' r='30' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='30' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" class="foto-socio" alt="Sin foto">
                                <?php endif; ?>
                                <div>
                                    <div class="nombre"><?php echo $socio['nombre']; ?></div>
                                    <div class="email"><?php echo $socio['email']; ?></div>
                                    <div>
                                        <?php if($socio['rol'] == 'junta'): ?>
                                            <span class="badge-junta">🔴 JUNTA</span>
                                            <?php if (!empty($socio['cargo'])): ?>
                                                <span class="badge-cargo"><?php echo htmlspecialchars($socio['cargo']); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge-socio">🟢 SOCIO</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="acciones">
                                <?php if ($socio['id'] == $_SESSION['usuario_id']): ?>
                                    <?php if ($es_superadmin): ?>
                                        <a href="editar_socio.php?id=<?php echo $socio['id']; ?>" class="btn-editar">✏️ Editar</a>
                                        <span class="text-secondary text-sm italic">(Eres tú)</span>
                                    <?php else: ?>
                                        <span class="text-secondary text-sm italic">(Eres tú)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($es_superadmin): ?>
                                        <a href="editar_socio.php?id=<?php echo $socio['id']; ?>" class="btn-editar">✏️ Editar</a>
                                        <?php if ($socio['rol'] == 'socio'): ?>
                                            <a href="admin_usuarios.php?hacer_junta=<?php echo $socio['id']; ?>" class="btn-junta" onclick="return confirm('¿Ascender a este socio a JUNTA?')">⬆️ Hacer Junta</a>
                                        <?php else: ?>
                                            <a href="admin_usuarios.php?quitar_junta=<?php echo $socio['id']; ?>" class="btn-socio" onclick="return confirm('¿Quitar el rango de JUNTA?')">⬇️ Quitar Junta</a>
                                        <?php endif; ?>
                                        <a href="admin_usuarios.php?eliminar=<?php echo $socio['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar definitivamente a este socio?')">🗑️ Eliminar</a>
                                    <?php else: ?>
                                        <span class="text-secondary text-sm">🔒 Solo Superadmin</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-secondary text-center py-4">No hay socios activos.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Listado Completo (solo superadmin) -->
    <?php if ($es_superadmin): ?>
        <section class="bg-surface-container rounded-xl chrome-border overflow-hidden">
            <div class="bg-surface-container-high px-6 py-4 border-b border-outline-variant flex justify-between items-center flex-wrap gap-3">
                <h3 class="font-headline-md text-headline-md text-on-background uppercase flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Listado Completo
                </h3>
                <div class="flex gap-2">
                    <button id="btnToggle" class="btn-listado" onclick="toggleListado()">📋 Ver Listado</button>
                    <a href="?exportar_csv=1" class="btn-csv">📥 Excel</a>
                </div>
            </div>
            <div class="p-6">
                <div id="listadoCompleto" class="listado-completo">
                    <table class="tabla-socios">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($socio = $todos->fetch_assoc()): ?>
                            <tr>
                                <td class="text-secondary text-sm"><?php echo $socio['id']; ?></td>
                                <td>
                                    <?php if (!empty($socio['foto']) && file_exists('uploads/perfiles/' . $socio['foto'])): ?>
                                        <img src="uploads/perfiles/<?php echo $socio['foto']; ?>" style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:1px solid rgba(255,255,255,0.1);">
                                    <?php else: ?>
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='18' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" style="width:32px;height:32px;object-fit:cover;border-radius:50%;">
                                    <?php endif; ?>
                                </td>
                                <td class="font-label-md text-on-background"><?php echo $socio['nombre']; ?></td>
                                <td class="text-secondary"><?php echo $socio['email']; ?></td>
                                <td>
                                    <?php if($socio['rol'] == 'junta'): ?>
                                        <span class="badge-junta">JUNTA</span>
                                    <?php else: ?>
                                        <span class="badge-socio">SOCIO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary"><?php echo $socio['cargo'] ?? '-'; ?></td>
                                <td>
                                    <?php if($socio['aprobado'] == 1): ?>
                                        <span class="text-green-400 font-label-sm">✅ Aprobado</span>
                                    <?php else: ?>
                                        <span class="badge-pendiente">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary text-sm"><?php echo date('d/m/Y', strtotime($socio['fecha_registro'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <p class="text-secondary text-sm mt-4">Total: <?php echo $todos->num_rows; ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <a href="index.php" class="text-primary hover:underline font-label-md inline-flex items-center gap-1 mt-2">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver al inicio
    </a>
</main>

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="index.php">
        <span class="material-symbols-outlined">home_app_logo</span>
        <span class="font-label-sm uppercase mt-1">Home</span>
    </a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="salidas.php">
        <span class="material-symbols-outlined">motorcycle</span>
        <span class="font-label-sm uppercase mt-1">Salidas</span>
    </a>
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="actas.php">
            <span class="material-symbols-outlined">description</span>
            <span class="font-label-sm uppercase mt-1">Actas</span>
        </a>
    <?php endif; ?>
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1 hover:text-primary transition-colors" href="estatutos.php">
            <span class="material-symbols-outlined">gavel</span>
            <span class="font-label-sm uppercase mt-1">Estatutos</span>
        </a>
    <?php endif; ?>
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-primary-container bg-on-primary-container/10 rounded-xl p-1 transition-transform duration-100 scale-95 relative" href="admin_usuarios.php">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">groups</span>
            <span class="font-label-sm uppercase mt-1">Socios</span>
            <?php if($count > 0): ?>
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-background"><?php echo $count; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</nav>

<script>
    function toggleListado() {
        const div = document.getElementById('listadoCompleto');
        div.classList.toggle('visible');
        document.getElementById('btnToggle').textContent = div.classList.contains('visible') ? '📋 Ocultar Listado' : '📋 Ver Listado';
    }
</script>

</body>
</html>