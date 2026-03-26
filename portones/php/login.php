<?php
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(0);
// ============================================================
//  login.php — Autenticación de usuarios
//  Ruta servidor: /var/www/proyects/api/portones/login.php
// ============================================================

// 1. Headers CORS y tipo de respuesta
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Responder al preflight OPTIONS directamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Sólo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit();
}

// 3. Conexión a la BD
try {
    include("/var/www/proyects/api/dbconection.php");
}
catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión a la base de datos.']);
    exit();
}
// $conn es el objeto mysqli expuesto por dbConnection.php

// 4. Leer JSON del body
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$user = isset($body['username']) ? trim($body['username']) : '';
$pass = isset($body['password']) ? trim($body['password']) : '';

if (empty($user) || empty($pass)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario y contraseña son requeridos.']);
    exit();
}

// 5. Consulta con prepared statement
// NOTA: Si la contraseña está hasheada (MD5, SHA1, bcrypt) en la BD,
//       ajusta la comparación aquí (ej. $pass = md5($pass);)
$sql = "SELECT id_usuario, username, password, rol FROM usuario WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error interno del servidor.']);
    exit();
}

$stmt->bind_param('s', $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.']);
    $stmt->close();
    exit();
}

$usuario = $result->fetch_assoc();
$stmt->close();

// 6. Verificar contraseña (texto plano por ahora; cambiar en producción)
if ($usuario['password'] !== $pass) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.']);
    exit();
}

// 7. Respuesta exitosa
echo json_encode([
    'ok' => true,
    'rol' => $usuario['rol'],
    'nombre' => $usuario['username'],
    'mensaje' => 'Inicio de sesión exitoso.'
]);

$conn->close();
