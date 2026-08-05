<?php
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "club";

$conexion = new mysqli($servidor, $usuario, $contraseña, $base_datos);

if ($conexion->connect_error) {
    die("ERROR GRAVE: No pude conectar a la base de datos: " . $conexion->connect_error);
}
?>