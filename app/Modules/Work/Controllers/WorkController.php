<?php

namespace Phast\App\Modules\Work\Controllers;

use Phast\App\Controllers\BaseController;
use Phast\System\Auth\AuthManager;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Database\DB; // Para cargar catálogos
use Phast\App\Modules\Work\Models\Entities\ExtensionWork;
use \Phast\System\Rendering\Core\ViewData;
class WorkController {
    protected ViewData $dataView;
    public function __construct(protected AuthManager $auth) {

    }
    public function createAction(Request $request, Response $response) {
        // Cargamos los catálogos necesarios para los <select> del formulario
        $workTypes = DB::table('work_types')->get();
        $organizationalUnits = DB::table('organizational_units')->get();

        $viewData = new ViewData(
            pageTitle: 'Registrar Nuevo Trabajo de Extensión',
            extra: [
                'workTypes' => $workTypes,
                'units' => $organizationalUnits
            ]
        );

        return $response->view('work/create', $viewData);
    }


    public function storeAction(Request $request, Response $response) {
        // --- Validación de datos comunes a todos los tipos de trabajo ---
        $validatedCommonData = $request->validate([
            'title' => 'required|string|max:255',
            'work_type_id' => 'required|numeric|exists:work_types,id',
            'organizational_unit_id' => 'required|numeric|exists:organizational_units,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $userId = $this->auth->id();
        // Estado inicial "Borrador". Asumimos que ID=1 es 'Borrador' en la tabla work_statuses
        $initialStatusId = 1;

        // Creamos el registro principal en `extension_works`
        $work = ExtensionWork::create([
            'title' => $validatedCommonData['title'],
            'work_type_id' => $validatedCommonData['work_type_id'],
            'organizational_unit_id' => $validatedCommonData['organizational_unit_id'],
            'start_date' => $validatedCommonData['start_date'],
            'end_date' => $validatedCommonData['end_date'],
            'primary_responsible_user_id' => $userId,
            'current_status_id' => $initialStatusId,
            'is_draft' => 1,
        ]);

        // --- Lógica para guardar los detalles específicos del tipo de trabajo ---
        // Aquí es donde manejamos la lógica dinámica
        $this->storeWorkDetails($request, $work->id, $work->work_type_id);

        // Redirigir a la página de edición del trabajo recién creado
        // o a la lista de trabajos.
        return $response->redirect(route('work.edit', ['id' => $work->id])) // Asumimos que existirá una ruta de edición
            ->withSuccess('El trabajo ha sido creado como borrador. Ahora puedes añadir más detalles y evidencias.');

    }

    private function storeWorkDetails(Request $request, int $workId, int $workTypeId): void {
        // Asumimos que los IDs en la tabla `work_types` corresponden a:
        // 1: Proyecto, 2: Actividad, 3: Publicación, 4: Asistencia Técnica

        switch ($workTypeId) {
            case 1: // Proyecto de Extensión
                $details = $request->validate([
                    'project_category' => 'required|string',
                    'general_description' => 'required|string',
                    // ... más validaciones para los campos de proyecto
                ]);
                DB::table('project_details')->insert(['extension_work_id' => $workId] + $details);
                break;

            case 2: // Actividad de Extensión
                $details = $request->validate([
                    'introduction' => 'required|string',
                    // ... más validaciones para los campos de actividad
                ]);
                DB::table('activity_details')->insert(['extension_work_id' => $workId] + $details);
                break;
            case 3: // Publicación
                $details = $request->validate([
                    'publication_title' => 'required|string',
                    'publication_type' => 'required|string',
                    'publication_date' => 'nullable|date',
                    // ... más validaciones para los campos de publicación
                ]);
                DB::table('publication_details')->insert(['extension_work_id' => $workId] + $details);
            case 4: // Asistencia Técnica
                $details = $request->validate([
                    'service_description' => 'required|string',
                    // ... más validaciones para los campos de asistencia técnica
                ]);
                DB::table('technical_assistance_details')->insert(['extension_work_id' => $workId] + $details);
                break;
            default:
                throw new \InvalidArgumentException('Tipo de trabajo no soportado: ' . $workTypeId);

            // ... casos para Publicación y Asistencia Técnica
        }
    }
}