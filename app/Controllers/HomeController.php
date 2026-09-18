<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Nivel;
use App\Models\Progreso;
use App\Models\GamificacionConfig;

/**
 * HomeController.php
 * Controlador de la página principal (Home) del Aprendiz.
 * Gestiona la carga del mapa de niveles gamificado y el progreso del alumno.
 */
class HomeController extends Controller {

    private User $userModel;
    private Nivel $nivelModel;
    private Progreso $progresoModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->nivelModel = new Nivel();
        $this->progresoModel = new Progreso();
        iniciarSesion();
    }

    /**
     * Carga y muestra el mapa de progreso del Aprendiz.
     */
    public function index(): void {
        $autenticado = estaAutenticado();
        $usuario = null;
        $progreso = [];
        $mapaProgreso = [];
        $racha = 0;
        $rango = $this->userModel->calcularRangoClinico(0);
        $insigniasGanadas = [];
        $totalInsignias = 0;
        $leaderboard = [];
        $inicioSemana = '';

        if ($autenticado) {
            $rol = obtenerRolSesion();
            // Redirigir a sus respectivos paneles si no es aprendiz
            if ($rol === 'admin') {
                $this->redirect('admin');
                return;
            } elseif ($rol === 'instructor') {
                $this->redirect('instructor');
                return;
            }

            $uid = $_SESSION['usuario_id'];
            $usuario = $this->userModel->obtenerPorId($uid);
            $progreso = $this->progresoModel->obtenerProgresoPorUsuario($uid);

            // Indexar progreso por rap_id para búsqueda rápida
            foreach ($progreso as $p) {
                $mapaProgreso[$p['rap_id']] = $p;
            }

            // Panel lateral: racha, rango, insignias y ranking de la semana. Antes
            // mostraba cifras fijas de una maqueta (vidas, gemas, "ligas" bloqueadas)
            // que no correspondían a ninguna funcionalidad del sistema.
            $racha           = $this->userModel->calcularRachaDias($uid);
            $xpPorNivel      = (new GamificacionConfig())->obtenerValor('xp_por_nivel', 500);
            $rango           = $this->userModel->calcularRangoClinico((int) ($usuario['xp_puntos'] ?? 0), $xpPorNivel);
            $insigniasGanadas = $this->userModel->obtenerInsigniasGanadas($uid);
            $totalInsignias   = count($this->userModel->obtenerTodasInsignias());
            $leaderboard      = $this->userModel->obtenerLeaderboardSemanal($usuario['programa_id'] ?? null);
            $inicioSemana     = $this->userModel->obtenerInicioSemana();
        }

        // Obtener todos los niveles y RAPs activos
        $niveles = $this->nivelModel->obtenerNivelesConRaps();

        $this->render('home', [
            'autenticado' => $autenticado,
            'usuario' => $usuario,
            'progreso' => $progreso,
            'mapaProgreso' => $mapaProgreso,
            'niveles' => $niveles,
            'racha' => $racha,
            'rango' => $rango,
            'insigniasGanadas' => $insigniasGanadas,
            'totalInsignias' => $totalInsignias,
            'leaderboard' => $leaderboard,
            'inicioSemana' => $inicioSemana
        ]);
    }
}
