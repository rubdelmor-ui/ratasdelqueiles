<?php
session_start();
include 'conexion.php';

// Verificar que el usuario está logueado (cualquier socio puede descargar)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$salida_id = isset($_GET['salida_id']) ? intval($_GET['salida_id']) : 0;
if ($salida_id == 0) {
    die("ID de salida no válido.");
}

// Obtener datos de la salida (destino)
$sql_salida = "SELECT destino FROM salidas WHERE id = $salida_id";
$res_salida = $conexion->query($sql_salida);
if ($res_salida->num_rows == 0) {
    die("Salida no encontrada.");
}
$salida = $res_salida->fetch_assoc();
$titulo = $salida['destino'];

// Obtener lista de asistentes (socios + acompañantes) ordenada alfabéticamente
$sql = "SELECT u.nombre as socio, i.id as inscripcion_id
        FROM inscripciones i
        JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.salida_id = $salida_id
        ORDER BY u.nombre ASC";
$result = $conexion->query($sql);

$datos = [];
while ($row = $result->fetch_assoc()) {
    $socio_nombre = $row['socio'];
    $insc_id = $row['inscripcion_id'];
    // Obtener acompañantes
    $sql_acomp = "SELECT nombre FROM acompanantes WHERE inscripcion_id = $insc_id ORDER BY nombre ASC";
    $res_acomp = $conexion->query($sql_acomp);
    $acompanantes = [];
    while ($acomp = $res_acomp->fetch_assoc()) {
        $acompanantes[] = $acomp['nombre'];
    }
    $datos[] = [
        'socio' => $socio_nombre,
        'acompanantes' => $acompanantes
    ];
}

// Configurar cabeceras para descargar CSV que Excel abre
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Asistentes_' . date('Y-m-d') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $titulo) . '.xls"');

$salida = fopen('php://output', 'w');
fputs($salida, "\xEF\xBB\xBF"); // BOM para tildes y ñ

// Escribir título
fputcsv($salida, ['LISTA DE ASISTENTES - ' . strtoupper($titulo)], ';');
fputcsv($salida, ['Fecha: ' . date('d/m/Y H:i')], ';');
fputcsv($salida, [], ';'); // línea en blanco

// Encabezados
fputcsv($salida, ['SOCIO', 'ACOMPAÑANTES'], ';');

// Datos
foreach ($datos as $item) {
    if (count($item['acompanantes']) > 0) {
        foreach ($item['acompanantes'] as $acomp) {
            fputcsv($salida, [$item['socio'], $acomp], ';');
        }
    } else {
        fputcsv($salida, [$item['socio'], ''], ';');
    }
}

fclose($salida);
exit;