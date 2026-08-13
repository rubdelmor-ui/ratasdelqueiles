<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin puede subir actas
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

$error_mensaje = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha_reunion'];
    $asistentes = $_POST['asistentes'];
    $autor = $_POST['autor'];
    $nombre_unico = ''; // Dejamos string vacío por defecto si no sube nada

    if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
        $archivo = $_FILES['archivo_pdf'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension == 'pdf') {
            // --- CLOUDINARY UPLOAD ---
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            
            // Firma y configuración correcta
            $signature = sha1("folder=ratas_actas_pdf&timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/raw/upload");
            // Pasamos los datos extra para que Cloudinary procese bien el archivo
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
            // Omitimos curl_close($ch); para evitar el aviso de obsolescencia de PHP 8.5
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_unico = $json['secure_url']; // URL de Cloudinary
            } else {
                $detalle = isset($json['error']['message']) ? $json['error']['message'] : 'Respuesta desconocida';
                $error_mensaje = "Cloudinary ha rechazado el PDF. Motivo: " . $detalle;
            }
        } else {
            $error_mensaje = "Solo se permiten archivos PDF.";
        }
    }

    if (!$error_mensaje) {
        // CORRECCIÓN: Añadimos 'texto_acta' a la consulta y le pasamos un string vacío ('')
        $sql = "INSERT INTO actas (titulo, fecha_reunion, autor, firmas, archivo_pdf, texto_acta) 
                VALUES ('$titulo', '$fecha', '$autor', '$asistentes', '$nombre_unico', '')";
        
        if ($conexion->query($sql) === TRUE) {
            $conexion->query("UPDATE configuracion SET valor = NOW() WHERE clave = 'ultima_acta'");
            if ($conexion->affected_rows == 0) {
                $conexion->query("INSERT INTO configuracion (clave, valor) VALUES ('ultima_acta', NOW())");
            }
            header("Location: actas.php");
            exit;
        } else {
            $error_mensaje = "Error en base de datos: " . $conexion->error;
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Acta - Ratas del Queiles</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: { extend: { colors: { "primary": "#ffb59e", "surface-container": "#201f1f", "surface-container-high": "#2a2a2a", "outline-variant": "#5c4037", "background": "#131313", "on-background": "#e5e2e1", "primary-container": "#ff5719" } } }
        }
    </script>
    <style>
        .input-dark { background-color: #1a1a1a; border: 1px solid rgba(255, 255, 255, 0.1); color: #e5e2e1; padding: 0.6rem 0.8rem; border-radius: 0.25rem; width: 100%; }
        .input-dark:focus { outline: none; border-color: #ffb59e; }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen p-6">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold uppercase mb-6 text-primary">➕ Nueva Acta</h2>
        
        <?php if($error_mensaje): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4 font-bold border border-red-500/30">
                <?php echo $error_mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="bg-surface-container rounded-xl border border-outline-variant p-6">
            <form action="nueva_acta.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="label-dark">Título *</label>
                    <input type="text" name="titulo" class="input-dark" placeholder="Ej: Acta Asamblea General 2026" required>
                </div>
                <div>
                    <label class="label-dark">Fecha de Reunión *</label>
                    <input type="date" name="fecha_reunion" value="<?php echo date('Y-m-d'); ?>" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark">Número de asistentes (Firmas) *</label>
                    <input type="number" name="asistentes" min="0" value="0" class="input-dark" required>
                </div>
                <div>
                    <label class="label-dark">Autor / Secretario</label>
                    <input type="text" name="autor" class="input-dark" placeholder="Quien redacta el acta">
                </div>
                <div>
                    <label class="label-dark">Subir PDF (opcional pero recomendado)</label>
                    <input type="file" name="archivo_pdf" accept=".pdf" class="input-dark">
                </div>

                <div class="flex gap-4 pt-4 border-t border-outline-variant">
                    <button type="submit" class="bg-primary-container text-black font-bold uppercase px-6 py-2 rounded">✅ Guardar Acta</button>
                    <a href="actas.php" class="bg-surface-container-high text-white font-bold uppercase px-6 py-2 rounded border border-outline-variant text-center">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>