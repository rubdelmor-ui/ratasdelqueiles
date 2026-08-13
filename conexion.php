<?php
// --- LECTOR DEL ARCHIVO .env (Para desarrollo local) ---
// Si existe un archivo .env en la misma carpeta, lo lee y carga las variables.
if (file_exists(__DIR__ . '/.env')) {
    $lineas = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        // Ignorar comentarios
        if (strpos(trim($linea), '#') === 0) continue;
        // Separar nombre y valor
        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            putenv(trim($nombre) . '=' . trim($valor));
        }
    }
}

// --- CONEXIÓN A LA BASE DE DATOS ---
// Ahora cogemos los datos EXCLUSIVAMENTE de las variables de entorno.
// Cero contraseñas hardcodeadas en este archivo.
$servidor   = getenv('DB_HOST');
$usuario    = getenv('DB_USER');
$contraseña = getenv('DB_PASS'); 
$base_datos = getenv('DB_NAME');
$puerto     = getenv('DB_PORT');

// Usamos el constructor de mysqli que incluye el puerto
$conexion = new mysqli($servidor, $usuario, $contraseña, $base_datos, $puerto);

if ($conexion->connect_error) {
    die("ERROR GRAVE: No pude conectar a la base de datos. Verifica tus variables de entorno.");
}

// Aseguramos que los caracteres especiales (ñ, acentos) se lean bien
$conexion->set_charset("utf8mb4");
?>