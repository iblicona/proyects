<?php
require 'config/db.php';
session_start();
header('Content-Type: application/json');

// Verificación de sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No has iniciado sesión"]);
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];
$rol = $_SESSION['rol'] ?? 'user';

switch($metodo) {
    
    case 'GET':
        $id_usuario_sesion = $_SESSION['user_id'];
        
        // 1. CASO: HISTORIAL (Admin ve todo, Usuario ve lo suyo)
        if (isset($_GET['historial'])) {
            if ($rol === 'admin') {
                $sql = "SELECT p.*, u.nombre as usuario, l.titulo as libro 
                        FROM prestamos p 
                        JOIN usuarios u ON p.id_usuario = u.id 
                        JOIN libros l ON p.id_libro = l.id 
                        ORDER BY p.fecha_solicitud DESC";
                $stmt = $pdo->query($sql);
            } else {
                $sql = "SELECT p.*, l.titulo as libro 
                        FROM prestamos p 
                        JOIN libros l ON p.id_libro = l.id 
                        WHERE p.id_usuario = $id_usuario_sesion
                        ORDER BY p.fecha_solicitud DESC";
                $stmt = $pdo->query($sql);
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } 
        
        // 2. CASO: SOLICITUDES ACTIVAS (Solo Admin)
        elseif (isset($_GET['solicitudes']) && $rol === 'admin') {
            $sql = "SELECT p.*, u.nombre as usuario, l.titulo as libro 
                    FROM prestamos p 
                    JOIN usuarios u ON p.id_usuario = u.id 
                    JOIN libros l ON p.id_libro = l.id 
                    WHERE p.estado IN ('pendiente', 'aprobado')
                    ORDER BY p.estado DESC, p.fecha_solicitud ASC";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } 
        
        // 3. CASO: LISTADO DE LIBROS (Con marca de ya_solicitado para usuarios)
        else {
            $sql = "SELECT l.*, 
                    (SELECT COUNT(*) FROM prestamos p 
                     WHERE p.id_libro = l.id 
                     AND p.id_usuario = $id_usuario_sesion 
                     AND p.estado = 'pendiente') as ya_solicitado
                    FROM libros l 
                    ORDER BY l.creado_en DESC";
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        // Solo Admin: Registrar o Editar libros
        if ($rol !== 'admin') {
            http_response_code(403);
            die(json_encode(["error" => "No autorizado"]));
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['id'])) {
            // ACCIÓN: EDITAR LIBRO
            $sql = "UPDATE libros SET titulo=?, autor=?, genero=?, editorial=?, anio=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['titulo'], $data['autor'], $data['genero'], 
                $data['editorial'], $data['anio'], $data['id']
            ]);
            echo json_encode(["status" => "editado"]);
        } else {
            // ACCIÓN: CREAR LIBRO NUEVO
            $sql = "INSERT INTO libros (titulo, autor, genero, editorial, anio) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['titulo'], $data['autor'], $data['genero'], 
                $data['editorial'], $data['anio']
            ]);
            echo json_encode(["status" => "creado"]);
        }
        break;

    case 'PATCH':
        // USUARIO: Envía una solicitud de préstamo
        $data = json_decode(file_get_contents("php://input"), true);
        $id_usuario = $_SESSION['user_id'];
        $id_libro = $data['id_libro'];
        $dias = $data['dias_prestamo'];

        $sql = "INSERT INTO prestamos (id_usuario, id_libro, dias_solicitados, estado) VALUES (?, ?, ?, 'pendiente')";
        $pdo->prepare($sql)->execute([$id_usuario, $id_libro, $dias]);

        echo json_encode(["status" => "solicitud_enviada"]);
        break;

    case 'PUT':
        // ADMIN: Decidir sobre solicitudes (Aprobar, Rechazar o Devolver)
        if ($rol !== 'admin') {
            http_response_code(403);
            die(json_encode(["error" => "No autorizado"]));
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id_solicitud = $data['id_solicitud'];
        $id_libro = $data['id_libro'];
        $accion = $data['accion'];

        if ($accion === 'aprobar') {
            $pdo->prepare("UPDATE prestamos SET estado='aprobado', fecha_entrega=CURDATE() WHERE id=?")
                ->execute([$id_solicitud]);
            $pdo->prepare("UPDATE libros SET prestado=1 WHERE id=?")
                ->execute([$id_libro]);
            echo json_encode(["status" => "aprobado"]);
        } 
        elseif ($accion === 'devolver') {
            $pdo->prepare("UPDATE prestamos SET estado='devuelto' WHERE id=?")
                ->execute([$id_solicitud]);
            $pdo->prepare("UPDATE libros SET prestado=0 WHERE id=?")
                ->execute([$id_libro]);
            echo json_encode(["status" => "devuelto"]);
        } 
        else {
            $pdo->prepare("UPDATE prestamos SET estado='rechazado' WHERE id=?")
                ->execute([$id_solicitud]);
            echo json_encode(["status" => "rechazado"]);
        }
        break;

    case 'DELETE':
        // Solo Admin: Eliminar libro
        if ($rol !== 'admin') {
            http_response_code(403);
            die(json_encode(["error" => "No autorizado"]));
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo->prepare("DELETE FROM libros WHERE id = ?")->execute([$id]);
            echo json_encode(["status" => "eliminado"]);
        } else {
            echo json_encode(["error" => "ID no proporcionado"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}