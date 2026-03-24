<?php
session_start();
include("var/www/proyects/api/dbconection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND pass = '$password'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) > 0) {

        $_SESSION['usuario'] = $usuario;

        header("Location: ../opciones.html");
        exit();

    } else {
        echo "Usuario o contraseña incorrectos";
    }
}
?>