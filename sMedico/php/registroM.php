<?php
include("pruebaConexion.php");
if(isset($_POST['enviar2'])) {
    $nombre = $_POST['nombre'];
    $apellidoP = $_POST['apellido_paterno'];
    $apellidoM = $_POST['apellido_materno'];
    $genero = $_POST['genero'];
    $correoI = $_POST['correo'];
    $telefono = $_POST['celular'];
    $cedula = $_POST['cedula_profesional'];
    $institucionE = $_POST['institucion_egreso'];
    $especialidad= $_POST['especialidad'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];


    $sql1 = "INSERT INTO Personal_Medico (nombre, apellido_paterno, apellido_materno, genero, correo, telefono, cedula_profesional, institucion_egreso, especialidad, usuario, contraseña) 
            VALUES ('$nombre', '$apellidoP', '$apellidoM', '$genero', '$correoI', '$telefono', '$cedula', '$institucionE', '$especialidad', '$usuario', '$password')";

    $sql2 = "INSERT INTO Usuarios (usuario, pass) VALUES ('$usuario', '$password')";

    if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2)) {
        echo "Registro exitoso.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }
