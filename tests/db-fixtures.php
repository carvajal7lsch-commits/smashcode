<?php
// Helper exclusivo de pruebas locales. No se carga desde la aplicación.
require_once __DIR__ . '/../includes/solo_cli.php';
$root = $argv[1] ?? dirname(__DIR__);
require $root . '/config/conexion.php';
require $root . '/includes/funciones.php';
if (($_ENV['APP_ENV'] ?? '') !== 'local' || !in_array(DB_HOST,['127.0.0.1','localhost'],true)) {
    fwrite(STDERR,"Las pruebas de escritura exigen APP_ENV=local y una base local.\n"); exit(1);
}
$input=json_decode(stream_get_contents(STDIN),true,512,JSON_THROW_ON_ERROR);
$pdo=obtenerConexion();
if(($input['action'] ?? '')==='init') {
    $suffix=bin2hex(random_bytes(5)); $password='Regression'.$suffix.'A7';
    $program=$pdo->query('SELECT id FROM programa_formacion WHERE activo=1 LIMIT 1')->fetchColumn();
    $users=[];
    foreach(['aprendiz','instructor','admin','temporal','otroaprendiz'] as $name) {
        $role=$name==='temporal'?'instructor':($name==='otroaprendiz'?'aprendiz':$name);
        $id=generarUUID(); $email=$name.'.'.$suffix.'@smashcode.test';
        $stmt=$pdo->prepare('INSERT INTO usuarios (id,nombre_completo,correo,contrasena,rol,programa_id,ficha_sena,correo_verificado,debe_cambiar_clave) VALUES (?,?,?,?,?,?,?,1,?)');
        $stmt->execute([$id,'Regresión '.$name,$email,password_hash($password,PASSWORD_BCRYPT),$role,$program,'REGRESION',$name==='temporal'?1:0]);
        $users[$name]=['id'=>$id,'correo'=>$email,'rol'=>$role];
    }
    echo json_encode(['users'=>$users,'password'=>$password]);
} else {
    $stmt=$pdo->prepare($input['sql']); $stmt->execute($input['params'] ?? []);
    echo json_encode(['rows'=>$stmt->columnCount()?$stmt->fetchAll():[],'affected'=>$stmt->rowCount()]);
}
