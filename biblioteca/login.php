<?php
require 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->execute([$_POST['correo']]);
    $user = $stmt->fetch();

    // Busca esta parte en tu login.php y déjala así:
    if ($user && $_POST['password'] === $user['password']) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol']     = $user['rol'];
        $_SESSION['nombre']  = $user['nombre'];
        header("Location: index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
    <div class="container card p-4 shadow" style="max-width: 400px;">
        <h3 class="text-center mb-3">Biblioteca</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="correo" class="form-control mb-2" placeholder="Correo" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</body>
</html>