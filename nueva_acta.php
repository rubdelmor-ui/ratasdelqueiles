<?php
session_start();
include 'conexion.php';

// SEGURIDAD: Solo el Superadmin puede crear actas
$superadmin_email = 'admin@club.com'; // <--- CAMBIA SI ES OTRO
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || $_SESSION['usuario_email'] != $superadmin_email) {
    header("Location: actas.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nueva Acta</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#131313">
    <link rel="apple-touch-icon" href="images/logo2.jpg">
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .contenedor { max-width: 600px; background: white; padding: 30px; margin: 0 auto; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.2); }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .boton { background: #0056b3; color: white; font-weight: bold; border: none; padding: 12px 20px; margin-top: 20px; cursor: pointer; border-radius: 5px; font-size: 18px; width: 100%; }
        .boton:hover { background: #003d80; }
        a { display: block; text-align: center; margin-top: 20px; color: #333; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>📤 Subir Nueva Acta (PDF)</h1>
        
        <!-- IMPORTANTE: enctype="multipart/form-data" para subir archivos -->
        <form action="guardar_acta.php" method="POST" enctype="multipart/form-data">
            
            <label>Título del Acta *</label>
            <input type="text" name="titulo" placeholder="Ej: Acta Asamblea General 2026" required>

            <label>Fecha de la reunión *</label>
            <input type="date" name="fecha_reunion" value="<?php echo date('Y-m-d'); ?>" required>

            <label>Número de asistentes *</label>
            <input type="number" name="asistentes" min="0" value="0" required>

            <label>Autor (quién redacta el acta)</label>
            <input type="text" name="autor" placeholder="Ej: Secretario del Club">

            <label>Archivo PDF (adjunta tu acta) *</label>
            <input type="file" name="archivo_pdf" accept=".pdf" required>

            <button type="submit" class="boton">✅ Subir Acta</button>
        </form>
        <a href="actas.php">⬅ Volver al listado</a>
    </div>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('ServiceWorker registrado con éxito', registration.scope);
                })
                .catch(error => {
                    console.log('Fallo al registrar ServiceWorker', error);
                });
        });
    }
</script>
</body>
</html>