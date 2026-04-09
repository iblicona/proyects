<?php
require 'config/db.php'; // Usa la conexión $conn de tu maestro
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = mysqli_real_escape_string($conn, $_POST['correo']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
    $res = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($res);

    // Verificación de contraseña plana (como pidió tu maestro)
    if ($user && $pass === $user['password']) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol']     = $user['rol'];
        $_SESSION['nombre']  = $user['nombre'];

        // Guardamos en variables para pasarlas a JS
        $js_id = $user['id'];
        $js_rol = $user['rol'];
        $js_nom = $user['nombre'];
        
        $success = true;
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
    <div class="container card p-4 shadow" style="max-width: 400px;">
        <h3 class="text-center mb-3">📚 Biblioteca</h3>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-2">
                <label class="small fw-bold">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required>
            </div>
            <div class="mb-3">
                <label class="small fw-bold">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button class="btn btn-primary w-100">Entrar al Sistema</button>
        </form>
    </div>

    <?php if(isset($success) && $success): ?>
    <script>
        // PASO VITAL: Guardamos los datos en el navegador antes de ir al .html
        localStorage.setItem('user_id', '<?php echo $js_id; ?>');
        localStorage.setItem('rol', '<?php echo $js_rol; ?>');
        localStorage.setItem('nombre', '<?php echo $js_nom; ?>');
        
        // Redireccionamos al index.html
        window.location.href = "index.html";
    </script>
    <?php endif; ?>

</body>
</html>