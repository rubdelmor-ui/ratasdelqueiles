<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin puede editar actas
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

$error_mensaje = null;

// =========================================================================
// 1. PROCESAR EL FORMULARIO
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha_reunion'];
    $asistentes = $_POST['asistentes'];
    $autor = $_POST['autor'];
    $archivo_antiguo = $_POST['archivo_antiguo'];

    $nombre_unico = $archivo_antiguo; // Mantenemos el antiguo por defecto

    // Verificar si se subió un PDF nuevo
    if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
        $archivo = $_FILES['archivo_pdf'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if ($extension == 'pdf') {
            // --- CLOUDINARY UPLOAD ---
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            $signature = sha1("folder=ratas_actas_pdf&timestamp=" . $timestamp . $api_secret);

            // Cambiamos a 'auto/upload' y pasamos los datos exactos del archivo (nombre y tipo)
            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/raw/upload");
            $cfile = new CURLFile($archivo['tmp_name'], $archivo['type'], $archivo['name']);
            $data = [
                'file' => $cfile, 'api_key' => $api_key, 'timestamp' => $timestamp,
                'signature' => $signature, 'folder' => 'ratas_actas_pdf'
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $respuesta = curl_exec($ch);
            
            // Ya no usamos curl_close($ch); para evitar el aviso 'Deprecated'
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_unico = $json['secure_url']; // Guardamos la URL segura
            } else {
                // Capturamos el error exacto de Cloudinary para saber qué pasa
                $detalle = isset($json['error']['message']) ? $json['error']['message'] : 'Respuesta desconocida';
                $error_mensaje = "Cloudinary ha rechazado el PDF. Motivo: " . $detalle;
            }
        } else {
            $error_mensaje = "Solo se permiten archivos PDF.";
        }
    }

    if (!$error_mensaje) {
        $sql_update = "UPDATE actas SET 
                titulo = '$titulo', 
                fecha_reunion = '$fecha', 
                autor = '$autor', 
                firmas = '$asistentes', 
                archivo_pdf = '$nombre_unico' 
                WHERE id = $id";

        if ($conexion->query($sql_update) === TRUE) {
            header("Location: actas.php");
            exit;
        } else {
            $error_mensaje = "Error al actualizar: " . $conexion->error;
        }
    }
}

// =========================================================================
// 2. CARGAR DATOS PARA EL FORMULARIO
// =========================================================================
// Ahora cargamos la información tanto si venimos de 'actas.php' (GET) 
// como si hemos fallado al subir por POST, para que no salte error de variable indefinida.
if (isset($_GET['id'])) {
    $id_buscar = intval($_GET['id']);
} else if (isset($_POST['id'])) {
    $id_buscar = intval($_POST['id']);
} else {
    header("Location: actas.php");
    exit;
}

$sql = "SELECT * FROM actas WHERE id = $id_buscar";
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
if (!$fila) {
    header("Location: actas.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Acta - Ratas del Queiles</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ffb59e", "surface-container": "#201f1f", "surface-container-high": "#2a2a2a", "outline-variant": "#5c4037", "background": "#131313", "on-background": "#e5e2e1", "primary-container": "#ff5719" } } }
        }
    </script>
    <style>
    .input-dark { 
        background-color: #1a1a1a !important; 
        border: 1px solid rgba(255, 255, 255, 0.1) !important; 
        color: #ffffff !important; 
        padding: 0.6rem 0.8rem !important; 
        border-radius: 0.25rem !important; 
        width: 100% !important; 
        font-family: 'Hanken Grotesk', sans-serif !important;
    }
    .input-dark:focus { 
        outline: none !important; 
        border-color: #ffb59e !important; 
        box-shadow: 0 0 0 2px rgba(255,181,158,0.2) !important;
    }
    .input-dark::-webkit-calendar-picker-indicator {
        filter: invert(1) !important;
    }
    .label-dark { 
        display: block; 
        color: #b0b0b0; 
        font-family: 'JetBrains Mono', monospace; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        margin-bottom: 0.25rem; 
    }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen p-6">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold uppercase mb-6 text-primary">✏️ Editar Acta</h2>
        
        <?php if($error_mensaje): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4 border border-red-500/30 font-bold">
                <?php echo $error_mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="bg-surface-container rounded-xl border border-outline-variant p-6">
            <form action="editar_acta.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                <input type="hidden" name="archivo_antiguo" value="<?php echo htmlspecialchars($fila['archivo_pdf'] ?? ''); ?>">

                <div>
                    <label class="label-dark">Título *</label>
                    <input type="text" name="titulo" value="<?php echo htmlspecialchars($fila['titulo'] ?? ''); ?>" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark">Fecha *</label>
                    <input type="date" name="fecha_reunion" value="<?php echo $fila['fecha_reunion'] ?? ''; ?>" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark">Número de asistentes (Firmas) *</label>
                    <input type="number" name="asistentes" value="<?php echo $fila['firmas'] ?? '0'; ?>" min="0" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark">Autor</label>
                    <input type="text" name="autor" value="<?php echo htmlspecialchars($fila['autor'] ?? ''); ?>" class="input-dark">
                </div>
                
                <div class="bg-surface-container-high p-4 rounded mt-4">
                    <label class="label-dark text-primary">PDF Actual</label>
                    <?php if (!empty($fila['archivo_pdf'])): ?>
                        <p class="text-sm truncate">📄 <?php echo (strpos($fila['archivo_pdf'], 'http') === 0) ? 'Guardado en la Nube' : htmlspecialchars($fila['archivo_pdf']); ?></p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">No hay PDF asociado.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="label-dark">Reemplazar PDF (opcional)</label>
                    <input type="file" name="archivo_pdf" accept=".pdf" class="input-dark">
                </div>

                <div class="flex gap-4 pt-4 border-t border-outline-variant">
                    <button type="submit" class="bg-primary-container text-black font-bold uppercase px-6 py-2 rounded">💾 Actualizar Acta</button>
                    <a href="actas.php" class="bg-surface-container-high text-white font-bold uppercase px-6 py-2 rounded text-center border border-outline-variant">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 