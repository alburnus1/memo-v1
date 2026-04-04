<?php
include 'config.php'; // Tu conexión a la BD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $beneficio = $_POST['beneficio'];
    $link = $_POST['link'];

    $sql = "INSERT INTO partners (nombre, tipo, beneficio_texto, link_contacto, es_premium) 
            VALUES (?, ?, ?, ?, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $tipo, $beneficio, $link]);

    $mensaje = "¡Gracias! Revisaremos tu solicitud y te contactaremos pronto.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a memo.</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Haz crecer tu consulta con memo.</h2>
    <?php if (isset($mensaje)) echo "<p style='color:green;'>$mensaje</p>"; ?>
    
    <form method="POST">
        <label>Nombre del Profesional/Centro:</label><br>
        <input type="text" name="nombre" required style="width:100%; margin-bottom:10px;"><br>
        
        <label>Especialidad:</label><br>
        <select name="tipo" style="width:100%; margin-bottom:10px;">
            <option value="kinesiologia">Kinesiología</option>
            <option value="farmacia">Farmacia</option>
            <option value="laboratorio">Laboratorio</option>
            <option value="psicologia">Psicología</option>
        </select><br>
        
        <label>Beneficio para el paciente (ej: 10% dcto):</label><br>
        <input type="text" name="beneficio" required style="width:100%; margin-bottom:10px;"><br>
        
        <label>Link de Agenda o WhatsApp:</label><br>
        <input type="url" name="link" placeholder="https://wa.me/..." style="width:100%; margin-bottom:20px;"><br>
        
        <button type="submit" style="width:100%; padding:15px; background:#007bff; color:white; border:none; border-radius:5px;">
            Enviar Solicitud de Partner
        </button>
    </form>
</body>
</html>