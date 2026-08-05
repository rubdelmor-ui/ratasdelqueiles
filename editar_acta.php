<?php
session_start();
include 'conexion.php';

// Seguridad: Solo Superadmin
$superadmin_email = 'admin@club.com';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM actas WHERE id = $id";
$resultado = $conexion->query($sql);
$fila = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editar Acta</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .contenedor { max-width: 600px; background: white; padding: 30px; margin: 0 auto; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .boton { background: #ff9800; color: white; font-weight: bold; border: none; padding: 12px 20px; margin-top: 20px; cursor: pointer; border-radius: 5px; font-size: 18px; width: 100%; }
        .boton:hover { background: #e68900; }
        a { display: block; text-align: center; margin-top: 20px; color: #333; }
        .archivo-actual { background: #f0f0f0; padding: 10px; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>✏️ Editar Acta</h1>
        <form action="actualizar_acta.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
            <input type="hidden" name="archivo_antiguo" value="<?php echo $fila['archivo_pdf']; ?>">

            <label>Título *</label>
            <input type="text" name="titulo" value="<?php echo $fila['titulo']; ?>" required>

            <label>Fecha *</label>
            <input type="date" name="fecha_reunion" value="<?php echo $fila['fecha_reunion']; ?>" required>

            <label>Número de asistentes *</label>
            <input type="number" name="asistentes" value="<?php echo $fila['firmas']; ?>" min="0" required>

            <label>Autor</label>
            <input type="text" name="autor" value="<?php echo $fila['autor']; ?>">

            <label>PDF actual</label>
            <div class="archivo-actual">
                📄 <?php echo $fila['archivo_pdf']; ?>
            </div>

            <label>Reemplazar PDF (si quieres cambiarlo, selecciona uno nuevo)</label>
            <input type="file" name="archivo_pdf" accept=".pdf">

            <button type="submit" class="boton">💾 Actualizar Acta</button>
        </form>
        <a href="actas.php">⬅ Volver</a>
    </div>
</body>
</html>