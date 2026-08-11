<?php
session_start();
include 'conexion.php';

// Solo superadmin puede guardar
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

$contenido = $_POST['contenido'];
$texto_imagen = $_POST['texto_imagen'] ?? '';

// Obtener imagen actual para saber si hay que borrar la antigua
$sql_img = "SELECT imagen FROM contenido_home WHERE seccion = 'bienvenida'";
$res_img = $conexion->query($sql_img);
$imagen_actual = null;
if ($res_img && $res_img->num_rows > 0) {
    $fila_img = $res_img->fetch_assoc();
    $imagen_actual = $fila_img['imagen'];
}

$nombre_imagen = $imagen_actual; // mantener por defecto

// Manejar subida de nueva imagen
if ($_FILES['imagen']['error'] == 0) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($extension, $tipos_permitidos)) {
        // Crear carpeta images/home si no existe
        if (!is_dir('images/home')) {
            mkdir('images/home', 0777, true);
        }
        $nombre_imagen = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $ruta_destino = 'images/home/' . $nombre_imagen;
        move_uploaded_file($archivo['tmp_name'], $ruta_destino);
        // Borrar imagen antigua si existe
        if (!empty($imagen_actual) && file_exists('images/home/' . $imagen_actual)) {
            unlink('images/home/' . $imagen_actual);
        }
    }
}

// Actualizar o insertar (usamos ON DUPLICATE KEY UPDATE o UPDATE)
$sql = "UPDATE contenido_home SET 
        contenido = '$contenido',
        texto_imagen = '$texto_imagen',
        imagen = '$nombre_imagen'
        WHERE seccion = 'bienvenida'";

if ($conexion->query($sql) === TRUE) {
    // Si no se actualizó ninguna fila (porque no existía), insertamos
    if ($conexion->affected_rows == 0) {
        $sql_insert = "INSERT INTO contenido_home (seccion, contenido, texto_imagen, imagen) VALUES ('bienvenida', '$contenido', '$texto_imagen', '$nombre_imagen')";
        $conexion->query($sql_insert);
    }
    header("Location: editar_home.php?ok=1");
} else {
    echo "Error al guardar: " . $conexion->error;
}
$conexion->close();
?>