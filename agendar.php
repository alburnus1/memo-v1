<?php
// error_reporting(0); // Descomenta en producción
require_once 'config.php';

// 1. Captura y validación de ID
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 2. Consulta de Partner (blindada contra errores)
$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
if (!$stmt) { die("❌ Error en la consulta base: " . $conn->error); }
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

if (!$partner) {
    header("Location: index.php");
    exit();
}

// 3. Preparación de datos del Partner (con valores por defecto)
$nombre_partner = htmlspecialchars($partner['nombre']);
$especialidad = !empty($partner['especialidad']) ? htmlspecialchars($partner['especialidad']) : 'Profesional de la Salud';
$direccion = !empty($partner['direccion']) ? htmlspecialchars($partner['direccion']) : 'Consulta Presencial u Online';
$estrellas = 4.8; // Valor hardcoded para simular validación social
$resenas = 127;   /* Valor hardcoded */
$anos_experiencia = 10; /* Valor hardcoded */
$foto_perfil = 'https://via.placeholder.com/150'; /* Imagen genérica */

// 4. Lógica de Agendamiento
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    // ... (Tu lógica de inserción actual sigue aquí, asegúrate de blindarla) ...
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $nombre = "Usuario de Prueba"; 

    $insert = "INSERT INTO citas (partner_id, nombre_paciente, fecha_cita, hora_cita, estado) VALUES (?, ?, ?, ?, 'confirmada')";
    $stmt_ins = $conn->prepare($insert);
    
    if ($stmt_ins) {
        $stmt_ins->bind_param("isss", $id_partner, $nombre, $fecha, $hora);
        if ($stmt_ins->execute()) {
            $mensaje = "✅ Cita agendada con éxito";
        } else {
            $mensaje = "❌ Error al agendar: " . $stmt_ins->error;
        }
    } else {
        $mensaje = "❌ Error en la consulta: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Sesión - <?php echo $nombre_partner; ?> | memo.</title>
    <style>
        :root {
            --bg-color: #f9f9f9;
            --text-main: #1a1a1a;
            --text-muted: #8e8e93;
            --memo-blue: #4f80ff;
            --memo-green: #4caf50; /* Verde éxito */
            --card-bg: #ffffff;
            --star-color: #fbc02d; /* Color de estrellas */
            --font-stack: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            font-family: var(--font-stack);
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
            -webkit-font-smoothing: antialiased;
        }

        /* Nav Superior */
        .nav-header {
            width: 100%;
            max-width: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
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

        /* Contenedor Mobile-First */
        .main-container {
            width: 100%;
            max-width: 500px;
            padding: 0 15px 40px 15px;
            box-sizing: border-box;
        }

        /* Mensaje de Éxito Estilizado */
        .alert {
            background-color: #e8f5e9;
            color: var(--memo-green);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 30px 24px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }

        /* --- Perfil del Profesional --- */
        .professional-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .professional-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .professional-details {
            display: flex;
            flex-direction: column;
        }

        .professional-name {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .professional-specialty {
            color: var(--memo-blue);
            font-weight: 600;
            font-size: 14px;
            margin-top: 3px;
            margin-bottom: 5px;
        }

        .professional-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .star-rating {
            color: var(--star-color);
            font-size: 14px;
        }

        /* --- Título de Sección --- */
        h1.page-title {
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* --- Badges de Confianza --- */
        .trust-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .trust-badge {
            background-color: #fafbfc;
            border: 1px solid #e1e8ed;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 13px;
            color: var(--text-main);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trust-badge-icon {
            font-size: 16px;
        }

        /* --- Formularios y Sección de Agendamiento --- */
        .section-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
            display: block;
        }

        input[type="date"], select {
            width: 100%;
            padding: 18px;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            font-size: 16px;
            font-family: var(--font-stack);
            margin-bottom: 20px;
            box-sizing: border-box;
            background: #fafafa;
            color: var(--text-main);
            outline: none;
        }

        /* Botón de Confirmación Estilo Memo */
        .btn-memo-confirm {
            width: 100%;
            background-color: var(--memo-blue);
            color: white;
            padding: 18px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-memo-confirm:hover {
            background-color: #3d6ef2;
        }

        .direccion-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 15px;
            text-align: left;
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
        <div class="alert">
            <span>✅</span> <?php echo $mensaje; ?>
        </div> 
        <?php endif; ?>

        <div class="card">
            
            <div class="professional-profile">
                <img src="<?php echo $foto_perfil; ?>" alt="Foto de <?php echo $nombre_partner; ?>" class="professional-photo">
                <div class="professional-details">
                    <h2 class="professional-name"><?php echo $nombre_partner; ?></h2>
                    <span class="professional-specialty"><?php echo $especialidad; ?></span>
                    <div class="professional-rating">
                        <span class="star-rating">★★★★★</span>
                        <strong><?php echo number_format($estrellas, 1); ?></strong> (<?php echo $resenas; ?> reseñas)
                    </div>
                </div>
            </div>

            <div class="trust-badges">
                <div class="trust-badge">
                    <span class="trust-badge-icon">🎓</span> <?php echo $estrellas; ?> de calificación promedio
                </div>
                <div class="trust-badge">
                    <span class="trust-badge-icon">📅</span> Confirma hoy, paga en consulta
                </div>
            </div>

            <h1 class="page-title">Solicitar Sesión</h1>
            <p class="subtitle">Asegura tu lugar en la agenda del profesional. Selecciona la fecha y hora que mejor te acomoden.</p>

            <form method="POST">
                <span class="section-label">SELECCIONE FECHA</span>
                <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">

                <span class="section-label">BLOQUE HORARIO</span>
                <select name="hora" required>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                </select>

                <button type="submit" name="agendar" class="btn-memo-confirm">Confirmar y Agendar Cita</button>
            </form>

            <div class="direccion-info">
                <span>📍</span> <span><?php echo $direccion; ?></span>
            </div>
        </div>
    </main>

</body>
</html>