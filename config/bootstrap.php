<?php
/** Carga común para web y CLI; credenciales.php es opcional y no se versiona. */
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once __DIR__.(is_file(__DIR__.'/credenciales.php')?'/credenciales.php':'/credenciales.example.php');
