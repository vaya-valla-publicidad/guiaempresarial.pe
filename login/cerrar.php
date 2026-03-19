<?php
session_start();
session_destroy();
header("Location: /guiaempresarial.pe/index.php");
exit;