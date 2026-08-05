<?php
session_start();
include 'conexion.php';

// 🔐 Solo superadmin puede acceder
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

// Procesar acciones (añadir, editar, borrar, ordenar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $tipo = $_POST['tipo'];
                $titulo = $_POST['titulo'] ?? '';
                $contenido = $_POST['contenido'] ?? '';
                $url = $_POST['url'] ?? '';
                $imagen = '';

                // Manejar subida de imagen (si se seleccionó)
                if ($_FILES['imagen']['error'] == 0 && in_array($_FILES['imagen']['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $imagen = 'home_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['imagen']['tmp_name'], 'uploads/home/' . $imagen);
                }

                $sql = "INSERT INTO home_content (tipo, titulo, contenido, url, imagen, orden) 
                        VALUES ('$tipo', '$titulo', '$contenido', '$url', '$imagen', (SELECT COALESCE(MAX(orden),0)+1 FROM home_content))";
                $conexion->query($sql);
                break;

            case 'edit':
                $id = intval($_POST['id']);
                $tipo = $_POST['tipo'];
                $titulo = $_POST['titulo'] ?? '';
                $contenido = $_POST['contenido'] ?? '';
                $url = $_POST['url'] ?? '';
                $imagen = $_POST['imagen_actual'] ?? '';

                if ($_FILES['imagen']['error'] == 0 && in_array($_FILES['imagen']['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    // Borrar imagen antigua si existe
                    if (!empty($imagen) && file_exists('uploads/home/' . $imagen)) {
                        unlink('uploads/home/' . $imagen);
                    }
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $imagen = 'home_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['imagen']['tmp_name'], 'uploads/home/' . $imagen);
                }

                $sql = "UPDATE home_content SET 
                        tipo = '$tipo', 
                        titulo = '$titulo', 
                        contenido = '$contenido', 
                        url = '$url', 
                        imagen = '$imagen' 
                        WHERE id = $id";
                $conexion->query($sql);
                break;

            case 'delete':
                $id = intval($_POST['id']);
                // Obtener imagen para borrar
                $sql_img = "SELECT imagen FROM home_content WHERE id = $id";
                $res_img = $conexion->query($sql_img);
                if ($row = $res_img->fetch_assoc()) {
                    if (!empty($row['imagen']) && file_exists('uploads/home/' . $row['imagen'])) {
                        unlink('uploads/home/' . $row['imagen']);
                    }
                }
                $conexion->query("DELETE FROM home_content WHERE id = $id");
                break;

            case 'reorder':
                // Actualizar órdenes (recibimos array de IDs en orden)
                $ordenes = $_POST['orden'] ?? [];
                foreach ($ordenes as $orden => $id) {
                    $id = intval($id);
                    $orden_val = intval($orden) + 1;
                    $conexion->query("UPDATE home_content SET orden = $orden_val WHERE id = $id");
                }
                break;
        }
        header("Location: admin_home.php");
        exit;
    }
}

// Obtener contenido actual
$sql_content = "SELECT * FROM home_content ORDER BY orden ASC";
$content = $conexion->query($sql_content);
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Home - Ratas del Queiles</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        .chrome-border { border: 1px solid rgba(255,255,255,0.1); }
        body { background: #131313; color: #e5e2e1; font-family: 'Hanken Grotesk', sans-serif; }
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
        .btn-danger { background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .btn-danger:hover { background: #a71d2a; }
        .card { background: #1a1a1a; border: 1px solid rgba(255,255,255,0.05); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
        .superadmin-tag { background: #ffaa00; color: #000; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: bold; margin-left: 5px; }
    </style>
</head>
<body class="min-h-screen flex flex-col noise-bg">

<header class="bg-background border-b border-outline-variant flex justify-between items-center w-full px-margin-mobile py-unit h-16 sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <img alt="Logo" class="h-8 w-8 object-contain rounded-full border border-outline-variant" src="images/logo2.jpg">
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile uppercase text-primary tracking-tighter">Ratas del Queiles</h1>
    </div>
    <div>
        <span class="superadmin-tag">👑 SUPERADMIN</span>
        <a href="index.php" class="text-secondary hover:text-primary ml-4 text-sm">⬅ Volver al inicio</a>
    </div>
</header>

<main class="flex-grow p-4 md:p-6 max-w-4xl mx-auto w-full">
    <h2 class="font-headline-lg text-headline-lg text-on-background uppercase tracking-tight">📝 Editar Página de Inicio</h2>
    <p class="text-secondary text-sm mb-6">Añade, edita o elimina bloques de contenido (texto, imágenes, noticias, enlaces).</p>

    <!-- Formulario para añadir nuevo bloque -->
    <div class="card">
        <h3 class="font-headline-md text-headline-md text-on-background uppercase mb-4">➕ Añadir nuevo bloque</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="tipo">Tipo</label>
                    <select name="tipo" id="tipo" class="input-dark" onchange="toggleFields()">
                        <option value="texto">Texto</option>
                        <option value="imagen">Imagen</option>
                        <option value="enlace">Enlace</option>
                        <option value="noticia">Noticia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="titulo">Título (opcional)</label>
                    <input type="text" name="titulo" id="titulo" class="input-dark" placeholder="Título del bloque">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="contenido">Contenido</label>
                <textarea name="contenido" id="contenido" rows="3" class="input-dark" placeholder="Texto, descripción de la noticia, etc."></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="url">URL (para enlaces)</label>
                    <input type="text" name="url" id="url" class="input-dark" placeholder="https://ejemplo.com">
                </div>
                <div>
                    <label class="block text-secondary text-sm uppercase font-label-md mb-1" for="imagen">Imagen (JPG, PNG, GIF, WEBP)</label>
                    <input type="file" name="imagen" id="imagen" class="input-dark" style="padding: 0.5rem;" accept="image/*">
                </div>
            </div>
            <button type="submit" class="btn-primary mt-4 w-full md:w-auto">Añadir bloque</button>
        </form>
    </div>

    <!-- Listado de bloques existentes -->
    <h3 class="font-headline-md text-headline-md text-on-background uppercase mt-8 mb-4">📋 Bloques actuales</h3>
    <div id="sortable">
        <?php while ($row = $content->fetch_assoc()): ?>
            <div class="card flex flex-col md:flex-row justify-between items-start md:items-center gap-4" data-id="<?php echo $row['id']; ?>">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-secondary text-xs uppercase font-label-md"><?php echo $row['tipo']; ?></span>
                        <span class="badge-orden text-xs bg-surface-container-high px-2 py-0.5 rounded">Orden: <?php echo $row['orden']; ?></span>
                    </div>
                    <div class="font-headline-md text-headline-md text-on-background"><?php echo htmlspecialchars($row['titulo']); ?></div>
                    <div class="text-secondary text-sm line-clamp-2"><?php echo htmlspecialchars($row['contenido']); ?></div>
                    <?php if (!empty($row['url'])): ?>
                        <div class="text-xs text-primary truncate">🔗 <?php echo htmlspecialchars($row['url']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($row['imagen']) && file_exists('uploads/home/' . $row['imagen'])): ?>
                        <img src="uploads/home/<?php echo $row['imagen']; ?>" class="mt-2 max-h-20 rounded border border-outline-variant" alt="Imagen">
                    <?php endif; ?>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <!-- Botón Editar (redirige a editar_bloque.php) -->
                    <a href="editar_bloque.php?id=<?php echo $row['id']; ?>" class="btn-primary text-sm px-3 py-1">✏️ Editar</a>
                    <!-- Botón Eliminar (formulario POST) -->
                    <form action="" method="POST" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn-danger text-sm px-3 py-1" onclick="return confirm('¿Eliminar este bloque?')">🗑️ Eliminar</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Botón para reordenar (solo si hay más de un bloque) -->
    <?php if ($content->num_rows > 1): ?>
        <form action="" method="POST" id="reorderForm">
            <input type="hidden" name="action" value="reorder">
            <input type="hidden" name="orden" id="ordenInput" value="">
            <button type="submit" class="btn-primary mt-4 w-full md:w-auto">💾 Guardar orden actual</button>
        </form>
    <?php endif; ?>

</main>

<script>
    // Mostrar/ocultar campos según el tipo
    function toggleFields() {
        const tipo = document.getElementById('tipo').value;
        const urlField = document.getElementById('url').closest('.grid').querySelector('div:last-child');
        // Podríamos ocultar/mostrar según necesidad, pero lo dejamos visible siempre por simplicidad.
    }

    // Reordenar con drag & drop (usando HTML5 Drag and Drop)
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('sortable');
        if (!container) return;

        let dragItem = null;

        container.addEventListener('dragstart', function(e) {
            dragItem = e.target.closest('.card');
            if (!dragItem) return;
            dragItem.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dragItem.dataset.id);
        });

        container.addEventListener('dragend', function(e) {
            if (dragItem) {
                dragItem.style.opacity = '1';
                dragItem = null;
            }
        });

        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            const target = e.target.closest('.card');
            if (!target || target === dragItem) return;
            const rect = target.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            if (e.clientY < midY) {
                container.insertBefore(dragItem, target);
            } else {
                container.insertBefore(dragItem, target.nextSibling);
            }
        });

        // Manejar envío del orden
        document.getElementById('reorderForm')?.addEventListener('submit', function(e) {
            const items = container.querySelectorAll('.card');
            const ids = [];
            items.forEach(el => {
                const id = el.dataset.id;
                if (id) ids.push(id);
            });
            document.getElementById('ordenInput').value = ids.join(',');
        });
    });
</script>

</body>
</html>