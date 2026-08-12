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

$sql = "SELECT * FROM salidas WHERE fecha_salida >= CURDATE() ORDER BY fecha_salida ASC";
$resultado = $conexion->query($sql);
$todas_salidas = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $todas_salidas[] = $row;
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
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas - Ratas del Queiles</title>
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
        .countdown, .responsable, .asistentes-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            font-weight: 500;
            color: #ffb59e;
            background: rgba(255, 87, 25, 0.1);
            padding: 2px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255, 87, 25, 0.3);
            display: inline-block;
            margin-top: 4px;
        }
        .countdown.terminado { color: #ffb4ab; background: rgba(255,0,0,0.15); border-color: rgba(255,0,0,0.3); }
        .responsable .material-symbols-outlined,
        .asistentes-badge .material-symbols-outlined { font-size: 14px; vertical-align: middle; }
        .asistentes-badge { cursor: pointer; transition: background 0.2s, border-color 0.2s; }
        .asistentes-badge:hover { background: rgba(255, 87, 25, 0.25); border-color: #ffb59e; }
        .modal-inscritos {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-inscritos.visible { display: flex; }
        .modal-inscritos .modal-contenido {
            background: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 24px;
            max-width: 400px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
        }
        .modal-inscritos .modal-titulo {
            font-family: 'Anybody', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: #e5e2e1;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 8px;
        }
        .modal-inscritos .modal-lista { list-style: none; padding: 0; margin: 0; }
        .modal-inscritos .modal-lista li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #ccc;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-inscritos .modal-lista li:last-child { border-bottom: none; }
        .modal-inscritos .modal-lista li .material-symbols-outlined { font-size: 20px; color: #ffb59e; }
        .modal-inscritos .btn-cerrar-modal {
            margin-top: 16px;
            background: #ffb59e;
            color: #000;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .modal-inscritos .btn-cerrar-modal:hover { background: #ff8a6a; }
        .btn-excel-modal {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
            text-decoration: none;
        }
        .btn-excel-modal:hover { background: #218838; }
        .btn-excel-modal .material-symbols-outlined { font-size: 20px; }
        .img-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #2a2a2a;
            color: #666;
            font-size: 0.8rem;
            text-transform: uppercase;
            width: 100%;
            height: 100%;
        }
        .debug-info {
            font-size: 0.55rem;
            color: #ffaa00;
            background: #1a1a1a;
            padding: 2px 4px;
            border-radius: 2px;
            margin-top: 2px;
            word-break: break-all;
            font-family: 'JetBrains Mono', monospace;
        }
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
        <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3" href="salidas.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">motorcycle</span> Salidas</a>
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
        <?php if($es_junta): ?>
            <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="estatutos.php"><span class="material-symbols-outlined">gavel</span> Estatutos</a>
        <?php endif; ?>
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
            <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">Próximas Salidas</h2>
            <p class="text-on-surface-variant max-w-xl font-label-md text-label-md uppercase">Rutas programadas. Asfalto, gasolina y hermandad.</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <span class="px-3 py-1 bg-surface-container-high border border-outline-variant rounded text-on-background font-label-sm uppercase flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-tertiary-container animate-pulse"></span> Temporada Activa
            </span>
            <?php if($es_superadmin): ?>
                <a href="nueva_salida.php" class="px-3 py-1 bg-primary-container text-black font-label-sm uppercase rounded border border-black hover:bg-primary">+ Nueva</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($todas_salidas) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter mt-2">
            <?php foreach ($todas_salidas as $salida): ?>
                <?php
                // Contar total de asistentes
                $count_sql = "SELECT COUNT(*) as total FROM inscripciones WHERE salida_id = " . $salida['id'];
                $count_result = $conexion->query($count_sql);
                $total_socios = $count_result->fetch_assoc()['total'];

                $count_acomp_sql = "SELECT COUNT(*) as total FROM acompanantes a 
                                    JOIN inscripciones i ON a.inscripcion_id = i.id 
                                    WHERE i.salida_id = " . $salida['id'];
                $count_acomp_result = $conexion->query($count_acomp_sql);
                $total_acompanantes = $count_acomp_result->fetch_assoc()['total'];

                $total_inscritos = $total_socios + $total_acompanantes;

                // Lista de asistentes para el modal
                $nombres_sql = "SELECT u.nombre, i.id as inscripcion_id 
                                FROM inscripciones i 
                                JOIN usuarios u ON i.usuario_id = u.id 
                                WHERE i.salida_id = " . $salida['id'] . " 
                                ORDER BY u.nombre ASC";
                $nombres_result = $conexion->query($nombres_sql);
                $lista_asistentes = [];
                while ($row_n = $nombres_result->fetch_assoc()) {
                    $insc_id = $row_n['inscripcion_id'];
                    $socio_nombre = $row_n['nombre'];
                    $sql_acomp = "SELECT nombre FROM acompanantes WHERE inscripcion_id = $insc_id ORDER BY nombre ASC";
                    $res_acomp = $conexion->query($sql_acomp);
                    $acompanantes = [];
                    while ($acomp = $res_acomp->fetch_assoc()) {
                        $acompanantes[] = $acomp['nombre'];
                    }
                    $lista_asistentes[] = [
                        'socio' => $socio_nombre,
                        'acompanantes' => $acompanantes
                    ];
                }
                $json_asistentes = json_encode($lista_asistentes, JSON_HEX_APOS | JSON_HEX_QUOT);

                $ya_apuntado = false;
                if (isset($_SESSION['usuario_id'])) {
                    $check_sql = "SELECT id FROM inscripciones WHERE salida_id = " . $salida['id'] . " AND usuario_id = " . $_SESSION['usuario_id'];
                    $check_result = $conexion->query($check_sql);
                    if ($check_result->num_rows > 0) $ya_apuntado = true;
                }

                $fecha_iso = '';
                if (!empty($salida['fecha_salida']) && !empty($salida['hora_quedada'])) {
                    try {
                        $dt = new DateTime($salida['fecha_salida'] . ' ' . $salida['hora_quedada']);
                        $fecha_iso = $dt->format('Y-m-d\TH:i:s');
                    } catch (Exception $e) { $fecha_iso = ''; }
                }

                // ---- MANEJO DE IMAGEN CON CLOUDINARY ----
                $nombre_imagen = $salida['imagen'];
                $ruta_defecto = '/assets/images/fondo_moto.jpg'; // Ruta absoluta local para fallback

                // Comprobar si la imagen guardada en BD es una URL de Cloudinary
                $imagen_existe = (!empty($nombre_imagen) && strpos($nombre_imagen, 'http') === 0);
                
                $src_imagen = '';
                if ($imagen_existe) {
                    $src_imagen = $nombre_imagen; // Usamos directamente la URL
                } else {
                    $src_imagen = $ruta_defecto; // Usamos el fallback local
                }
                ?>
                <article class="surface-texture rounded-lg border border-outline-variant/30 p-4 flex flex-col sm:flex-row gap-4 hover:border-outline transition-colors group relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-surface-variant group-hover:bg-primary transition-colors"></div>
                    <div class="flex-shrink-0 w-full sm:w-28 h-28 rounded bg-surface-variant border border-outline-variant/50 relative overflow-hidden">
                        <?php if (!empty($src_imagen)): ?>
                            <img src="<?php echo $src_imagen; ?>" alt="<?php echo $salida['destino']; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="img-placeholder">
                                <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($es_superadmin): ?>
                            <div class="debug-info">
                                <?php if (empty($nombre_imagen)): ?>
                                    ⚠️ Sin imagen asignada
                                <?php elseif ($imagen_existe): ?>
                                    ✅ Nube
                                <?php else: ?>
                                    ❌ No en nube
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-headline-md text-[18px] text-on-background uppercase tracking-tight"><?php echo $salida['destino']; ?></h4>
                                <span class="px-2 py-0.5 bg-surface-variant text-on-surface-variant font-label-sm uppercase rounded border border-outline-variant/50"><?php echo date('d M', strtotime($salida['fecha_salida'])); ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-on-surface-variant font-label-md text-label-md mb-2 flex-wrap text-sm">
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">calendar_month</span> <?php echo date('d M, H:i', strtotime($salida['fecha_salida'] . ' ' . $salida['hora_quedada'])); ?></span>
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">location_on</span> <?php echo $salida['punto_encuentro']; ?></span>
                            </div>
                            <p class="text-on-secondary-container text-sm line-clamp-2"><?php echo $salida['descripcion']; ?></p>
                            <div class="countdown mt-1" data-fecha="<?php echo $fecha_iso; ?>">
                                <?php echo !empty($fecha_iso) ? '⏳ Calculando...' : '📅 Fecha no disponible'; ?>
                            </div>
                            <?php if (!empty($salida['responsable'])): ?>
                                <div class="responsable mt-1">
                                    <span class="material-symbols-outlined">badge</span>
                                    Responsable: <?php echo htmlspecialchars($salida['responsable']); ?>
                                </div>
                            <?php endif; ?>
                            <button class="asistentes-badge mt-1" data-asistentes='<?php echo $json_asistentes; ?>' data-total="<?php echo $total_inscritos; ?>" data-salida-id="<?php echo $salida['id']; ?>">
                                <span class="material-symbols-outlined">group</span>
                                Asistentes: <?php echo $total_inscritos; ?>
                            </button>
                        </div>
                        <div class="flex justify-between items-end border-t border-outline-variant/30 pt-2 mt-2 flex-wrap gap-2">
                            <div class="flex-1 text-center">
                                <?php if (isset($_SESSION['usuario_id'])): ?>
                                    <?php if ($ya_apuntado): ?>
                                        <a href="apuntarse.php?salida_id=<?php echo $salida['id']; ?>" class="text-error font-label-sm uppercase hover:underline text-xs">❌ Ya no voy</a>
                                    <?php else: ?>
                                        <a href="apuntarse_salida.php?salida_id=<?php echo $salida['id']; ?>" class="text-primary font-label-sm uppercase hover:underline text-xs">📌 Apuntarse</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-secondary font-label-sm uppercase text-xs">🔒 Inicia sesión</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                                <?php if ($es_superadmin): ?>
                                    <a href="editar_salida.php?id=<?php echo $salida['id']; ?>" class="text-secondary hover:text-primary font-label-sm uppercase text-xs">✏️ Editar</a>
                                    <a href="borrar_salida.php?id=<?php echo $salida['id']; ?>" class="text-error hover:text-error/80 font-label-sm uppercase text-xs" onclick="return confirm('¿Seguro?')">🗑️</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-surface-container rounded-lg p-8 text-center border border-outline-variant">
            <span class="material-symbols-outlined text-6xl text-secondary">motorcycle</span>
            <h3 class="font-headline-md text-on-background mt-4">No hay salidas programadas</h3>
            <p class="text-secondary mt-2">Próximamente publicaremos nuevas rutas.</p>
        </div>
    <?php endif; ?>
</main>

<!-- ===== MODAL DE ASISTENTES ===== -->
<div id="modalInscritos" class="modal-inscritos">
    <div class="modal-contenido">
        <div class="modal-titulo">
            <span class="material-symbols-outlined" style="font-size:24px; vertical-align:middle;">group</span>
            <span style="vertical-align:middle;">Asistentes a la salida</span>
        </div>
        <ul id="modalLista" class="modal-lista"></ul>
        <?php if ($es_superadmin): ?>
            <a id="btnDescargarExcel" href="#" class="btn-excel-modal" target="_blank">
                <span class="material-symbols-outlined">download</span> Descargar Excel
            </a>
        <?php endif; ?>
        <button class="btn-cerrar-modal" id="cerrarModalInscritos">Cerrar</button>
    </div>
</div>

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="index.php"><span class="material-symbols-outlined">home_app_logo</span><span class="font-label-sm uppercase text-xs">Home</span></a>
    <a class="flex flex-col items-center justify-center text-primary-container bg-on-primary-container/10 rounded-xl p-1" href="salidas.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">motorcycle</span><span class="font-label-sm uppercase text-xs">Salidas</span></a>
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
    <?php if($es_junta): ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="estatutos.php"><span class="material-symbols-outlined">gavel</span><span class="font-label-sm uppercase text-xs">Estatutos</span></a>
    <?php endif; ?>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Contador regresivo
        function actualizar() {
            const ahora = new Date().getTime();
            document.querySelectorAll('.countdown').forEach(function(c) {
                const fecha = c.getAttribute('data-fecha');
                if (!fecha || fecha === '') { c.innerHTML = '📅 Fecha no disponible'; c.classList.remove('terminado'); return; }
                const target = new Date(fecha).getTime();
                if (isNaN(target)) { c.innerHTML = '📅 Fecha no válida'; c.classList.remove('terminado'); return; }
                const diff = target - ahora;
                if (diff <= 0) { c.innerHTML = '🏁 ¡Ya ha empezado!'; c.classList.add('terminado'); return; }
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                c.innerHTML = '⏳ Faltan: ' + (d>0?d+'d ':'') + h+'h '+m+'m '+s+'s';
                c.classList.remove('terminado');
            });
        }
        setInterval(actualizar, 1000);
        actualizar();

        // Modal
        const modal = document.getElementById('modalInscritos');
        const modalLista = document.getElementById('modalLista');
        const cerrarModal = document.getElementById('cerrarModalInscritos');
        const btnExcel = document.getElementById('btnDescargarExcel');

        function abrirModal(elemento) {
            const asistentesAttr = elemento.getAttribute('data-asistentes');
            const salidaId = elemento.getAttribute('data-salida-id');
            
            if (salidaId && btnExcel) {
                btnExcel.href = 'descargar_asistentes_excel.php?salida_id=' + salidaId;
            }

            let listaHTML = '';
            try {
                const data = JSON.parse(asistentesAttr);
                if (data.length === 0) {
                    listaHTML = '<li><span style="color:#888;">No hay asistentes apuntados todavía.</span></li>';
                } else {
                    data.forEach(function(item) {
                        listaHTML += '<li><span class="material-symbols-outlined">person</span> ' + item.socio + '</li>';
                        if (item.acompanantes.length > 0) {
                            item.acompanantes.forEach(function(acomp) {
                                listaHTML += '<li style="padding-left: 2rem; font-size: 0.85rem; color: #aaa;"><span class="material-symbols-outlined" style="font-size: 16px;">person_add</span> ' + acomp + ' (acompañante)</li>';
                            });
                        }
                    });
                }
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                listaHTML = '<li><span style="color:#888;">Error al cargar la lista.</span></li>';
            }
            modalLista.innerHTML = listaHTML;
            modal.classList.add('visible');
        }

        document.querySelectorAll('.asistentes-badge').forEach(function(elemento) {
            elemento.addEventListener('click', function(e) {
                e.stopPropagation();
                abrirModal(this);
            });
        });

        function cerrarModalFunc() { modal.classList.remove('visible'); }
        cerrarModal.addEventListener('click', cerrarModalFunc);
        modal.addEventListener('click', function(e) { if (e.target === modal) cerrarModalFunc(); });
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