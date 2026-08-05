<?php
session_start();
include 'conexion.php';

// 🔐 Solo el superadmin puede descargar el Excel
$es_superadmin = (isset($_SESSION['usuario_email']) && $_SESSION['usuario_email'] == 'admin@club.com');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'junta' || !$es_superadmin) {
    header("Location: salidas.php");
    exit;
}

$salida_id = isset($_GET['salida_id']) ? intval($_GET['salida_id']) : 0;
if ($salida_id == 0) {
    die("ID de salida no válido.");
}

// Obtener datos de la salida
$sql_salida = "SELECT destino FROM salidas WHERE id = $salida_id";
$res_salida = $conexion->query($sql_salida);
if ($res_salida->num_rows == 0) {
    die("Salida no encontrada.");
}
$salida = $res_salida->fetch_assoc();
$nombre_salida = $salida['destino'];

// Obtener lista de asistentes (socios + acompañantes)
$sql_asistentes = "SELECT u.nombre, i.id as inscripcion_id 
                   FROM inscripciones i 
                   JOIN usuarios u ON i.usuario_id = u.id 
                   WHERE i.salida_id = $salida_id 
                   ORDER BY u.nombre ASC";
$res_asistentes = $conexion->query($sql_asistentes);

$lista = [];
while ($row = $res_asistentes->fetch_assoc()) {
    $insc_id = $row['inscripcion_id'];
    $socio = $row['nombre'];
    // Obtener acompañantes de esta inscripción
    $sql_acomp = "SELECT nombre FROM acompanantes WHERE inscripcion_id = $insc_id ORDER BY nombre ASC";
    $res_acomp = $conexion->query($sql_acomp);
    $acompanantes = [];
    while ($acomp = $res_acomp->fetch_assoc()) {
        $acompanantes[] = $acomp['nombre'];
    }
    $lista[] = [
        'tipo' => 'Socio',
        'nombre' => $socio
    ];
    foreach ($acompanantes as $acomp) {
        $lista[] = [
            'tipo' => 'Acompañante',
            'nombre' => $acomp
        ];
    }
}

// Ordenar alfabéticamente por nombre
usort($lista, function($a, $b) {
    return strcmp($a['nombre'], $b['nombre']);
});

// Generar CSV con punto y coma
$filename = 'Asistentes_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nombre_salida) . '_' . date('Y-m-d') . '.xls';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$salida_csv = fopen('php://output', 'w');
// Escribir BOM para UTF-8 (tildes y ñ)
fputs($salida_csv, "\xEF\xBB\xBF");

// Escribir cabeceras
fputcsv($salida_csv, ['Tipo', 'Nombre'], ';');

if (count($lista) > 0) {
    foreach ($lista as $item) {
        fputcsv($salida_csv, [$item['tipo'], $item['nombre']], ';');
    }
} else {
    fputcsv($salida_csv, ['No hay asistentes apuntados a esta salida.'], ';');
}

fclose($salida_csv);
exit;
?>