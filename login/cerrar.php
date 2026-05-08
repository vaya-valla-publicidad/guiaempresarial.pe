<?php
session_start();
session_destroy();
require_once __DIR__ . '/../includes/config.php';
header("Location: " . APP_URL . "/index");
exit;