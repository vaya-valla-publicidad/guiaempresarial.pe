<?php
session_start();
unset($_SESSION['usuario_publico_id']);
unset($_SESSION['usuario_publico_nombre']);
unset($_SESSION['usuario_publico_foto']);
header('Location: index.php');
exit;