<?php
include 'conexion.php';

// Recoger los datos del formulario
$tipo = $_POST['tipo'];
$nombre = $_POST['nombre'];
$fecha = $_POST['fecha_evento'];
$lugar = $_POST['lugar'];
$menu = $_POST['menu'];

// ---- MANEJO DEL ARCHIVO (la novedad) ----
$archivo = $_FILES['archivo'];
$nombre_original = $archivo['name'];
$ruta_temporal = $archivo['tmp_name'];
$tamanio = $archivo['size'];
$error = $archivo['error'];

// Crear un nombre único para evitar que se pisen archivos (ej: 20260815_123456.jpg)
$extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
$nombre_unico = date('Ymd_His') . '.' . $extension;
$ruta_destino = 'uploads/' . $nombre_unico;

// Verificar que no haya error y que sea un tipo válido
$tipos_permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
if ($error == 0 && in_array(strtolower($extension), $tipos_permitidos)) {
    // Mover el archivo de la carpeta temporal a la carpeta "uploads"
    if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
        // Guardar en la base de datos
        $sql = "INSERT INTO carteles (tipo, nombre, fecha_evento, lugar, menu, archivo_nombre) 
                VALUES ('$tipo', '$nombre', '$fecha', '$lugar', '$menu', '$nombre_unico')";
        
        if ($conexion->query($sql) === TRUE) {
            header("Location: carteles.php");
        } else {
            echo "Error en la base de datos: " . $conexion->error;
        }
    } else {
        echo "Error al mover el archivo. Revisa los permisos de la carpeta 'uploads'.";
    }
} else {
    echo "Error: archivo no válido o corrupto. Solo JPG, PNG y PDF.";
}

$conexion->close();
?>