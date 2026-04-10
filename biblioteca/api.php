<?php
require 'config/db.php'; 
header('Content-Type: application/json');

// Intentar sesión PHP primero; si no existe, usar los headers del frontend
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback: si no hay sesión PHP activa, leer headers enviados por app.js
if (!isset($_SESSION['user_id'])) {
    $header_id  = $_SERVER['HTTP_X_USER_ID']  ?? null;
    $header_rol = $_SERVER['HTTP_X_USER_ROL'] ?? null;

    if ($header_id && $header_rol) {
        // Verificar que el usuario realmente exista en la BD antes de confiar en el header
        $check = $conn->prepare("SELECT id, rol FROM usuarios WHERE id = ?");
        $check->bind_param("i", $header_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();

        if ($row) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['rol']     = $row['rol']; // Usamos el rol de la BD, no el del header (más seguro)
        }
    }
}

// Si después de todo no hay sesión, rechazar
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(["error" => "No autorizado"]));
}

$metodo    = $_SERVER['REQUEST_METHOD'];
$rol       = $_SESSION['rol'] ?? 'user';
$id_sesion = (int)$_SESSION['user_id'];

switch($metodo) {
    case 'GET':
        // 1. CASO: HISTORIAL
        if (isset($_GET['historial'])) {
            if ($rol === 'admin') {
                $sql = "SELECT p.*, u.nombre as usuario, l.titulo as libro 
                        FROM prestamos p 
                        JOIN usuarios u ON p.id_usuario = u.id 
                        JOIN libros l ON p.id_libro = l.id 
                        ORDER BY p.fecha_solicitud DESC";
            } else {
                $sql = "SELECT p.*, l.titulo as libro 
                        FROM prestamos p 
                        JOIN libros l ON p.id_libro = l.id 
                        WHERE p.id_usuario = $id_sesion 
                        ORDER BY p.fecha_solicitud DESC";
            }
            $res = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        } 
        // 2. CASO: SOLICITUDES ACTIVAS (Solo Admin)
        elseif (isset($_GET['solicitudes']) && $rol === 'admin') {
            $sql = "SELECT p.*, u.nombre as usuario, l.titulo as libro 
                    FROM prestamos p 
                    JOIN usuarios u ON p.id_usuario = u.id 
                    JOIN libros l ON p.id_libro = l.id 
                    WHERE p.estado IN ('pendiente', 'aprobado') 
                    ORDER BY p.estado DESC, p.fecha_solicitud ASC";
            $res = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        } 
        // 3. CASO: LISTADO DE LIBROS
        else {
            $sql = "SELECT l.*, 
                    (SELECT COUNT(*) FROM prestamos p 
                     WHERE p.id_libro = l.id 
                     AND p.id_usuario = $id_sesion 
                     AND p.estado = 'pendiente') as ya_solicitado 
                    FROM libros l 
                    ORDER BY l.creado_en DESC";
            $res = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_all($res, MYSQLI_ASSOC));
        }
        break;

    case 'POST':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));
        $data = json_decode(file_get_contents("php://input"), true);
        
        $t  = mysqli_real_escape_string($conn, $data['titulo']    ?? '');
        $a  = mysqli_real_escape_string($conn, $data['autor']     ?? '');
        $g  = mysqli_real_escape_string($conn, $data['genero']    ?? '');
        $e  = mysqli_real_escape_string($conn, $data['editorial'] ?? '');
        $an = mysqli_real_escape_string($conn, $data['anio']      ?? '');

        if (!empty($data['id'])) {
            $id  = (int)$data['id'];
            $sql = "UPDATE libros SET titulo='$t', autor='$a', genero='$g', editorial='$e', anio='$an' WHERE id=$id";
        } else {
            $sql = "INSERT INTO libros (titulo, autor, genero, editorial, anio) VALUES ('$t', '$a', '$g', '$e', '$an')";
        }
        mysqli_query($conn, $sql);
        echo json_encode(["status" => "ok"]);
        break;

    case 'PATCH':
        $data = json_decode(file_get_contents("php://input"), true);
        $lib  = (int)($data['id_libro']      ?? 0);
        $d    = (int)($data['dias_prestamo'] ?? 7);
        
        $sql = "INSERT INTO prestamos (id_usuario, id_libro, dias_solicitados, estado) 
                VALUES ($id_sesion, $lib, $d, 'pendiente')";
        mysqli_query($conn, $sql);
        echo json_encode(["status" => "solicitud_enviada"]);
        break;

    case 'PUT':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));
        $data = json_decode(file_get_contents("php://input"), true);
        $ids  = (int)($data['id_solicitud'] ?? 0);
        $idl  = (int)($data['id_libro']     ?? 0);
        $acc  = $data['accion'] ?? '';

        if ($acc === 'aprobar') {
            mysqli_query($conn, "UPDATE prestamos SET estado='aprobado', fecha_entrega=CURDATE() WHERE id=$ids");
            mysqli_query($conn, "UPDATE libros SET prestado=1 WHERE id=$idl");
            echo json_encode(["status" => "aprobado"]);
        } elseif ($acc === 'devolver') {
            mysqli_query($conn, "UPDATE prestamos SET estado='devuelto' WHERE id=$ids");
            mysqli_query($conn, "UPDATE libros SET prestado=0 WHERE id=$idl");
            echo json_encode(["status" => "devuelto"]);
        } else {
            mysqli_query($conn, "UPDATE prestamos SET estado='rechazado' WHERE id=$ids");
            echo json_encode(["status" => "rechazado"]);
        }
        break;

    case 'DELETE':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));
        $id = (int)($_GET['id'] ?? 0);
        mysqli_query($conn, "DELETE FROM libros WHERE id = $id");
        echo json_encode(["status" => "eliminado"]);
        break;
}