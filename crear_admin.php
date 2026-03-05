<?php
require_once 'includes/db.php';
$pdo = getDB();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->query("INSERT INTO admins (usuario, password) VALUES ('admin', '$hash')");
echo "¡Admin creado! Ya puedes borrar este archivo. Usuario: admin | Contraseña: admin123";
?>