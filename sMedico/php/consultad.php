<?php
include("pruebaConexion.php");
if(isset($_POST['enviarconsultad'])) {
    $matricula = $_POST['matricula'];
    $name = $_POST['name'];
    $genero = $_POST['genero'];
    $escolaridad = $_POST['escolaridad'];
    $sintomas = $_POST['sintomas'];
    $fechac = $_POST['appointment-date'];
    $presion = $_POST['presion'];
    $temperatura = $_POST['temperatura'];
    $medicamentoDosis = $_POST['medidosis'];
    $sql1 = "INSERT INTO consulta_docente (matricula, name, genero, escolaridad, sintomas, fecha, presion, temperatura, medicamentoDosis) 
            VALUES ('$matricula', '$name', '$genero', '$escolaridad', '$sintomas', '$fechac', '$presion', '$temperatura', '$medicamentoDosis')";
            if (mysqli_query($conn, $sql1)) {
        echo "Registro exitoso.";
        header("Location: ../consulta.html");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }
