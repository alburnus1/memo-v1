<?php 
include 'config.php'; 
session_start();


// Lógica de Match de Partners
$indicaciones_texto = strtolower($consulta['indicaciones']);
$partner_sugerido = null;

// Mapeo de palabras clave a tipos de partner
$keywords = [
    'kinesiologia' => ['kine', 'kinesiologo', 'sesiones', 'rehabilitacion'],
    'farmacia'     => ['paracetamol', 'ibuprofeno', 'receta', 'comprar', 'clorfenamina'],
    'laboratorio'  => ['examen', 'radiografia', 'sangre', 'rayos x', 'scann'],
    'psicologia'   => ['terapia', 'psicologo', 'salud mental']
];

foreach ($keywords as $tipo => $words) {
    foreach ($words as $word) {
        if (strpos($indicaciones_texto, $word) !== false) {
            // Buscamos un partner de ese tipo que sea Premium
            $stmt_p = $pdo->prepare("SELECT * FROM partners WHERE tipo = ? AND es_premium = 1 LIMIT 1");
            $stmt_p->execute([$tipo]);
            $partner_sugerido = $stmt_p->fetch();
            break 2;
        }
    }
}

if(!isset($_SESSION['user']) || !isset($_GET['id'])) { header("Location: index.php"); exit; }

$id = intval($_GET['id']);
$uid = $_SESSION['user']['id'];

$res = $conn->query("SELECT * FROM consultas WHERE id = $id AND usuario_id = $uid");
$data = $res->fetch_assoc();

if (!$data) { die("No tienes permiso para ver esta ficha."); }

function tieneDato($texto, $busqueda) {
    return (stripos($texto, $busqueda) !== false);
}

$texto = $data['indicaciones'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ficha Médica - memo.</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #4361ee; --bg: #f8f9fc; --dark: #1a1c23; --yellow: #ffb703; --green: #2ec4b6; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 20px; color: #334155; line-height: 1.6; }
        .container { max-width: 500px; margin: auto; }
        
        .nav { display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center; }
        .btn-back { text-decoration: none; color: var(--blue); font-weight: 600; font-size: 14px; }
        
        .ficha { background: white; border-radius: 30px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; }
        .badge { background: #eef2ff; color: var(--blue); padding: 5px 12px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        
        h1 { font-size: 26px; margin: 15px 0 5px; color: var(--dark); letter-spacing: -0.5px; }
        .fecha { color: #94a3b8; font-size: 13px; display: block; margin-bottom: 25px; }
        
        .seccion { margin-bottom: 25px; }
        .label { display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
        .texto { font-size: 16px; color: #475569; white-space: pre-line; margin-bottom: 15px; }

        /* Estilo Único de Tarjetas (Cards) */
        .item-card { background: #fdfdfd; border: 1px solid #edf2f7; padding: 15px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .item-info strong { display: block; color: var(--dark); font-size: 15px; }
        .item-info span { font-size: 13px; color: #64748b; display: block; line-height: 1.2; margin-top: 2px; }
        
        .btn-action { text-decoration: none; padding: 8px 14px; border-radius: 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .btn-blue { background: var(--blue); color: white; }
        .btn-yellow { background: var(--yellow); color: white; }
        .btn-green { background: var(--green); color: white; }

        .audio-box { background: #f1f5f9; padding: 15px; border-radius: 20px; margin-top: 10px; }
        audio { width: 100%; height: 35px; margin-top: 8px; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav">
        <a href="index.php" class="btn-back">← Historial</a>
        <span style="font-size: 16px; font-weight: 800; color: var(--dark);">memo.</span>
    </div>

    <div class="ficha">
        <span class="badge">Ficha Médica Digital</span>
        <h1><?php echo htmlspecialchars($data['diagnostico']); ?></h1>
        <span class="fecha">Registrada el <?php echo date("d/m/Y", strtotime($data['fecha'])); ?></span>

        <div class="seccion">
            <span class="label">Resumen de la consulta</span>
            <div class="texto"><?php echo htmlspecialchars($data['indicaciones']); ?></div>
        </div>

        <div class="seccion">
            <span class="label">Medicamentos</span>
            <?php 
            $farmacos = ['paracetamol', 'clorfenamina', 'ibuprofeno', 'amoxicilina', 'aspirina', 'omeprazol', 'loratadina', 'diclofenaco', 'enantyum'];
            $hayMeds = false;
            foreach ($farmacos as $f) {
                if (preg_match('/' . $f . '\s+([^.,\n]*)/i', $texto, $matches)) {
                    $hayMeds = true;
                    echo '<div class="item-card">
                            <div class="item-info">
                                <strong>💊 '.ucfirst($f).'</strong>
                                <span>'.(trim($matches[1]) ?: "Según indicación").'</span>
                            </div>
                            <a href="https://www.google.com/search?q=comprar+'.$f.'" target="_blank" class="btn-action btn-blue">Comprar</a>
                          </div>';
                }
            }
            if(!$hayMeds) echo "<p style='font-size:13px; color:#cbd5e1;'>No se detectaron medicamentos.</p>";
            ?>
        </div>

        <div class="seccion">
            <span class="label">Exámenes y Procedimientos</span>
            <?php 
            $examenes = ['radiografía', 'radiografia', 'sangre', 'orina', 'ecografía', 'ecografia', 'resonancia', 'scanner', 'perfil'];
            $hayEx = false;
            foreach ($examenes as $ex) {
                if (stripos($texto, $ex) !== false) {
                    $hayEx = true;
                    echo '<div class="item-card">
                            <div class="item-info">
                                <strong>🔬 '.ucfirst($ex).'</strong>
                                <span>Solicitado en la consulta</span>
                            </div>
                            <a href="https://www.google.com/search?q=laboratorio+para+'.$ex.'" target="_blank" class="btn-action btn-yellow">Ver Centros</a>
                          </div>';
                }
            }
            if(!$hayEx) echo "<p style='font-size:13px; color:#cbd5e1;'>No se detectaron exámenes.</p>";
            ?>
        </div>

        <div class="seccion">
            <span class="label">Derivaciones</span>
            <?php 
            $especialistas = ['kinesiólogo', 'kinesiologo', 'psicólogo', 'psicologo', 'traumatólogo', 'nutricionista', 'oftalmólogo', 'especialista'];
            $hayDer = false;
            foreach ($especialistas as $esp) {
                if (stripos($texto, $esp) !== false) {
                    $hayDer = true;
                    echo '<div class="item-card">
                            <div class="item-info">
                                <strong>👨‍⚕️ '.ucfirst($esp).'</strong>
                                <span>Interconsulta recomendada</span>
                            </div>
                            <a href="https://www.google.com/search?q=agendar+hora+'.$esp.'" target="_blank" class="btn-action btn-green">Agendar</a>
                          </div>';
                }
            }
            if(!$hayDer) echo "<p style='font-size:13px; color:#cbd5e1;'>No se detectaron derivaciones.</p>";
            ?>
        </div>
        
<?php if ($partner_sugerido): ?>
<div style="background: #f0f7ff; border: 1px solid #007bff; padding: 15px; border-radius: 10px; margin-top: 20px;">
    <h4 style="color: #0056b3; margin-top: 0;">🎁 Beneficio Exclusivo</h4>
    <p>Según tu indicación de <strong><?php echo ucfirst($partner_sugerido['tipo']); ?></strong>:</p>
    <p><strong><?php echo $partner_sugerido['nombre']; ?></strong> ofrece: 
       <span style="color: #28a745; font-weight: bold;"><?php echo $partner_sugerido['beneficio_texto']; ?></span>
    </p>
    <a href="agendar.php?id=<?php echo $partner_sugerido['id']; ?>" 
       style="display: block; background: #007bff; color: white; text-align: center; padding: 10px; border-radius: 5px; text-decoration: none; font-weight: bold;">
       Agendar Hora / Contactar
    </a>
</div>
<?php endif; ?>

        <div class="audio-box">
            <span class="label">Audio Original</span>
            <audio controls src="<?php echo $data['audio_url']; ?>"></audio>
        </div>
    </div>
</div>

</body>
</html>