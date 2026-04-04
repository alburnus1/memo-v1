<?php
include 'config.php';

$partner_id = $_GET['partner_id'] ?? null;

if ($partner_id) {
    // 1. Sumar el clic al partner en la columna clicks_recibidos
    $stmt = $pdo->prepare("UPDATE partners SET clicks_recibidos = clicks_recibidos + 1 WHERE id = ?");
    $stmt->execute([$partner_id]);

    // 2. Obtener el link de destino
    $stmt_link = $pdo->prepare("SELECT link_contacto FROM partners WHERE id = ?");
    $stmt_link->execute([$partner_id]);
    $partner = $stmt_link->fetch();

    if ($partner && !empty($partner['link_contacto'])) {
        header("Location: " . $partner['link_contacto']);
        exit();
    }
}

// Si algo falla, volver al home
header("Location: index.php");
exit();