<?php
// 1. Incluir la conexión (Asegúrate de que config.php esté en el servidor de QA)
require_once 'config.php';

// 2. Capturar el ID del partner desde la URL
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. Si no viene un ID válido, redirigir al index
if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 4. Buscar el partner en la Base de Datos usando Prepared Statements (Más seguro)
$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

// 5. Si el partner NO existe en la BD de este servidor, redirigir
if (!$partner) {
    // TIP: Mientras pruebas, puedes comentar la línea de abajo y usar un die() 
    // para saber si el problema es que el ID 21 no existe en QA.
    // die("Error: El partner $id_partner no existe en la BD de QA.");
    header("Location: index.php");
    exit();
}

// 6. Si llegamos aquí, el partner existe. Mostramos el banner.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar con <?php echo htmlspecialchars($partner['nombre']); ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; }
        .banner-container { margin-bottom: 20px; }
        .banner-img { max-width: 100%; height: auto; border-radius: 8px; cursor: pointer; }
        .btn-agendar { background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; text-decoration: none; font-size: 1.2rem; }
    </style>
</head>
<body>

    <div class="banner-container">
        <a href="registro_clic.php?id_partner=<?php echo $id_partner; ?>">
            <img src="<?php echo $partner['banner_url']; ?>" alt="Banner Partner" class="banner-img">
        </a>
    </div>

    <h1>Hola, estás agendando a través de <?php echo htmlspecialchars($partner['nombre']); ?></h1>
    
    <p>Selecciona una fecha disponible:</p>
    <br><br>
    <a href="#" class="btn-agendar">Confirmar Cita</a>

</body>
</html>