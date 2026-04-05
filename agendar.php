<?php
// 1. Forzar UTF-8 y manejo de errores
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Asegurar que la conexión a la base de datos use UTF-8
$conn->set_charset("utf8");

// 2. Validar ID del Partner
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 3. Consultar datos del Partner
$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

if (!$partner) {
    header("Location: index.php");
    exit();
}

// 4. Procesar Variables del Partner
$nombre = htmlspecialchars($partner['nombre']);
$especialidad = htmlspecialchars($partner['especialidad'] ?? 'Profesional de la Salud');
$direccion = htmlspecialchars($partner['direccion'] ?? 'Dirección no especificada');
$foto = !empty($partner['foto_url']) ? $partner['foto_url'] : 'https://via.placeholder.com/150';
$bio = htmlspecialchars($partner['bio'] ?? '');
$precio = isset($partner['precio']) ? "$" . number_format($partner['precio'], 0, ',', '.') : 'Consulte valor';
$duracion = htmlspecialchars($partner['duracion'] ?? '50 min');

// 5. Lógica de guardado de cita (POST)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $usuario_nom = "Usuario de Prueba"; // Esto vendrá de la sesión más adelante

    $insert = "INSERT INTO citas (partner_id, nombre_paciente, fecha_cita, hora_cita, estado) VALUES (?, ?, ?, ?, 'confirmada')";
    $stmt_ins = $conn->prepare($insert);
    $stmt_ins->bind_param("isss", $id_partner, $usuario_nom, $fecha, $hora);
    
    if ($stmt_ins->execute()) {
        $mensaje = "✅ Cita agendada con éxito";
    } else {
        $mensaje = "❌ Error al agendar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Sesión | memo.</title>
    <style>
        :root {
            --memo-blue: #4f80ff;
            --text-main: #1a1a1a;
            --text-muted: #8e8e93;
            --bg-body: #f9f9f9;
            --bg-pill: #f4f7f6;
        }

        /* CRÍTICO: Asegura que los paddings no sumen al ancho total */
        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            margin: 0;
            padding: 0;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Navegación */
        .nav-header {
            width: 100%;
            max-width: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }

        .back-link {
            color: var(--memo-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .memo-logo {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.5px;
        }

        /* Contenedor Principal */
        .main-container {
            width: 100%;
            max-width: 500px;
            padding: 0 15px 40px 15px;
        }

        .alert {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        .card {
            background: #ffffff;
            border-radius: 28px;
            padding: 30px 24px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        }

        /* Perfil del Profesional */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .profile-img {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            background: #eee;
        }

        .profile-info h2 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .profile-info p {
            font-size: 14px;
            color: var(--memo-blue);
            margin: 2px 0 0;
            font-weight: 600;
        }

        .bio-text {
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-main);
            margin: 15px 0;
        }

        .info-pills {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .pill {
            background: var(--bg-pill);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .location-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        /* Título de Agendamiento */
        h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 20px 0;
            letter-spacing: -0.8px;
        }

        .label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.5px;
        }

        /* Inputs y Selects Corregidos */
        input[type="date"], select {
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            background-color: #fcfcfc;
            font-size: 16px;
            font-family: inherit;
            margin-bottom: 18px;
            outline: none;
            color: var(--text-main);
            display: block; /* Evita comportamientos inline extraños */
        }

        .btn-confirm {
            width: 100%;
            background-color: var(--memo-blue);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 18px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.1s;
        }

        .btn-confirm:active {
            transform: scale(0.98);
        }

    </style>
</head>
<body>

    <nav class="nav-header">
        <a href="javascript:history.back()" class="back-link">← Volver</a>
        <span class="memo-logo">memo.</span>
    </nav>

    <main class="main-container">
        
        <?php if($mensaje): ?> 
            <div class="alert"><?php echo $mensaje; ?></div> 
        <?php endif; ?>

        <div class="card">
            <div class="profile-section">
                <img src="<?php echo $foto; ?>" class="profile-img" alt="<?php echo $nombre; ?>">
                <div class="profile-info">
                    <h2><?php echo $nombre; ?></h2>
                    <p><?php echo $especialidad; ?></p>
                </div>
            </div>

            <?php if($bio): ?>
                <div class="bio-text"><?php echo $bio; ?></div>
            <?php endif; ?>

            <div class="info-pills">
                <div class="pill">⏱ <?php echo $duracion; ?></div>
                <div class="pill">💰 <?php echo $precio; ?></div>
            </div>

            <div class="location-box">
                <span>📍</span> <?php echo $direccion; ?>
            </div>

            <hr style="border: 0; border-top: 1px solid #f0f0f0; margin-bottom: 25px;">

            <h1>Agendar Sesión</h1>

            <form method="POST">
                <span class="label">Selecciona el día</span>
                <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">

                <span class="label">Bloque horario</span>
                <select name="hora" required>
                    <option value="" disabled selected>Elige un horario</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:30">04:30 PM</option>
                </select>

                <button type="submit" name="agendar" class="btn-confirm">Confirmar Cita</button>
            </form>
        </div>
    </main>

</body>
</html>