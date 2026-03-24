<?php
require("includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (estaLogueado()) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $pass === '') {
        header('Location: index.html?error=vacio');
        exit;
    } elseif (!login($usuario, $pass)) {
        header('Location: index.html?error=incorrecto');
        exit;
    }
    
    // Si login() no redirige por su cuenta, lo hacemos aquí:
    header('Location: admin.php');
    exit;
} else {
    header('Location: index.html');
    exit;
}
?>