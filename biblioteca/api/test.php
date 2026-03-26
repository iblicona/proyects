<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "conexion.php";

if ($conn) {
    echo "Conectado correctamente con SSL";
} else {
    echo "Error";
}