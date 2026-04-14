<?php
// api.php — Backend principal
session_start();
require "config/db.php";
header('Content-Type: application/json');

// ------------------------------------------------------------------
// AUTENTICACIÓN
// biblioteca.html es un archivo .html estático, por eso la sesión PHP
// a veces no persiste entre páginas. Como fallback, el frontend manda
// el user_id en un header y lo verificamos contra la BD.
// ------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    $hid = isset($_SERVER['HTTP_X_USER_ID']) ? (int)$_SERVER['HTTP_X_USER_ID'] : 0;

    if ($hid > 0) {
        $chk = $conn->prepare("SELECT id, rol FROM usuarios WHERE id = ?");
        $chk->bind_param("i", $hid);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();

        if ($row) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['rol']     = $row['rol'];
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(["error" => "No autorizado"]));
}

$metodo    = $_SERVER['REQUEST_METHOD'];
$rol       = $_SESSION['rol'] ?? 'user';
$id_sesion = (int)$_SESSION['user_id'];

// ------------------------------------------------------------------
switch ($metodo) {

    // ---- LEER --------------------------------------------------
    case 'GET':

        // Historial de préstamos
        if (isset($_GET['historial'])) {
            if ($rol === 'admin') {
                $sql = "SELECT p.*, u.nombre AS usuario, l.titulo AS libro
                        FROM prestamos p
                        JOIN usuarios u ON p.id_usuario = u.id
                        JOIN libros   l ON p.id_libro   = l.id
                        ORDER BY p.fecha_solicitud DESC";
            } else {
                $sql = "SELECT p.*, l.titulo AS libro
                        FROM prestamos p
                        JOIN libros l ON p.id_libro = l.id
                        WHERE p.id_usuario = $id_sesion
                        ORDER BY p.fecha_solicitud DESC";
            }
            $res = $conn->query($sql);
            if (!$res) die(json_encode(["error" => $conn->error]));
            echo json_encode($res->fetch_all(MYSQLI_ASSOC));

        // Solicitudes activas (solo admin)
        } elseif (isset($_GET['solicitudes']) && $rol === 'admin') {
            $sql = "SELECT p.*, u.nombre AS usuario, l.titulo AS libro
                    FROM prestamos p
                    JOIN usuarios u ON p.id_usuario = u.id
                    JOIN libros   l ON p.id_libro   = l.id
                    WHERE p.estado IN ('pendiente','aprobado')
                    ORDER BY p.estado DESC, p.fecha_solicitud ASC";
            $res = $conn->query($sql);
            if (!$res) die(json_encode(["error" => $conn->error]));
            echo json_encode($res->fetch_all(MYSQLI_ASSOC));

        // Listado de libros
        } else {
            $sql = "SELECT l.*,
                        (SELECT COUNT(*) FROM prestamos p
                         WHERE p.id_libro   = l.id
                           AND p.id_usuario = $id_sesion
                           AND p.estado     = 'pendiente') AS ya_solicitado
                    FROM libros l
                    ORDER BY l.creado_en DESC";
            $res = $conn->query($sql);
            if (!$res) die(json_encode(["error" => $conn->error]));
            echo json_encode($res->fetch_all(MYSQLI_ASSOC));
        }
        break;

    // ---- CREAR / EDITAR LIBRO ----------------------------------
    case 'POST':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) die(json_encode(["error" => "JSON inválido"]));

        $t  = $conn->real_escape_string(trim($data['titulo']    ?? ''));
        $a  = $conn->real_escape_string(trim($data['autor']     ?? ''));
        $g  = $conn->real_escape_string(trim($data['genero']    ?? ''));
        $e  = $conn->real_escape_string(trim($data['editorial'] ?? ''));
        $an = (int)($data['anio'] ?? 0);

        if ($t === '' || $a === '') {
            die(json_encode(["error" => "Título y autor son obligatorios"]));
        }

        if (!empty($data['id'])) {
            // Editar libro existente
            $id  = (int)$data['id'];
            $sql = "UPDATE libros
                    SET titulo='$t', autor='$a', genero='$g', editorial='$e', anio=$an
                    WHERE id=$id";
        } else {
            // Insertar libro nuevo
            $sql = "INSERT INTO libros (titulo, autor, genero, editorial, anio)
                    VALUES ('$t','$a','$g','$e',$an)";
        }

        if ($conn->query($sql)) {
            echo json_encode(["status" => "ok", "insertId" => $conn->insert_id]);
        } else {
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // ---- SOLICITAR PRÉSTAMO ------------------------------------
    case 'PATCH':
        $data = json_decode(file_get_contents("php://input"), true);
        $lib  = (int)($data['id_libro']      ?? 0);
        $dias = (int)($data['dias_prestamo'] ?? 7);

        if ($lib <= 0) die(json_encode(["error" => "id_libro inválido"]));

        $sql = "INSERT INTO prestamos (id_usuario, id_libro, dias_solicitados, estado)
                VALUES ($id_sesion, $lib, $dias, 'pendiente')";

        if ($conn->query($sql)) {
            echo json_encode(["status" => "solicitud_enviada"]);
        } else {
            echo json_encode(["error" => $conn->error]);
        }
        break;

    // ---- APROBAR / RECHAZAR / DEVOLVER -------------------------
    case 'PUT':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));

        $data = json_decode(file_get_contents("php://input"), true);
        $ids  = (int)($data['id_solicitud'] ?? 0);
        $idl  = (int)($data['id_libro']     ?? 0);
        $acc  = $data['accion'] ?? '';

        if ($acc === 'aprobar') {
            $conn->query("UPDATE prestamos SET estado='aprobado', fecha_entrega=CURDATE() WHERE id=$ids");
            $conn->query("UPDATE libros SET prestado=1 WHERE id=$idl");
            echo json_encode(["status" => "aprobado"]);

        } elseif ($acc === 'devolver') {
            $conn->query("UPDATE prestamos SET estado='devuelto' WHERE id=$ids");
            $conn->query("UPDATE libros SET prestado=0 WHERE id=$idl");
            echo json_encode(["status" => "devuelto"]);

        } elseif ($acc === 'rechazar') {
            $conn->query("UPDATE prestamos SET estado='rechazado' WHERE id=$ids");
            echo json_encode(["status" => "rechazado"]);

        } else {
            echo json_encode(["error" => "Acción desconocida"]);
        }
        break;

    // ---- ELIMINAR LIBRO ----------------------------------------
    case 'DELETE':
        if ($rol !== 'admin') die(json_encode(["error" => "No autorizado"]));

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) die(json_encode(["error" => "ID inválido"]));

        if ($conn->query("DELETE FROM libros WHERE id = $id")) {
            echo json_encode(["status" => "eliminado"]);
        } else {
            echo json_encode(["error" => $conn->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}

$conn->close();
?>