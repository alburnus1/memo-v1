<?php
// 1. Configuración de errores para ver qué pasa realmente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';
session_start();

// 2. Limpiar cualquier salida extra para que solo devuelva JSON
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

try {
    $nombre = $_POST['nombre'] ?? 'Usuario';
    $email  = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass   = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Email o contraseña vacíos']);
        exit;
    }

    // 3. Verificar si el usuario ya existe
    $check = $conn->query("SELECT id, nombre, password FROM usuarios WHERE email = '$email'");
    
    if ($check && $check->num_rows > 0) {
        // LOGIN: El usuario ya existe, verificamos pass
        $u = $check->fetch_assoc();
        if (password_verify($pass, $u['password'])) {
            $_SESSION['user'] = ['id' => $u['id'], 'nombre' => $u['nombre']];
            echo json_encode(['success' => true, 'action' => 'login']);
        } else {
            echo json_encode(['success' => false, 'message' => 'La contraseña es incorrecta']);
        }
    } else {
        // REGISTRO: Usuario nuevo
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $ins = "INSERT INTO usuarios (nombre, email, password) VALUES ('$nombre', '$email', '$hash')";
        
        if ($conn->query($ins)) {
            $_SESSION['user'] = ['id' => $conn->insert_id, 'nombre' => $nombre];
            echo json_encode(['success' => true, 'action' => 'register']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al insertar: ' . $conn->error]);
        }
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fatal: ' . $e->getMessage()]);
}
exit;