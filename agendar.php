<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// 1. Verificamos conexión
if ($conn->connect_error) {
    die("❌ Error de conexión a la BD: " . $conn->connect_error);
}

// 2. Verificamos qué ID está llegando
$id_get = isset($_GET['id']) ? $_GET['id'] : 'NO VIENE NADA';
echo "🔍 Debug: El ID recibido en la URL es: " . htmlspecialchars($id_get) . "<br>";

$id_partner = intval($id_get);

if ($id_partner <= 0) {
    die("❌ Error: El ID no es válido (debe ser un número mayor a 0).");
}

// 3. Verificamos si el ID existe en la tabla
$query = "SELECT id, nombre, activo FROM partners WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

if (!$partner) {
    // Si entra aquí, es que la consulta SQL no devolvió nada
    echo "❌ Error: El partner con ID $id_partner no existe en la tabla 'partners' de QA.<br>";
    
    // Vamos a listar qué IDs hay disponibles para ayudarte
    $res_list = $conn->query("SELECT id FROM partners");
    echo "💡 IDs disponibles en la tabla actualmente: ";
    while($row = $res_list->fetch_assoc()) { echo $row['id'] . " "; }
    die();
}

if ($partner['activo'] != 1) {
    die("❌ Error: El partner existe pero está desactivado (activo = 0).");
}

echo "✅ ÉXITO: Partner encontrado: " . $partner['nombre'];
// hola mundo 123
?>