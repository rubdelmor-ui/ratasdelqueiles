<?php
session_start();
include 'conexion.php';

// 🔐 Solo el admin (superadmin) puede guardar salidas
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

$destino = $_POST['destino'];
$fecha = $_POST['fecha_salida'];
$hora = $_POST['hora_quedada'];
$punto = $_POST['punto_encuentro'];
$descripcion = $_POST['descripcion'];
$responsable = $_POST['responsable'];

$nombre_imagen = NULL;
if ($_FILES['imagen']['error'] == 0) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($extension, $tipos_permitidos)) {
        $nombre_imagen = date('Ymd_His') . '.' . $extension;
        $ruta_destino = 'images/salidas/' . $nombre_imagen;
        move_uploaded_file($archivo['tmp_name'], $ruta_destino);
    }
}

$sql = "INSERT INTO salidas (destino, fecha_salida, hora_quedada, punto_encuentro, descripcion, imagen, responsable) 
        VALUES ('$destino', '$fecha', '$hora', '$punto', '$descripcion', '$nombre_imagen', '$responsable')";

if ($conexion->query($sql) === TRUE) {
    header("Location: salidas.php");
} else {
    echo "Error al guardar: " . $conexion->error;
}
$conexion->close();
?>