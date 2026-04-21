<?php
// ============================================================
//  registro.php — Registro de alumno / docente / administrativo
//  Genera automáticamente el token QR (ITLA-{id_persona})
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

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Datos comunes (tabla persona) ────────────────────────────────────────────
$nombre            = trim($body['nombre']            ?? '');
$apellido_paterno  = trim($body['apellido_paterno']  ?? '');
$apellido_materno  = trim($body['apellido_materno']  ?? '');
$fecha_nacimiento  = !empty($body['fecha_nacimiento']) ? trim($body['fecha_nacimiento']) : null;
$telefono          = !empty($body['telefono'])  ? trim($body['telefono'])  : null;
$correo            = !empty($body['correo'])    ? trim($body['correo'])    : null;
$tipo_persona      = trim($body['tipo_persona'] ?? '');

if (empty($nombre) || empty($apellido_paterno) || empty($tipo_persona)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Nombre, apellido paterno y tipo de persona son requeridos.']);
    exit();
}

$tipos_validos = ['alumno', 'docente', 'administrativo'];
if (!in_array($tipo_persona, $tipos_validos)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => "Tipo '{$tipo_persona}' no válido."]);
    exit();
}

$conn->begin_transaction();

try {
    // 1. Insertar en persona
    $stmt = $conn->prepare(
        "INSERT INTO persona (nombre, apellido_paterno, apellido_materno,
                              fecha_nacimiento, telefono, correo, tipo_persona)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) throw new Exception("Error preparando persona: " . $conn->error);
    $stmt->bind_param('sssssss',
        $nombre, $apellido_paterno, $apellido_materno,
        $fecha_nacimiento, $telefono, $correo, $tipo_persona
    );
    $stmt->execute();
    $id_persona = $conn->insert_id;
    $stmt->close();

    // 2. Insertar en tabla específica
    if ($tipo_persona === 'alumno') {
        $matricula            = !empty($body['matricula'])         ? trim($body['matricula'])   : null;
        $grado                = !empty($body['grado'])             ? intval($body['grado'])      : null;
        $grupo                = !empty($body['grupo'])             ? trim($body['grupo'])        : null;
        $cuatrimestre         = !empty($body['cuatrimestre'])      ? intval($body['cuatrimestre']) : null;
        $id_nivel             = !empty($body['nivel_educativo'])   ? intval($body['nivel_educativo']) : null;
        $id_carrera           = !empty($body['carrera'])           ? intval($body['carrera'])   : null;
        $estado_institucional = !empty($body['estado_institucional']) ? trim($body['estado_institucional']) : 'activo';

        if (!$id_nivel)
            throw new Exception("El nivel educativo es requerido para alumno.");

        // Si es Preparatoria (nivel 1) no se requiere carrera
        if ($id_nivel == 1) $id_carrera = null;

        $stmt = $conn->prepare(
            "INSERT INTO alumno (id_persona, matricula, grado, grupo, cuatrimestre,
                                  id_nivel, id_carrera, estado_institucional)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) throw new Exception("Error preparando alumno: " . $conn->error);
        $stmt->bind_param('isisiiis',
            $id_persona, $matricula, $grado, $grupo, $cuatrimestre,
            $id_nivel, $id_carrera, $estado_institucional
        );
        $stmt->execute();
        $stmt->close();

    } elseif ($tipo_persona === 'docente') {
        $especialidad = !empty($body['especialidad'])   ? trim($body['especialidad'])         : null;
        $id_nivel     = !empty($body['nivel_docente'])  ? intval($body['nivel_docente'])      : null;
        if (!$id_nivel) throw new Exception("El nivel educativo es requerido para docente.");

        $stmt = $conn->prepare("INSERT INTO docente (id_persona, especialidad, id_nivel) VALUES (?, ?, ?)");
        if (!$stmt) throw new Exception("Error preparando docente: " . $conn->error);
        $stmt->bind_param('isi', $id_persona, $especialidad, $id_nivel);
        $stmt->execute();
        $stmt->close();

    } elseif ($tipo_persona === 'administrativo') {
        $puesto       = !empty($body['puesto'])       ? trim($body['puesto'])       : null;
        $departamento = !empty($body['departamento']) ? trim($body['departamento']) : null;

        $stmt = $conn->prepare("INSERT INTO administrativo (id_persona, puesto, departamento) VALUES (?, ?, ?)");
        if (!$stmt) throw new Exception("Error preparando administrativo: " . $conn->error);
        $stmt->bind_param('iss', $id_persona, $puesto, $departamento);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    // Token QR único: ITLA-{id_persona} (no requiere columna adicional en BD)
    $qr_token = 'ITLA-' . str_pad($id_persona, 6, '0', STR_PAD_LEFT);

    echo json_encode([
        'ok'         => true,
        'id_persona' => $id_persona,
        'qr_token'   => $qr_token,
        'nombre'     => $nombre . ' ' . $apellido_paterno,
        'tipo'       => $tipo_persona,
        'mensaje'    => 'Registro guardado correctamente.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar: ' . $e->getMessage()]);
}

$conn->close();
