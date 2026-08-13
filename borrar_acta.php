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

// Obtener el nombre del PDF
$sql_select = "SELECT archivo_pdf FROM actas WHERE id = $id";
$resultado = $conexion->query($sql_select);
$fila = $resultado->fetch_assoc();
$archivo_a_borrar = $fila['archivo_pdf'];

// Eliminar el registro de la base de datos
$sql_delete = "DELETE FROM actas WHERE id = $id";
if ($conexion->query($sql_delete) === TRUE) {
    // Si no es un enlace de Cloudinary y existe localmente, lo borra. 
    // Los de Cloudinary se quedan ahí hasta que los borres desde su panel.
    if (!empty($archivo_a_borrar) && strpos($archivo_a_borrar, 'http') !== 0 && file_exists('pdf_actas/' . $archivo_a_borrar)) {
        unlink('pdf_actas/' . $archivo_a_borrar);
    }
    header("Location: actas.php");
} else {
    echo "Error al borrar: " . $conexion->error;
}
$conexion->close();
?>