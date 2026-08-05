<?php
session_start();
include 'conexion.php';

$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta') {
    header("Location: admin_usuarios.php");
    exit;
}

$id = intval($_POST['id']);
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$cargo = $_POST['cargo'] ?? '';
$aprobado = intval($_POST['aprobado']);

// Si es superadmin, permite cambiar rol; si no, mantiene el rol actual
if ($es_superadmin) {
    $rol = $_POST['rol'];
} else {
    // Mantener el rol actual
    $sql_rol = "SELECT rol FROM usuarios WHERE id = $id";
    $res_rol = $conexion->query($sql_rol);
    $fila_rol = $res_rol->fetch_assoc();
    $rol = $fila_rol['rol'];
}

// Manejar foto
$foto_actual = null;
$sql_foto = "SELECT foto FROM usuarios WHERE id = $id";
$res_foto = $conexion->query($sql_foto);
$fila_foto = $res_foto->fetch_assoc();
$foto_actual = $fila_foto['foto'] ?? null;

$nombre_foto = $foto_actual;
if ($_FILES['foto']['error'] == 0) {
    $archivo = $_FILES['foto'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($extension, $tipos_permitidos)) {
        $nombre_foto = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $ruta_destino = 'uploads/perfiles/' . $nombre_foto;
        move_uploaded_file($archivo['tmp_name'], $ruta_destino);
        if (!empty($foto_actual) && file_exists('uploads/perfiles/' . $foto_actual)) {
            unlink('uploads/perfiles/' . $foto_actual);
        }
    }
}

$sql = "UPDATE usuarios SET 
        nombre = '$nombre',
        email = '$email',
        rol = '$rol',
        cargo = '$cargo',
        aprobado = $aprobado,
        foto = '$nombre_foto'
        WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    header("Location: admin_usuarios.php");
} else {
    echo "Error al actualizar: " . $conexion->error;
}
$conexion->close();
?>