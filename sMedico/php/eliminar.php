 <?php
include("pruebaConexion.php");

if(isset($_GET['id_medicamento'])) {
    $id = $_GET['id_medicamento'];
    $eliminar = "DELETE FROM Medicamentos WHERE id_medicamento = $id";
    $resultado = mysqli_query($conn, $eliminar);
    echo "Registro eliminado correctamente";
    header("Location: medicamentos_inicio.php");
}

?>