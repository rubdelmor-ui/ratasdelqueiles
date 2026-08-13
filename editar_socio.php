<?php
session_start();
include 'conexion.php';

// Solo superadmin puede editar
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: admin_usuarios.php");
    exit;
}

// =========================================================================
// 1. PROCESAR EL FORMULARIO (POST)
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $cargo = $_POST['cargo'] ?? '';
    $aprobado = intval($_POST['aprobado']);

    // Si es superadmin, permite cambiar rol; si no, mantiene el rol actual
    if ($es_superadmin) {
        $rol = $_POST['rol'];
    } else {
        $sql_rol = "SELECT rol FROM usuarios WHERE id = $id";
        $res_rol = $conexion->query($sql_rol);
        $fila_rol = $res_rol->fetch_assoc();
        $rol = $fila_rol['rol'];
    }

    // Manejar foto: obtenemos la actual primero
    $sql_foto = "SELECT foto FROM usuarios WHERE id = $id";
    $res_foto = $conexion->query($sql_foto);
    $fila_foto = $res_foto->fetch_assoc();
    $nombre_foto = $fila_foto['foto'] ?? null;

    // Subida a Cloudinary
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $archivo = $_FILES['foto'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $tipos_permitidos)) {
            // --- CLOUDINARY UPLOAD ---
            $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
            $api_key = getenv('CLOUDINARY_API_KEY');
            $api_secret = getenv('CLOUDINARY_API_SECRET');
            $timestamp = time();
            
            // CORREGIDO: Firma de seguridad y carpeta
            $signature = sha1("folder=ratas_perfiles&timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
            $cfile = new CURLFile($archivo['tmp_name']);
            $data = [
                'file' => $cfile, 'api_key' => $api_key, 'timestamp' => $timestamp,
                'signature' => $signature, 'folder' => 'ratas_perfiles'
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita error SSL local
            $respuesta = curl_exec($ch);
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_foto = $json['secure_url']; // Guardamos la URL segura
            }
        }
    }

    // Construir consulta SQL (Sin intentar editar la pregunta de seguridad secreta)
    $sql_update = "UPDATE usuarios SET 
            nombre = '$nombre',
            email = '$email',
            rol = '$rol',
            cargo = '$cargo',
            aprobado = $aprobado,
            foto = '$nombre_foto'
            WHERE id = $id";

    if ($conexion->query($sql_update) === TRUE) {
        header("Location: admin_usuarios.php");
        exit;
    } else {
        $error_mensaje = "Error al actualizar: " . $conexion->error;
    }
}

// =========================================================================
// 2. CARGAR DATOS PARA EL FORMULARIO (GET)
// =========================================================================
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM usuarios WHERE id = $id";
    $resultado = $conexion->query($sql);
    $fila = $resultado->fetch_assoc();
    if (!$fila) {
        header("Location: admin_usuarios.php");
        exit;
    }
} else if (!isset($_POST['id'])) {
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
        .input-dark {
            background-color: #0d0d0d;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 0.6rem 0.8rem;
            border-radius: 0.25rem;
            width: 100%;
            transition: border-color 0.2s;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 1rem;
        }
        .input-dark:focus { outline: none; border-color: #ffb59e; box-shadow: 0 0 0 2px rgba(255,181,158,0.2); }
        select.input-dark option { background-color: #0d0d0d; color: #ffffff; }
        .foto-actual { max-width: 120px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.1); }
        .label-dark { display: block; color: #b0b0b0; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col noise-bg">

<!-- ===== HEADER ===== -->
<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-4 py-2 h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-bold text-xl uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div class="flex items-center gap-3">
        <?php
        $foto_usuario = null;
        if (isset($_SESSION['usuario_id'])) {
            $sql_foto = "SELECT foto FROM usuarios WHERE id = " . intval($_SESSION['usuario_id']);
            $res_foto = $conexion->query($sql_foto);
            if ($res_foto && $res_foto->num_rows > 0) {
                $row_foto = $res_foto->fetch_assoc();
                $foto_usuario = $row_foto['foto'];
            }
        }
        ?>
        <?php if (!empty($foto_usuario) && strpos($foto_usuario, 'http') === 0): ?>
            <img src="<?php echo $foto_usuario; ?>" alt="Foto" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.1);">
        <?php else: ?>
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='18' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" alt="Sin foto" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.1);">
        <?php endif; ?>
    </div>
</header>

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-4xl mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="text-2xl font-bold uppercase tracking-tight">✏️ Editar Socio</h2>
            <p class="text-secondary mt-1">Modifica los datos del socio.</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="admin_usuarios.php" class="text-secondary hover:text-primary transition-colors font-bold uppercase text-sm inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <?php if(isset($error_mensaje)): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4"><?php echo $error_mensaje; ?></div>
        <?php endif; ?>

        <form action="editar_socio.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">

            <div>
                <label class="label-dark" for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($fila['nombre']); ?>" class="input-dark" required>
            </div>
            <div>
                <label class="label-dark" for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($fila['email']); ?>" class="input-dark" required>
            </div>

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

            <div>
                <label class="label-dark" for="cargo">Cargo (solo Junta)</label>
                <input type="text" name="cargo" id="cargo" value="<?php echo htmlspecialchars($fila['cargo'] ?? ''); ?>" class="input-dark" placeholder="Ej: Presidente, Secretario, Tesorero...">
            </div>

            <div>
                <label class="label-dark" for="aprobado">Estado</label>
                <select name="aprobado" id="aprobado" class="input-dark">
                    <option value="1" <?php if($fila['aprobado']==1) echo 'selected'; ?>>Aprobado</option>
                    <option value="0" <?php if($fila['aprobado']==0) echo 'selected'; ?>>Pendiente</option>
                </select>
            </div>

            <div>
                <label class="label-dark">Foto actual</label>
                <?php if (!empty($fila['foto']) && strpos($fila['foto'], 'http') === 0): ?>
                    <img src="<?php echo $fila['foto']; ?>" class="foto-actual" alt="Foto">
                    <p class="text-secondary text-sm mt-1">Guardada en la nube</p>
                <?php else: ?>
                    <p class="text-secondary text-sm">Sin foto o no es de la nube</p>
                <?php endif; ?>
            </div>

            <div>
                <label class="label-dark" for="foto">Cambiar foto (opcional)</label>
                <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,.gif,.webp" class="input-dark" style="padding: 0.5rem;">
                <p class="text-secondary text-xs mt-1">Formatos permitidos: JPG, PNG, GIF, WEBP.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-outline-variant/30">
                <button type="submit" class="flex-1 bg-[#ff5719] text-black font-bold text-[16px] uppercase px-8 py-3 rounded-sm border-2 border-black shadow-[inset_0_0_0_2px_rgba(255,255,255,0.2)] hover:bg-[#ffb59e] transition-colors">
                    💾 Guardar Cambios
                </button>
                <a href="admin_usuarios.php" class="flex-1 bg-[#2a2a2a] text-[#e5e2e1] font-bold text-[16px] uppercase px-8 py-3 rounded-sm border border-[#5c4037] hover:border-[#ffb59e] transition-colors text-center">
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