<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Instructor;
use App\Models\Nivel;

/**
 * InstructorController.php
 * Controlador para las vistas y reportes del Instructor en SmashCode.
 * Incluye gestión de Niveles (HU10).
 */
class InstructorController extends Controller {

    private Instructor $instructorModel;
    private Nivel $nivelModel;

    public function __construct() {
        parent::__construct();
        $this->instructorModel = new Instructor();
        $this->nivelModel      = new Nivel();
        iniciarSesion();

        // Verificar rol instructor obligatoriamente
        if (!estaAutenticado() || obtenerRolSesion() !== 'instructor') {
            $this->redirect('login');
        }
    }

    /**
     * Muestra el dashboard del instructor con el listado y progreso de sus alumnos.
     */
    public function index(): void {
        $totalAprendices = $this->instructorModel->obtenerTotalAprendices();
        $completaronAlgo = $this->instructorModel->obtenerCompletaronAlgo();
        $promedioQuiz    = $this->instructorModel->obtenerPromedioQuiz();
        $aprendices      = $this->instructorModel->obtenerListadoAprendices();

        $this->render('instructor/dashboard', [
            'totalAprendices' => $totalAprendices,
            'completaronAlgo' => $completaronAlgo,
            'promedioQuiz'    => $promedioQuiz,
            'aprendices'      => $aprendices
        ]);
    }

    /* ========================================================
     * HU10 — Gestión de Niveles (6 niveles fijos MCER)
     * ======================================================== */

    /**
     * Lista los 6 niveles con estadísticas de RAPs.
     */
    public function niveles(): void {
        $niveles = $this->nivelModel->obtenerTodos();
        $exito   = limpiar($_GET['exito'] ?? '');
        $error   = limpiar($_GET['error']  ?? '');

        $this->render('instructor/niveles', compact('niveles', 'exito', 'error'));
    }



    /**
     * Muestra el panel de RAPs para el Instructor (Read-Only) con el estado de sus 5 componentes (HU03).
     */
    public function raps(): void {
        $pdo = obtenerConexion();
        $stmt = $pdo->query(
            'SELECT r.id, r.titulo, r.activo AS rap_activo, n.nombre AS nivel_nombre, n.orden AS nivel_orden,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1) AS total_vocabulario,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1 AND (v.transcripcion_ipa IS NOT NULL AND v.transcripcion_ipa <> "")) AS total_pronunciacion,
                    (SELECT COUNT(*) FROM ejercicio e WHERE e.rap_id = r.id AND e.activo = 1) AS total_ejercicios,
                    (SELECT COUNT(*) FROM dialogo d WHERE d.rap_id = r.id AND d.activo = 1) AS total_dialogos,
                    (SELECT COUNT(*) FROM quiz q WHERE q.rap_id = r.id AND q.activo = 1) AS tiene_quiz,
                    (SELECT COUNT(p.id) FROM quiz q JOIN pregunta p ON p.quiz_id = q.id WHERE q.rap_id = r.id AND q.activo = 1) AS total_preguntas_quiz
             FROM rap r
             JOIN nivel n ON n.id = r.nivel_id
             ORDER BY n.orden, r.orden'
        );
        $raps = $stmt->fetchAll();
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error']  ?? '');

        $this->render('instructor/raps', compact('raps', 'exito', 'error'));
    }
}
