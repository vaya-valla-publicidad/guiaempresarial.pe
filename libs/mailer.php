<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';
function enviarCorreo(string $destinatario, string $nombre, string $asunto, string $cuerpo): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'TUCORREO@gmail.com';        // ← tu Gmail real
        $mail->Password   = 'xxxx xxxx xxxx xxxx';       // ← nueva contraseña de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('TU_GMAIL@gmail.com', 'Guía Empresarial');  // ← tu Gmail real
        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}