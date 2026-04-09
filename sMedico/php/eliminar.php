 <?php
include("pruebaConexion.php");

if(isset($_GET['id_medicamento'])) {
    $id = $_GET['id_medicamento'];
    $eliminar = "DELETE FROM Medicamentos WHERE id_medicamento = $id";
    $resultado = mysqli_query($conn, $eliminar);
    echo "El id del elemento";
    echo "Registro eliminado correctamente", $id;
    header("Location: ../medicamentos_inicio.php");
}
?>