<?php
// ============================================================
//  acceso.php — Control de acceso QR / Matrícula
//  Acepta: { matricula } OR { qr_token }  +  tipo_evento
//  qr_token format: ITLA-000042  (id_persona con padding)
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

$body        = json_decode(file_get_contents('php://input'), true) ?? [];
$tipo_evento = isset($body['tipo_evento']) ? trim($body['tipo_evento']) : 'entrada';
$matricula   = isset($body['matricula'])   ? trim($body['matricula'])   : '';
$qr_token    = isset($body['qr_token'])    ? trim($body['qr_token'])   : '';

// Extraer id_persona desde token QR (ITLA-000042 → 42)
$id_persona_qr = null;
if (!empty($qr_token) && preg_match('/^ITLA-(\d+)$/i', $qr_token, $m)) {
    $id_persona_qr = intval($m[1]);
}

if (empty($matricula) && $id_persona_qr === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Se requiere matrícula o código QR válido.']);
    exit();
}

// ── PASO 1: Buscar persona y su tipo ─────────────────────────────────────────
$persona = null;
$tipo_persona = null;

if ($id_persona_qr !== null) {
    // Buscar por id_persona
    $stmt = $conn->prepare("SELECT * FROM persona WHERE id_persona = ? LIMIT 1");
    $stmt->bind_param('i', $id_persona_qr);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    if ($res->num_rows > 0) {
        $persona      = $res->fetch_assoc();
        $tipo_persona = $persona['tipo_persona'];
    }
} else {
    // Buscar alumno por matrícula (compatibilidad con monitoreo manual)
    $stmt = $conn->prepare(
        "SELECT p.*, a.matricula, a.id_nivel, a.grupo, a.estado_institucional,
                a.id_alumno
         FROM alumno a
         INNER JOIN persona p ON p.id_persona = a.id_persona
         WHERE a.matricula = ? LIMIT 1"
    );
    $stmt->bind_param('s', $matricula);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $persona = $row;
        $tipo_persona = 'alumno';
        // Incluir datos del alumno en $persona para evitar segunda consulta
        $persona['_alumno_loaded'] = true;
    }
}

if (!$persona) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'permitido' => false,
        'mensaje' => "Persona no encontrada en el sistema."]);
    exit();
}

$id_persona     = $persona['id_persona'];
$nombre_completo = trim($persona['nombre'] . ' ' . $persona['apellido_paterno'] . ' ' . $persona['apellido_materno']);

// ── PASO 2: Obtener estado según tipo ────────────────────────────────────────
$estado_institucional = 'activo';
$id_nivel = null;
$grupo    = null;
$mat_display = $matricula;

if ($tipo_persona === 'alumno') {
    if (!empty($persona['_alumno_loaded'])) {
        // Ya tenemos los datos del alumno del JOIN anterior
        $estado_institucional = $persona['estado_institucional'] ?? 'activo';
        $id_nivel             = $persona['id_nivel'] ?? null;
        $grupo                = $persona['grupo']    ?? null;
        $mat_display          = $persona['matricula'] ?? '';
    } else {
        $stmt = $conn->prepare(
            "SELECT matricula, id_nivel, grupo, estado_institucional
             FROM alumno WHERE id_persona = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id_persona);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        if ($res->num_rows > 0) {
            $al = $res->fetch_assoc();
            $estado_institucional = $al['estado_institucional'] ?? 'activo';
            $id_nivel             = $al['id_nivel']  ?? null;
            $grupo                = $al['grupo']     ?? null;
            $mat_display          = $al['matricula'] ?? '';
        }
    }
} elseif ($tipo_persona === 'docente') {
    // Docentes: activos por defecto (no tienen estado_institucional en la tabla)
    $mat_display = 'DOC-' . $id_persona;
    $estado_institucional = 'activo';
} elseif ($tipo_persona === 'administrativo') {
    $mat_display = 'ADM-' . $id_persona;
    $estado_institucional = 'activo';
}

// ── PASO 3: Verificar estado institucional ────────────────────────────────────
if ($estado_institucional !== 'activo') {
    $msgs = [
        'suspendido' => 'Acceso bloqueado: usuario suspendido.',
        'baja'       => 'Acceso bloqueado: baja definitiva.',
        'egresado'   => 'Acceso bloqueado: credencial de egresado vencida.',
    ];
    echo json_encode([
        'ok'                   => true,
        'permitido'            => false,
        'estado_institucional' => $estado_institucional,
        'nombre'               => $nombre_completo,
        'matricula'            => $mat_display,
        'tipo_persona'         => $tipo_persona,
        'mensaje'              => $msgs[$estado_institucional] ?? "Acceso bloqueado: estado '{$estado_institucional}'."
    ]);
    $conn->close();
    exit();
}

// ── PASO 4: Verificar horario (Preparatoria + Salida) ─────────────────────────
$razon_denegacion = null;
if ((int)$id_nivel === 1 && $tipo_evento === 'salida' && $grupo) {
    $hora_actual = date('H:i:s');
    $stmt = $conn->prepare("SELECT hora_salida_permitida FROM horario_grupo WHERE grupo = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $grupo);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        if ($res->num_rows > 0) {
            $hora_permitida = $res->fetch_assoc()['hora_salida_permitida'];
            if ($hora_actual < $hora_permitida)
                $razon_denegacion = "Salida denegada por horario. Permitida desde: {$hora_permitida}.";
        }
    }
}

if ($razon_denegacion !== null) {
    registrarAsistencia($conn, $id_persona, $tipo_evento);
    echo json_encode([
        'ok'                   => true,
        'permitido'            => false,
        'estado_institucional' => $estado_institucional,
        'nombre'               => $nombre_completo,
        'matricula'            => $mat_display,
        'id_nivel'             => $id_nivel,
        'tipo_persona'         => $tipo_persona,
        'mensaje'              => $razon_denegacion
    ]);
    $conn->close();
    exit();
}

// ── PASO 5: Acceso PERMITIDO ──────────────────────────────────────────────────
registrarAsistencia($conn, $id_persona, $tipo_evento);
echo json_encode([
    'ok'                   => true,
    'permitido'            => true,
    'estado_institucional' => $estado_institucional,
    'nombre'               => $nombre_completo,
    'matricula'            => $mat_display,
    'id_nivel'             => $id_nivel,
    'tipo_persona'         => $tipo_persona,
    'mensaje'              => ucfirst($tipo_evento) . ' registrada correctamente.'
]);
$conn->close();

function registrarAsistencia($conn, $id_persona, $tipo_evento) {
    $fecha    = date('Y-m-d');
    $hora_now = date('H:i:s');
    if ($tipo_evento === 'entrada') {
        $stmt = $conn->prepare(
            "INSERT INTO asistencia (id_persona, fecha, hora_entrada, tipo_registro)
             VALUES (?, ?, ?, 'entrada')"
        );
        if (!$stmt) return;
        $stmt->bind_param('iss', $id_persona, $fecha, $hora_now);
        $stmt->execute();
        $stmt->close();
    } elseif ($tipo_evento === 'salida') {
        $stmt = $conn->prepare(
            "UPDATE asistencia SET hora_salida = ?, tipo_registro = 'salida'
             WHERE id_persona = ? AND fecha = ? AND hora_salida IS NULL
             ORDER BY hora_entrada DESC LIMIT 1"
        );
        if (!$stmt) return;
        $stmt->bind_param('sis', $hora_now, $id_persona, $fecha);
        $stmt->execute();
        $filas = $stmt->affected_rows;
        $stmt->close();
        if ($filas === 0) {
            $stmt2 = $conn->prepare(
                "INSERT INTO asistencia (id_persona, fecha, hora_salida, tipo_registro)
                 VALUES (?, ?, ?, 'salida')"
            );
            if (!$stmt2) return;
            $stmt2->bind_param('iss', $id_persona, $fecha, $hora_now);
            $stmt2->execute();
            $stmt2->close();
        }
    }
}
