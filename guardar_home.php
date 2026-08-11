<?php
session_start();
include 'conexion.php';

// Solo superadmin puede guardar[cite: 1]
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: index.php");
    exit;
}

$contenido = $_POST['contenido'];
$texto_imagen = $_POST['texto_imagen'] ?? '';

// Obtener imagen actual[cite: 1]
$sql_img = "SELECT imagen FROM contenido_home WHERE seccion = 'bienvenida'";
$res_img = $conexion->query($sql_img);
$imagen_actual = null;
if ($res_img && $res_img->num_rows > 0) {
    $fila_img = $res_img->fetch_assoc();
    $imagen_actual = $fila_img['imagen'];
}

$nombre_imagen = $imagen_actual; // mantener por defecto[cite: 1]

// Manejar subida de nueva imagen a Cloudinary
if ($_FILES['imagen']['error'] == 0) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($extension, $tipos_permitidos)) {
        // Credenciales de Cloudinary (desde variables de entorno de Render)
        $cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
        $api_key = getenv('CLOUDINARY_API_KEY');
        $api_secret = getenv('CLOUDINARY_API_SECRET');
        $timestamp = time();

        // Generar la firma de seguridad
        $signature = sha1("timestamp=" . $timestamp . $api_secret);

        // Preparar la petición cURL
        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud_name}/image/upload");
        $cfile = new CURLFile($archivo['tmp_name']);
        
        $data = [
            'file' => $cfile,
            'api_key' => $api_key,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => 'ratas_home' // Carpeta virtual en Cloudinary
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $respuesta = curl_exec($ch);
        curl_close($ch);
        
        $json = json_decode($respuesta, true);
        
        // Si la subida fue exitosa, guardamos la URL segura
        if (isset($json['secure_url'])) {
            $nombre_imagen = $json['secure_url']; 
            // Nota: Ya no usamos unlink() local porque la imagen antigua está en Cloudinary.
        }
    }
}

// Actualizar o insertar[cite: 1]
$sql = "UPDATE contenido_home SET 
        contenido = '$contenido',
        texto_imagen = '$texto_imagen',
        imagen = '$nombre_imagen'
        WHERE seccion = 'bienvenida'";

if ($conexion->query($sql) === TRUE) {
    if ($conexion->affected_rows == 0) {
        $sql_insert = "INSERT INTO contenido_home (seccion, contenido, texto_imagen, imagen) VALUES ('bienvenida', '$contenido', '$texto_imagen', '$nombre_imagen')";
        $conexion->query($sql_insert);
    }
    header("Location: editar_home.php?ok=1");
} else {
    echo "Error al guardar: " . $conexion->error;
}
$conexion->close();
?>