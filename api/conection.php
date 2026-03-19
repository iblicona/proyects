<?php

$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "admin";
$password = "Semillita1*";
$base_datos = "datos";

$conn = mysqli_connect($host, $usuario, $password, $base_datos);

if (!$conn){
    echo "Error de Conexion Exitosa";
    }
    else{
        echo"Conexion Exitosa";
    }
?>