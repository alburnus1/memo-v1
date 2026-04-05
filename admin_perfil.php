<?php
// 1. Gestión de Sesión y Seguridad
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

// Forzar UTF-8 en la conexión
$conn->set_charset("utf8");

// Redirigir al login si no hay sesión iniciada
if (!isset($_SESSION['partner_id'])) {
    header("Location: login.php");
    exit();
}

$id_partner = $_SESSION['partner_id'];
$mensaje = "";

// 2. Lógica de Actualización (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nombre       = $_POST['nombre'];
    $especialidad = $_POST['especialidad'];
    $direccion    = $_POST['direccion'];
    $bio          = $_POST['bio'];
    $precio       = intval($_POST['precio']);
    $duracion     = $_POST['duracion'];
    $modalidad    = $_POST['modalidad'];
    $foto_url     = $_POST['foto_url'];

    $sql_update = "UPDATE partners SET 
                    nombre = ?, 
                    especialidad = ?, 
                    direccion = ?, 
                    bio = ?, 
                    precio = ?, 
                    duracion = ?, 
                    modalidad = ?, 
                    foto_url = ? 
                   WHERE id = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssisssi", $nombre, $especialidad, $direccion, $bio, $precio, $duracion, $modalidad, $foto_url, $id_partner);
    
    if ($stmt->execute()) {
        $mensaje = "✅ Perfil actualizado con éxito.";
    } else {
        $mensaje = "❌ Error al actualizar: " . $conn->error;
    }
}

// 3. Obtener datos actuales para el formulario
$query = "SELECT * FROM partners WHERE id = ?";
$stmt_get = $conn->prepare($query);
$stmt_get->bind_param("i", $id_partner);
$stmt_get->execute();
$p = $stmt_get->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <div style="position: absolute; top: 20px; right: 20px;">
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php" style="text-decoration:none; color:#8e8e93; font-size:12px; font-weight:600; background:#f0f0f0; padding:8px 12px; border-radius:12px;">
            Cerrar Sesión
        </a>
    <?php endif; ?>
</div>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Perfil | memo.</title>
    <style>
        :root {
            --memo-blue: #4f80ff;
            --text-main: #1a1a1a;
            --text-muted: #8e8e93;
            --bg-body: #f9f9f9;
        }

        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 50px;
        }

        .header-admin {
            width: 100%;
            max-width: 500px;
            padding: 30px 20px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-admin h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
        }

        .logout-btn {
            font-size: 13px;
            color: #ff4f4f;
            text-decoration: none;
            font-weight: 600;
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 15px;
        }

        .alert {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        .card {
            background: #ffffff;
            border-radius: 28px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.5px;
            margin-top: 18px;
        }

        input, textarea, select {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #efefef;
            background-color: #fcfcfc;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            color: var(--text-main);
        }

        textarea {
            height: 100px;
            resize: none;
            line-height: 1.4;
        }

        .row {
            display: flex;
            gap: 15px;
        }

        .col { flex: 1; }

        .btn-save {
            width: 100%;
            background-color: var(--memo-blue);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(79, 128, 255, 0.2);
        }

        .preview-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--memo-blue);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="header-admin">
        <h1>Mi Perfil</h1>
        <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>

    <div class="container">
        
        <?php if($mensaje): ?>
            <div class="alert"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form class="card" method="POST">
            
            <span class="label" style="margin-top: 0;">Nombre Profesional</span>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" required>

            <span class="label">Especialidad</span>
            <input type="text" name="especialidad" value="<?php echo htmlspecialchars($p['especialidad']); ?>" placeholder="Ej: Psicóloga Clínica UdeC">

            <span class="label">Sobre mí (Breve reseña en 1ra persona)</span>
            <textarea name="bio" placeholder="Ej: Ayudo a personas a gestionar su ansiedad..."><?php echo htmlspecialchars($p['bio']); ?></textarea>

            <div class="row">
                <div class="col">
                    <span class="label">Precio ($)</span>
                    <input type="number" name="precio" value="<?php echo $p['precio']; ?>" placeholder="40000">
                </div>
                <div class="col">
                    <span class="label">Duración</span>
                    <input type="text" name="duracion" value="<?php echo htmlspecialchars($p['duracion']); ?>" placeholder="50 min">
                </div>
            </div>

            <span class="label">Modalidad</span>
            <select name="modalidad">
                <option value="Presencial y Online" <?php if($p['modalidad'] == 'Presencial y Online') echo 'selected'; ?>>Presencial y Online</option>
                <option value="Solo Online" <?php if($p['modalidad'] == 'Solo Online') echo 'selected'; ?>>Solo Online</option>
                <option value="Solo Presencial" <?php if($p['modalidad'] == 'Solo Presencial') echo 'selected'; ?>>Solo Presencial</option>
            </select>

            <span class="label">Dirección de atención</span>
            <input type="text" name="direccion" value="<?php echo htmlspecialchars($p['direccion']); ?>" placeholder="Cochrane 345, Concepción">

            <span class="label">URL de tu Foto (Avatar)</span>
            <input type="text" name="foto_url" value="<?php echo htmlspecialchars($p['foto_url']); ?>" placeholder="https://dominio.com/foto.jpg">

            <button type="submit" name="update_profile" class="btn-save">Guardar Cambios</button>

            <a href="agendar.php?id=<?php echo $id_partner; ?>" target="_blank" class="preview-link">Ver mi página de agendamiento →</a>
        </form>
    </div>

</body>
</html>