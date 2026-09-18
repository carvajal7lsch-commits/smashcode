<?php
require __DIR__ . '/../includes/solo_cli.php';
require __DIR__ . '/../config/bootstrap.php';
if (($_ENV['APP_ENV'] ?? '')!=='local' || DB_HOST!=='127.0.0.1' || DB_PUERTO!==(int)($argv[2] ?? 3308)) exit(1);
$pdo=new PDO('mysql:host='.DB_HOST.';port='.DB_PUERTO,DB_USUARIO,DB_CLAVE);
$actual=str_replace('\\','/',(string)$pdo->query('SELECT @@datadir')->fetchColumn());
$expected=str_replace('\\','/',realpath($argv[1] ?? '') ?: '');
if ($expected==='' || strcasecmp(rtrim($actual,'/'),rtrim($expected,'/'))!==0) {
    fwrite(STDERR,"El puerto 3308 pertenece a otra carpeta de datos.\n"); exit(1);
}
echo "Instancia MySQL local verificada.\n";
