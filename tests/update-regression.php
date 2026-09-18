<?php
/** Casos locales de activación. Usa una cuenta propia y nunca envía correo real. */
require_once __DIR__.'/../includes/solo_cli.php';
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../includes/funciones.php';
require_once __DIR__.'/../app/Core/Autoloader.php';
\App\Core\Autoloader::registrar();
if (($_ENV['APP_ENV'] ?? '')!=='local' || DB_HOST!=='127.0.0.1' || !in_array(DB_PUERTO,[3308,3309],true) || SMTP_USER!=='' || SMTP_PASS!=='') {fwrite(STDERR,'La prueba exige MySQL local y ninguna credencial SMTP.');exit(1);}
$pdo=obtenerConexion();
$real=rtrim(str_replace('\\','/',$pdo->query('SELECT @@datadir')->fetchColumn()),'/');
$expected=rtrim(str_replace('\\','/',realpath($_ENV['LOCAL_MYSQL_DATADIR'] ?? '') ?: ''),'/');
if ($expected==='' || strcasecmp($real,$expected)!==0) {fwrite(STDERR,'Instancia no verificada.');exit(1);}
$checks=[];
function verificar(string $nombre,bool $ok): void {global $checks;$checks[]=['name'=>$nombre,'passed'=>$ok];if(!$ok) throw new RuntimeException($nombre);}
function ejecutarSQL(PDO $pdo,string $sql): void {$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);try{$s=$pdo->query($sql);do{$s->fetchAll();}while($s->nextRowset());$s->closeCursor();}finally{$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);}}
$uid=generarUUID();$correo=$uid.'@smashcode.test';$model=new \App\Models\User();$created=false;
try {
    $pdo->prepare('INSERT INTO usuarios(id,nombre_completo,correo,contrasena,rol,activo,correo_verificado) VALUES (?,?,?,?,?,1,0)')->execute([$uid,'Prueba actualización',$correo,password_hash('UpdateFixture2026!',PASSWORD_BCRYPT),'aprendiz']);$created=true;
    $sql=file_get_contents(__DIR__.'/../database/migraciones/2026_09_17_activacion_cuenta.sql');
    ejecutarSQL($pdo,$sql);
    verificar('Repetir migración conserva la cuenta pendiente',!$model->obtenerParaActivacion($uid)['correo_verificado']);
    ejecutarSQL($pdo,$sql);
    verificar('Segunda repetición tampoco activa la cuenta',!$model->obtenerParaActivacion($uid)['correo_verificado']);
    $auth=(new ReflectionClass(\App\Controllers\AuthController::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($auth,'userModel'))->setValue($auth,$model);
    $send=new ReflectionMethod($auth,'enviarCorreoActivacion');
    $_ENV['APP_ENV']='production';$_ENV['MAIL_ENABLED']='true';
    verificar('Fallo SMTP de producción devuelve false',$send->invoke($auth,$uid,$correo,'Prueba')===false);
    verificar('Fallo SMTP no verifica el correo en producción',!$model->obtenerParaActivacion($uid)['correo_verificado']);
    $_ENV['APP_ENV']='local';$_ENV['MAIL_ENABLED']='true';
    $send->invoke($auth,$uid,$correo,'Prueba');
    verificar('Fallo SMTP real en local también conserva activación pendiente',!$model->obtenerParaActivacion($uid)['correo_verificado']);
    $_ENV['MAIL_ENABLED']='false';$send->invoke($auth,$uid,$correo,'Prueba');
    verificar('Demo local sin correo permite ingresar',(bool)$model->obtenerParaActivacion($uid)['correo_verificado']);
    $pdo->prepare('UPDATE usuarios SET correo_verificado=0 WHERE id=?')->execute([$uid]);
    $token=bin2hex(random_bytes(32));$vence=$pdo->query('SELECT DATE_ADD(NOW(),INTERVAL 24 HOUR)')->fetchColumn();
    $model->crearTokenActivacion($uid,$token,$vence);
    $s=$pdo->prepare('SELECT token FROM token_activacion WHERE usuario_id=? AND usado=0');$s->execute([$uid]);
    verificar('Token se guarda como hash SHA-256',$s->fetchColumn()===hash('sha256',$token));
    verificar('Token alterado se rechaza',$model->activarCuenta(bin2hex(random_bytes(32)))===false);
    verificar('Token con formato inválido se rechaza',$model->activarCuenta('falso')===false);
    verificar('Token válido activa la cuenta',$model->activarCuenta($token));
    verificar('Cuenta queda verificada',(bool)$model->obtenerParaActivacion($uid)['correo_verificado']);
    verificar('Token solo se utiliza una vez',$model->activarCuenta($token)===false);
    $pdo->prepare('UPDATE usuarios SET correo_verificado=0 WHERE id=?')->execute([$uid]);
    $token=bin2hex(random_bytes(32));$model->crearTokenActivacion($uid,$token,$pdo->query('SELECT DATE_SUB(NOW(),INTERVAL 1 MINUTE)')->fetchColumn());
    verificar('Token vencido se rechaza',$model->activarCuenta($token)===false);
    $token=bin2hex(random_bytes(32));$model->crearTokenActivacion($uid,$token,$vence);
    $pdo->prepare('UPDATE usuarios SET activo=0 WHERE id=?')->execute([$uid]);
    verificar('Cuenta suspendida no se activa',$model->activarCuenta($token)===false);
    $pdo->prepare('UPDATE usuarios SET activo=1,eliminado=1 WHERE id=?')->execute([$uid]);
    verificar('Cuenta eliminada no se activa',$model->activarCuenta($token)===false);
    $pdo->prepare('UPDATE usuarios SET eliminado=0,bloqueado=1 WHERE id=?')->execute([$uid]);
    verificar('Cuenta bloqueada no se activa',$model->activarCuenta($token)===false);
    $pdo->prepare('UPDATE usuarios SET bloqueado=0 WHERE id=?')->execute([$uid]);
    try {$model->crearTokenActivacion($uid,$token,$vence);throw new RuntimeException('Duplicado admitido');} catch(PDOException $e) {}
    $s=$pdo->prepare('SELECT usado FROM token_activacion WHERE token=?');$s->execute([hash('sha256',$token)]);
    verificar('Emisión fallida conserva el token anterior',(int)$s->fetchColumn()===0);
    verificar('Reenvío reciente exige espera',$model->puedeReenviarActivacion($uid)===false);
    $pdo->prepare('UPDATE token_activacion SET creado_en=DATE_SUB(NOW(),INTERVAL 61 SECOND) WHERE usuario_id=?')->execute([$uid]);
    verificar('Reenvío disponible después de un minuto',$model->puedeReenviarActivacion($uid));
    $legacy=bin2hex(random_bytes(32));$pdo->prepare('INSERT INTO token_activacion(id,usuario_id,token,expira_en) VALUES (?,?,?,?)')->execute([generarUUID(),$uid,$legacy,$vence]);
    verificar('Enlaces emitidos antes del arreglo siguen funcionando',$model->activarCuenta($legacy));
} finally {
    if($created)$pdo->prepare('DELETE FROM usuarios WHERE id=?')->execute([$uid]);
    $_ENV['APP_ENV']='local';
    echo json_encode($checks,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
}
