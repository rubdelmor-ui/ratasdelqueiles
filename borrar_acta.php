<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

$id = $_GET['id'];

// Obtener el nombre del PDF para borrarlo del disco
$sql_select = "SELECT archivo_pdf FROM actas WHERE id = $id";
$resultado = $conexion->query($sql_select);
$fila = $resultado->fetch_assoc();
$archivo_a_borrar = $fila['archivo_pdf'];

// Eliminar el registro de la base de datos
$sql_delete = "DELETE FROM actas WHERE id = $id";
if ($conexion->query($sql_delete) === TRUE) {
    // Borrar el archivo físico
    if (!empty($archivo_a_borrar) && file_exists('pdf_actas/' . $archivo_a_borrar)) {
        unlink('pdf_actas/' . $archivo_a_borrar);
    }
    header("Location: actas.php");
} else {
    echo "Error al borrar: " . $conexion->error;
}
$conexion->close();
?>