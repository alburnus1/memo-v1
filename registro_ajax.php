<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $nombre = $_POST['nombre'] ?? 'Usuario';

    if (empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Email y password requeridos']);
        exit;
    }

    // Hash de seguridad
    $password_hash = password_hash($pass, PASSWORD_BCRYPT);
    $rol = 'paciente';

    $stmt = $conn->prepare("INSERT INTO usuarios (email, password, rol, nombre) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $password_hash, $rol, $nombre);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_rol'] = $rol;
        $_SESSION['user_nom'] = $nombre;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'El correo ya existe o hubo un error']);
    }
}