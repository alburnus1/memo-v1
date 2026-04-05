<?php
require_once 'config.php';

// 1. Capturar ID y validar que sea un número
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 2. Consultar el partner (ya con la columna 'activo' creada)
$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

// 3. Si no existe, redirigir al index
if (!$partner) {
    header("Location: index.php");
    exit();
}

// 4. Datos del partner para el HTML
$nombre_partner = htmlspecialchars($partner['nombre']);
$banner = !empty($partner['banner_url']) ? $partner['banner_url'] : 'https://via.placeholder.com/800x200?text=Bienvenido';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar - <?php echo $nombre_partner; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; color: #333; text-align: center; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .banner-container { width: 100%; margin-bottom: 25px; overflow: hidden; border-radius: 0 0 15px 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .banner-img { width: 100%; height: auto; display: block; }
        h1 { color: #2c3e50; font-size: 1.5rem; margin-top: 20px; }
        .card { background: white; padding: 30px; border-radius: 15px; shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-agendar { display: inline-block; background: #4a90e2; color: white; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold; margin-top: 20px; transition: background 0.3s; }
        .btn-agendar:hover { background: #357abd; }
    </style>
</head>
<body>

    <div class="banner-container">
        <img src="<?php echo $banner; ?>" alt="Banner de <?php echo $nombre_partner; ?>" class="banner-img">
    </div>

    <div class="container">
        <div class="card">
            <h1>Estás agendando con<br><strong><?php echo $nombre_partner; ?></strong></h1>
            <p>Selecciona una hora disponible para tu sesión.</p>
            
            <a href="#" class="btn-agendar">Ver Horarios Disponibles</a>
        </div>
    </div>

</body>
</html>