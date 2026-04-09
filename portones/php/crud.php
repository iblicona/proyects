<?php
// ============================================================
//  crud.php — Endpoint REST para gestión CRUD de tablas
//  Ruta servidor: /var/www/proyects/api/portones/php/crud.php
//
//  Acepta POST con JSON: { accion, tabla, id?, datos? }
//  Acciones: listar | crear | actualizar | eliminar
// ============================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
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
];

if (!array_key_exists($tabla, $tablas_permitidas)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => "Tabla '{$tabla}' no permitida."]);
    exit();
}

$pk = $tablas_permitidas[$tabla];

switch ($accion) {
    case 'listar':
        accion_listar($conn, $tabla, $pk);
        break;
    case 'crear':
        accion_crear($conn, $tabla, $body['datos'] ?? []);
        break;
    case 'actualizar':
        accion_actualizar($conn, $tabla, $pk, $body['id'] ?? null, $body['datos'] ?? []);
        break;
    case 'eliminar':
        accion_eliminar($conn, $tabla, $pk, $body['id'] ?? null);
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => "Acción '{$accion}' no reconocida."]);
}

$conn->close();

// ── LISTAR ────────────────────────────────────────────────────────────────────
function accion_listar($conn, $tabla, $pk)
{
    // Consultas con JOIN para mayor legibilidad en el panel
    $queries = [
        'persona' => "
            SELECT p.*,
                   a.matricula,
                   a.estado_institucional,
                   d.especialidad,
                   adm.puesto
            FROM persona p
            LEFT JOIN alumno         a   ON a.id_persona   = p.id_persona
            LEFT JOIN docente        d   ON d.id_persona   = p.id_persona
            LEFT JOIN administrativo adm ON adm.id_persona = p.id_persona
            ORDER BY p.id_persona DESC",

        'alumno' => "
            SELECT a.*,
                   p.nombre, p.apellido_paterno, p.apellido_materno,
                   p.correo, p.telefono
            FROM alumno a
            JOIN persona p ON p.id_persona = a.id_persona
            ORDER BY a.id_alumno DESC",

        'docente' => "
            SELECT d.*,
                   p.nombre, p.apellido_paterno, p.apellido_materno,
                   p.correo, p.telefono
            FROM docente d
            JOIN persona p ON p.id_persona = d.id_persona
            ORDER BY d.id_docente DESC",

        'administrativo' => "
            SELECT adm.*,
                   p.nombre, p.apellido_paterno, p.apellido_materno,
                   p.correo, p.telefono
            FROM administrativo adm
            JOIN persona p ON p.id_persona = adm.id_persona
            ORDER BY adm.id_administrativo DESC",

        'asistencia' => "
            SELECT ast.*,
                   CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_persona
            FROM asistencia ast
            JOIN persona p ON p.id_persona = ast.id_persona
            ORDER BY ast.fecha DESC, ast.hora_entrada DESC
            LIMIT 300",

        'visita' => "
            SELECT v.*,
                   CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_persona
            FROM visita v
            JOIN persona p ON p.id_persona = v.id_persona
            ORDER BY v.id_visita DESC",
    ];

    $sql = $queries[$tabla] ?? "SELECT * FROM `{$tabla}` ORDER BY `{$pk}` DESC";

    $result = $conn->query($sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error en consulta: ' . $conn->error]);
        return;
    }

    $filas = [];
    while ($fila = $result->fetch_assoc()) {
        $filas[] = $fila;
    }

    echo json_encode(['ok' => true, 'datos' => $filas, 'total' => count($filas)]);
}

// ── CREAR ─────────────────────────────────────────────────────────────────────
function accion_crear($conn, $tabla, $datos)
{
    if (empty($datos)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'No se recibieron datos para crear.']);
        return;
    }

    // Validar nombres de columna (prevención de SQL injection en identificadores)
    foreach (array_keys($datos) as $col) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => "Nombre de columna inválido: {$col}"]);
            return;
        }
    }

    $columnas     = array_keys($datos);
    $valores      = array_values($datos);
    $placeholders = implode(', ', array_fill(0, count($columnas), '?'));
    $cols_str     = '`' . implode('`, `', $columnas) . '`';

    $sql  = "INSERT INTO `{$tabla}` ({$cols_str}) VALUES ({$placeholders})";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando inserción: ' . $conn->error]);
        return;
    }

    $tipos = str_repeat('s', count($valores)); // MySQL convierte tipos automáticamente
    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        echo json_encode([
            'ok'     => true,
            'id'     => $conn->insert_id,
            'mensaje'=> 'Registro creado correctamente.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al insertar: ' . $stmt->error]);
    }
    $stmt->close();
}

// ── ACTUALIZAR ────────────────────────────────────────────────────────────────
function accion_actualizar($conn, $tabla, $pk, $id, $datos)
{
    if (!$id || empty($datos)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID y datos son requeridos para actualizar.']);
        return;
    }

    $sets   = [];
    $valores = [];

    foreach ($datos as $col => $val) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => "Nombre de columna inválido: {$col}"]);
            return;
        }
        $sets[]   = "`{$col}` = ?";
        $valores[] = $val;
    }
    $valores[] = $id; // para el WHERE

    $sql  = "UPDATE `{$tabla}` SET " . implode(', ', $sets) . " WHERE `{$pk}` = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando actualización: ' . $conn->error]);
        return;
    }

    $tipos = str_repeat('s', count($valores));
    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        echo json_encode([
            'ok'       => true,
            'afectados'=> $stmt->affected_rows,
            'mensaje'  => 'Registro actualizado correctamente.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar: ' . $stmt->error]);
    }
    $stmt->close();
}

// ── ELIMINAR ──────────────────────────────────────────────────────────────────
function accion_eliminar($conn, $tabla, $pk, $id)
{
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'ID es requerido para eliminar.']);
        return;
    }

    $sql  = "DELETE FROM `{$tabla}` WHERE `{$pk}` = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error preparando eliminación: ' . $conn->error]);
        return;
    }

    $stmt->bind_param('s', $id);

    if ($stmt->execute()) {
        echo json_encode([
            'ok'       => true,
            'afectados'=> $stmt->affected_rows,
            'mensaje'  => 'Registro eliminado correctamente.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'mensaje' => 'Error al eliminar: ' . $stmt->error]);
    }
    $stmt->close();
}
