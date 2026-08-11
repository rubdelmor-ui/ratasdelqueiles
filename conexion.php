<?php
// Usamos las variables de entorno de Render si existen (getenv),
// si no existen, usa tus datos de Railway por defecto.
$servidor   = getenv('DB_HOST') ?: 'maglev.proxy.rlwy.net';
$usuario    = getenv('DB_USER') ?: 'root';
$contraseña = getenv('DB_PASS') ?: 'HYHhmfSxkOomqYSCJnUvNXvtUxVCGYVX'; 
$base_datos = getenv('DB_NAME') ?: 'railway';
$puerto     = getenv('DB_PORT') ?: '12318';

// Usamos el constructor de mysqli que incluye el puerto
$conexion = new mysqli($servidor, $usuario, $contraseña, $base_datos, $puerto);

if ($conexion->connect_error) {
    die("ERROR GRAVE: No pude conectar a la base de datos: " . $conexion->connect_error);
}

// Aseguramos que los caracteres especiales (ñ, acentos) se lean bien
$conexion->set_charset("utf8mb4");
?>