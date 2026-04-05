<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre, password FROM partners WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['partner_id'] = $user['id'];
        $_SESSION['partner_nom'] = $user['nombre'];
        header("Location: admin_perfil.php");
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | memo.</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #f9f9f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 350px; text-align: center; }
        .logo { font-weight: 800; font-size: 24px; margin-bottom: 30px; display: block; }
        input { width: 100%; padding: 15px; margin-bottom: 15px; border-radius: 12px; border: 1px solid #eee; outline: none; }
        button { width: 100%; padding: 15px; background: #4f80ff; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }
        .error { color: red; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <form class="login-card" method="POST">
        <span class="logo">memo.</span>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar al Dashboard</button>
    </form>
</body>
</html>