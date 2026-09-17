<?php
require __DIR__ . '/../includes/solo_cli.php';
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../includes/funciones.php';
if (($_ENV['APP_ENV'] ?? '')!=='local' || DB_HOST!=='127.0.0.1' || DB_PUERTO!==3308) {
    fwrite(STDERR, "Solo entorno aislado local.\n"); exit(1);
}
$pdo=obtenerConexion();
$stmt=$pdo->query("SELECT COUNT(*) FROM usuarios WHERE correo IN ('aprendiz@smashcode.test','instructor@smashcode.test','admin@smashcode.test')");
if ((int)$stmt->fetchColumn()>0) exit("Las cuentas locales ya existen; no se cambian sus contraseñas.\n");
$program=$pdo->query('SELECT id FROM programa_formacion WHERE activo=1 LIMIT 1')->fetchColumn();
$password='SmashLocal'.bin2hex(random_bytes(5)).'7';
$pdo->beginTransaction();
foreach(['aprendiz','instructor','admin'] as $role) {
    $stmt=$pdo->prepare('INSERT INTO usuarios (id,nombre_completo,correo,contrasena,rol,programa_id,ficha_sena,correo_verificado) VALUES (?,?,?,?,?,?,?,1)');
    $stmt->execute([generarUUID(),'Prueba '.ucfirst($role),$role.'@smashcode.test',password_hash($password,PASSWORD_BCRYPT),$role,$program,'LOCAL']);
}
$pdo->commit();
$dir=dirname(__DIR__).'/.local'; if(!is_dir($dir)) mkdir($dir,0775,true);
file_put_contents($dir.'/local-users.json',json_encode(['password'=>$password,'correos'=>['aprendiz@smashcode.test','instructor@smashcode.test','admin@smashcode.test']],JSON_PRETTY_PRINT));
echo "Cuentas de prueba creadas. Credenciales en .local/local-users.json (ignorado por Git).\n";
