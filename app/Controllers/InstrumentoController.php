<?php

namespace app\Controllers;

use Throwable;
use app\Core\Response;
use app\Core\Validator;
use app\Models\InstrumentoModel;

/**
 * Controlador del módulo de instrumentos.
 *
 * Responsabilidades:
 * - Recibir la petición HTTP
 * - Validar y sanear datos
 * - Llamar al modelo
 * - Responder siempre en JSON usando Response
 *
 * Criterio de validación:
 * - Todos los errores se acumulan en $errors
 * - Al final, si hay errores, se responde una sola vez con Response::validation()
 */
class InstrumentoController extends BaseApiController
{
    /**
     * Modelo del módulo instrumentos.
     */
    private InstrumentoModel $model;

    /**
     * Constructor.
     *
     * Se instancia directamente el modelo.
     */
	public function __construct(?InstrumentoModel $model = null)
	{
		$this->model = $model ?? new InstrumentoModel();
	}

    /**
     * Listado para DataTables server-side.
     *
     * Flujo:
     * 1. Pide al modelo la estructura de DataTables
     * 2. Responde con Response::datatable()
     */
    public function index(): void
    {
        try {
            $data = $this->model->datatable();

            Response::datatable($data);
        } catch (Throwable $e) {
            Response::serverError('Error al listar los instrumentos', [
                'detalle' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene un instrumento por ID.
     *
     * Validaciones:
     * - id obligatorio y entero positivo
     *
     * Flujo:
     * 1. Valida el id con Validator
     * 2. Si hay errores, responde validation
     * 3. Busca el registro
     * 4. Si no existe, responde notFound
     * 5. Si existe, responde success
     */
    public function show(int $id): void
    {
        $errors = [];

        $id = Validator::requirePositiveInt($id, 'id', $errors);

        if (!empty($errors)) {
            Response::validation($errors, 'Errores de validación');
        }

        try {
            $instrumento = $this->model->getById($id);

            if (!$instrumento) {
                Response::notFound('Instrumento no encontrado');
            }

            Response::success($instrumento, 'Instrumento obtenido correctamente');
        } catch (Throwable $e) {
            Response::serverError('Error al obtener el instrumento', [
                'detalle' => $e->getMessage()
            ]);
        }
    }

    /**
     * Registra un nuevo instrumento.
     *
     * Campos obligatorios:
     * - tag
     * - descripcion
     * - tipo
     * - planta
     * - area
     * - ubicacion_exacta
     *
     * Campos opcionales:
     * - foto
     * - observacion
     *
     * Nota:
     * - estado, created_at y updated_at no se envían
     *   porque la base de datos los resuelve automáticamente.
     */
    public function store(): void
    {
      
       
        $input = $this->obtenerFormInput();

		$data  = $input['fields'];
		$files = $input['files'];
		
	
		$errors = [];

        // =========================
        // Campos obligatorios
        // =========================
        $tag = Validator::requireString($data['tag'] ?? null, 'tag', $errors);
        $tag = Validator::maxLength($tag, 50);
        $tag = Validator::sanitize($tag);
        
        // TAG usado internamente para comparar
		$tagNormalizado = strtoupper($tag);
		$tagNormalizado = str_replace(['-', ' ', '_'], '', $tagNormalizado);
        
        

        $descripcion = Validator::requireString($data['descripcion'] ?? null, 'descripcion', $errors);
        $descripcion = Validator::maxLength($descripcion, 150);
        $descripcion = Validator::sanitize($descripcion);

        $tipo = Validator::requireString($data['tipo'] ?? null, 'tipo', $errors);
        $tipo = Validator::maxLength($tipo, 50);
        $tipo = Validator::sanitize($tipo);

		$planta_id = Validator::requirePositiveInt($data['planta_id'] ?? null, 'planta_id', $errors);
		
		/*
        $planta = Validator::requireString($data['planta'] ?? null, 'planta', $errors);
        $planta = Validator::maxLength($planta, 50);
        $planta = Validator::sanitize($planta);
		*/
        $area = Validator::requireString($data['area'] ?? null, 'area', $errors);
        $area = Validator::maxLength($area, 100);
        $area = Validator::sanitize($area);

        $ubicacionExacta = Validator::requireString($data['ubicacion_exacta'] ?? null, 'ubicacion_exacta', $errors);
        $ubicacionExacta = Validator::sanitize($ubicacionExacta, true, false);

		$ESTADO_ALLOWED = ['activo', 'inactivo'];

		$estado = Validator::trimOrNull($data['estado'] ?? null);
		$estado = Validator::enum($estado, 'estado', $ESTADO_ALLOWED, $errors);
		$estado = Validator::sanitize($estado);
        // =========================
        // Campos opcionales
        // =========================

        $observacion = Validator::trimOrNull($data['observacion'] ?? null);
        $observacion = Validator::sanitize($observacion, true, false);
        
        
        // =========================
		// Archivo opcional
		// =========================
		$foto = $files['foto'] ?? null;
		$rutaFoto = null;

		if ($foto && isset($foto['error']) && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
			if ($foto['error'] !== UPLOAD_ERR_OK) {
				$errors['foto'][] = 'Error al subir la imagen.';
			} else {
				$nombreOriginal = $foto['name'] ?? '';
				$tmpName        = $foto['tmp_name'] ?? '';
				$tamano         = $foto['size'] ?? 0;

				$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
				$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

				if (!in_array($extension, $permitidas, true)) {
					$errors['foto'][] = 'Formato de imagen no permitido. Solo: jpg, jpeg, png, webp.';
				}

				// Límite ejemplo: 3 MB
				if ($tamano > 3 * 1024 * 1024) {
					$errors['foto'][] = 'La imagen no debe superar los 3 MB.';
				}

				if ($tmpName === '' || !is_uploaded_file($tmpName)) {
					$errors['foto'][] = 'No se recibió un archivo válido.';
				}
			}
		}
		
        $this->validateOrFail($errors);

        try {
			// =========================
        	// Validación de negocio
        	// =========================
        	
        	
      
            $existe = $this->model->getByTagAndPlanta($tagNormalizado, $planta_id);

            if ($existe) {
                Response::conflict('Ya existe un instrumento registrado con ese tag', [
                
                    'tag' => $existe['tag'],
					'planta' => $existe['planta']
				]);
            }
            
         
            
				
			// =========================
			// Guardar imagen si fue enviada
			// =========================
			if ($foto && isset($foto['error']) && $foto['error'] === UPLOAD_ERR_OK) {
				$carpetaFisica = ROOT . '/public/uploads/instrumentos/';

				if (!is_dir($carpetaFisica)) {
					if (!mkdir($carpetaFisica, 0775, true) && !is_dir($carpetaFisica)) {
						Response::serverError('No se pudo crear la carpeta de imágenes.', []);
					}
				}

				$extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));

				// nombre seguro y único
				$nombreFinal = ($tag ? preg_replace('/[^A-Za-z0-9_-]/', '_', $tag) : 'instrumento')
					. '_'
					. time()
					. '.'
					. $extension;

				$rutaFisica = $carpetaFisica . $nombreFinal;

				// ruta relativa para guardar en BD
				$rutaFoto = 'uploads/instrumentos/' . $nombreFinal;

				if (!move_uploaded_file($foto['tmp_name'], $rutaFisica)) {
					Response::serverError('No se pudo guardar la imagen en el servidor.', []);
				}
			}
			
			// =========================
        	// Guardar en BD
        	// =========================

            $id = $this->model->create([
                'tag' => $tag,
                'tag_normalizado' => $tagNormalizado,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
                'planta_id' => $planta_id,
                'area' => $area,
                'ubicacion_exacta' => $ubicacionExacta,
                'foto' => $rutaFoto,
                'observacion' => $observacion,
                'estado' => $estado,
            ]);

            $instrumento = $this->model->getById($id);

            if (!$instrumento) {
                Response::serverError(
                    'Se registró el instrumento, pero no se pudo recuperar el registro creado',
                    ['id' => $id]
                );
            }

            Response::created($instrumento, 'Instrumento registrado correctamente');
        } catch (Throwable $e) {
            Response::serverError('Error al registrar el instrumento', [
                'detalle' => $e->getMessage()
            ]);
        }
    }

	/**
	 * Actualiza un instrumento existente.
	 *
	 * Este método espera multipart/form-data porque puede recibir:
	 * - campos de texto
	 * - archivo de imagen
	 *
	 * Casos soportados:
	 * 1. Si NO suben nueva foto, se conserva la foto actual.
	 * 2. Si suben nueva foto, se reemplaza la actual.
	 * 3. Si envían quitar_foto=1, se elimina la referencia y queda sin foto.
	 *
	 * Recomendación frontend:
	 * - enviar siempre FormData
	 * - incluir un campo oculto "id"
	 * - incluir opcionalmente "quitar_foto" con valor 1 si el usuario desea borrar la foto
	 */
	public function update(): void
	{
		$input = $this->obtenerFormInput();

		$data  = $input['fields'];
		$files = $input['files'];

		$errors = [];

		// =========================
		// ID obligatorio
		// =========================
		$id = Validator::requirePositiveInt($data['id'] ?? null, 'id', $errors);

		// =========================
		// Campos obligatorios
		// =========================
		$tag = Validator::requireString($data['tag'] ?? null, 'tag', $errors);
		$tag = Validator::maxLength($tag, 50);
		$tag = Validator::sanitize($tag);
		// Versión normalizada para comparar
		$tagNormalizado = strtoupper($tag);
		$tagNormalizado = str_replace(['-', ' ', '_'], '', $tagNormalizado);
		
		
		

		$descripcion = Validator::requireString($data['descripcion'] ?? null, 'descripcion', $errors);
		$descripcion = Validator::maxLength($descripcion, 150);
		$descripcion = Validator::sanitize($descripcion);

		$tipo = Validator::requireString($data['tipo'] ?? null, 'tipo', $errors);
		$tipo = Validator::maxLength($tipo, 50);
		$tipo = Validator::sanitize($tipo);



		$planta_id = Validator::requirePositiveInt($data['planta_id'] ?? null, 'planta_id', $errors);
		
/*
		$planta = Validator::requireString($data['planta'] ?? null, 'planta', $errors);
		$planta = Validator::maxLength($planta, 50);
		$planta = Validator::sanitize($planta);
*/
		$area = Validator::requireString($data['area'] ?? null, 'area', $errors);
		$area = Validator::maxLength($area, 100);
		$area = Validator::sanitize($area);

		$ubicacionExacta = Validator::requireString($data['ubicacion_exacta'] ?? null, 'ubicacion_exacta', $errors);
		$ubicacionExacta = Validator::sanitize($ubicacionExacta, true, false);

		// =========================
		// Campos opcionales
		// =========================
		$observacion = Validator::trimOrNull($data['observacion'] ?? null);
		$observacion = Validator::sanitize($observacion, true, false);

		$estado = Validator::trimOrNull($data['estado'] ?? null);
		$estado = Validator::enum($estado, 'estado', ['activo', 'inactivo'], $errors);
		$estado = Validator::sanitize($estado);

		// quitar_foto = 1 => dejar foto vacía
		$quitarFoto = isset($data['quitar_foto']) && (string)$data['quitar_foto'] === '1';

		// =========================
		// Archivo opcional
		// =========================
		$fotoNueva = $files['foto'] ?? null;

		if ($fotoNueva && isset($fotoNueva['error']) && $fotoNueva['error'] !== UPLOAD_ERR_NO_FILE) {
			if ($fotoNueva['error'] !== UPLOAD_ERR_OK) {
				$errors['foto'][] = 'Error al subir la imagen.';
			} else {
				$nombreOriginal = $fotoNueva['name'] ?? '';
				$tmpName        = $fotoNueva['tmp_name'] ?? '';
				$tamano         = $fotoNueva['size'] ?? 0;

				$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
				$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

				if (!in_array($extension, $permitidas, true)) {
					$errors['foto'][] = 'Formato de imagen no permitido. Solo: jpg, jpeg, png, webp.';
				}

				if ($tamano > 3 * 1024 * 1024) {
					$errors['foto'][] = 'La imagen no debe superar los 3 MB.';
				}

				if ($tmpName === '' || !is_uploaded_file($tmpName)) {
					$errors['foto'][] = 'No se recibió un archivo válido.';
				}
			}
		}

		$this->validateOrFail($errors);

		try {
			$actual = $this->model->getById($id);

			if (!$actual) {
				Response::notFound('Instrumento no encontrado');
			}

			$otro = $this->model->getByTagAndPlanta($tagNormalizado, $planta_id);

			if ($otro && (int)$otro['id'] !== $id) {
				Response::conflict('Ya existe otro instrumento con ese tag', [
					'tag' => $otro['tag'],
					'planta' => $otro['planta']
				]);
			}

			// =========================
			// Resolver foto final
			// =========================
			$rutaFotoFinal = $actual['foto'] ?? null;

			// Caso 1: el usuario pidió quitar la foto
			if ($quitarFoto) {
				$rutaFotoFinal = null;
			}

			// Caso 2: suben una foto nueva
			if ($fotoNueva && isset($fotoNueva['error']) && $fotoNueva['error'] === UPLOAD_ERR_OK) {
				$carpetaFisica = ROOT . '/public/uploads/instrumentos/';

				if (!is_dir($carpetaFisica)) {
					if (!mkdir($carpetaFisica, 0775, true) && !is_dir($carpetaFisica)) {
						Response::serverError('No se pudo crear la carpeta de imágenes.', []);
					}
				}

				$extension = strtolower(pathinfo($fotoNueva['name'], PATHINFO_EXTENSION));

				$nombreFinal = ($tag ? preg_replace('/[^A-Za-z0-9_-]/', '_', $tag) : 'instrumento')
					. '_'
					. time()
					. '.'
					. $extension;

				$rutaFisica = $carpetaFisica . $nombreFinal;
				$rutaFotoFinal = 'uploads/instrumentos/' . $nombreFinal;

				if (!move_uploaded_file($fotoNueva['tmp_name'], $rutaFisica)) {
					Response::serverError('No se pudo guardar la nueva imagen en el servidor.', []);
				}
			}

			$ok = $this->model->update($id, [
				'tag' => $tag,
				'tag_normalizado' => $tagNormalizado,
				'descripcion' => $descripcion,
				'tipo' => $tipo,
				'planta_id' => $planta_id,
				'area' => $area,
				'ubicacion_exacta' => $ubicacionExacta,
				'foto' => $rutaFotoFinal,
				'observacion' => $observacion,
				'estado' => $estado,
			]);

			if (!$ok) {
				Response::serverError('No se pudo actualizar el instrumento', [
					'id' => $id
				]);
			}

			$instrumento = $this->model->getById($id);

			if (!$instrumento) {
				Response::serverError(
					'Se actualizó el instrumento, pero no se pudo recuperar el registro actualizado',
					['id' => $id]
				);
			}

			Response::success($instrumento, 'Instrumento actualizado correctamente');
		} catch (Throwable $e) {
			Response::serverError('Error al actualizar el instrumento', [
				'detalle' => $e->getMessage()
			]);
		}
	}

    /**
     * Elimina un instrumento por ID.
     *
     * Validaciones:
     * - id obligatorio y entero positivo
     *
     * Nota:
     * - Hoy usa delete() del modelo
     * - Si luego migras a borrado lógico, este método puede mantenerse igual
     */
    public function delete(int $id): void
    {
        $errors = [];

        $id = Validator::requirePositiveInt($id, 'id', $errors);

        $this->validateOrFail($errors);

        try {
            $instrumento = $this->model->getById($id);

            if (!$instrumento) {
                Response::notFound('Instrumento no encontrado');
            }

            $ok = $this->model->delete($id);

            if (!$ok) {
                Response::serverError('No se pudo eliminar el instrumento', [
                    'id' => $id
                ]);
            }

            Response::success([], 'Instrumento eliminado correctamente');
        } catch (Throwable $e) {
            Response::serverError('Error al eliminar el instrumento', [
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
