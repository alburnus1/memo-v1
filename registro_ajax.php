<?php
session_start();
include 'config.php';

// Limpiamos errores previos para enviar un JSON limpio
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre'] ?? 'Usuario');
    $email  = $conn->real_escape_string($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Email y contraseña requeridos']);
        exit;
    }

    // Encriptamos la contraseña para seguridad
    $password_hash = password_hash($pass, PASSWORD_BCRYPT);

    // Intentamos insertar
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password_hash', 'paciente')";
    
    if ($conn->query($sql)) {
        $new_id = $conn->insert_id;
        $_SESSION['user'] = ['id' => $new_id, 'nombre' => $nombre];
        echo json_encode(['success' => true]);
    } else {
        // Si el correo ya existe, intentamos loguear en lugar de registrar
        $res = $conn->query("SELECT id, nombre, password FROM usuarios WHERE email = '$email'");
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user'] = ['id' => $user['id'], 'nombre' => $user['nombre']];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta para este email.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cuenta.']);
        }
    }
}
?>