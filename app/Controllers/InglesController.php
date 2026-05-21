<?php
declare(strict_types=1);

namespace app\Controllers;

use app\Models\InglesModel;
use app\Core\Response;
use app\Core\Validator;
use PDOException;
use Throwable;

/**
 * Controlador del módulo Inglés.
 *
 * Regla principal:
 * - Todas las validaciones acumulan errores en $errors
 * - Se responde una sola vez con Response::validation()
 */
class InglesController extends BaseApiController
{
 
	public function search(): void
	{
		try {
			$ingles = new InglesModel();
			Response::raw($ingles->search());
		} catch (Throwable $e) {
			Response::serverError('Error al buscar palabras', [
				'detalle' => $e->getMessage()
			]);
		}
	}
    /**
     * Listado para DataTables.
     */
    public function listar(): void
    {
        try {
            $modelo = new InglesModel();
            $resultado = $modelo->datatable();

            Response::datatable($resultado);
        } catch (PDOException $e) {
            Response::serverError('Error de base de datos', [
                'detalle' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            Response::serverError('Error inesperado al listar', [
                'detalle' => $e->getMessage()
            ]);
        }
    }
/**
 * Registra una nueva palabra.
 *
 * Uso esperado:
 * - Petición API / AJAX
 * - Body JSON con los campos del formulario
 *
 * Qué valida:
 * - english y spanish obligatorios
 * - opposite_id entero positivo opcional
 * - pos y level dentro de valores permitidos
 * - longitudes máximas según columnas SQL
 *
 * Qué hace:
 * 1. Lee el JSON de entrada
 * 2. Valida y sanitiza los campos
 * 3. Si hay errores, responde 422 con Response::validation()
 * 4. Si todo está bien, construye el payload limpio
 * 5. Llama al modelo para guardar
 * 6. Responde success o serverError
 */
public function guardar(): void
{

    try {
        $in = $this->obtenerJsonInput();

        // Valores permitidos según esquema
        $POS_ALLOWED   = ['verb', 'phrasal_verb', 'noun', 'adjective', 'adverb', 'expression'];
        $LEVEL_ALLOWED = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        // Acumulador de errores
        $errors = [];

        // =========================
        // Campos requeridos
        // =========================
        $english = Validator::requireString($in['english'] ?? null, 'english', $errors);
        $spanish = Validator::requireString($in['spanish'] ?? null, 'spanish', $errors);

        // =========================
        // Campos opcionales
        // =========================
        $pronunciation = Validator::trimOrNull($in['pronunciation'] ?? null);
        $pos           = Validator::trimOrNull($in['pos'] ?? null);
        $level         = Validator::trimOrNull($in['level'] ?? null);
        $example_en    = Validator::trimOrNull($in['example_en'] ?? null);
        $example_es    = Validator::trimOrNull($in['example_es'] ?? null);
        $notes         = Validator::trimOrNull($in['notes'] ?? null);
        $source        = Validator::trimOrNull($in['source'] ?? null);

        // FK opcional
        $opposite_id = Validator::positiveIntOrNull($in['opposite_id'] ?? null, 'opposite_id', $errors);

        // =========================
        // Longitudes máximas
        // =========================
        $english       = Validator::maxLength($english, 120);
        $pronunciation = Validator::maxLength($pronunciation, 120);
        $spanish       = Validator::maxLength($spanish, 180);
        $example_en    = Validator::maxLength($example_en, 240);
        $example_es    = Validator::maxLength($example_es, 240);
        $notes         = Validator::maxLength($notes, 240);
        $source        = Validator::maxLength($source, 120);

        // =========================
        // Enums permitidos
        // =========================
        $pos   = Validator::enum($pos, 'pos', $POS_ALLOWED, $errors);
        $level = Validator::enum($level, 'level', $LEVEL_ALLOWED, $errors);

        // =========================
        // Sanitización final
        // =========================
        $english       = Validator::sanitize($english);
        $pronunciation = Validator::sanitize($pronunciation);
        $spanish       = Validator::sanitize($spanish);
        $example_en    = Validator::sanitize($example_en);
        $example_es    = Validator::sanitize($example_es);
        $notes         = Validator::sanitize($notes);
        $source        = Validator::sanitize($source);

        // =========================
        // Si hay errores, responder 422
        // =========================
        $this->validateOrFail($errors, 'Errores de validación');

        // =========================
        // Payload limpio para el modelo
        // =========================
        $clean = [
            'english'       => $english,
            'pronunciation' => $pronunciation,
            'spanish'       => $spanish,
            'pos'           => $pos,
            'level'         => $level,
            'example_en'    => $example_en,
            'example_es'    => $example_es,
            'notes'         => $notes,
            'opposite_id'   => $opposite_id,
            'source'        => $source,
        ];

        $ingles = new InglesModel();
        $exito = $ingles->crear($clean);

        if (!$exito) {
            Response::serverError('Error al guardar palabra', []);
        }

        Response::success([], 'Palabra guardado exitosamente');

    } catch (PDOException $e) {
        Response::serverError('Error de base de datos', [
            'detalle' => $e->getMessage()
        ]);
    } catch (Throwable $e) {
        Response::serverError('Error inesperado', [
            'detalle' => $e->getMessage()
        ]);
    }
}

    /**
     * Actualizar palabra.
     *
     * Uso:
     * PUT /api/ingles/actualizar
     * Body JSON:
     * {
     *   "id": 1,
     *   "english": "...",
     *   "spanish": "..."
     * }
     */
/**
 * Actualiza una palabra existente.
 *
 * Uso esperado:
 * - Petición AJAX / API
 * - Body JSON con todos los campos del formulario
 *
 * Qué valida:
 * - id obligatorio y entero positivo
 * - english y spanish obligatorios
 * - opposite_id entero positivo opcional
 * - pos y level dentro de valores permitidos
 * - longitudes máximas según columnas SQL
 *
 * Qué hace:
 * 1. Lee el JSON
 * 2. Valida y sanitiza
 * 3. Si hay errores, responde 422 con Response::validation()
 * 4. Si todo está bien, llama al modelo
 * 5. Responde success tanto si actualizó como si no hubo cambios
 */
	public function actualizar(): void
	{
	

		try {
			$in = $this->obtenerJsonInput();

			$POS_ALLOWED   = ['verb', 'phrasal_verb', 'noun', 'adjective', 'adverb', 'expression'];
			$LEVEL_ALLOWED = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

			$errors = [];

			// =========================
			// ID obligatorio
			// =========================
			$id = Validator::requirePositiveInt($in['id'] ?? null, 'id', $errors);

			// =========================
			// Campos requeridos
			// =========================
			$english = Validator::requireString($in['english'] ?? null, 'english', $errors);
			$spanish = Validator::requireString($in['spanish'] ?? null, 'spanish', $errors);

			// =========================
			// Campos opcionales
			// =========================
			$pronunciation = Validator::trimOrNull($in['pronunciation'] ?? null);
			$pos           = Validator::trimOrNull($in['pos'] ?? null);
			$level         = Validator::trimOrNull($in['level'] ?? null);
			$example_en    = Validator::trimOrNull($in['example_en'] ?? null);
			$example_es    = Validator::trimOrNull($in['example_es'] ?? null);
			$notes         = Validator::trimOrNull($in['notes'] ?? null);
			$source        = Validator::trimOrNull($in['source'] ?? null);

			// FK opcional
			$opposite_id = Validator::positiveIntOrNull($in['opposite_id'] ?? null, 'opposite_id', $errors);

			// =========================
			// Longitudes máximas
			// =========================
			$english       = Validator::maxLength($english, 120);
			$pronunciation = Validator::maxLength($pronunciation, 120);
			$spanish       = Validator::maxLength($spanish, 180);
			$example_en    = Validator::maxLength($example_en, 240);
			$example_es    = Validator::maxLength($example_es, 240);
			$notes         = Validator::maxLength($notes, 240);
			$source        = Validator::maxLength($source, 120);

			// =========================
			// Validación de enums
			// =========================
			$pos   = Validator::enum($pos, 'pos', $POS_ALLOWED, $errors);
			$level = Validator::enum($level, 'level', $LEVEL_ALLOWED, $errors);

			// =========================
			// Sanitización
			// =========================
			$english       = Validator::sanitize($english);
			$pronunciation = Validator::sanitize($pronunciation);
			$spanish       = Validator::sanitize($spanish);
			$example_en    = Validator::sanitize($example_en);
			$example_es    = Validator::sanitize($example_es);
			$notes         = Validator::sanitize($notes);
			$source        = Validator::sanitize($source);

			// =========================
			// Si hay errores, responder 422
			// =========================
			$this->validateOrFail($errors, 'Errores de validación');

			// =========================
			// Payload limpio para el modelo
			// =========================
			$clean = [
				'id'            => $id,
				'english'       => $english,
				'pronunciation' => $pronunciation,
				'spanish'       => $spanish,
				'pos'           => $pos,
				'level'         => $level,
				'example_en'    => $example_en,
				'example_es'    => $example_es,
				'notes'         => $notes,
				'opposite_id'   => $opposite_id,
				'source'        => $source,
			];

			$modelo = new InglesModel();
			$exito = $modelo->actualizar($clean);

			if ($exito > 0) {
				Response::success([], 'Actualizado correctamente');
			}

			Response::success([], 'No se modificó ningún campo');

		} catch (PDOException $e) {
			Response::serverError('Error de base de datos', [
				'detalle' => $e->getMessage()
			]);
		} catch (Throwable $e) {
			Response::serverError('Error inesperado', [
				'detalle' => $e->getMessage()
			]);
		}
	}

    /**
     * Eliminar palabra.
     *
     * Uso:
     * DELETE /api/ingles/{id}
     */
    public function eliminar($id): void
    {
        $errors = [];

        // ===== ID obligatorio (ruta) =====
        $id = Validator::requirePositiveInt($id, 'id', $errors);

        $this->validateOrFail($errors);

        try {
            $modelo = new InglesModel();
            $ok = $modelo->eliminar($id);

            if (!$ok) {
                Response::serverError('Error al eliminar palabra', []);
            }

            Response::success([], 'Eliminado correctamente');

        } catch (Throwable $e) {
            Response::serverError('Error inesperado', [
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
