<?php
include("pruebaConexion.php");
if(isset($_POST['enviar3'])) {
    $nombre = $_POST['name'];
    $nombre_generico = $_POST['generic-name'];
    $tipo_dosis = $_POST['dose'];
    $cantiddad = $_POST['quantity'];
    $presenyacion = $_POST['presentation'];
    $sql1 = "INSERT INTO Medicamentos (Medicamento, nombre_generico, dosis, Cantidad, Presentacion) 
            VALUES ('$nombre', '$nombre_generico', '$tipo_dosis', '$cantiddad', '$presenyacion')";
            if (mysqli_query($conn, $sql1)) {
        echo "Registro exitoso.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }