<?php
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
$entrada=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
// Nunca se usan credenciales reales en esta prueba; SMTP siempre apunta a loopback.
$_ENV['APP_ENV']=$entrada['environment'] ?? 'local';
$_ENV['MAIL_ENABLED']=$entrada['enabled'] ?? 'true';
$_ENV['MAIL_TRANSPORT']='local';
$_ENV['SMTP_HOST']='127.0.0.1';
$_ENV['SMTP_PORT']=(string)($entrada['port'] ?? 1);
$_ENV['SMTP_USER']='remitente@smashcode.test';
$_ENV['SMTP_PASS']='';
$_ENV['SMTP_FROM_EMAIL']='';
$_ENV['SMTP_FROM_NAME']='SmashCode';
$_ENV['APP_URL']='https://smashcode.test/campus';
require_once dirname(__DIR__).'/config/credenciales.php';
require_once dirname(__DIR__).'/includes/funciones.php';
require_once dirname(__DIR__).'/includes/correo.php';
require_once dirname(__DIR__).'/app/Services/CorreoPlantillas.php';
use App\Services\CorreoPlantillas;

if (($entrada['action'] ?? '')==='fallback') {
    require_once dirname(__DIR__).'/app/Core/Autoloader.php';
    \App\Core\Autoloader::registrar();
    $_ENV['APP_URL']='javascript:invalido';$_SESSION=[];
    $auth=(new ReflectionClass(\App\Controllers\AuthController::class))->newInstanceWithoutConstructor();
    $welcome=(new ReflectionMethod($auth,'enviarCorreoBienvenida'))->invoke($auth,'destinatario@smashcode.test','Prueba');
    register_shutdown_function(static function() use($welcome) {
        echo json_encode(['bienvenida_fallida'=>$welcome===false,'alternativa_conservada'=>($_SESSION['credenciales_temporales']['clave'] ?? '')==='ClaveFicticia2026!']);
    });
    $admin=(new ReflectionClass(\App\Controllers\AdminController::class))->newInstanceWithoutConstructor();
    (new ReflectionMethod($admin,'entregarCredencialesTemporales'))->invoke($admin,'Prueba','destinatario@smashcode.test','aprendiz','','','ClaveFicticia2026!');
    exit;
}

$login=CorreoPlantillas::urlAplicacion('login');
$mensajes=[
    CorreoPlantillas::bienvenida('Cristian &amp; Melo',$login),
    CorreoPlantillas::recuperacion('Cuenta <script>ejemplo</script>',CorreoPlantillas::urlAplicacion('restablecer?token=seguro&ejemplo=1')),
    CorreoPlantillas::credenciales('Cuenta de prueba','cuenta@smashcode.test','instructor','A<&"2026!','Salud &amp; Bienestar',$login),
    CorreoPlantillas::bloqueo('Cuenta de prueba',CorreoPlantillas::urlAplicacion('recuperar'))
];
$resultado=['disponible'=>correoDisponible(),'enviados'=>[],'html'=>array_column($mensajes,'html')];
if (($entrada['action'] ?? '')==='send') foreach($mensajes as $mensaje) $resultado['enviados'][]=enviarCorreo('destinatario@smashcode.test',$mensaje['asunto'],$mensaje['html']);
$resultado['url']=$login;
$resultado['urls_invalidas']=[];
foreach(['javascript:alert(1)','https://usuario@smashcode.test','https://smashcode.test/?foo=1'] as $base) {
    $_ENV['APP_URL']=$base;
    try {CorreoPlantillas::urlAplicacion('login');$resultado['urls_invalidas'][]=false;} catch (RuntimeException $e) {$resultado['urls_invalidas'][]=true;}
}
unset($_ENV['APP_URL']);
$_SERVER['HTTP_HOST']='127.0.0.1:8097';$_SERVER['REMOTE_ADDR']='192.0.2.20';$_SERVER['HTTP_X_FORWARDED_PROTO']='https';
$_ENV['TRUSTED_PROXIES']='';$_ENV['SESSION_COOKIE_SECURE']='false';$_SERVER['HTTPS']='off';
$resultado['proxy_no_confiable']=CorreoPlantillas::urlAplicacion('login');
$_ENV['TRUSTED_PROXIES']='192.0.2.20';
$resultado['proxy_confiable']=CorreoPlantillas::urlAplicacion('login');
echo json_encode($resultado,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
