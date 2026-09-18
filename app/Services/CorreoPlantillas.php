<?php
namespace App\Services;

class CorreoPlantillas {
    private static function escapar(string $valor): string {
        return htmlspecialchars(html_entity_decode($valor,ENT_QUOTES,'UTF-8'),ENT_QUOTES,'UTF-8');
    }

    public static function urlAplicacion(string $ruta): string {
        $base=trim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ''));
        if ($base!=='') {
            $url=parse_url($base);
            if (!$url || !in_array($url['scheme'] ?? '',['http','https'],true) || empty($url['host']) || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])) throw new \RuntimeException('APP_URL debe ser una URL HTTP o HTTPS válida.');
            return rtrim($base,'/').'/'.ltrim($ruta,'/');
        }
        $host=$_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!preg_match('/^(?:[a-z0-9.-]+|\[::1\])(?::\d+)?$/i',$host)) throw new \RuntimeException('Host no válido para correo.');
        $proxies=array_filter(array_map('trim',explode(',',(string)($_ENV['TRUSTED_PROXIES'] ?? ''))));
        $proxySeguro=in_array($_SERVER['REMOTE_ADDR'] ?? '',$proxies,true) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')==='https';
        $https=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || $proxySeguro || filter_var($_ENV['SESSION_COOKIE_SECURE'] ?? 'false',FILTER_VALIDATE_BOOLEAN);
        return ($https?'https://':'http://').$host.PROYECTO_PATH.'/'.ltrim($ruta,'/');
    }

    public static function bienvenida(string $nombre,string $url): array {
        return self::plantilla('Tu cuenta en SmashCode está lista','Bienvenido a SmashCode',$nombre,
            '<p>Tu cuenta de aprendiz ya está activa. Empieza a practicar inglés para comunicarte con más seguridad en situaciones clínicas.</p>'
            .'<ol><li>Inicia sesión con el correo y la contraseña que registraste.</li><li>Abre el primer módulo y completa sus actividades en orden.</li><li>Consulta tu avance y tus logros desde tu perfil.</li></ol>',
            'Iniciar sesión',$url,'Si no creaste esta cuenta, ignora este correo.');
    }

    public static function recuperacion(string $nombre,string $url): array {
        return self::plantilla('Restablece tu contraseña de SmashCode','Crea una nueva contraseña',$nombre,
            '<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Usa el botón para elegir una nueva.</p>'
            .'<p><strong>El enlace vence en 24 horas y solo se puede usar una vez.</strong></p>'
            .'<p>Después podrás iniciar sesión con tu correo y la nueva contraseña.</p>',
            'Crear nueva contraseña',$url,'Si no solicitaste este cambio, ignora el mensaje. Tu contraseña actual se conserva.');
    }

    public static function credenciales(string $nombre,string $correo,string $rol,string $clave,string $programa,string $url,string $ficha=''): array {
        $perfil=$rol==='instructor'?'instructor':'aprendiz';
        $contenido='<p>El administrador generó una clave temporal para tu cuenta de <strong>'.self::escapar($perfil).'</strong>.</p>'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f0;border-radius:12px;margin:20px 0"><tr><td style="padding:18px;line-height:1.7">Correo<br><strong>'.self::escapar($correo).'</strong><br><br>Clave temporal<br><code style="font-size:19px;font-weight:700;letter-spacing:.5px">'.self::escapar($clave).'</code></td></tr></table>'
            .($ficha!==''?'<p>Ficha SENA: <strong>'.self::escapar($ficha).'</strong>.</p>':'')
            .($programa!==''?'<p>Programa: <strong>'.self::escapar($programa).'</strong>.</p>':'')
            .'<ol><li>Inicia sesión con estas credenciales.</li><li>Cambia la clave temporal cuando la plataforma te lo solicite.</li><li>Accede a tu panel de '.$perfil.'.</li></ol>';
        return self::plantilla('Tu acceso temporal a SmashCode','Accede a tu cuenta',$nombre,$contenido,
            'Ir a iniciar sesión',$url,'No compartas tu contraseña. Si no esperabas este acceso, contacta al administrador de tu programa.');
    }

    public static function bloqueo(string $nombre,string $url): array {
        return self::plantilla('Aviso de seguridad de tu cuenta SmashCode','Recupera el acceso a tu cuenta',$nombre,
            '<p>Bloqueamos temporalmente el acceso después de varios intentos de inicio de sesión con una contraseña incorrecta.</p>'
            .'<p>Si fuiste tú, puedes recuperar el acceso creando una nueva contraseña.</p>',
            'Recuperar acceso',$url,'Si no reconoces estos intentos, restablece tu contraseña y contacta al administrador de tu programa.');
    }

    private static function plantilla(string $asunto,string $titulo,string $nombre,string $contenido,string $accion,string $url,string $nota): array {
        if (!filter_var($url,FILTER_VALIDATE_URL) || !in_array(parse_url($url,PHP_URL_SCHEME),['http','https'],true)) throw new \RuntimeException('Enlace de correo no válido.');
        $titulo=self::escapar($titulo);$nombre=self::escapar($nombre);$accion=self::escapar($accion);$url=self::escapar($url);$nota=self::escapar($nota);
        $html='<!doctype html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$titulo.'</title></head>'
            .'<body style="margin:0;padding:0;background:#f4f6f3;color:#24302b;font-family:Arial,Helvetica,sans-serif">'
            .'<div style="display:none;font-size:1px;color:#f4f6f3;max-height:0;overflow:hidden">'.$titulo.'. '.$accion.' de forma segura.</div>'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:32px 16px">'
            .'<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#fff;border:1px solid #e0e6dd;border-radius:16px">'
            .'<tr><td style="padding:28px 32px 22px;border-bottom:1px solid #e5ebe2"><span style="font-size:21px;font-weight:800;letter-spacing:.6px;color:#286a22">SMASHCODE</span><br><span style="font-size:12px;color:#66766a">Inglés para la comunicación clínica</span></td></tr>'
            .'<tr><td style="padding:30px 32px;font-size:15px;line-height:1.7"><h1 style="margin:0 0 22px;font-size:27px;line-height:1.25;color:#203128">'.$titulo.'</h1><p>Hola, '.$nombre.'.</p>'.$contenido
            .'<table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0"><tr><td bgcolor="#286a22" style="border-radius:8px"><a href="'.$url.'" style="display:inline-block;padding:14px 22px;color:#fff;text-decoration:none;font-size:15px;font-weight:700">'.$accion.'</a></td></tr></table>'
            .'<p style="font-size:12px;color:#526457">Si el botón no abre, copia este enlace en tu navegador:<br><a href="'.$url.'" style="color:#286a22;word-break:break-all">'.$url.'</a></p>'
            .'<p style="margin-top:26px;padding-top:18px;border-top:1px solid #e5ebe2;font-size:13px;color:#526457">'.$nota.'</p></td></tr>'
            .'<tr><td style="padding:20px 32px;background:#f9faf8;border-radius:0 0 16px 16px;font-size:12px;line-height:1.6;color:#66766a">Este es un mensaje automático de SmashCode.<br>Si necesitas ayuda, contacta al administrador de tu programa.</td></tr></table></td></tr></table></body></html>';
        return ['asunto'=>$asunto,'html'=>$html];
    }
}
