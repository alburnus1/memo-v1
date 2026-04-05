<?php 
include 'config.php'; 
session_start();
$apiKey = "19aa781dc1f7406ba58faece251dc77e"; 

// --- ACTUALIZADOR SEGURO ---
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
            $conn->query("UPDATE consultas SET diagnostico = 'Consulta Médica', indicaciones = '$texto' WHERE id = $id_db");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --dark: #121826; --accent: #4361ee; --red: #ef233c; --yellow: #fab005; }
        body { font-family: 'Inter', sans-serif; background: #fdfdfd; margin: 0; padding: 20px; color: #2b2d42; }
        
        /* Header y Logo */
        .nav-header { display: flex; justify-content: space-between; align-items: center; max-width: 450px; margin: 0 auto 20px auto; }
        .logo { font-weight: 800; font-size: 22px; letter-spacing: -1.5px; margin: 0; }
        .btn-logout { text-decoration: none; color: #8e8e93; font-size: 12px; font-weight: 600; background: #f0f0f0; padding: 6px 12px; border-radius: 10px; }

        .hero { background: var(--dark); color: white; padding: 40px 20px; border-radius: 28px; text-align: center; margin-bottom: 25px; position: relative; }
        .btn-rec { width: 75px; height: 75px; background: var(--red); border-radius: 50%; border: 4px solid rgba(255,255,255,0.2); cursor: pointer; transition: 0.3s; outline: none; }
        .btn-rec.recording { background: #2ecc71; animation: pulse 1.5s infinite; }
        
        .card { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 15px; border: 1px solid #f0f0f0; cursor: pointer; transition: 0.2s; }
        .card.loading { border: 1px solid #ffe066; background: #fffcf0; cursor: default; }
        
        .pill { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 8px; background: #edf2f4; color: var(--accent); }
        .pill.waiting { background: var(--yellow); color: #fff; }
        
        .spinner { width: 18px; height: 18px; border: 2px solid #f3f3f3; border-top: 2px solid var(--yellow); border-radius: 50%; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 8px; }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(46, 204, 113, 0); } 100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); } }

        /* Estilos del Modal */
        #modalRegistro { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.96); z-index:99999; flex-direction:column; align-items:center; justify-content:center; padding:20px; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); }
        .modal-card { width:100%; max-width:360px; background:white; padding:40px 25px; border-radius:32px; box-shadow: 0 15px 45px rgba(0,0,0,0.08); text-align:center; box-sizing: border-box; }
        .modal-card input { width: 100%; padding: 16px; margin-bottom: 12px; border-radius: 14px; border: 1px solid #f0f0f0; background: #f9f9f9; font-size: 16px; outline: none; box-sizing: border-box; }
        .btn-modal { width:100%; padding:18px; background:var(--accent); color:white; border:none; border-radius:18px; font-weight:700; font-size:16px; cursor:pointer; }
    </style>
</head>
<body>

<div style="max-width: 450px; margin: auto;">
    
    <nav class="nav-header">
        <h1 class="logo">memo.</h1>
        <?php if(isset($_SESSION['user'])): ?>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-logout">Entrar</a>
        <?php endif; ?>
    </nav>

    <div class="hero">
        <p id="status" style="opacity:0.7; font-size:14px; margin: 0 0 15px 0;">Toca para grabar</p>
        <button class="btn-rec" id="btn" onclick="toggleRecord()"></button>
    </div>

    <div id="historial">
        <?php
        if (isset($_SESSION['user'])):
            $uid = $_SESSION['user']['id'];
            $res = $conn->query("SELECT * FROM consultas WHERE usuario_id = $uid ORDER BY id DESC LIMIT 10");
            while($row = $res->fetch_assoc()):
                $is_loading = ($row['diagnostico'] === 'Procesando...');
        ?>
            <div class="card <?php echo $is_loading ? 'loading' : ''; ?>" 
                 <?php if(!$is_loading) { ?> onclick="window.location='detalle.php?id=<?php echo $row['id']; ?>'" <?php } ?>>
                <?php if ($is_loading): ?>
                    <span class="pill waiting">⏳ ANALIZANDO</span>
                    <h3 style="margin: 12px 0 6px; color: #856404;"><span class="spinner"></span> Procesando audio...</h3>
                    <p style="color: #666; font-size: 14px;">Estamos redactando tu ficha médica.</p>
                <?php else: ?>
                    <span class="pill">Consulta Médica</span>
                    <h3 style="margin: 12px 0 6px;"><?php echo htmlspecialchars($row['diagnostico']); ?></h3>
                    <p style="color: #666; font-size: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo htmlspecialchars($row['indicaciones']); ?>
                    </p>
                <?php endif; ?>
                <div style="font-size: 10px; color: #bbb; margin-top: 10px;">📅 <?php echo $row['fecha']; ?></div>
            </div>
        <?php 
            endwhile; 
        else: ?>
            <p style="text-align: center; color: #bbb; margin-top: 40px;">Tus notas aparecerán aquí después de grabar.</p>
        <?php endif; ?>
    </div>
</div>

<div id="modalRegistro">
    <div class="modal-card">
        <span style="font-weight:900; font-size:26px; letter-spacing:-1.5px; display:block; margin-bottom:10px;">memo.</span>
        <h2 style="font-size:18px; font-weight:700; margin-bottom:8px;">¡Audio recibido!</h2>
        <p style="color:#8e8e93; font-size:14px; margin-bottom:25px; line-height:1.4;">Ingresa tu correo para procesar la nota y guardarla en tu cuenta.</p>
        
        <input type="text" id="reg_nombre" placeholder="Tu nombre">
        <input type="email" id="reg_email" placeholder="Correo electrónico">
        <input type="password" id="reg_pass" placeholder="Crea una contraseña">
        
        <button onclick="registrarYContinuar()" class="btn-modal">Guardar y ver resultado</button>
    </div>
</div>

<script>
let mr; let chunks = []; let isRecording = false;
var usuarioLogueado = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;

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
                
                btn.style.opacity = "0.3";
                btn.disabled = true;

                // ACTIVAR MODAL SI NO ESTÁ LOGUEADO
                if (!usuarioLogueado) {
                    document.getElementById('modalRegistro').style.display = 'flex';
                }

                await fetch('upload_audio.php', { method: 'POST', body: fd });
                
                // Si está logueado, recargamos de una. Si no, esperamos al modal.
                if (usuarioLogueado) { location.reload(); }
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

async function registrarYContinuar() {
    const nombre = document.getElementById('reg_nombre').value;
    const email = document.getElementById('reg_email').value;
    const pass = document.getElementById('reg_pass').value;

    if (!email || !pass) { alert("Completa los datos para continuar."); return; }

    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('email', email);
    formData.append('password', pass);

    const resp = await fetch('registro_ajax.php', { method: 'POST', body: formData });
    const data = await resp.json();

    if (data.success) {
        location.reload();
    } else {
        alert(data.message || "Error al registrar");
    }
}

// Recarga automática para procesando
if (document.body.innerText.includes("ANALIZANDO")) {
    setTimeout(() => { location.reload(); }, 5000);
}
</script>
</body>
</html>