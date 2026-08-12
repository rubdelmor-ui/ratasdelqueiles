<?php
session_start();
include 'conexion.php';

$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM home_content WHERE id = $id";
$res = $conexion->query($sql);
$bloque = $res->fetch_assoc();
if (!$bloque) {
    header("Location: admin_home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $url = $_POST['url'] ?? '';
    $imagen = $_POST['imagen_actual'] ?? ''; // Mantiene la URL de Cloudinary actual por defecto

    // --- MANEJO DE NUEVA IMAGEN A CLOUDINARY ---
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $archivo = $_FILES['imagen'];
        $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
        $api_key = getenv('CLOUDINARY_API_KEY');
        $api_secret = getenv('CLOUDINARY_API_SECRET');
        $timestamp = time();
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $respuesta = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($respuesta, true);
        if (isset($json['secure_url'])) {
            $imagen = $json['secure_url']; // Nueva URL segura de Cloudinary
        }
    }

    $titulo = $conexion->real_escape_string($titulo);
    $contenido = $conexion->real_escape_string($contenido);
    $url = $conexion->real_escape_string($url);

    $sql = "UPDATE home_content SET 
            tipo = '$tipo', 
            titulo = '$titulo', 
            contenido = '$contenido', 
            url = '$url', 
            imagen = '$imagen' 
            WHERE id = $id";
    $conexion->query($sql);
    header("Location: admin_home.php");
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Bloque - Ratas del Queiles</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@600;700;800&family=Hanken+Grotesk:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body { background: #131313; color: #e5e2e1; font-family: 'Hanken Grotesk', sans-serif; min-height: 100vh; }
        .input-dark {
            background-color: #1a1a1a;
            border: 1px solid rgba(255,255,255,0.1);
            color: #e5e2e1;
            padding: 0.6rem 0.8rem;
            border-radius: 0.25rem;
            width: 100%;
            transition: border-color 0.2s;
        }
        .input-dark:focus { outline: none; border-color: #ffb59e; }
        .btn-primary { background: #ff5719; color: #000; border: 2px solid #000; padding: 0.5rem 1rem; border-radius: 0.25rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-primary:hover { background: #ffb59e; }
        .chrome-border { border: 1px solid rgba(255,255,255,0.1); }
        .bg-surface { background: #201f1f; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-surface rounded-xl chrome-border p-6 max-w-2xl w-full">
        <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">✏️ Editar bloque</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($bloque['imagen']); ?>">
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="input-dark">
                    <option value="texto" <?php if($bloque['tipo']=='texto') echo 'selected'; ?>>Texto</option>
                    <option value="imagen" <?php if($bloque['tipo']=='imagen') echo 'selected'; ?>>Imagen</option>
                    <option value="enlace" <?php if($bloque['tipo']=='enlace') echo 'selected'; ?>>Enlace</option>
                    <option value="noticia" <?php if($bloque['tipo']=='noticia') echo 'selected'; ?>>Noticia</option>
                </select>
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" class="input-dark" value="<?php echo htmlspecialchars($bloque['titulo']); ?>">
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="contenido">Contenido</label>
                <textarea name="contenido" id="contenido" rows="4" class="input-dark"><?php echo htmlspecialchars($bloque['contenido']); ?></textarea>
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="url">URL (para enlaces)</label>
                <input type="text" name="url" id="url" class="input-dark" value="<?php echo htmlspecialchars($bloque['url']); ?>">
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1">Imagen actual</label>
                <?php if (!empty($bloque['imagen']) && strpos($bloque['imagen'], 'http') === 0): ?>
                    <img src="<?php echo $bloque['imagen']; ?>" class="max-h-40 rounded border border-outline-variant mt-2">
                <?php else: ?>
                    <p class="text-secondary text-sm">Sin imagen en la nube</p>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="imagen">Cambiar imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept="image/*" class="input-dark" style="padding: 0.5rem;">
            </div>
            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn-primary flex-1">💾 Guardar cambios</button>
                <a href="admin_home.php" class="btn-primary flex-1 text-center bg-transparent border border-outline-variant hover:border-primary text-on-surface-variant">Cancelar</a>
            </div>
        </form>
    </div>
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