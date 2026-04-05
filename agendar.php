<?php
require_once 'config.php';
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_partner <= 0) { header("Location: index.php"); exit(); }

$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

if (!$partner) { header("Location: index.php"); exit(); }
$nombre_partner = htmlspecialchars($partner['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Sesión | memo.</title>
    <style>
        :root {
            --bg-color: #f9f9f9;
            --text-main: #1a1a1a;
            --text-muted: #8e8e93;
            --memo-blue: #4f80ff;
            --card-bg: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            align-items: center;
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

        .card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 30px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .badge {
            background: #f0f2ff;
            color: var(--memo-blue);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 25px;
        }

        .section-label {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: block;
        }

        /* Slot del Partner (Estilo Medicamentos) */
        .partner-slot {
            background: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 18px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .partner-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .partner-icon { font-size: 20px; }

        .partner-name-text {
            font-weight: 600;
            font-size: 15px;
        }

        /* Botón Estilo Memo */
        .btn-memo {
            background-color: var(--memo-blue);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
        }

        .calendar-area {
            border: 1px dashed #e5e5e5;
            border-radius: 18px;
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            background: #fafafa;
        }

    </style>
</head>
<body>

    <nav class="nav-header">
        <a href="javascript:history.back()" class="back-link">← Volver</a>
        <span class="memo-logo">memo.</span>
    </nav>

    <main class="main-container">
        <div class="card">
            <span class="badge">Agendamiento Digital</span>
            <h1>Solicitar Sesión</h1>
            <p class="subtitle">Registrada para hoy, <?php echo date('d/m/Y'); ?></p>

            <span class="section-label">PROFESIONAL ASIGNADO</span>
            <div class="partner-slot">
                <div class="partner-info">
                    <span class="partner-icon">👤</span>
                    <span class="partner-name-text"><?php echo $nombre_partner; ?></span>
                </div>
                <div class="btn-memo" style="background: transparent; color: var(--memo-blue); padding: 0;">PERFIL</div>
            </div>

            <span class="section-label">DISPONIBILIDAD</span>
            <div class="calendar-area">
                Seleccione una fecha en el calendario para ver los bloques horarios.
            </div>

            <div style="margin-top: 30px;">
                <button class="btn-memo" style="width: 100%; padding: 18px;">Confirmar y Agendar</button>
            </div>
        </div>
    </main>

</body>
</html>