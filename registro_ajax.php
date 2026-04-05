<?php
session_start();
include 'config.php';

// Limpieza de salida para evitar errores de JSON
ob_start();
header('Content-Type: application/json');

$nombre = $_POST['nombre'] ?? 'Usuario';
$email  = trim($_POST['email'] ?? '');
$pass   = trim($_POST['password'] ?? '');

if (empty($email) || empty($pass)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$password_hash = password_hash($pass, PASSWORD_BCRYPT);

// Intentar Registro
$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password_hash', 'paciente')";

if ($conn->query($sql)) {
    $_SESSION['user'] = ['id' => $conn->insert_id, 'nombre' => $nombre];
    ob_end_clean();
    echo json_encode(['success' => true]);
} else {
    // Si falla, intentar Login (el correo ya existe)
    $res = $conn->query("SELECT id, nombre, password FROM usuarios WHERE email = '$email'");
    if ($res && $res->num_rows > 0) {
        $u = $res->fetch_assoc();
        if (password_verify($pass, $u['password'])) {
            $_SESSION['user'] = ['id' => $u['id'], 'nombre' => $u['nombre']];
            ob_end_clean();
            echo json_encode(['success' => true]);
        } else {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
    } else {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
}