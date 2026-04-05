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

// Datos Dinámicos
$nombre = htmlspecialchars($partner['nombre']);
$especialidad = htmlspecialchars($partner['especialidad']);
$direccion = htmlspecialchars($partner['direccion']);
$foto = !empty($partner['foto_url']) ? $partner['foto_url'] : 'https://via.placeholder.com/150';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar | memo.</title>
    <style>
        :root { --memo-blue: #4f80ff; --text-main: #1a1a1a; --text-muted: #8e8e93; }
        body { font-family: -apple-system, sans-serif; background: #f9f9f9; margin: 0; display: flex; flex-direction: column; align-items: center; }
        
        .nav-header { width: 100%; max-width: 500px; display: flex; justify-content: space-between; padding: 20px; box-sizing: border-box; }
        .main-container { width: 100%; max-width: 500px; padding: 0 15px 40px; box-sizing: border-box; }
        
        .card { background: white; border-radius: 28px; padding: 30px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        /* Perfil Profesional Estilo Memo */
        .profile-box { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .profile-img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; background: #eee; }
        .profile-info h2 { font-size: 18px; margin: 0; font-weight: 700; }
        .profile-info p { font-size: 13px; color: var(--memo-blue); margin: 2px 0 0; font-weight: 600; }

        .location-tag { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); margin-bottom: 25px; }

        h1 { font-size: 24px; font-weight: 800; margin: 0 0 20px 0; letter-spacing: -0.5px; }
        .label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; display: block; }
        
        input, select { width: 100%; padding: 16px; border-radius: 14px; border: 1px solid #efefef; background: #fcfcfc; font-size: 15px; margin-bottom: 15px; outline: none; }
        
        .btn-confirm { width: 100%; background: var(--memo-blue); color: white; border: none; padding: 18px; border-radius: 16px; font-weight: 700; font-size: 15px; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>

    <nav class="nav-header">
        <a href="javascript:history.back()" style="text-decoration:none; color:var(--memo-blue); font-size:14px; font-weight:500;">← Volver</a>
        <span style="font-weight:800; font-size:18px;">memo.</span>
    </nav>

    <main class="main-container">
        <div class="card">
            <div class="profile-box">
                <img src="<?php echo $foto; ?>" class="profile-img">
                <div class="profile-info">
                    <h2><?php echo $nombre; ?></h2>
                    <p><?php echo $especialidad; ?></p>
                </div>
            </div>

            <div class="location-tag">
                <span>📍</span> <?php echo $direccion; ?>
            </div>

            <h1>Agendar Sesión</h1>

            <form method="POST">
                <span class="label">Selecciona el día</span>
                <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>">

                <span class="label">Bloque horario</span>
                <select name="hora" required>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:30">11:30 AM</option>
                    <option value="16:00">04:00 PM</option>
                </select>

                <button type="submit" name="agendar" class="btn-confirm">Confirmar Cita</button>
            </form>
        </div>
    </main>

</body>
</html>