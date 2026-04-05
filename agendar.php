<?php
require_once 'config.php';
$id_partner = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_partner <= 0) { header("Location: index.php"); exit(); }

// Consultar Partner
$query = "SELECT * FROM partners WHERE id = ? AND activo = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_partner);
$stmt->execute();
$partner = $result->fetch_assoc();
if (!$partner) { header("Location: index.php"); exit(); }

// Lógica de Guardado (Procesar Formulario)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $nombre = "Usuario de Prueba"; // Aquí luego irá el nombre del usuario logueado

    $insert = "INSERT INTO citas (partner_id, nombre_paciente, fecha_cita, hora_cita) VALUES (?, ?, ?, ?)";
    $stmt_ins = $conn->prepare($insert);
    $stmt_ins->bind_param("isss", $id_partner, $nombre, $fecha, $hora);
    
    if ($stmt_ins->execute()) {
        $mensaje = "✅ Cita agendada con éxito";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar | memo.</title>
    <style>
        /* Reutilizamos los estilos anteriores de memo. */
        :root { --memo-blue: #4f80ff; --text-main: #1a1a1a; --text-muted: #8e8e93; }
        body { font-family: -apple-system, sans-serif; background: #f9f9f9; display: flex; flex-direction: column; align-items: center; margin:0; }
        .nav-header { width: 100%; max-width: 500px; display: flex; justify-content: space-between; padding: 20px; box-sizing: border-box; }
        .main-container { width: 100%; max-width: 500px; padding: 0 15px; }
        .card { background: white; border-radius: 28px; padding: 30px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .section-label { color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; margin: 20px 0 10px; display: block; }
        
        /* Inputs Estilo Memo */
        input[type="date"], select {
            width: 100%; padding: 15px; border-radius: 12px; border: 1px solid #f0f0f0; 
            font-size: 16px; margin-bottom: 15px; outline: none; background: #fafafa;
        }
        .btn-submit {
            width: 100%; background: var(--memo-blue); color: white; border: none; 
            padding: 18px; border-radius: 15px; font-weight: 700; font-size: 16px; cursor: pointer;
        }
        .alert { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="nav-header">
        <span style="color:var(--memo-blue); font-weight:500;">← Volver</span>
        <span style="font-weight:800;">memo.</span>
    </nav>

    <main class="main-container">
        <div class="card">
            <?php if($mensaje): ?> <div class="alert"><?php echo $mensaje; ?></div> <?php endif; ?>
            
            <h1 style="font-size:26px; margin-bottom:5px;">Agendar Cita</h1>
            <p style="color:var(--text-muted); font-size:14px; margin-bottom:25px;">Con <?php echo htmlspecialchars($partner['nombre']); ?></p>

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

                <button type="submit" name="agendar" class="btn-submit">Confirmar Cita</button>
            </form>
        </div>
    </main>

</body>
</html>