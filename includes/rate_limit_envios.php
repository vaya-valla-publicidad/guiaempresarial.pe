<?php
function verificarRateLimitEnvios($email, $conexion) {
    $conexion->query("DELETE FROM rate_limit_envios WHERE fecha < " . (time() - 86400));
    
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM rate_limit_envios WHERE email = ? AND fecha > ?");
    $hace_24h = time() - 86400;
    $stmt->bind_param("si", $email, $hace_24h);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $total_24h = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    if ($total_24h >= 6) {
        return ['permitido' => false, 'razon' => 'limite_24h'];
    }
    
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM rate_limit_envios WHERE email = ? AND fecha > ?");
    $hace_30min = time() - 1800;
    $stmt->bind_param("si", $email, $hace_30min);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $total_30min = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    if ($total_30min >= 3) {
        return ['permitido' => false, 'razon' => 'limite_30min'];
    }
    
    return ['permitido' => true];
}

function registrarEnvioCodigo($email, $conexion) {
    $stmt = $conexion->prepare("INSERT INTO rate_limit_envios (email, fecha) VALUES (?, ?)");
    $stmt->bind_param("si", $email, time());
    $stmt->execute();
    $stmt->close();
}

function obtenerMensajeBloqueo() {
    return "Has solicitado demasiados códigos. Por seguridad, inténtalo más tarde.";
}

function limpiarRateLimitAntiguo($conexion) {
    $conexion->query("DELETE FROM rate_limit_envios WHERE fecha < " . (time() - 86400));
}
?>