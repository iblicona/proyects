<?php
include("pruebaConexion.php");
if(isset($_POST['agregaru'])) {
    $matricula = $_POST['matricula'];
    $tipo = $_POST['disposable'];
    $cantidad = $_POST['quantity'];
    $sql1 = "INSERT INTO Utensilios (matricula, tipo, cantidad) 
            VALUES ('$matricula', '$tipo', '$cantidad')";
            if (mysqli_query($conn, $sql1)) {
        echo "Registro exitoso.";
        header("Location: ../utensilios.php?msg=agregado");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
 }
?>