<?php
// ============================================================
//  acceso.php — Lógica de monitoreo / control de acceso QR
//  Ruta servidor: /var/www/proyects/api/portones/acceso.php
//
//  Esquema real de la tabla asistencia:
//    id_asistencia | id_persona | fecha | hora_entrada | hora_salida | tipo_registro
//
//  Flujo:
//    1. Recibe { matricula, tipo_evento: 'entrada'|'salida' }
//    2. Busca alumno JOINando persona → obtiene id_persona, estado, nivel, grupo
//    3. Si estado_institucional ≠ 'activo' → deniega (sin registrar en asistencia)
//    4. Si es Preparatoria (id_nivel=1) + salida → verifica horario_grupo
//    5. Registra en asistencia con las columnas correctas
//    6. Devuelve resultado
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

require_once('../../api/dbConection.php');

$body        = json_decode(file_get_contents('php://input'), true);
$matricula   = isset($body['matricula'])   ? trim($body['matricula'])   : '';
$tipo_evento = isset($body['tipo_evento']) ? trim($body['tipo_evento']) : 'entrada'; // 'entrada' | 'salida'

if (empty($matricula)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'La matrícula es requerida.']);
    exit();
}

// ── PASO 1: Buscar alumno con JOIN a persona ───────────────────────────────
$sql_alumno = "
    SELECT
        a.id_alumno,
        a.matricula,
        a.id_nivel,
        a.grupo,
        a.estado_institucional,
        p.id_persona,
        p.nombre,
        p.apellido_paterno,
        p.apellido_materno
    FROM alumno a
    INNER JOIN persona p ON p.id_persona = a.id_persona
    WHERE a.matricula = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql_alumno);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno al preparar consulta.']);
    exit();
}

$stmt->bind_param('s', $matricula);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'ok'        => false,
        'permitido' => false,
        'mensaje'   => "Matrícula '{$matricula}' no encontrada en el sistema."
    ]);
    exit();
}

$alumno          = $result->fetch_assoc();
$id_persona      = $alumno['id_persona'];
$nombre_completo = $alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno'];

// ── PASO 2: Verificar estado institucional ─────────────────────────────────
if ($alumno['estado_institucional'] !== 'activo') {
    $estado = $alumno['estado_institucional'];
    $msgs   = [
        'suspendido' => 'Acceso bloqueado: alumno suspendido.',
        'baja'       => 'Acceso bloqueado: alumno con baja definitiva.',
        'egresado'   => 'Acceso bloqueado: credencial de egresado vencida.',
    ];
    $mensaje = $msgs[$estado] ?? "Acceso bloqueado: estado '{$estado}'.";

    // No registramos en asistencia intentos de suspendidos / baja / egresados
    echo json_encode([
        'ok'                   => true,
        'permitido'            => false,
        'estado_institucional' => $estado,
        'nombre'               => $nombre_completo,
        'matricula'            => $matricula,
        'mensaje'              => $mensaje
    ]);
    $conn->close();
    exit();
}

// ── PASO 3: Verificar horario (sólo Preparatoria + Salida) ─────────────────
$razon_denegacion = null;

if ((int)$alumno['id_nivel'] === 1 && $tipo_evento === 'salida') {
    $grupo       = $alumno['grupo'];
    $hora_actual = date('H:i:s');

    $sql_h = "SELECT hora_salida_permitida FROM horario_grupo WHERE grupo = ? LIMIT 1";
    $stmt2 = $conn->prepare($sql_h);

    if ($stmt2) {
        $stmt2->bind_param('s', $grupo);
        $stmt2->execute();
        $res_h = $stmt2->get_result();
        $stmt2->close();

        if ($res_h->num_rows > 0) {
            $hora_permitida = $res_h->fetch_assoc()['hora_salida_permitida'];
            if ($hora_actual < $hora_permitida) {
                $razon_denegacion = "Salida denegada por horario. Permitida desde: {$hora_permitida}. Hora actual: {$hora_actual}.";
            }
        }
        // Si grupo no está en horario_grupo se permite la salida
    }
}

if ($razon_denegacion !== null) {
    // Registrar intento de salida denegada en asistencia
    registrarAsistencia($conn, $id_persona, $tipo_evento);

    echo json_encode([
        'ok'                   => true,
        'permitido'            => false,
        'estado_institucional' => $alumno['estado_institucional'],
        'nombre'               => $nombre_completo,
        'matricula'            => $matricula,
        'id_nivel'             => $alumno['id_nivel'],
        'grupo'                => $alumno['grupo'],
        'mensaje'              => $razon_denegacion
    ]);
    $conn->close();
    exit();
}

// ── PASO 4: Acceso PERMITIDO ─────────────────────────────────────────────────
registrarAsistencia($conn, $id_persona, $tipo_evento);

echo json_encode([
    'ok'                   => true,
    'permitido'            => true,
    'estado_institucional' => $alumno['estado_institucional'],
    'nombre'               => $nombre_completo,
    'matricula'            => $matricula,
    'id_nivel'             => $alumno['id_nivel'],
    'grupo'                => $alumno['grupo'],
    'mensaje'              => ucfirst($tipo_evento) . ' registrada correctamente.'
]);

$conn->close();

// ── FUNCIÓN AUXILIAR ──────────────────────────────────────────────────────────
/**
 * Registra una entrada o salida en la tabla asistencia.
 *
 * Esquema real:
 *   id_asistencia | id_persona | fecha DATE | hora_entrada TIME | hora_salida TIME | tipo_registro ENUM('entrada','salida')
 *
 * Lógica:
 *  - 'entrada': inserta nueva fila con hora_entrada = NOW().
 *  - 'salida':  busca la última entrada del día sin hora_salida y la actualiza;
 *               si no existe, inserta fila nueva con hora_salida = NOW().
 *
 * @param mysqli $conn
 * @param int    $id_persona
 * @param string $tipo_evento  'entrada' | 'salida'
 */
function registrarAsistencia($conn, $id_persona, $tipo_evento) {
    $fecha     = date('Y-m-d');
    $hora_now  = date('H:i:s');

    if ($tipo_evento === 'entrada') {
        $sql  = "INSERT INTO asistencia (id_persona, fecha, hora_entrada, tipo_registro)
                 VALUES (?, ?, ?, 'entrada')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return;
        $stmt->bind_param('iss', $id_persona, $fecha, $hora_now);
        $stmt->execute();
        $stmt->close();

    } elseif ($tipo_evento === 'salida') {
        // Intentar actualizar la última entrada sin salida del mismo día
        $sql_upd = "UPDATE asistencia
                    SET hora_salida = ?, tipo_registro = 'salida'
                    WHERE id_persona = ?
                      AND fecha = ?
                      AND hora_salida IS NULL
                    ORDER BY hora_entrada DESC
                    LIMIT 1";
        $stmt = $conn->prepare($sql_upd);
        if (!$stmt) return;
        $stmt->bind_param('sis', $hora_now, $id_persona, $fecha);
        $stmt->execute();
        $filas_afectadas = $stmt->affected_rows;
        $stmt->close();

        // Si no había fila de entrada pendiente, crear registro de salida directamente
        if ($filas_afectadas === 0) {
            $sql_ins = "INSERT INTO asistencia (id_persona, fecha, hora_salida, tipo_registro)
                        VALUES (?, ?, ?, 'salida')";
            $stmt2 = $conn->prepare($sql_ins);
            if (!$stmt2) return;
            $stmt2->bind_param('iss', $id_persona, $fecha, $hora_now);
            $stmt2->execute();
            $stmt2->close();
        }
    }
}
