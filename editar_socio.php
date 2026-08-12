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
// 1. PROCESAR EL FORMULARIO (POST)[cite: 25]
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $cargo = $_POST['cargo'] ?? '';
    $aprobado = intval($_POST['aprobado']);
    $pregunta_seguridad = $_POST['pregunta_seguridad'] ?? '';
    $respuesta_seguridad_raw = $_POST['respuesta_seguridad'] ?? '';

    // Si es superadmin, permite cambiar rol; si no, mantiene el rol actual
    if ($es_superadmin) {
        $rol = $_POST['rol'];
    } else {
        $sql_rol = "SELECT rol FROM usuarios WHERE id = $id";
        $res_rol = $conexion->query($sql_rol);
        $fila_rol = $res_rol->fetch_assoc();
        $rol = $fila_rol['rol'];
    }

    // Manejar respuesta de seguridad
    $respuesta_seguridad = null;
    if (!empty($respuesta_seguridad_raw)) {
        $respuesta_seguridad = password_hash($respuesta_seguridad_raw, PASSWORD_DEFAULT);
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
            $signature = sha1("timestamp=" . $timestamp . $api_secret);

            $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
            $cfile = new CURLFile($archivo['tmp_name']);
            $data = [
                'file' => $cfile, 'api_key' => $api_key, 'timestamp' => $timestamp,
                'signature' => $signature, 'folder' => 'ratas_perfiles'
            ];
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $respuesta = curl_exec($ch);
            curl_close($ch);
            
            $json = json_decode($respuesta, true);
            if (isset($json['secure_url'])) {
                $nombre_foto = $json['secure_url']; // Guardamos la URL segura
            }
        }
    }

    // Construir consulta SQL
    $sql_update = "UPDATE usuarios SET 
            nombre = '$nombre',
            email = '$email',
            rol = '$rol',
            cargo = '$cargo',
            aprobado = $aprobado,
            foto = '$nombre_foto',
            pregunta_seguridad = '$pregunta_seguridad'";

    if ($respuesta_seguridad !== null) {
        $sql_update .= ", respuesta_seguridad = '$respuesta_seguridad'";
    }

    $sql_update .= " WHERE id = $id";

    if ($conexion->query($sql_update) === TRUE) {
        header("Location: admin_usuarios.php");
        exit;
    } else {
        $error_mensaje = "Error al actualizar: " . $conexion->error;
    }
}

// =========================================================================
// 2. CARGAR DATOS PARA EL FORMULARIO (GET)[cite: 27]
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
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
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
            <img src="<?php echo $foto_usuario; ?>" alt="Foto" class="user-avatar" id="avatar-button" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.1);cursor:pointer;">
        <?php else: ?>
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%232a2a2a'/%3E%3Ctext x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='18' fill='%23666' font-family='Arial'%3E👤%3C/text%3E%3C/svg%3E" alt="Sin foto" class="user-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.1);cursor:pointer;">
        <?php endif; ?>
        <div class="relative" id="settings-menu">
            <button id="settings-button" class="text-on-surface-variant hover:bg-surface-container-high transition-colors duration-200 ease-in-out p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary">
                <span class="material-symbols-outlined">settings</span>
            </button>
            <div id="settings-dropdown" class="absolute right-0 mt-2 w-48 bg-surface-container border border-outline-variant rounded-lg shadow-lg py-1 hidden z-50">
                <?php if(isset($_SESSION['usuario_nombre'])): ?>
                    <div class="px-4 py-2 border-b border-outline-variant">
                        <span class="block text-on-background font-label-md"><?php echo $_SESSION['usuario_nombre']; ?></span>
                        <span class="block text-secondary font-label-sm text-xs"><?php echo $_SESSION['rol']; ?></span>
                    </div>
                    <a href="logout.php" class="block px-4 py-2 text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors font-label-md">
                        <span class="material-symbols-outlined text-[18px] align-middle mr-2">logout</span> Cerrar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="flex-grow p-4 md:p-6 flex flex-col gap-6 pb-24 md:pb-8 max-w-container-max mx-auto w-full">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b border-outline-variant pb-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">✏️ Editar Socio</h2>
            <p class="text-secondary mt-1">Modifica los datos del socio.</p>
        </div>
        <div class="mt-3 md:mt-0">
            <a href="admin_usuarios.php" class="text-secondary hover:text-primary transition-colors font-label-md uppercase text-sm inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
            </a>
        </div>
    </div>

    <div class="bg-surface-container rounded-xl chrome-border p-6 md:p-8">
        <?php if(isset($error_mensaje)): ?>
            <div class="bg-red-900/50 text-red-200 p-4 rounded mb-4"><?php echo $error_mensaje; ?></div>
        <?php endif; ?>

        <!-- NOTA: El form envía a sí mismo -->
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
                <label class="label-dark" for="pregunta_seguridad">Pregunta de seguridad</label>
                <select name="pregunta_seguridad" id="pregunta_seguridad" class="input-dark">
                    <option value="">Selecciona una pregunta...</option>
                    <option value="¿Cuál es el nombre de tu primera mascota?" <?php if($fila['pregunta_seguridad']=='¿Cuál es el nombre de tu primera mascota?') echo 'selected'; ?>>¿Cuál es el nombre de tu primera mascota?</option>
                    <option value="¿Cuál es tu ciudad natal?" <?php if($fila['pregunta_seguridad']=='¿Cuál es tu ciudad natal?') echo 'selected'; ?>>¿Cuál es tu ciudad natal?</option>
                    <option value="¿Cuál es el apellido de soltera de tu madre?" <?php if($fila['pregunta_seguridad']=='¿Cuál es el apellido de soltera de tu madre?') echo 'selected'; ?>>¿Cuál es el apellido de soltera de tu madre?</option>
                    <option value="¿Cuál es tu comida favorita?" <?php if($fila['pregunta_seguridad']=='¿Cuál es tu comida favorita?') echo 'selected'; ?>>¿Cuál es tu comida favorita?</option>
                    <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?" <?php if($fila['pregunta_seguridad']=='¿Cuál es el nombre de tu mejor amigo de la infancia?') echo 'selected'; ?>>¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                </select>
            </div>
            <div>
                <label class="label-dark" for="respuesta_seguridad">Respuesta de seguridad</label>
                <input type="text" name="respuesta_seguridad" id="respuesta_seguridad" class="input-dark" value="" placeholder="Solo rellena si quieres cambiar la respuesta actual">
                <p class="text-secondary text-xs mt-1">Si la dejas vacía, se mantendrá la respuesta anterior.</p>
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
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => { navigator.serviceWorker.register('sw.js'); });
    }
</script>
</body>
</html>