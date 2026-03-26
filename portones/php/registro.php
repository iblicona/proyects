<?php
// ============================================================
//  registro.php — Registro de alumno / docente / administrativo
//  Ruta servidor: /var/www/proyects/api/portones/registro.php
//
//  Esquema real:
//    persona  (id_persona, nombre, apellido_paterno, apellido_materno,
//              fecha_nacimiento, telefono, correo, tipo_persona)
//    alumno   (id_alumno, id_persona, matricula, grado, grupo,
//              cuatrimestre, id_nivel, id_carrera, estado_institucional)
//    docente  (id_docente, id_persona, especialidad, id_nivel)
//    administrativo (id_administrativo, id_persona, puesto, departamento)
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

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Datos comunes (tabla persona) ─────────────────────────────────────────
$nombre = isset($body['nombre']) ? trim($body['nombre']) : '';
$apellido_paterno = isset($body['apellido_paterno']) ? trim($body['apellido_paterno']) : '';
$apellido_materno = isset($body['apellido_materno']) ? trim($body['apellido_materno']) : '';
$fecha_nacimiento = !empty($body['fecha_nacimiento']) ? trim($body['fecha_nacimiento']) : null;
$telefono = !empty($body['telefono']) ? trim($body['telefono']) : null;
$correo = !empty($body['correo']) ? trim($body['correo']) : null;
$tipo_persona = isset($body['tipo_persona']) ? trim($body['tipo_persona']) : '';

if (empty($nombre) || empty($apellido_paterno) || empty($tipo_persona)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Nombre, apellido paterno y tipo de persona son requeridos.']);
    exit();
}

$tipos_validos = ['alumno', 'docente', 'administrativo'];
if (!in_array($tipo_persona, $tipos_validos)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => "Tipo '{$tipo_persona}' no válido para registro."]);
    exit();
}

// ── Transacción para consistencia ────────────────────────────────────────
$conn->begin_transaction();

try {
    // 1. Insertar en persona
    $sql_p = "INSERT INTO persona
                (nombre, apellido_paterno, apellido_materno,
                 fecha_nacimiento, telefono, correo, tipo_persona)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_p);
    if (!$stmt)
        throw new Exception("Error preparando persona: " . $conn->error);

    $stmt->bind_param('sssssss',
        $nombre, $apellido_paterno, $apellido_materno,
        $fecha_nacimiento, $telefono, $correo, $tipo_persona
    );
    $stmt->execute();
    $id_persona = $conn->insert_id;
    $stmt->close();

    // 2. Insertar en tabla específica
    if ($tipo_persona === 'alumno') {
        // Campos de alumno — todos los del esquema real, incluyendo cuatrimestre
        $matricula = !empty($body['matricula']) ? trim($body['matricula']) : null;
        $grado = !empty($body['grado']) ? intval($body['grado']) : null;
        $grupo = !empty($body['grupo']) ? trim($body['grupo']) : null;
        $cuatrimestre = !empty($body['cuatrimestre']) ? intval($body['cuatrimestre']) : null;
        $id_nivel = !empty($body['nivel_educativo']) ? intval($body['nivel_educativo']) : null;
        $id_carrera = !empty($body['carrera']) ? intval($body['carrera']) : null;
        $estado_institucional = !empty($body['estado_institucional']) ? trim($body['estado_institucional']) : 'activo';

        if (!$id_nivel)
            throw new Exception("El nivel educativo es requerido para alumno.");

        $sql_a = "INSERT INTO alumno
                    (id_persona, matricula, grado, grupo, cuatrimestre,
                     id_nivel, id_carrera, estado_institucional)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_a);
        if (!$stmt)
            throw new Exception("Error preparando alumno: " . $conn->error);

        $stmt->bind_param('isisiiis',
            $id_persona, $matricula, $grado, $grupo, $cuatrimestre,
            $id_nivel, $id_carrera, $estado_institucional
        );
        $stmt->execute();
        $stmt->close();

    }
    elseif ($tipo_persona === 'docente') {
        $especialidad = !empty($body['especialidad']) ? trim($body['especialidad']) : null;
        $id_nivel = !empty($body['nivel_docente']) ? intval($body['nivel_docente']) : null;

        if (!$id_nivel)
            throw new Exception("El nivel educativo es requerido para docente.");

        $sql_d = "INSERT INTO docente (id_persona, especialidad, id_nivel)
                  VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_d);
        if (!$stmt)
            throw new Exception("Error preparando docente: " . $conn->error);

        $stmt->bind_param('isi', $id_persona, $especialidad, $id_nivel);
        $stmt->execute();
        $stmt->close();

    }
    elseif ($tipo_persona === 'administrativo') {
        $puesto = !empty($body['puesto']) ? trim($body['puesto']) : null;
        $departamento = !empty($body['departamento']) ? trim($body['departamento']) : null;

        $sql_adm = "INSERT INTO administrativo (id_persona, puesto, departamento)
                    VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_adm);
        if (!$stmt)
            throw new Exception("Error preparando administrativo: " . $conn->error);

        $stmt->bind_param('iss', $id_persona, $puesto, $departamento);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'id_persona' => $id_persona,
        'mensaje' => 'Registro guardado correctamente.'
    ]);

}
catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar: ' . $e->getMessage()]);
}

$conn->close();
