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


    $sql = "INSERT INTO Medicos (nombre_medico, apellido_paterno, apellido_materno, genero, correo, telefono, cedula_profesional, institucion_egreso, especialidad) 
            VALUES ('$nombre', '$apellidoP', '$apellidoM', '$genero', '$correoI', '$telefono', '$cedula', '$institucionE', '$especialidad')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Registro exitoso.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }

?>
