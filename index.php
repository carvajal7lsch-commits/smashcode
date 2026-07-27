<?php
/**
 * index.php — Front Controller / Entry Point principal del sistema SmashCode.
 * Inicializa el autoloader, carga las sesiones, configura las rutas y despacha las peticiones.
 */

// 1. Cargar dependencias de Composer (PHPMailer, JWT, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 2. Cargar utilidades procedimentales heredadas (funciones globales y manejo de sesión)
require_once __DIR__ . '/config/sesion.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';

// 3. Registrar el Autocargador PSR-4 del núcleo MVC
require_once __DIR__ . '/app/Core/Autoloader.php';
\App\Core\Autoloader::registrar();

// 4. Inicializar el enrutador
$app = new \App\Core\App();

// ==================== DEFINICIÓN DE RUTAS ====================

// --- Panel Principal (Aprendiz) ---
$app->get('/', 'HomeController@index');
$app->get('/aprendiz/rap', 'AprendizController@rap');
$app->post('/aprendiz/rap/marcar-vocabulario', 'AprendizController@toggleVocabMarcado');
$app->post('/aprendiz/rap/guardar-progreso', 'AprendizController@guardarProgreso');
$app->post('/aprendiz/rap/guardar-quiz', 'AprendizController@guardarIntentoQuiz');
$app->get('/aprendiz/vocabulario', 'AprendizController@vocabulario');
$app->get('/aprendiz/dialogos', 'AprendizController@dialogos');
$app->get('/aprendiz/ejercicios', 'AprendizController@ejercicios');
$app->get('/aprendiz/glosario', 'AprendizController@glosario');
$app->get('/aprendiz/perfil', 'AprendizController@perfil');
$app->post('/aprendiz/perfil/actualizar', 'AprendizController@actualizarPerfil');

// --- Autenticación y Registro ---
$app->get('/login', 'AuthController@showLogin');
$app->post('/login/ingresar', 'AuthController@ingresar');
$app->post('/login/registrar', 'AuthController@registrar');
$app->get('/logout', 'AuthController@logout');

// --- Recuperación de Contraseñas ---
$app->get('/recuperar', 'AuthController@showRecuperar');
$app->post('/recuperar/enviar', 'AuthController@enviarEnlace');
$app->get('/restablecer', 'AuthController@showRestablecer');
$app->post('/restablecer/guardar', 'AuthController@guardarClave');

// --- Panel de Administración ---
$app->get('/admin', 'AdminController@index');

// --- Panel de Instructor ---
$app->get('/instructor', 'InstructorController@index');

// --- Módulo de Niveles (HU10) — Admin ---
$app->get('/admin/niveles',              'AdminController@niveles');
$app->get('/admin/niveles/editar',       'AdminController@editarNivel');
$app->post('/admin/niveles/actualizar',  'AdminController@actualizarNivel');
$app->post('/admin/niveles/toggle',      'AdminController@toggleNivel');

// --- Gestión de RAPs (HU03) ---
$app->get('/admin/raps',                 'AdminController@raps');
$app->post('/admin/raps/toggle',         'AdminController@toggleRap');
$app->get('/instructor/raps',            'InstructorController@raps');

// --- Gestión de Catálogos (HU18) ---
$app->get('/admin/catalogos',            'CatalogosController@index');
$app->post('/admin/catalogos/guardar',   'CatalogosController@guardar');
$app->post('/admin/catalogos/toggle',    'CatalogosController@toggle');

// --- Gestión de Vocabulario (HU19) ---
$app->get('/admin/vocabulario',          'VocabularioController@index');
$app->get('/admin/vocabulario/crear',    'VocabularioController@crear');
$app->post('/admin/vocabulario/guardar', 'VocabularioController@guardar');
$app->get('/admin/vocabulario/editar',   'VocabularioController@editar');
$app->post('/admin/vocabulario/actualizar', 'VocabularioController@actualizar');
$app->post('/admin/vocabulario/toggle',  'VocabularioController@toggle');
$app->post('/admin/vocabulario/sugerir', 'VocabularioController@sugerir');

// --- Módulo de Niveles (HU10) — Instructor ---
$app->get('/instructor/niveles',             'InstructorController@niveles');

// --- Módulo de Aprendices (HU23) — Instructor ---
$app->get('/instructor/aprendices',          'InstructorController@aprendices');

// --- Gestión de Usuarios (HU04) ---
$app->get('/admin/usuarios', 'AdminController@usuarios');
$app->get('/admin/usuarios/crear', 'AdminController@crearUsuario');
$app->post('/admin/usuarios/guardar', 'AdminController@guardarUsuario');
$app->get('/admin/usuarios/editar', 'AdminController@editarUsuario');
$app->post('/admin/usuarios/actualizar', 'AdminController@actualizarUsuario');
$app->post('/admin/usuarios/suspender', 'AdminController@suspenderUsuario');
$app->post('/admin/usuarios/eliminar', 'AdminController@eliminarUsuario');
$app->get('/admin/usuarios/actividad', 'AdminController@actividadUsuario');

// --- Alta de Instructores (HU09) ---
$app->get('/admin/usuarios/instructor', 'AdminController@crearInstructor');
$app->post('/admin/usuarios/instructor/guardar', 'AdminController@guardarInstructor');

// --- Cambio de Contraseña Forzado (primer login instructor) ---
$app->get('/cambiar-clave', 'AuthController@showCambiarClave');
$app->post('/cambiar-clave/guardar', 'AuthController@guardarCambiarClave');

// --- Programas de Formación (HU17) ---
$app->get('/admin/programas',                'AdminController@programas');
$app->get('/admin/programas/crear',          'AdminController@crearPrograma');
$app->post('/admin/programas/guardar',       'AdminController@guardarPrograma');
$app->get('/admin/programas/editar',         'AdminController@editarPrograma');
$app->post('/admin/programas/actualizar',    'AdminController@actualizarPrograma');
$app->post('/admin/programas/toggle',        'AdminController@togglePrograma');
$app->post('/admin/programas/eliminar',      'AdminController@eliminarPrograma');

// ==============================================================

// 5. Ejecutar la aplicación
$app->run();