<?php
session_start();
include 'conexion.php';

// Solo superadmin puede subir
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: estatutos.php");
    exit;
}

// Manejar el archivo
$archivo = $_FILES['archivo_pdf'];
$error = $archivo['error'];

if ($error == 0 && strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) == 'pdf') {
    // Obtener el nombre antiguo para borrarlo después
    $sql_old = "SELECT valor FROM configuracion WHERE clave = 'estatutos_pdf'";
    $resultado = $conexion->query($sql_old);
    $fila = $resultado->fetch_assoc();
    $nombre_antiguo = $fila['valor'];

    // Generar nombre único
    $nombre_unico = 'estatutos_' . date('Ymd_His') . '.pdf';
    $ruta_destino = 'pdf_estatutos/' . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        // Borrar el antiguo si existe
        if (!empty($nombre_antiguo) && file_exists('pdf_estatutos/' . $nombre_antiguo)) {
            unlink('pdf_estatutos/' . $nombre_antiguo);
        }

        // Actualizar la base de datos
        $sql = "UPDATE configuracion SET valor = '$nombre_unico' WHERE clave = 'estatutos_pdf'";
        if ($conexion->query($sql) === TRUE) {
            header("Location: estatutos.php?exito=1");
        } else {
            echo "Error al actualizar la base de datos: " . $conexion->error;
        }
    } else {
        echo "Error al mover el archivo. Revisa los permisos de la carpeta 'pdf_estatutos'.";
    }
} else {
    echo "Error: solo se permiten archivos PDF.";
}
?>