<?php
// ============================================================
//  invitado.php — Registro de visitantes externos
//  Ruta servidor: /var/www/proyects/api/portones/invitado.php
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

require_once('../../api/dbconection.php');

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// --- Datos del visitante ---
$nombre           = isset($body['nombre'])            ? trim($body['nombre'])            : '';
$apellido_paterno = isset($body['apellido_paterno'])  ? trim($body['apellido_paterno'])  : '';
$apellido_materno = isset($body['apellido_materno'])  ? trim($body['apellido_materno'])  : '';
$telefono         = isset($body['telefono'])          ? trim($body['telefono'])          : null;

// --- Datos de la visita ---
$curp             = isset($body['curp'])              ? trim($body['curp'])              : '';
$motivo           = isset($body['motivo'])            ? trim($body['motivo'])            : '';
$persona_a_visitar= isset($body['persona_a_visitar']) ? trim($body['persona_a_visitar']) : '';
$fecha_visita     = isset($body['fecha'])             ? trim($body['fecha'])             : date('Y-m-d');
$hora_llegada     = isset($body['hora'])              ? trim($body['hora'])              : date('H:i:s');
$duracion_horas   = isset($body['duracion_horas'])    ? intval($body['duracion_horas'])  : 1;

if (empty($nombre) || empty($apellido_paterno) || empty($curp) || empty($motivo) || empty($persona_a_visitar)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios (nombre, apellido paterno, CURP, motivo, persona a visitar).']);
    exit();
}

// Tipo de persona forzado a 'invitado'
$tipo_persona = 'invitado';

$conn->begin_transaction();

try {
    // 1. Insertar en persona como 'invitado'
    $sql_persona = "INSERT INTO persona
                      (nombre, apellido_paterno, apellido_materno, telefono, tipo_persona)
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_persona);
    if (!$stmt) throw new Exception("Error preparando persona: " . $conn->error);

    $stmt->bind_param('sssss',
        $nombre, $apellido_paterno, $apellido_materno, $telefono, $tipo_persona
    );
    $stmt->execute();
    $id_persona = $conn->insert_id;
    $stmt->close();

    // 2. Insertar en visita
    $sql_visita = "INSERT INTO visita
                     (id_persona, curp, motivo, persona_a_visitar, fecha_visita, hora_llegada, duracion_horas)
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_visita);
    if (!$stmt) throw new Exception("Error preparando visita: " . $conn->error);

    $stmt->bind_param('isssssi',
        $id_persona, $curp, $motivo, $persona_a_visitar,
        $fecha_visita, $hora_llegada, $duracion_horas
    );
    $stmt->execute();
    $id_visita = $conn->insert_id;
    $stmt->close();

    $conn->commit();

    echo json_encode([
        'ok'         => true,
        'id_persona' => $id_persona,
        'id_visita'  => $id_visita,
        'mensaje'    => 'Invitado registrado correctamente.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al registrar invitado: ' . $e->getMessage()]);
}

$conn->close();
