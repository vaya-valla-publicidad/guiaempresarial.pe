<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';

function enviarCorreo(string $destinatario, string $nombre, string $asunto, string $cuerpo): bool
{
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($destinatario, $nombre);
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $cuerpo;
    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}

function plantillaCorreoOTP(string $nombre, string $codigo, string $motivo): string
{
  $titulo = $motivo === 'registro' ? 'Confirma tu cuenta' : 'Tu código de acceso';
  $mensaje = $motivo === 'registro'
    ? "Bienvenido a Guía Empresarial. Para finalizar tu registro, ingresa el siguiente código en la pantalla de verificación:"
    : "Has solicitado un código temporal para acceder a tu cuenta en Guía Empresarial. Ingrésalo a continuación:";

  return "
    <div style='font-family: \"Segoe UI\", Roboto, \"Helvetica Neue\", sans-serif; background-color: #f3f4f6; padding: 40px 20px; line-height: 1.5;'>
      <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);'>
        <div style='background-color: #111827; padding: 24px; text-align: center; border-bottom: 3px solid #10b981;'>
          <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px;'>Guía Empresarial</h2>
        </div>
        <div style='padding: 32px 28px;'>
          <h3 style='color: #1f2937; font-size: 19px; margin-top: 0; margin-bottom: 20px;'>Hola, {$nombre}</h3>
          <p style='color: #4b5563; font-size: 15px; margin-bottom: 32px;'>
            {$mensaje}
          </p>
          <div style='text-align: center; margin: 0 0 32px 0;'>
            <div style='display: inline-block; background-color: #f8fafc; color: #0f172a; font-size: 34px; font-weight: 700; letter-spacing: 10px; padding: 18px 24px; border-radius: 8px; border: 1px dashed #cbd5e1;'>
              {$codigo}
            </div>
          </div>
          <p style='color: #64748b; font-size: 13.5px; text-align: center; margin-bottom: 0;'>
            Este código es válido por 10 minutos.<br>Si no lo solicitaste, puedes ignorar este correo de forma segura.
          </p>
        </div>
      </div>
      <div style='text-align: center; margin-top: 24px; color: #94a3b8; font-size: 12px;'>
        &copy; " . date('Y') . " Guía Empresarial. Todos los derechos reservados.
      </div>
    </div>";
}