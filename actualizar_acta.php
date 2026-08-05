<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$fecha = $_POST['fecha_reunion'];
$asistentes = $_POST['asistentes'];
$autor = $_POST['autor'];
$archivo_antiguo = $_POST['archivo_antiguo'];

// Verificar si se subió un PDF nuevo
if ($_FILES['archivo_pdf']['error'] == 0) {
    $archivo = $_FILES['archivo_pdf'];
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_unico = date('Ymd_His') . '.' . $extension;
    $ruta_destino = 'pdf_actas/' . $nombre_unico;

    if (strtolower($extension) == 'pdf') {
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            // Borrar el PDF antiguo
            if (!empty($archivo_antiguo) && file_exists('pdf_actas/' . $archivo_antiguo)) {
                unlink('pdf_actas/' . $archivo_antiguo);
            }
            // Actualizar con el nuevo nombre
            $sql = "UPDATE actas SET 
                    titulo = '$titulo', 
                    fecha_reunion = '$fecha', 
                    autor = '$autor', 
                    firmas = '$asistentes', 
                    archivo_pdf = '$nombre_unico' 
                    WHERE id = $id";
        } else {
            echo "Error al mover el nuevo PDF.";
            exit;
        }
    } else {
        echo "Solo se permiten archivos PDF.";
        exit;
    }
} else {
    // Si no se subió PDF nuevo, actualizar solo los datos
    $sql = "UPDATE actas SET 
            titulo = '$titulo', 
            fecha_reunion = '$fecha', 
            autor = '$autor', 
            firmas = '$asistentes' 
            WHERE id = $id";
}

if ($conexion->query($sql) === TRUE) {
    header("Location: actas.php");
} else {
    echo "Error al actualizar: " . $conexion->error;
}
$conexion->close();
?>