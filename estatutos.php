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

// =========================================================================
// 1. PROCESAR SUBIDA DE NUEVOS ESTATUTOS (Solo Superadmin)
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && $es_superadmin) {
    if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
        $archivo = $_FILES['archivo_pdf'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension == 'pdf') {
            // --- CLOUDINARY UPLOAD ---
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            
            // CORREGIDO: Añadido folder=ratas_estatutos a la firma
            $signature = sha1("folder=ratas_estatutos&timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/raw/upload");
            $cfile = new CURLFile($archivo['tmp_name']);
            $data = [
                'file' => $cfile, 'api_key' => $api_key, 'timestamp' => $timestamp,
                'signature' => $signature, 'folder' => 'ratas_estatutos'
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // CORREGIDO: Evita errores SSL
            $respuesta = curl_exec($ch);
            curl_close($ch);
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_unico = $json['secure_url']; // URL de Cloudinary
                
                $sql_update = "UPDATE configuracion SET valor = '$nombre_unico' WHERE clave = 'estatutos_pdf'";
                if ($conexion->query($sql_update) === TRUE) {
                    if ($conexion->affected_rows == 0) {
                        $conexion->query("INSERT INTO configuracion (clave, valor) VALUES ('estatutos_pdf', '$nombre_unico')");
                    }
                    header("Location: estatutos.php?exito=1");
                    exit;
                } else {
                    $error_mensaje = "Error al actualizar la base de datos: " . $conexion->error;
                }
            } else {
                $error_mensaje = "Error al subir a Cloudinary.";
            }
        } else {
            $error_mensaje = "Error: solo se permiten archivos PDF.";
        }
    }
}

// =========================================================================
// 2. OBTENER ESTATUTOS ACTUALES
// =========================================================================
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
                extend: { colors: { "primary": "#ffb59e", "surface-container": "#201f1f", "surface-container-high": "#2a2a2a", "outline-variant": "#5c4037", "background": "#131313", "on-background": "#e5e2e1", "secondary": "#c6c6c6" } }
            }
        }
    </script>
    <style>
        .noise-bg { position: relative; }
        .noise-bg::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.05; z-index: -1; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); pointer-events: none; }
        .chrome-border { border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-pdf-estatutos { background: #dc3545; color: white; padding: 10px 24px; border-radius: 4px; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-pdf-estatutos:hover { background: #b02a37; }
        .btn-subir { background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; }
        .btn-subir:hover { background: #218838; }
        .superadmin-tag { background: #ffaa00; color: #000; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.7rem; margin-left: 5px; }
        .input-dark { background-color: #1a1a1a; border: 1px solid rgba(255, 255, 255, 0.1); color: #e5e2e1; padding: 0.6rem; border-radius: 0.25rem; width: 100%; font-family: 'Hanken Grotesk', sans-serif; font-size: 1rem; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-bold uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div><a href="index.php" class="text-secondary hover:text-primary text-sm font-bold uppercase">⬅ Volver al inicio</a></div>
</header>

<main class="flex-grow p-4 md:p-6 max-w-4xl mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold uppercase">Estatutos del Club</h2>
            <p class="text-secondary mt-1 uppercase text-sm">Normas fundamentales y código de conducta.</p>
        </div>
        <?php if($es_superadmin): ?>
            <div class="mt-4 md:mt-0"><span class="superadmin-tag">👑 SUPERADMIN</span></div>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['exito'])): ?>
        <div class="bg-green-900/50 text-green-200 p-4 rounded mt-4 text-sm border border-green-500/30">✅ Estatutos actualizados correctamente.</div>
    <?php endif; ?>
    <?php if(isset($error_mensaje)): ?>
        <div class="bg-red-900/50 text-red-200 p-4 rounded mt-4 text-sm border border-red-500/30"><?php echo $error_mensaje; ?></div>
    <?php endif; ?>

    <div class="bg-surface-container rounded-xl chrome-border p-6 text-center mt-6">
        <?php 
        if (!empty($pdf_estatutos) && strpos($pdf_estatutos, 'http') === 0): 
        ?>
            <span class="material-symbols-outlined text-6xl text-primary">picture_as_pdf</span>
            <h3 class="text-xl font-bold mt-4">Estatutos disponibles</h3>
            <p class="text-secondary text-sm mt-2">Guardados en la nube segura</p>
            <a href="<?php echo $pdf_estatutos; ?>" target="_blank" class="btn-pdf-estatutos mt-4">📄 Ver Estatutos</a>
        <?php else: ?>
            <span class="material-symbols-outlined text-6xl text-secondary">description</span>
            <h3 class="text-xl font-bold mt-4">No hay estatutos disponibles</h3>
            <p class="text-secondary text-sm">El documento aún no ha sido subido a la nube por la administración.</p>
        <?php endif; ?>
    </div>

    <?php if ($es_superadmin): ?>
        <div class="bg-surface-container rounded-xl chrome-border p-6 mt-6">
            <h3 class="font-bold text-primary uppercase mb-4">🛠️ Gestión de Estatutos</h3>
            <form action="estatutos.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                <div>
                    <label class="block text-secondary text-xs uppercase font-bold mb-2" for="archivo_pdf">Selecciona un nuevo archivo PDF:</label>
                    <input type="file" name="archivo_pdf" accept=".pdf" required class="input-dark">
                </div>
                <button type="submit" class="btn-subir">📤 Subir a la Nube / Actualizar</button>
            </form>
        </div>
    <?php endif; ?>
</main>
<script>
    if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('sw.js'); }); }
</script>
</body>
</html>