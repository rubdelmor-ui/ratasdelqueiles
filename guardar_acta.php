<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

// Recoger datos del formulario
$titulo = $_POST['titulo'];
$fecha = $_POST['fecha_reunion'];
$asistentes = $_POST['asistentes'];
$autor = $_POST['autor'];

// ---- MANEJAR EL ARCHIVO PDF ----
$archivo = $_FILES['archivo_pdf'];
$nombre_original = $archivo['name'];
$ruta_temporal = $archivo['tmp_name'];
$error = $archivo['error'];

// Crear un nombre único para el PDF (ej: 20260815_123456.pdf)
$extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
$nombre_unico = date('Ymd_His') . '.' . $extension;
$ruta_destino = 'pdf_actas/' . $nombre_unico;

// Verificar que sea un PDF y no haya error
if ($error == 0 && strtolower($extension) == 'pdf') {
    if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
        // Guardar en la base de datos (sin el texto largo, solo el PDF)
        $sql = "INSERT INTO actas (titulo, fecha_reunion, autor, firmas, archivo_pdf) 
                VALUES ('$titulo', '$fecha', '$autor', '$asistentes', '$nombre_unico')";
        
        if ($conexion->query($sql) === TRUE) {
            header("Location: actas.php");
        } else {
            echo "Error en la base de datos: " . $conexion->error;
        }
    } else {
        echo "Error al mover el archivo. Revisa los permisos de la carpeta 'pdf_actas'.";
    }
} else {
    echo "Error: Solo se permiten archivos PDF.";
}

$conexion->close();
?>