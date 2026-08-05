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
    $imagen_actual = $fila['imagen'] ?? null;
} else {
    $contenido_actual = '';
    $imagen_actual = null;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Home - Ratas del Queiles</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
        textarea.input-dark { resize: vertical; min-height: 120px; }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .btn-guardar { background: #28a745; color: white; font-weight: bold; border: none; padding: 0.75rem 2rem; border-radius: 0.25rem; cursor: pointer; transition: background 0.2s; }
        .btn-guardar:hover { background: #218838; }
        .btn-cancelar { background: #dc3545; color: white; font-weight: bold; border: none; padding: 0.75rem 2rem; border-radius: 0.25rem; cursor: pointer; transition: background 0.2s; text-decoration: none; display: inline-block; text-align: center; }
        .btn-cancelar:hover { background: #c82333; }
        .imagen-actual { max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-top: 8px; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-margin-mobile py-unit h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
</header>

<aside class="hidden md:flex flex-col fixed left-0 top-16 bottom-0 w-64 bg-surface-container border-r border-outline-variant p-4 z-40">
    <nav class="flex flex-col gap-2 mt-4">
        <a class="flex items-center gap-3 text-primary-container bg-on-primary-container/10 rounded-lg p-3" href="index.php"><span class="material-symbols-outlined" style="font-variation-settings:'FILL'1;">home_app_logo</span> Home</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span> Salidas</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="actas.php"><span class="material-symbols-outlined">description</span> Actas</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="estatutos.php"><span class="material-symbols-outlined">gavel</span> Estatutos</a>
        <a class="flex items-center gap-3 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg p-3" href="admin_usuarios.php"><span class="material-symbols-outlined">groups</span> Socios</a>
    </nav>
</aside>

<main class="flex-grow p-margin-mobile flex flex-col gap-gutter pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">✏️ Editar contenido de la Home</h2>
            <p class="text-on-surface-variant max-w-xl font-label-md text-label-md uppercase">Modifica el texto de bienvenida y la imagen que se muestra en la página principal.</p>
        </div>
        <div class="mt-4 md:mt-0">
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
        <form action="guardar_home.php" method="POST" enctype="multipart/form-data">
            <div>
                <label class="label-dark" for="contenido">Texto de bienvenida</label>
                <textarea name="contenido" id="contenido" class="input-dark" rows="6" placeholder="Escribe aquí el texto que aparecerá debajo del saludo..."><?php echo htmlspecialchars($contenido_actual); ?></textarea>
                <p class="text-secondary text-xs mt-1">Puedes usar saltos de línea para separar párrafos.</p>
            </div>

            <div class="mt-4">
                <label class="label-dark">Imagen actual</label>
                <?php if (!empty($imagen_actual) && file_exists('images/home/' . $imagen_actual)): ?>
                    <img src="images/home/<?php echo $imagen_actual; ?>" class="imagen-actual" alt="Imagen actual">
                    <p class="text-secondary text-sm mt-1"><?php echo $imagen_actual; ?></p>
                <?php else: ?>
                    <p class="text-secondary text-sm">No hay imagen configurada.</p>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <label class="label-dark" for="imagen">Subir nueva imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño recomendado: 1200x400px.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30 mt-4">
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

<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-unit pb-safe h-20 bg-surface-container border-t border-outline-variant">
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="index.php"><span class="material-symbols-outlined">home_app_logo</span><span class="font-label-sm uppercase text-xs">Home</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="salidas.php"><span class="material-symbols-outlined">motorcycle</span><span class="font-label-sm uppercase text-xs">Salidas</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="actas.php"><span class="material-symbols-outlined">description</span><span class="font-label-sm uppercase text-xs">Actas</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="estatutos.php"><span class="material-symbols-outlined">gavel</span><span class="font-label-sm uppercase text-xs">Estatutos</span></a>
    <a class="flex flex-col items-center justify-center text-on-surface-variant p-1" href="admin_usuarios.php"><span class="material-symbols-outlined">groups</span><span class="font-label-sm uppercase text-xs">Socios</span></a>
</nav>

</body>
</html>