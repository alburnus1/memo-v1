<?php 
include 'config.php'; 
session_start();
// Asegúrate de que esta API KEY sea la correcta (termina en 88e en tu upload_audio)
$apiKey = "19aa781dc1f7406ba58faece251dc77e"; 


// --- LOGIN / REGISTRO SIMPLE ---
if (isset($_POST['registro'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("INSERT IGNORE INTO usuarios (nombre, email) VALUES ('$nombre', '$email')");
    $res = $conn->query("SELECT id, nombre FROM usuarios WHERE email = '$email'");
    $_SESSION['user'] = $res->fetch_assoc();
}

// --- ACTUALIZADOR SEGURO (Solo procesa lo del usuario logueado) ---
if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $pendientes = $conn->query("SELECT id, indicaciones FROM consultas WHERE diagnostico = 'Procesando...' AND usuario_id = $uid");

    while($row = $pendientes->fetch_assoc()) {
        $tId = $row['indicaciones']; 
        $id_db = $row['id'];

        $ch = curl_init("https://api.assemblyai.com/v2/transcript/$tId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $apiKey"]);
        $res = json_decode(curl_exec($ch), true);

        if (isset($res['status']) && $res['status'] === 'completed') {
            $texto = $conn->real_escape_string($res['text']);
            // Aquí la IA ya terminó, actualizamos el título a 'Consulta Médica'
            $conn->query("UPDATE consultas SET diagnostico = 'Consulta Médica', indicaciones = '$texto' WHERE id = $id_db");
        } elseif (isset($res['status']) && $res['status'] === 'error') {
            $conn->query("UPDATE consultas SET diagnostico = 'Error de Audio', indicaciones = 'No se pudo procesar la grabación.' WHERE id = $id_db");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>memo.</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --dark: #121826; --accent: #4361ee; --red: #ef233c; --yellow: #fab005; }
        body { font-family: 'Inter', sans-serif; background: #fdfdfd; margin: 0; padding: 20px; color: #2b2d42; }
        .hero { background: var(--dark); color: white; padding: 40px 20px; border-radius: 28px; text-align: center; margin-bottom: 25px; }
        .btn-rec { width: 75px; height: 75px; background: var(--red); border-radius: 50%; border: 4px solid rgba(255,255,255,0.2); cursor: pointer; transition: 0.3s; }
        .btn-rec.recording { background: #2ecc71; animation: pulse 1.5s infinite; }
        
        /* Estilos de Card */
        .card { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 15px; border: 1px solid #f0f0f0; cursor: pointer; transition: 0.2s; }
        .card.loading { border: 1px solid #ffe066; background: #fffcf0; cursor: default; }
        
        .pill { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 8px; background: #edf2f4; color: var(--accent); }
        .pill.waiting { background: var(--yellow); color: #fff; }
        
        /* Spinner */
        .spinner { width: 18px; height: 18px; border: 2px solid #f3f3f3; border-top: 2px solid var(--yellow); border-radius: 50%; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 8px; }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(46, 204, 113, 0); } 100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); } }
        
        input { width: 100%; padding: 12px; margin: 8px 0; border-radius: 12px; border: 1px solid #ddd; outline: none; box-sizing: border-box; }
        .btn-login { background: var(--accent); color: white; border: none; padding: 14px; border-radius: 12px; width: 100%; font-weight: 600; cursor: pointer; }
    </style>
    <div style="position: absolute; top: 20px; right: 20px;">
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php" style="text-decoration:none; color:#8e8e93; font-size:12px; font-weight:600; background:#f0f0f0; padding:8px 12px; border-radius:12px;">
            Cerrar Sesión
        </a>
    <?php endif; ?>
</div>
</head>
<body>

<div style="max-width: 450px; margin: auto;">
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="card" style="margin-top: 50px; text-align: center;">
            <h1 style="letter-spacing: -1.5px;">memo.</h1>
            <p>Identifícate para guardar tus memos</p>
            <form method="POST">
                <input type="text" name="nombre" placeholder="Tu nombre" required>
                <input type="email" name="email" placeholder="tu@email.com" required>
                <button type="submit" name="registro" class="btn-login">Entrar</button>
            </form>
        </div>
    <?php else: ?>
        <div class="hero">
            <h1 style="margin:0; letter-spacing:-1.5px">memo.</h1>
            <p id="status" style="opacity:0.7; font-size:14px; margin: 15px 0;">Toca para grabar</p>
            <button class="btn-rec" id="btn" onclick="toggleRecord()"></button>
        </div>

        <div id="historial">
            <?php
            $uid = $_SESSION['user']['id'];
            $res = $conn->query("SELECT * FROM consultas WHERE usuario_id = $uid ORDER BY id DESC LIMIT 10");
            while($row = $res->fetch_assoc()):
                $is_loading = ($row['diagnostico'] === 'Procesando...');
            ?>
            <div class="card <?php echo $is_loading ? 'loading' : ''; ?>" 
                 <?php if(!$is_loading) { ?> onclick="window.location='detalle.php?id=<?php echo $row['id']; ?>'" <?php } ?>>
                
                <?php if ($is_loading): ?>
                    <span class="pill waiting">⏳ ANALIZANDO</span>
                    <h3 style="margin: 12px 0 6px; color: #856404;">
                        <span class="spinner"></span> Procesando audio...
                    </h3>
                    <p style="color: #666; font-size: 14px;">Estamos redactando tu ficha médica, un momento.</p>
                <?php else: ?>
                    <span class="pill">Diagnóstico</span>
                    <h3 style="margin: 12px 0 6px;"><?php echo htmlspecialchars($row['diagnostico']); ?></h3>
                    <p style="color: #666; font-size: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo htmlspecialchars($row['indicaciones']); ?>
                    </p>
                <?php endif; ?>
                <div style="font-size: 10px; color: #bbb; margin-top: 10px;">📅 <?php echo $row['fecha']; ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<script>
let mr; let chunks = []; let isRecording = false;

async function toggleRecord() {
    const btn = document.getElementById('btn');
    const status = document.getElementById('status');

    if (!isRecording) {
        try {
            const s = await navigator.mediaDevices.getUserMedia({ audio: true });
            mr = new MediaRecorder(s);
            chunks = [];
            mr.ondataavailable = e => chunks.push(e.data);
            mr.onstop = async () => {
                status.innerText = "Subiendo audio...";
                const blob = new Blob(chunks, { type: 'audio/wav' });
                const fd = new FormData(); fd.append('audio', blob);
                
                // Efecto visual de envío
                btn.style.opacity = "0.3";
                btn.disabled = true;

                await fetch('upload_audio.php', { method: 'POST', body: fd });
                location.reload();
            };
            mr.start();
            isRecording = true;
            btn.classList.add('recording');
            status.innerText = "Grabando... Toca para parar";
        } catch(e) { alert("Por favor, permite el acceso al micrófono."); }
    } else {
        mr.stop();
        isRecording = false;
        btn.classList.remove('recording');
        status.innerText = "Enviando a la IA...";
    }
}

// Recarga automática mejorada: busca el texto de carga específicamente
if (document.body.innerText.includes("ANALIZANDO")) {
    console.log("Nota pendiente detectada. Actualizando en 5s...");
    setTimeout(() => { location.reload(); }, 5000);
}
</script>
</body>
</html>