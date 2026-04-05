<?php
require_once 'config.php';

// 1. Capturar ID y validar que sea un número
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_partner <= 0) {
    header("Location: index.php");
    exit();
}

// 2. Consultar el partner
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - <?php echo $nombre_partner; ?> | MEMO</title>
    <style>
        /* --- LOOK & FEEL MEMO --- */
        
        /* Paleta de colores (ejemplo, ajusta según MEMO) */
        :root {
            --memo-primary: #4a90e2;    /* Azul MEMO principal */
            --memo-secondary: #f4f7f6;  /* Fondo claro */
            --memo-dark: #2c3e50;      /* Texto principal */
            --memo-light: #7f8c8d;     /* Texto secundario */
            --memo-white: #ffffff;
            --memo-accent: #e74c3c;     /* Color de acento (opcional) */
            --font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 0;
            background-color: var(--memo-secondary);
            color: var(--memo-dark);
            -webkit-font-smoothing: antialiased;
        }

        /* --- Header de Partner Minimalista --- */
        .partner-header {
            background-color: var(--memo-white);
            padding: 15px 20px;
            border-bottom: 1px solid #e1e8ed;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .partner-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .partner-logo-placeholder {
            width: 40px;
            height: 40px;
            background-color: #e1e8ed;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--memo-light);
            font-size: 1.2rem;
        }

        .partner-text {
            display: flex;
            flex-direction: column;
        }

        .partner-label {
            font-size: 0.75rem;
            color: var(--memo-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .partner-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--memo-dark);
            margin: 0;
        }

        /* --- Contenido Principal --- */
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .agenda-card {
            background-color: var(--memo-white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
            text-align: center;
        }

        h1.page-title {
            font-size: 2.2rem;
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--memo-dark);
            font-weight: 700;
        }

        h1.page-title strong {
            color: var(--memo-primary);
        }

        p.page-subtitle {
            font-size: 1.1rem;
            color: var(--memo-light);
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- Espacio para el Calendario --- */
        .calendar-placeholder {
            border: 2px dashed #e1e8ed;
            padding: 60px;
            border-radius: 10px;
            color: var(--memo-light);
            font-style: italic;
            margin-bottom: 30px;
            background-color: #fafcfc;
        }

        /* --- Botón de Acción MEMO --- */
        .btn-memo {
            display: inline-block;
            background-color: var(--memo-primary);
            color: var(--memo-white);
            padding: 16px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s ease, transform 0.1s ease;
            box-shadow: 0 4px 6px rgba(74, 144, 226, 0.2);
        }

        .btn-memo:hover {
            background-color: #357abd; /* Un tono más oscuro del primario */
        }

        .btn-memo:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(74, 144, 226, 0.2);
        }

        /* --- Footer --- */
        .memo-footer {
            text-align: center;
            padding: 20px;
            margin-top: auto;
            color: var(--memo-light);
            font-size: 0.85rem;
        }
        
        /* Ajuste para asegurar que el footer se mantenga abajo */
        html, body { height: 100%; display: flex; flex-direction: column; }
    </style>
</head>
<body>

    <header class="partner-header">
        <div class="partner-info">
            <div class="partner-logo-placeholder">
                <?php echo strtoupper(substr($nombre_partner, 0, 1)); ?>
            </div>
            <div class="partner-text">
                <span class="partner-label">Agendando con</span>
                <h2 class="partner-name"><?php echo $nombre_partner; ?></h2>
            </div>
        </div>
        <div style="font-weight: bold; color: var(--memo-primary); font-size: 1.2rem;">MEMO</div>
    </header>

    <div class="container">
        <div class="agenda-card">
            <h1 class="page-title">Solicita tu <strong>Sesión</strong></h1>
            <p class="page-subtitle">A continuación, selecciona el horario que mejor se adapte a tu disponibilidad.</p>
            
            <div class="calendar-placeholder">
                [ Espacio para el calendario / widget de turnos ]
            </div>
            
            <a href="#" class="btn-memo">Confirmar Horario</a>
        </div>
    </div>

    <footer class="memo-footer">
        &copy; <?php echo date('Y'); ?> MEMO - Todos los derechos reservados.
    </footer>

</body>
</html>