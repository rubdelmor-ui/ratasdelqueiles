<?php
session_start();
include 'conexion.php';

// 🔐 Solo el admin (superadmin) puede borrar salidas
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

$id = $_GET['id'];
$sql_select = "SELECT imagen FROM salidas WHERE id = $id";
$resultado = $conexion->query($sql_select);
$fila = $resultado->fetch_assoc();
$imagen_a_borrar = $fila['imagen'];

$sql_delete = "DELETE FROM salidas WHERE id = $id";
if ($conexion->query($sql_delete) === TRUE) {
    if (!empty($imagen_a_borrar) && file_exists('images/salidas/' . $imagen_a_borrar)) {
        unlink('images/salidas/' . $imagen_a_borrar);
    }
    header("Location: salidas.php");
} else {
    echo "Error al borrar: " . $conexion->error;
}
$conexion->close();
?>