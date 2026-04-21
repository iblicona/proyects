<?php
// ============================================================
//  monitoreo.php — Datos de asistencia en tiempo real
//  GET: devuelve registros de hoy + estadísticas
// ============================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include('credenciales.php');
if (!$conn) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Sin conexión.']);
    exit();
}

$hoy = date('Y-m-d');

// ── Registros del día con datos de persona ────────────────────────────────────
$sql = "
    SELECT
        a.id_asistencia,
        a.id_persona,
        a.fecha,
        a.hora_entrada,
        a.hora_salida,
        a.tipo_registro,
        p.nombre,
        p.apellido_paterno,
        p.apellido_materno,
        p.tipo_persona,
        COALESCE(al.matricula, CONCAT(p.tipo_persona, '-', p.id_persona)) AS matricula,
        COALESCE(al.estado_institucional, 'activo') AS estado_institucional,
        al.id_nivel
    FROM asistencia a
    INNER JOIN persona p ON p.id_persona = a.id_persona
    LEFT JOIN alumno al ON al.id_persona = a.id_persona
    WHERE a.fecha = ?
    ORDER BY a.id_asistencia DESC
    LIMIT 100
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $hoy);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$registros = [];
$adentro   = 0;
$entradas  = 0;
$denegados = 0;
$salidas   = 0;

while ($row = $res->fetch_assoc()) {
    $registros[] = [
        'id'                   => $row['id_asistencia'],
        'nombre'               => trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']),
        'tipo_persona'         => $row['tipo_persona'],
        'matricula'            => $row['matricula'],
        'estado_institucional' => $row['estado_institucional'],
        'hora_entrada'         => $row['hora_entrada'] ? substr($row['hora_entrada'], 0, 5) : null,
        'hora_salida'          => $row['hora_salida']  ? substr($row['hora_salida'],  0, 5) : null,
        'tipo_registro'        => $row['tipo_registro'],
        'id_nivel'             => $row['id_nivel'],
    ];
    if ($row['tipo_registro'] === 'entrada' && !$row['hora_salida']) $adentro++;
    if ($row['tipo_registro'] === 'entrada' && $row['hora_entrada'])  $entradas++;
    if ($row['hora_salida']) $salidas++;
}

echo json_encode([
    'ok'        => true,
    'registros' => $registros,
    'stats'     => [
        'adentro'   => $adentro,
        'entradas'  => $entradas,
        'denegados' => $denegados,
        'salidas'   => $salidas,
    ]
]);

$conn->close();
