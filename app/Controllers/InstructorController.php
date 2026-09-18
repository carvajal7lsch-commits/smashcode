<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Instructor;
use App\Models\Nivel;
use App\Models\User;

/**
 * InstructorController.php
 * Controlador para las vistas y reportes del Instructor en SmashCode.
 * Incluye gestión de Niveles (HU10).
 */
class InstructorController extends Controller {

    private Instructor $instructorModel;
    private Nivel $nivelModel;

    /** Programa del instructor: define quiénes son "sus" aprendices (HU23). */
    private ?string $programaId = null;
    private ?string $programaNombre = null;

    public function __construct() {
        parent::__construct();
        $this->instructorModel = new Instructor();
        $this->nivelModel      = new Nivel();
        iniciarSesion();

        // Verificar rol instructor obligatoriamente
        if (!estaAutenticado() || obtenerRolSesion() !== 'instructor') {
            $this->redirect('login');
        }

        // HU23: "mis aprendices" son los del programa que el administrador le
        // asignó al instructor (HU09/HU17). Se lee en cada petición y no de la
        // sesión, para que un cambio de programa se vea sin volver a iniciar sesión.
        $instructor = (new User())->obtenerPorId($_SESSION['usuario_id']);
        $this->programaId     = !empty($instructor['programa_id']) ? $instructor['programa_id'] : null;
        $this->programaNombre = $instructor['programa_nombre'] ?? null;
    }

    /**
     * Lee los filtros de la URL y descarta lo que no tenga la forma esperada:
     * los ids deben ser UUID y el estado, uno de los permitidos en esa pantalla.
     * Un valor inválido se trata como "sin filtro" en lugar de romper la consulta.
     */
    private function leerFiltros(array $estadosPermitidos): array {
        $esUuid = static fn($valor): bool => is_string($valor)
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $valor) === 1;

        $nivel  = $_GET['nivel_id'] ?? '';
        $rap    = $_GET['rap_id'] ?? '';
        $estado = $_GET['estado'] ?? '';

        return [
            $esUuid($nivel) ? $nivel : '',
            $esUuid($rap) ? $rap : '',
            in_array($estado, $estadosPermitidos, true) ? $estado : ''
        ];
    }

    /**
     * Datos que las vistas usan para decirle al instructor a quién está viendo.
     */
    private function datosAlcance(): array {
        return [
            'programaNombre' => $this->programaNombre,
            'sinPrograma'    => $this->programaId === null
        ];
    }

    /**
     * Muestra el dashboard del instructor con el listado y progreso de sus alumnos.
     */
    public function index(): void {
        $this->render('instructor/dashboard', [
            'totalAprendices' => $this->instructorModel->obtenerTotalAprendices($this->programaId),
            'completaronAlgo' => $this->instructorModel->obtenerCompletaronAlgo($this->programaId),
            'promedioQuiz'    => $this->instructorModel->obtenerPromedioQuiz($this->programaId),
            'aprendices'      => $this->instructorModel->obtenerListadoAprendices($this->programaId)
        ] + $this->datosAlcance());
    }

    /**
     * Muestra el panel "Mis Aprendices" con opciones de filtrado (HU23).
     */
    public function aprendices(): void {
        [$nivel_id, $rap_id, $estado] = $this->leerFiltros(Instructor::ESTADOS_APRENDIZ);

        // Obtenemos niveles y raps para los dropdowns
        $nivelesConRaps = $this->nivelModel->obtenerNivelesConRaps();

        // Obtenemos los aprendices filtrados
        $aprendices = $this->instructorModel->obtenerListadoAprendicesFiltrado($nivel_id, $rap_id, $estado, $this->programaId);

        // HU06/HU23: avance de cada aprendiz modulo a modulo y RAP a RAP, para
        // resolver las columnas de la tabla sin una consulta por fila
        $avanceModulos = $this->instructorModel->obtenerAvancePorModuloDeAprendices($this->programaId);
        $avanceRaps    = $this->instructorModel->obtenerAvancePorRapDeAprendices($this->programaId);

        $this->render('instructor/aprendices', [
            'nivelesConRaps' => $nivelesConRaps,
            'aprendices'     => $aprendices,
            'avanceModulos'  => $avanceModulos,
            'avanceRaps'     => $avanceRaps,
            'filtroNivel'    => $nivel_id,
            'filtroRap'      => $rap_id,
            'filtroEstado'   => $estado
        ] + $this->datosAlcance());
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
            'SELECT r.id, r.titulo, r.orden AS rap_orden, r.activo AS rap_activo, n.nombre AS nivel_nombre, n.orden AS nivel_orden,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1) AS total_vocabulario,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1 AND (v.transcripcion_ipa IS NOT NULL AND v.transcripcion_ipa <> "")) AS total_pronunciacion,
                    (SELECT COUNT(*) FROM ejercicio e WHERE e.rap_id = r.id AND e.activo = 1) AS total_ejercicios,
                    (SELECT COUNT(*) FROM dialogo d WHERE d.rap_id = r.id AND d.activo = 1) AS total_dialogos,
                    (SELECT COUNT(*) FROM quiz q WHERE q.rap_id = r.id AND q.activo = 1) AS tiene_quiz,
                    (SELECT COUNT(p.id) FROM quiz q JOIN pregunta p ON p.quiz_id = q.id AND p.activo = 1 WHERE q.rap_id = r.id AND q.activo = 1) AS total_preguntas_quiz
             FROM rap r
             JOIN nivel n ON n.id = r.nivel_id
             WHERE n.orden <= 4
             ORDER BY n.orden, r.orden, r.activo DESC, r.titulo'
        );
        $raps = $stmt->fetchAll();
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error']  ?? '');

        $this->render('instructor/raps', compact('raps', 'exito', 'error'));
    }
    /* ========================================================
     * HU06 / HU23 — Resultados de quizzes y exportación CSV
     * ======================================================== */

    /**
     * Muestra los resultados de quizzes del grupo y los ejercicios que más
     * se fallan, con los mismos filtros de nivel/RAP/estado del panel de
     * aprendices. Es una vista de solo lectura: el instructor no edita
     * contenido ni cuentas desde aquí.
     */
    public function resultados(): void {
        [$nivel_id, $rap_id, $estado] = $this->leerFiltros(Instructor::ESTADOS_RESULTADO);

        $nivelesConRaps  = $this->nivelModel->obtenerNivelesConRaps();
        $resultados      = $this->instructorModel->obtenerResultadosQuiz($nivel_id, $rap_id, $estado, $this->programaId);
        $ejerciciosError = $this->instructorModel->obtenerEjerciciosConMasErrores($nivel_id, $rap_id, 10, $this->programaId);

        $this->render('instructor/resultados', [
            'nivelesConRaps'  => $nivelesConRaps,
            'resultados'      => $resultados,
            'ejerciciosError' => $ejerciciosError,
            'filtroNivel'     => $nivel_id,
            'filtroRap'       => $rap_id,
            'filtroEstado'    => $estado
        ] + $this->datosAlcance());
    }

    /**
     * Neutraliza el texto que Excel interpretaría como fórmula (inyección CSV).
     * Nombres y respuestas los escriben los aprendices: un nombre como
     * "=HYPERLINK(...)" se ejecutaría al abrir el reporte en el equipo del instructor.
     */
    private function celdaCsv($valor): string {
        $texto = (string) $valor;

        if ($texto !== '' && strpbrk($texto[0], "=+-@\t\r") !== false) {
            return "'" . $texto;
        }

        return $texto;
    }

    /**
     * Descarga el reporte CSV de resultados de quizzes (HU06/HU23).
     * Respeta los filtros activos, así que el archivo contiene exactamente
     * las mismas filas que el instructor tiene en pantalla.
     */
    public function exportar(): void {
        [$nivel_id, $rap_id, $estado] = $this->leerFiltros(Instructor::ESTADOS_RESULTADO);

        $resultados = $this->instructorModel->obtenerResultadosQuiz($nivel_id, $rap_id, $estado, $this->programaId);

        $nombreArchivo = 'resultados_quizzes_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $salida = fopen('php://output', 'w');

        // BOM UTF-8: sin esto Excel en Windows abre las tildes como caracteres sueltos
        fwrite($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($salida, [
            'ID Aprendiz',
            'Nombre',
            'Correo',
            'Ficha SENA',
            'Modulo',
            'Quiz',
            'Puntaje',
            'Puntaje minimo',
            'Aprobado',
            'Fecha',
            'Duracion (mm:ss)',
            'Numero de intento',
            'Detalle de respuestas'
        ], ';');

        foreach ($resultados as $r) {
            $segundos = (int) ($r['duracion_seg'] ?? 0);
            $duracion = sprintf('%02d:%02d', intdiv($segundos, 60), $segundos % 60);

            fputcsv($salida, [
                $this->celdaCsv($r['aprendiz_id']),
                $this->celdaCsv($r['nombre_completo']),
                $this->celdaCsv($r['correo']),
                $this->celdaCsv($r['ficha_sena'] ?? ''),
                $this->celdaCsv($r['modulo_nombre']),
                $this->celdaCsv('Quiz ' . $r['rap_titulo']),
                number_format((float) $r['puntaje'], 2, '.', ''),
                $r['puntaje_minimo'] === null ? '' : number_format((float) $r['puntaje_minimo'], 2, '.', ''),
                ((int) $r['aprobado'] === 1) ? 'Si' : 'No',
                $r['creado_en'],
                $duracion,
                $r['numero_intento'],
                $this->celdaCsv($r['detalle_respuestas'] ?? '')
            ], ';');
        }

        fclose($salida);
        exit;
    }
}
