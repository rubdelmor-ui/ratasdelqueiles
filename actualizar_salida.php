<?php
session_start();
include 'conexion.php';

// 🔐 Solo el admin (superadmin) puede actualizar salidas
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

$id = $_POST['id'];
$destino = $_POST['destino'];
$fecha = $_POST['fecha_salida'];
$hora = $_POST['hora_quedada'];
$punto = $_POST['punto_encuentro'];
$descripcion = $_POST['descripcion'];
$responsable = $_POST['responsable'];
$imagen_antigua = $_POST['imagen_antigua'];

$nombre_imagen = $imagen_antigua;
if ($_FILES['imagen']['error'] == 0) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($extension, $tipos_permitidos)) {
        $nombre_imagen = date('Ymd_His') . '.' . $extension;
        $ruta_destino = 'images/salidas/' . $nombre_imagen;
        move_uploaded_file($archivo['tmp_name'], $ruta_destino);
        if (!empty($imagen_antigua) && file_exists('images/salidas/' . $imagen_antigua)) {
            unlink('images/salidas/' . $imagen_antigua);
        }
    }
}

$sql = "UPDATE salidas SET 
        destino = '$destino', 
        fecha_salida = '$fecha', 
        hora_quedada = '$hora', 
        punto_encuentro = '$punto', 
        descripcion = '$descripcion',
        responsable = '$responsable',
        imagen = '$nombre_imagen'
        WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    header("Location: salidas.php");
} else {
    echo "Error al actualizar: " . $conexion->error;
}
$conexion->close();
?>