<?php
session_start();
include 'config.php';

// Desactivamos cualquier error de texto que ensucie el JSON
error_reporting(0);
header('Content-Type: application/json');

$nombre = $_POST['nombre'] ?? 'Usuario';
$email  = $_POST['email'] ?? '';
$pass   = $_POST['password'] ?? '';

if (!$email || !$pass) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$password_hash = password_hash($pass, PASSWORD_BCRYPT);

// Intentamos insertar. Si falla (porque el email existe), intentamos loguear.
$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password_hash', 'paciente')";

if ($conn->query($sql)) {
    $_SESSION['user'] = ['id' => $conn->insert_id, 'nombre' => $nombre];
    echo json_encode(['success' => true]);
} else {
    // Si el INSERT falló, es probable que el email ya exista. Buscamos al usuario.
    $res = $conn->query("SELECT id, nombre, password FROM usuarios WHERE email = '$email'");
    if ($res && $res->num_rows > 0) {
        $u = $res->fetch_assoc();
        if (password_verify($pass, $u['password'])) {
            $_SESSION['user'] = ['id' => $u['id'], 'nombre' => $u['nombre']];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'La contraseña es incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta']);
    }
}