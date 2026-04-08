<?php
include ("php/pruebaConexion.php");
if($conn) {
    $consulta = "SELECT * FROM Medicamentos";
    $resultado = mysqli_query($conn, $consulta);   
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inventario de Medicamentos</title>
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
    <span>Inventario de Medicamentos</span>
    <a href = "inventario.html">Volver a Inventario</a>
  </div>
  <div class="m-container">
    <h2>Agregar/Eliminar Medicamentos</h2><br>
    <form id="add-form" action="php/medicamentos.php" method = "post">

        <label for="name">Nombre comercial:</label>
        <input type="text" id="name" name="name" required><br>
        
        <label for="generic-name">Nombre genérico:</label>
        <input type="text" id="generic-name" name="generic-name" required><br><br>

        <label for="dose">Tipo de Dosis:</label>
        <select id="dose" name="dose" required>
          <option value="pastilla">Pastilla</option>
          <option value="tableta">Tableta</option>
        </select>
        
        <label for="quantity">Cantidad:</label>
        <input type="number" id="quantity" name="quantity" min="1" required>
        
        <label for="presentation">Presentación (mg):</label>
        <input type="number" id="presentation" name="presentation" min="1" required>
        
        <input type="submit" name="enviar3" id="enviar3" value="Agregar">
    </form>
    <a href="img/inv_med.pdf" target="_blank"><img src="img/impresion.png" alt="impresion"></a>
  </div>
    
    <div class="search-container">
      <h4>Buscar Medicamento:</h4>
      <input type="text" id="search-input" placeholder="Buscar medicamento...">
      <button id="search-button">Buscar</button>
    </div>
    
    <h2>Inventario</h2>
    <table  class="table table-bordered" id="inventory-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Nombre Genérico</th>
          <th>Tipo de Dosis</th>
          <th>Cantidad</th>
          <th>Presentación (mg)</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody id="inventory-tbody">
        <?php
        if ($resultado) {
            $num_rows = mysqli_num_rows($resultado);
            if ($num_rows > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $fila['id_medicamento'] . "</td>";
                    echo "<td>" . $fila['Medicamento'] . "</td>";
                    echo "<td>" . $fila['nombre_generico'] . "</td>";
                    echo "<td>" . $fila['dosis'] . "</td>";
                    echo "<td>" . $fila['Cantidad'] . "</td>";
                    echo "<td>" . $fila['Presentacion'] . "</td>";
                    echo "<td><a href='EditarRegistro.php?id=" . $fila['id_medicamento'] . "' class='btn btn-sm btn-primary'>Editar</a></td>";
                    echo "<td><a href='eliminar.php?id=" . $fila['id_medicamento'] . "' class='delete-button'>Eliminar</a></td>";
                    echo "</tr>";   
                }
            } else {
                echo "<tr><td colspan='8'>No se encontraron registros.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Error en la consulta.</td></tr>";
        }
        ?>
      </tbody>
    </table>
</body>
</html>