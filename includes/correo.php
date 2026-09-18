<?php
/** Envío SMTP con contenido HTML y alternativa legible en texto plano. */
require_once __DIR__.'/../vendor/autoload.php';
if (!defined('SMTP_HOST')) require_once __DIR__.'/../config/credenciales.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** Disponibilidad global: la respuesta no depende de que exista una cuenta. */
function correoDisponible(): bool {
    $enabled=$_ENV['MAIL_ENABLED'] ?? getenv('MAIL_ENABLED');
    if ($enabled!==false && $enabled!==null && $enabled!=='' && !filter_var($enabled,FILTER_VALIDATE_BOOLEAN)) return false;
    if (($_ENV['MAIL_TRANSPORT'] ?? '')==='local') return ($_ENV['APP_ENV'] ?? '')==='local' && in_array(SMTP_HOST,['127.0.0.1','localhost'],true);
    return SMTP_USER!=='' && SMTP_PASS!=='';
}

function textoCorreo(string $html): string {
    $html=preg_replace_callback('/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is',static function($m) {
        $etiqueta=trim(strip_tags($m[2]));$enlace=html_entity_decode($m[1],ENT_QUOTES,'UTF-8');
        return html_entity_decode($etiqueta,ENT_QUOTES,'UTF-8')===$enlace ? $enlace : $etiqueta.' ('.$enlace.')';
    },$html);
    $html=preg_replace('/<head\b[^>]*>.*?<\/head>|<div[^>]*display:none[^>]*>.*?<\/div>/is','',$html);
    $html=preg_replace('/<br\s*\/?>/i',"\n",$html);
    $html=preg_replace('/<li\b[^>]*>/i',"\n- ",$html);
    $html=preg_replace('/<\/(?:p|h[1-6]|tr|div|ol|ul)>/i',"\n\n",$html);
    return trim(preg_replace('/\n{3,}/',"\n\n",html_entity_decode(strip_tags($html),ENT_QUOTES,'UTF-8')));
}

function enviarCorreo(string $destinatario,string $asunto,string $cuerpo): bool {
    $enabled=$_ENV['MAIL_ENABLED'] ?? getenv('MAIL_ENABLED');
    if ($enabled!==false && $enabled!==null && $enabled!=='' && !filter_var($enabled,FILTER_VALIDATE_BOOLEAN)) return false;
    $mail=new PHPMailer(true);
    try {
        $mail->isSMTP();$mail->Host=SMTP_HOST;$mail->Port=(int)SMTP_PORT;
        if (($_ENV['MAIL_TRANSPORT'] ?? '')==='local') {
            if (($_ENV['APP_ENV'] ?? '')!=='local' || !in_array(SMTP_HOST,['127.0.0.1','localhost'],true)) throw new Exception('El transporte local requiere SMTP de loopback y APP_ENV=local.');
            $mail->SMTPAuth=false;$mail->SMTPSecure='';$mail->SMTPAutoTLS=false;
        } else {
            if (SMTP_USER==='' || SMTP_PASS==='') throw new Exception('Faltan credenciales SMTP.');
            $mail->SMTPAuth=true;$mail->Username=SMTP_USER;$mail->Password=SMTP_PASS;
            $mail->SMTPSecure=(int)SMTP_PORT===465?PHPMailer::ENCRYPTION_SMTPS:PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->CharSet=PHPMailer::CHARSET_UTF8;$mail->Timeout=10;
        $from=trim((string)($_ENV['SMTP_FROM_EMAIL'] ?? ''));
        if ($from==='') $from=SMTP_USER;
        if ($from==='') $from='no-reply@smashcode.test';
        $mail->setFrom($from,(string)($_ENV['SMTP_FROM_NAME'] ?? 'SmashCode'));
        $mail->addAddress($destinatario);$mail->isHTML(true);$mail->Subject=$asunto;$mail->Body=$cuerpo;$mail->AltBody=textoCorreo($cuerpo);
        $mail->send();return true;
    } catch (Exception $e) {
        error_log('[Correo] No se pudo completar el envío SMTP: '.$e->getMessage());return false;
    }
}
