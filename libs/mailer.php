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
  $titulo = 'Verificación de Seguridad';
  if ($motivo === 'registro') $titulo = 'Confirma tu cuenta';
  if ($motivo === 'acceso') $titulo = 'Tu código de acceso';
  if ($motivo === 'password') $titulo = 'Cambio de Contraseña';

  $mensaje = $motivo === 'registro'
    ? "Bienvenido a Guía Empresarial. Para finalizar tu registro, ingresa el siguiente código en la pantalla de verificación:"
    : ($motivo === 'password'
      ? "Has solicitado cambiar tu contraseña. Por seguridad, ingresa el siguiente código para autorizar la operación:"
      : "Has solicitado un código temporal para acceder a tu cuenta en Guía Empresarial. Ingrésalo a continuación:");

  return "
    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin: 0; padding: 0;'>
      <tr>
        <td align='center' style='padding: 40px 20px;'>
          <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 500px; text-align: left;'>
            <tr>
              <td style='padding-bottom: 25px;'>
                <div style='width: 35px; height: 4px; background-color: #fbbf24; margin-bottom: 12px;'></div>
                <h1 style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin: 0; color: #000000;'>" . APP_NAME . "</h1>
              </td>
            </tr>
            <tr>
              <td style='padding-bottom: 30px;'>
                <h2 style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 28px; font-weight: 800; line-height: 1.1; margin: 0 0 15px 0; letter-spacing: -0.5px; color: #000000;'>Confirmación de<br>identidad.</h2>
                <p style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 15px; color: #444444; line-height: 1.4; margin: 0;'>
                  Hola {$nombre}, copia tu código de verificación:
                </p>
              </td>
            </tr>
            <tr>
              <td style='padding: 20px 0;'>
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 52px; font-weight: 800; letter-spacing: 10px; color: #000000;'>
                  {$codigo}
                </div>
              </td>
            </tr>
            <tr>
              <td style='padding-bottom: 30px;'>
                <p style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 13px; color: #777777; margin: 0;'>
                  Válido por 10 min. Si no has sido tú, ignora este mensaje.
                </p>
              </td>
            </tr>
            <tr>
              <td style='padding-top: 15px;'>
                <p style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 10px; color: #999999; text-transform: uppercase; letter-spacing: 1px; margin: 0;'>
                  Seguridad de Cuenta &bull; " . date('Y') . "
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>";
}