<?php
session_start();
include 'conexion.php';

// Solo el admin (superadmin) puede guardar salidas[cite: 2]
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

// Manejar subida de nueva imagen a Cloudinary
if ($_FILES['imagen']['error'] == 0) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($extension, $tipos_permitidos)) {
        // Credenciales de Cloudinary
        $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
        $api_key = getenv('CLOUDINARY_API_KEY');
        $api_secret = getenv('CLOUDINARY_API_SECRET');
        $timestamp = time();

        $signature = sha1("timestamp=" . $timestamp . $api_secret);

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
        $cfile = new CURLFile($archivo['tmp_name']);
        
        $data = [
            'file' => $cfile,
            'api_key' => $api_key,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => 'ratas_salidas' // Otra carpeta para organizar en Cloudinary
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $respuesta = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($respuesta, true);
        
        if (isset($json['secure_url'])) {
            $nombre_imagen = $json['secure_url']; 
        }
    }
}

// Insertar en base de datos[cite: 2]
$sql = "INSERT INTO salidas (destino, fecha_salida, hora_quedada, punto_encuentro, descripcion, imagen, responsable) 
        VALUES ('$destino', '$fecha', '$hora', '$punto', '$descripcion', '$nombre_imagen', '$responsable')";

if ($conexion->query($sql) === TRUE) {
    header("Location: salidas.php");
} else {
    echo "Error al guardar: " . $conexion->error;
}
$conexion->close();
?>