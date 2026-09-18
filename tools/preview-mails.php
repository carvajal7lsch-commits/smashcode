<?php
/** Vista previa y envío explícito de mensajes ficticios. Exclusivamente CLI/local. */
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/config/credenciales.php';
require_once dirname(__DIR__).'/includes/funciones.php';
require_once dirname(__DIR__).'/includes/correo.php';
require_once dirname(__DIR__).'/app/Services/CorreoPlantillas.php';

if (($_ENV['APP_ENV'] ?? '')!=='local') { fwrite(STDERR,"Este comando solo se permite en APP_ENV=local.\n"); exit(1); }
$opciones=getopt('', ['output:','send-to:']);
if (isset($opciones['send-to']) && !filter_var($opciones['send-to'],FILTER_VALIDATE_EMAIL)) { fwrite(STDERR,"Destinatario inválido.\n"); exit(1); }
use App\Services\CorreoPlantillas;
$login=CorreoPlantillas::urlAplicacion('login');
$mensajes=[
    'bienvenida'=>CorreoPlantillas::bienvenida('Cuenta de prueba',$login),
    'recuperacion'=>CorreoPlantillas::recuperacion('Cuenta de prueba',CorreoPlantillas::urlAplicacion('restablecer?token=ejemplo-no-valido')),
    'credenciales'=>CorreoPlantillas::credenciales('Cuenta de prueba','cuenta@smashcode.test','aprendiz','EjemploTemporal2026!','Programa de demostración',$login),
    'bloqueo'=>CorreoPlantillas::bloqueo('Cuenta de prueba',CorreoPlantillas::urlAplicacion('recuperar'))
];
$resultados=[];
foreach ($mensajes as $tipo=>$mensaje) {
    $aviso='<p style="margin:16px auto;padding:16px;max-width:568px;background:#fff4cf;font-family:Arial,sans-serif;color:#594400"><strong>VISTA PREVIA · DATOS FICTICIOS</strong><br>Este mensaje es una prueba de diseño. No se creó ninguna cuenta ni se cambió una contraseña. La clave y el enlace de recuperación son ejemplos sin validez.</p>';
    $html=preg_replace('/(<body\b[^>]*>)/i','$1'.$aviso,$mensaje['html'],1);
    if (isset($opciones['output'])) {
        $carpeta=rtrim($opciones['output'],'/\\');
        if (!is_dir($carpeta) && !mkdir($carpeta,0775,true)) throw new RuntimeException('No se pudo crear la carpeta.');
        file_put_contents($carpeta.'/correo-'.$tipo.'.html',$html);
    }
    $fila=['tipo'=>$tipo,'asunto'=>$mensaje['asunto']];
    // El aviso de bloqueo se previsualiza; no se envía una falsa alerta de seguridad.
    if (isset($opciones['send-to']) && $tipo!=='bloqueo') $fila['enviado']=enviarCorreo($opciones['send-to'],'[Prueba SmashCode] '.$mensaje['asunto'],$html);
    $resultados[]=$fila;
}
echo json_encode($resultados,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
foreach($resultados as $fila) if (isset($fila['enviado']) && !$fila['enviado']) exit(1);
