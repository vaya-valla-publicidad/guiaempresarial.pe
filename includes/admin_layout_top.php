<?php
/**
 * Bloque común para la parte superior de los paneles administrativos
 * Incluye: Barra de progreso NProgress y Banner de Mantenimiento
 */
?>
<div id="nprogress-bar"></div>
<?php if (file_exists(__DIR__ . '/../mantenimiento.flag')): ?>
    <div id="mantenimiento-banner" class="mantenimiento-top-banner">
        <i class="bi bi-exclamation-triangle-fill"></i>
        EL SITIO WEB SE ENCUENTRA ACTUALMENTE EN MANTENIMIENTO (PÚBLICO BLOQUEADO)
    </div>
<?php else: ?>
    <div id="mantenimiento-banner" class="mantenimiento-top-banner online" style="display:none;">
        <i class="bi bi-check-circle-fill"></i>
        EL SITIO WEB ESTÁ EN LÍNEA Y ACCESIBLE PARA EL PÚBLICO
    </div>
<?php endif; ?>
