<?php
session_start();
include 'conexion.php';

// Solo superadmin puede editar
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: admin_usuarios.php");
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM usuarios WHERE id = $id";
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
if (!$fila) {
    header("Location: admin_usuarios.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Socio - Ratas del Queiles</title>
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
        select.input-dark option {
            background-color: #0d0d0d !important;
            color: #ffffff !important;
        }
        .foto-actual {
            max-width: 120px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.1);
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
</header>

<aside class="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface-container border-r border-outline-variant p-4 z-40">
    <nav class="flex flex-col gap-2 mt-4">
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="index.php"><span class="material-symbols-outlined">home_app_logo</span> Home</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span> Salidas</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="actas.php"><span class="material-symbols-outlined">description</span> Actas</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="estatutos.php"><span class="material-symbols-outlined">gavel</span> Estatutos</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="admin_usuarios.php"><span class="material-symbols-outlined">groups</span> Socios</a>
    </nav>
</aside>

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="header-titulo text-2xl md:text-3xl">✏️ Editar Socio</h2>
            <p class="header-subtitulo mt-1">Modifica los datos del socio. Solo el Superadmin puede hacerlo.</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="admin_usuarios.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <form action="actualizar_socio.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">

            <div>
                <label class="label-dark" for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($fila['nombre']); ?>" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark" for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($fila['email']); ?>" class="input-dark" required>
            </div>

            <!-- Rol (solo visible para superadmin) -->
            <?php if ($es_superadmin): ?>
                <div>
                    <label class="label-dark" for="rol">Rol</label>
                    <select name="rol" id="rol" class="input-dark">
                        <option value="socio" <?php if($fila['rol']=='socio') echo 'selected'; ?>>Socio</option>
                        <option value="junta" <?php if($fila['rol']=='junta') echo 'selected'; ?>>Junta</option>
                    </select>
                </div>
            <?php else: ?>
                <div>
                    <label class="label-dark">Rol</label>
                    <input type="text" class="input-dark" value="<?php echo ucfirst($fila['rol']); ?>" disabled>
                    <p class="text-secondary text-xs mt-1">Solo el Superadmin puede modificar el rol.</p>
                </div>
            <?php endif; ?>

            <!-- Cargo -->
            <div>
                <label class="label-dark" for="cargo">Cargo (solo para miembros de la Junta)</label>
                <input type="text" name="cargo" id="cargo" value="<?php echo htmlspecialchars($fila['cargo'] ?? ''); ?>" class="input-dark" placeholder="Ej: Presidente, Secretario, Tesorero...">
            </div>

            <!-- Estado -->
            <div>
                <label class="label-dark" for="aprobado">Estado</label>
                <select name="aprobado" id="aprobado" class="input-dark">
                    <option value="1" <?php if($fila['aprobado']==1) echo 'selected'; ?>>Aprobado</option>
                    <option value="0" <?php if($fila['aprobado']==0) echo 'selected'; ?>>Pendiente</option>
                </select>
            </div>

            <!-- Foto actual -->
            <div>
                <label class="label-dark">Foto actual</label>
                <?php if (!empty($fila['foto']) && file_exists('uploads/perfiles/' . $fila['foto'])): ?>
                    <img src="uploads/perfiles/<?php echo $fila['foto']; ?>" class="foto-actual" alt="Foto">
                <?php else: ?>
                    <p class="text-secondary text-sm">Sin foto</p>
                <?php endif; ?>
            </div>

            <!-- Cambiar foto -->
            <div>
                <label class="label-dark" for="foto">Cambiar foto (opcional)</label>
                <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos permitidos: JPG, PNG, GIF, WEBP.</p>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 bg-primary-container text-black font-headline-md text-[16px] uppercase px-8 py-3 rounded-sm border-2 border-black shadow-[inset_0_0_0_2px_rgba(255,255,255,0.2)] hover:bg-primary transition-colors flex items-center justify-center gap-2">
                    💾 Guardar Cambios
                </button>
                <a href="admin_usuarios.php" class="flex-1 bg-surface-container-high text-on-surface-variant font-headline-md text-[16px] uppercase px-8 py-3 rounded-sm border border-outline-variant hover:border-primary transition-colors flex items-center justify-center gap-2 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="index.php"><span class="material-symbols-outlined">home_app_logo</span><span class="font-label-sm uppercase text-xs">Home</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span><span class="font-label-sm uppercase text-xs">Salidas</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="actas.php"><span class="material-symbols-outlined">description</span><span class="font-label-sm uppercase text-xs">Actas</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="estatutos.php"><span class="material-symbols-outlined">gavel</span><span class="font-label-sm uppercase text-xs">Estatutos</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="admin_usuarios.php"><span class="material-symbols-outlined">groups</span><span class="font-label-sm uppercase text-xs">Socios</span></a>
</nav>

</body>
</html>