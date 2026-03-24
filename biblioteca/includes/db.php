<?php
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
        $db   = "biblioteca";
        $user = "axel";
        $pass = "admin1234";

        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

if (!isset($conn) || !$conn) {
    die(json_encode([
        "error" => "No se pudo establecer conexión con la base de datos"
    ]));
}

$conn->set_charset("utf8");

?>
