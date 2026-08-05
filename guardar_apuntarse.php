<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$salida_id = intval($_POST['salida_id']);
$usuario_id = $_SESSION['usuario_id'];
$con_acompanantes = isset($_POST['acompanantes']) && $_POST['acompanantes'] == 1;
$nombres_acompanantes = isset($_POST['acompanante']) ? $_POST['acompanante'] : [];

$sql_insc = "INSERT INTO inscripciones (salida_id, usuario_id) VALUES ($salida_id, $usuario_id)";
if ($conexion->query($sql_insc) === TRUE) {
    $inscripcion_id = $conexion->insert_id;
    if ($con_acompanantes && !empty($nombres_acompanantes)) {
        foreach ($nombres_acompanantes as $nombre) {
            $nombre = trim($nombre);
            if (!empty($nombre)) {
                $sql_acomp = "INSERT INTO acompanantes (inscripcion_id, nombre) VALUES ($inscripcion_id, '$nombre')";
                $conexion->query($sql_acomp);
            }
        }
    }
    header("Location: salidas.php");
} else {
    echo "Error al apuntarse: " . $conexion->error;
}
$conexion->close();
?>