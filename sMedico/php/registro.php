<?php
include("pruebaConexion.php");
if(isset($_POST['enviar'])) {
    $nombre = $_POST['nombre'];
    $apellidoP = $_POST['apellido_paterno'];
    $apellidoM = $_POST['apellido_materno'];
    $matricula = $_POST['matricula'];
    $genero = $_POST['genero'];
    $correoI = $_POST['correo'];
    $telefono = $_POST['celular'];
    $escolaridad = $_POST['escolaridad'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['address'];
    $contactoE = $_POST['emergency_contact'];
    $alergias = $_POST['alergies'];
    $enfermedades_cronicas = $_POST['chronic_diseases'];

   $sql = "INSERT INTO Alumnos (
    matricula,
    nombre_alumno,
    apellido_paterno,
    apellido_materno,
    genero,
    correo,
    telefono,
    carrera,
    fecha_nacimiento,
    direccion,
    contacto_emergencia,
    alergias,
    enfermedades_cronicas
) VALUES (
    '$matricula',
    '$nombre',
    '$apellidoP',
    '$apellidoM',
    '$genero',
    '$correoI',
    '$telefono',
    '$escolaridad',
    '$fecha_nacimiento',
    '$direccion',
    '$contactoE',
    '$alergias',
    '$enfermedades_cronicas'
)"; 
    if (mysqli_query($conn, $sql)) {
        echo "Registro exitoso.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }

?>
