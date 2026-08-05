<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$salida_id = $_GET['salida_id'];

$check_sql = "SELECT id FROM inscripciones WHERE salida_id = $salida_id AND usuario_id = $usuario_id";
$check_result = $conexion->query($check_sql);

if ($check_result->num_rows > 0) {
    // Los acompañantes se borran automáticamente por ON DELETE CASCADE
    $delete_sql = "DELETE FROM inscripciones WHERE salida_id = $salida_id AND usuario_id = $usuario_id";
    $conexion->query($delete_sql);
} else {
    // Caso de seguridad (no debería ocurrir, pero se deja por si acaso)
    $insert_sql = "INSERT INTO inscripciones (salida_id, usuario_id) VALUES ($salida_id, $usuario_id)";
    $conexion->query($insert_sql);
}

header("Location: salidas.php");
exit;
?>