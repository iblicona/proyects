<?php
include ("php/pruebaConexion.php");
if($conn) {
    $consulta = "SELECT * FROM Utensilios";
    $resultado = mysqli_query($conn, $consulta);   
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Material clínico</title>
  <meta charset="UTF-8">
  <style>
    .delete-button {
      background-color: red;
      color: white;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">"
  <link href="css/smcss.css" rel="stylesheet">
</head>
<body>
  <div class="med-container-fluid">
    <span>Material clínico</span>
    <a href = "inventario.html">Volver a Inventario</a>
  </div>
  <div class="m-container">
    <h2>Agregar/Eliminar Materiales</h2><br>
    <form id="add-form" action="php/utensilios.php" method = "post">

        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required>

        <label for="disposable">Tipo (desechable):</label>
        <select id="disposable" name="disposable" required>
          <option value="des">Desechable</option>
          <option value="nodes">No desechable</option>
        </select>
        
        <label for="quantity">Cantidad:</label>
        <input type="number" id="quantity" name="quantity" min="1" required>
        
       <input type="submit" name="agregaru" id="agregaru" value="AGREGAR">
     </form>
    <a href="img/reg_ut.pdf" target="_blank"><img src="img/impresion.png" alt="impresion"></a>
  </div>
    
    <div class="search-container">
      <h4>Buscar material:</h4>
      <input type="text" id="search-input" placeholder="Nombre de material...">
      <button id="search-button">Buscar</button>
    </div>
    
    <h2>Inventario de materiales</h2>
    <table id="inventory-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Tipo de material</th>
          <th>Cantidad</th>
        </tr>
      </thead>
      <tbody id="inventory-tbody"></tbody>
      <?php
        if ($resultado) {
            $num_rows = mysqli_num_rows($resultado);
            if ($num_rows > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>".$fila['id_utensilios']."</td>";
                    echo "<td>".$fila['nombre']."</td>";
                    echo "<td>".$fila['tipo']."</td>";
                    echo "<td>".$fila['cantidad']."</td>";
                    echo "<td><a href='php/editaru.php?id=".$fila['id_utensilios']."' class='btn btn-sm btn-primary'>Editar</a></td>";
                    echo "<td><a href='php/eliminaru.php?id=".$fila['id_utensilios']."' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este registro?\");'>Eliminar</a></td>";
                    echo "</tr>";   
                }
            } else {
                echo "<tr><td colspan='8'>No se encontraron registros.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Error en la consulta.</td></tr>";
        }
        ?>
    </table>

</body>
</html>