<?php
// ============================================================
//  crud.php — Endpoint REST para gestión CRUD de tablas
//  Acepta POST con JSON: { accion, tabla, id?, datos? }
//  Acciones: listar | crear | actualizar | eliminar
// ============================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit();
}

include('credenciales.php');
if (!$conn) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Sin conexión a la base de datos.']);
    exit();
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = trim($body['accion'] ?? '');
$tabla  = trim($body['tabla']  ?? '');

// ── Tablas permitidas y sus llaves primarias ──────────────────────────────
$tablas_permitidas = [
    'persona'        => 'id_persona',
    'alumno'         => 'id_alumno',
    'docente'        => 'id_docente',
    'administrativo' => 'id_administrativo',
    'asistencia'     => 'id_asistencia',
    'visita'         => 'id_visita',
    'usuario'        => 'id_usuario',
    'horario_grupo'  => 'id_horario',
    'carrera'        => 'id_carrera',
    'nivel'          => 'id_nivel',
];

if (!array_key_exists($tabla, $tablas_permitidas)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => "Tabla '{$tabla}' no permitida."]);
    exit();
}

$pk = $tablas_permitidas[$tabla];

switch ($accion) {
    case 'listar':    accion_listar($conn, $tabla, $pk);                             break;
    case 'crear':     accion_crear($conn, $tabla, $body['datos'] ?? []);              break;
    case 'actualizar':accion_actualizar($conn,$tabla,$pk,$body['id']??null,$body['datos']??[]); break;
    case 'eliminar':  accion_eliminar($conn, $tabla, $pk, $body['id'] ?? null);       break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => "Acción '{$accion}' no reconocida."]);
}

$conn->close();

// ── LISTAR ────────────────────────────────────────────────────────────────────
function accion_listar($conn, $tabla, $pk) {
    $result = $conn->query("SELECT * FROM `{$tabla}` ORDER BY `{$pk}` ASC");
    if (!$result) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al consultar: ' . $conn->error]);
        return;
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode(['ok' => true, 'datos' => $rows]);
}

// ── CREAR ─────────────────────────────────────────────────────────────────────
function accion_crear($conn, $tabla, $datos) {
    if (empty($datos)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'No se enviaron datos para crear.']);
        return;
    }
    $cols   = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($datos)));
    $pholds = implode(', ', array_fill(0, count($datos), '?'));
    $types  = str_repeat('s', count($datos));
    $vals   = array_values($datos);

    $stmt = $conn->prepare("INSERT INTO `{$tabla}` ({$cols}) VALUES ({$pholds})");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando INSERT: ' . $conn->error]);
        return;
    }
    $stmt->bind_param($types, ...$vals);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'id' => $conn->insert_id, 'mensaje' => 'Registro creado.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al crear: ' . $stmt->error]);
    }
    $stmt->close();
}

// ── ACTUALIZAR ────────────────────────────────────────────────────────────────
function accion_actualizar($conn, $tabla, $pk, $id, $datos) {
    if (!$id || empty($datos)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID y datos son requeridos para actualizar.']);
        return;
    }
    $sets  = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($datos)));
    $types = str_repeat('s', count($datos)) . 's';
    $vals  = array_merge(array_values($datos), [$id]);

    $stmt = $conn->prepare("UPDATE `{$tabla}` SET {$sets} WHERE `{$pk}` = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando UPDATE: ' . $conn->error]);
        return;
    }
    $stmt->bind_param($types, ...$vals);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'mensaje' => 'Registro actualizado.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar: ' . $stmt->error]);
    }
    $stmt->close();
}

// ── ELIMINAR ─────────────────────────────────────────────────────────────────
function accion_eliminar($conn, $tabla, $pk, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID es requerido para eliminar.']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM `{$tabla}` WHERE `{$pk}` = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando DELETE: ' . $conn->error]);
        return;
    }
    $stmt->bind_param('s', $id);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'mensaje' => 'Registro eliminado.']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al eliminar: ' . $stmt->error]);
    }
    $stmt->close();
}
