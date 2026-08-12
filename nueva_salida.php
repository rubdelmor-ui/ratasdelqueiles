<?php
session_start();
include 'conexion.php';

// 🔐 Solo el admin (superadmin) puede crear salidas[cite: 38]
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

// =========================================================================
// 1. PROCESAR EL FORMULARIO (Si se ha enviado por POST)[cite: 39]
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destino = $_POST['destino'];
    $fecha = $_POST['fecha_salida'];
    $hora = $_POST['hora_quedada'];
    $punto = $_POST['punto_encuentro'];
    $descripcion = $_POST['descripcion'];
    $responsable = $_POST['responsable'];

    $nombre_imagen = NULL;

    // Manejar subida de nueva imagen a Cloudinary[cite: 39]
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($extension, $tipos_permitidos)) {
            // Credenciales de Cloudinary
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            $signature = sha1("timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
            $cfile = new CURLFile($archivo['tmp_name']);
            
            $data = [
                'file' => $cfile,
                'api_key' => $api_key,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => 'ratas_salidas' // Carpeta en Cloudinary
            ];

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
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
        // Insertar en base de datos[cite: 39]
        // Evitamos inyección SQL
        $destino = $conexion->real_escape_string($destino);
        $punto = $conexion->real_escape_string($punto);
        $descripcion = $conexion->real_escape_string($descripcion);
        $responsable = $conexion->real_escape_string($responsable);

        $sql = "INSERT INTO salidas (destino, fecha_salida, hora_quedada, punto_encuentro, descripcion, imagen, responsable) 
                VALUES ('$destino', '$fecha', '$hora', '$punto', '$descripcion', '$nombre_imagen', '$responsable')";

        if ($conexion->query($sql) === TRUE) {
            header("Location: salidas.php");
            exit;
        } else {
            $error_mensaje = "Error al guardar en base de datos: " . $conexion->error;
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Salida - Ratas del Queiles</title>
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
                        "on-background": "#e5e2e1"
                    }
                }
            }
        }
    </script>
    <style>
        .noise-bg { position: relative; }
        .noise-bg::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.05; z-index: -1; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); pointer-events: none; }
        .chrome-border { border: 1px solid rgba(255, 255, 255, 0.1); }
        .input-dark { background-color: #1a1a1a; border: 1px solid rgba(255, 255, 255, 0.1); color: #e5e2e1; padding: 0.6rem 0.8rem; border-radius: 0.25rem; width: 100%; font-family: 'Hanken Grotesk', sans-serif; }
        .input-dark:focus { outline: none; border-color: #ffb59e; box-shadow: 0 0 0 2px rgba(255,181,158,0.2); }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<!-- Header básico simplificado -->
<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-bold uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div>
        <a href="logout.php" class="text-secondary hover:text-primary text-sm font-bold uppercase">Cerrar Sesión</a>
    </div>
</header>

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-4xl mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="text-2xl font-bold uppercase tracking-tight">📝 Nueva Salida</h2>
            <p class="text-secondary text-sm uppercase mt-1">Crea una nueva ruta para los socios.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="salidas.php" class="text-secondary hover:text-primary transition-colors font-bold uppercase text-sm inline-flex items-center gap-1">
                ⬅ Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        
        <?php if(isset($error_mensaje)): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4 text-sm border border-red-500/30"><?php echo $error_mensaje; ?></div>
        <?php endif; ?>

        <!-- Fíjate que el action ahora apunta a este mismo archivo -->
        <form action="nueva_salida.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="label-dark" for="destino">Destino *</label>
                <input type="text" name="destino" id="destino" class="input-dark" placeholder="Ej: Sierra Nevada" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-dark" for="fecha_salida">Fecha *</label>
                    <input type="date" name="fecha_salida" id="fecha_salida" value="<?php echo date('Y-m-d'); ?>" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark" for="hora_quedada">Hora *</label>
                    <input type="time" name="hora_quedada" id="hora_quedada" value="09:00" class="input-dark" required>
                </div>
            </div>
            <div>
                <label class="label-dark" for="punto_encuentro">Punto de encuentro</label>
                <input type="text" name="punto_encuentro" id="punto_encuentro" class="input-dark" placeholder="Ej: Gasolinera Norte">
            </div>
            <div>
                <label class="label-dark" for="descripcion">Descripción / Ruta</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="input-dark" placeholder="Detalles de la ruta, paradas..."></textarea>
            </div>
            <div>
                <label class="label-dark" for="responsable">Responsable de la salida</label>
                <input type="text" name="responsable" id="responsable" class="input-dark" placeholder="Ej: Juan Pérez">
            </div>
            <div>
                <label class="label-dark" for="imagen">Imagen de la salida (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept=".jpg,.jpeg,.png,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos permitidos: JPG, PNG, WEBP.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 bg-primary-container text-black font-bold text-[16px] uppercase px-8 py-3 rounded-sm border-2 border-black hover:bg-primary transition-colors flex items-center justify-center gap-2">
                    ✅ Guardar Salida
                </button>
                <a href="salidas.php" class="flex-1 bg-surface-container-high text-white font-bold text-[16px] uppercase px-8 py-3 rounded-sm border border-outline-variant hover:border-primary transition-colors flex items-center justify-center gap-2 text-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</main>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => { navigator.serviceWorker.register('sw.js'); });
    }
</script>
</body>
</html>