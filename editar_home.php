<?php
session_start();
include 'conexion.php';

// 🔐 Solo superadmin puede editar
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

// =========================================================================
// 1. PROCESAR EL FORMULARIO (POST)
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contenido = $_POST['contenido'];
    $texto_imagen = $_POST['texto_imagen'] ?? '';

    // Obtener imagen actual de la BD
    $sql_img = "SELECT imagen FROM contenido_home WHERE seccion = 'bienvenida'";
    $res_img = $conexion->query($sql_img);
    $imagen_actual = null;
    if ($res_img && $res_img->num_rows > 0) {
        $fila_img = $res_img->fetch_assoc();
        $imagen_actual = $fila_img['imagen'];
    }

    $nombre_imagen = $imagen_actual; // Mantener por defecto

    // Manejar subida a Cloudinary
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $tipos_permitidos)) {
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            
            // CORREGIDO: Incluir la carpeta 'ratas_home' dentro de la firma de seguridad
            $signature = sha1("folder=ratas_home&timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
            $cfile = new CURLFile($archivo['tmp_name']);
            $data = [
                'file' => $cfile, 'api_key' => $api_key, 'timestamp' => $timestamp,
                'signature' => $signature, 'folder' => 'ratas_home'
            ];
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita errores SSL en local
            $respuesta = curl_exec($ch);
            curl_close($ch);
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_imagen = $json['secure_url']; 
            } else {
                $error_mensaje = "Error al subir la imagen a la nube.";
            }
        } else {
            $error_mensaje = "Formato de imagen no permitido.";
        }
    }

    if (!isset($error_mensaje)) {
        // Evitar inyección SQL
        $contenido = $conexion->real_escape_string($contenido);
        $texto_imagen = $conexion->real_escape_string($texto_imagen);

        // Actualizar o insertar
        $sql_update = "UPDATE contenido_home SET 
                contenido = '$contenido',
                texto_imagen = '$texto_imagen',
                imagen = '$nombre_imagen'
                WHERE seccion = 'bienvenida'";

        if ($conexion->query($sql_update) === TRUE) {
            if ($conexion->affected_rows == 0) {
                $sql_insert = "INSERT INTO contenido_home (seccion, contenido, texto_imagen, imagen) VALUES ('bienvenida', '$contenido', '$texto_imagen', '$nombre_imagen')";
                $conexion->query($sql_insert);
            }
            header("Location: editar_home.php?ok=1");
            exit;
        } else {
            $error_mensaje = "Error al guardar: " . $conexion->error;
        }
    }
}

// =========================================================================
// 2. CARGAR DATOS PARA EL FORMULARIO (GET)
// =========================================================================
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
                        "primary": "#ffb59e",
                        "error": "#ffb4ab",
                        "surface-container": "#201f1f",
                        "primary-container": "#ff5719",
                        "outline-variant": "#5c4037",
                        "surface-container-high": "#2a2a2a",
                        "background": "#131313",
                        "on-background": "#e5e2e1",
                        "secondary": "#c6c6c6"
                    }
                }
            }
        }
    </script>
    <style>
        .noise-bg { position: relative; }
        .noise-bg::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.05; z-index: -1; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); pointer-events: none; }
        .chrome-border { border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-dark { background-color: #0d0d0d; border: 1px solid rgba(255, 255, 255, 0.15); color: #ffffff; padding: 0.6rem 0.8rem; border-radius: 0.25rem; width: 100%; font-family: 'Hanken Grotesk', sans-serif; }
        .input-dark:focus { outline: none; border-color: #ffb59e; box-shadow: 0 0 0 2px rgba(255,181,158,0.2); }
        textarea.input-dark { resize: vertical; min-height: 120px; }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; margin-bottom: 0.25rem; }
        .imagen-actual { max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-top: 8px; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-bold uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div>
        <a href="index.php" class="text-secondary hover:text-primary text-sm font-bold uppercase">⬅ Volver al inicio</a>
    </div>
</header>

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-4xl mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold uppercase">✏️ Editar contenido Home</h2>
            <p class="text-secondary text-sm mt-1 uppercase">Modifica el texto de bienvenida y la imagen principal.</p>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <?php if (isset($_GET['ok'])): ?>
            <div class="bg-green-900/50 border border-green-500/30 text-green-200 rounded-lg px-4 py-3 mb-6 flex items-center gap-2 text-sm">
                ✅ Contenido actualizado correctamente.
            </div>
        <?php endif; ?>
        <?php if(isset($error_mensaje)): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4 text-sm border border-red-500/30"><?php echo $error_mensaje; ?></div>
        <?php endif; ?>

        <form action="editar_home.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="label-dark" for="contenido">Texto de bienvenida</label>
                <textarea name="contenido" id="contenido" class="input-dark" rows="6"><?php echo htmlspecialchars($contenido_actual); ?></textarea>
            </div>
            <div>
                <label class="label-dark" for="texto_imagen">Texto encima de la imagen</label>
                <textarea name="texto_imagen" id="texto_imagen" class="input-dark" rows="4"><?php echo htmlspecialchars($texto_imagen_actual); ?></textarea>
            </div>

            <div class="bg-surface-container-high p-4 rounded mt-4">
                <label class="label-dark text-primary">Imagen actual</label>
                <?php if (!empty($imagen_actual) && strpos($imagen_actual, 'http') === 0): ?>
                    <img src="<?php echo $imagen_actual; ?>" class="imagen-actual" alt="Imagen actual">
                    <p class="text-sm text-secondary mt-2">Guardada en la Nube</p>
                <?php else: ?>
                    <p class="text-sm text-secondary">No hay imagen en la nube.</p>
                <?php endif; ?>
            </div>

            <div>
                <label class="label-dark" for="imagen">Subir nueva imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 bg-primary-container text-black font-bold uppercase px-6 py-3 rounded border-2 border-black hover:bg-primary transition-colors">
                    💾 Guardar Cambios
                </button>
                <a href="index.php" class="flex-1 bg-surface-container-high text-white font-bold uppercase px-6 py-3 rounded border border-outline-variant hover:border-primary transition-colors text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</main>
<script>
    if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('sw.js'); }); }
</script>
</body>
</html>