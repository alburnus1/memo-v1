<?php
// 1. Configuración de cabeceras para evitar errores de tildes
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Asegurar comunicación con DB en UTF-8
$conn->set_charset("utf8");

// 2. Validación de entrada
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 3. Obtener datos del Profesional
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

// Variables dinámicas (con fallbacks para evitar campos vacíos)
$nombre       = htmlspecialchars($partner['nombre']);
$especialidad = htmlspecialchars($partner['especialidad'] ?? 'Profesional de la Salud');
$direccion    = htmlspecialchars($partner['direccion'] ?? 'Dirección por confirmar');
$foto         = !empty($partner['foto_url']) ? $partner['foto_url'] : 'https://via.placeholder.com/150';
$bio          = htmlspecialchars($partner['bio'] ?? '');
$precio       = isset($partner['precio']) ? "$" . number_format($partner['precio'], 0, ',', '.') : 'A consultar';
$duracion     = htmlspecialchars($partner['duracion'] ?? '50 min');
$modalidad    = htmlspecialchars($partner['modalidad'] ?? 'Presencial / Online');

// 4. Lógica de registro de cita
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $paciente = "Usuario de Prueba"; // Temporal hasta tener sistema de login

    $insert = "INSERT INTO citas (partner_id, nombre_paciente, fecha_cita, hora_cita, estado) VALUES (?, ?, ?, ?, 'confirmada')";
    $stmt_ins = $conn->prepare($insert);
    $stmt_ins->bind_param("isss", $id_partner, $paciente, $fecha, $hora);
    
    if ($stmt_ins->execute()) {
        $mensaje = "✅ Cita agendada con éxito";
    } else {
        $mensaje = "❌ Error al procesar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar con <?php echo $nombre; ?> | memo.</title>
    <style>
        :root {
            --memo-blue: #4f80ff;
            --memo-blue-light: #f0f2ff;
            --text-main: #1a1a1a;
            --text-muted: #8e8e93;
            --bg-body: #f9f9f9;
            --bg-pill: #f4f7f6;
        }

        /* Reset crítico para mobile-first */
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
            -webkit-font-smoothing: antialiased;
        }

        /* Header Navigation */
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

        /* Contenedor de Contenido */
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .card {
            background: #ffffff;
            border-radius: 32px;
            padding: 30px 24px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
        }

        /* Header del Perfil */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }

        .profile-info h2 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .profile-info p {
            font-size: 14px;
            color: var(--memo-blue);
            margin: 3px 0 0;
            font-weight: 600;
        }

        /* Reseña / Bio */
        .bio-box {
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-main);
            margin-bottom: 20px;
            font-style: italic;
        }

        /* Pills de Información */
        .info-pills {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .pill {
            background: var(--bg-pill);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pill.featured {
            background: var(--memo-blue-light);
            color: var(--memo-blue);
        }

        .location-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Sección Agendar */
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
            margin-bottom: 10px;
            display: block;
            letter-spacing: 0.8px;
        }

        input[type="date"], select {
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #f2f2f2;
            background-color: #fafafa;
            font-size: 16px;
            font-family: inherit;
            margin-bottom: 20px;
            outline: none;
            color: var(--text-main);
            display: block;
            -webkit-appearance: none; /* Reset para iOS */
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
            box-shadow: 0 4px 15px rgba(79, 128, 255, 0.2);
        }

        .btn-confirm:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <nav class="nav-header">
        <a href="javascript:history.back()" class="back-link">← Volver</a>
        <div class="memo-logo">memo.</div>
    </nav>

    <main class="main-container">
        
        <?php if($mensaje): ?> 
            <div class="alert"><?php echo $mensaje; ?></div> 
        <?php endif; ?>

        <div class="card">
            <div class="profile-header">
                <img src="<?php echo $foto; ?>" class="profile-img" alt="<?php echo $nombre; ?>">
                <div class="profile-info">
                    <h2><?php echo $nombre; ?></h2>
                    <p><?php echo $especialidad; ?></p>
                </div>
            </div>

            <?php if($bio): ?>
                <div class="bio-box">"<?php echo $bio; ?>"</div>
            <?php endif; ?>

            <div class="info-pills">
                <div class="pill">⏱ <?php echo $duracion; ?></div>
                <div class="pill">💰 <?php echo $precio; ?></div>
                <div class="pill featured">💻 <?php echo $modalidad; ?></div>
            </div>

            <div class="location-box">
                <span>📍</span> <?php echo $direccion; ?>
            </div>

            <hr style="border: 0; border-top: 1px solid #f8f8f8; margin-bottom: 30px;">

            <h1>Solicitar Cita</h1>

            <form method="POST" accept-charset="UTF-8">
                <span class="label">Selecciona el día</span>
                <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">

                <span class="label">Horario disponible</span>
                <select name="hora" required>
                    <option value="" disabled selected>Elige una hora</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="16:00">04:00 PM</option>
                    <option value="17:30">05:30 PM</option>
                </select>

                <button type="submit" name="agendar" class="btn-confirm">Confirmar Horario</button>
            </form>
        </div>
    </main>

</body>
</html>