<?php
require "conexion.php";

$action = $_GET["action"] ?? "";

/* ─── USUARIOS ─── */
if ($action === "usuarios") {
    $res = $conn->query("SELECT id, usuario, tipo, creado_en FROM usuarios");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

/* AGREGAR USUARIO */
if ($action === "agregar_usuario") {
    $data = json_decode(file_get_contents("php://input"), true);

    $usuario = $data["usuario"];
    $password = md5($data["password"]);
    $tipo = $data["tipo"];

    $stmt = $conn->prepare("INSERT INTO usuarios(usuario,password,tipo) VALUES(?,?,?)");
    $stmt->bind_param("sss", $usuario, $password, $tipo);

    if ($stmt->execute()) echo json_encode(["ok"=>true]);
    else echo json_encode(["error"=>"Usuario existente"]);
}

/* ELIMINAR USUARIO */
if ($action === "eliminar_usuario") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data["id"];

    $conn->query("DELETE FROM usuarios WHERE id=$id");
    echo json_encode(["ok"=>true]);
}

/* CAMBIAR PASSWORD */
if ($action === "cambiar_password") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data["id"];
    $pass = md5($data["password"]);

    $conn->query("UPDATE usuarios SET password='$pass' WHERE id=$id");
    echo json_encode(["ok"=>true]);
}

/* ─── STOCK ─── */
if ($action === "stock") {
    $res = $conn->query("SELECT * FROM equipos");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

/* AGREGAR EQUIPO */
if ($action === "agregar_equipo") {
    $data = json_decode(file_get_contents("php://input"), true);

    $nombre = $data["equipo"];
    $cantidad = $data["cantidad"];

    $stmt = $conn->prepare("INSERT INTO equipos(equipo,cantidad) VALUES(?,?)");
    $stmt->bind_param("si", $nombre, $cantidad);
    $stmt->execute();

    echo json_encode(["ok"=>true]);
}

/* ACTUALIZAR */
if ($action === "actualizar_equipo") {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data["id"];
    $nombre = $data["equipo"];
    $cantidad = $data["cantidad"];

    $stmt = $conn->prepare("UPDATE equipos SET equipo=?, cantidad=? WHERE id=?");
    $stmt->bind_param("sii", $nombre, $cantidad, $id);
    $stmt->execute();

    echo json_encode(["ok"=>true]);
}

/* ELIMINAR */
if ($action === "eliminar_equipo") {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data["id"];

    $conn->query("DELETE FROM equipos WHERE id=$id");
    echo json_encode(["ok"=>true]);
}