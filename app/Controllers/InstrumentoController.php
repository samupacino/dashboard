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
	 * FLUJO DEL PROCESO:
	 *
	 * 1. Obtiene los datos enviados mediante multipart/form-data.
	 *    - fields: datos normales del formulario.
	 *    - files: archivos enviados, como la fotografía.
	 *
	 * 2. Valida y sanitiza los campos recibidos.
	 *    - Campos obligatorios.
	 *    - Longitudes máximas.
	 *    - planta_id como entero positivo.
	 *    - estado según valores permitidos.
	 *
	 * 3. Normaliza el TAG para evitar registros duplicados escritos
	 *    de diferentes formas.
	 *
	 *    Ejemplo:
	 *        PT-101
	 *        PT101
	 *        PT_101
	 *        PT 101
	 *
	 *    Todos generan:
	 *        PT101
	 *
	 * 4. Si se envió una fotografía:
	 *    - Verifica errores de subida reportados por PHP.
	 *    - Valida extensión.
	 *    - Valida tamaño máximo.
	 *    - Verifica que sea un archivo recibido correctamente.
	 *
	 * 5. Verifica que no exista otro instrumento con el mismo
	 *    tag_normalizado dentro de la misma planta.
	 *
	 *    La misma identificación puede existir en otra planta,
	 *    pero no puede repetirse dentro de una misma planta.
	 *
	 * 6. Si se recibió una fotografía válida:
	 *    - Crea la carpeta de imágenes si no existe.
	 *    - Genera un nombre seguro y único.
	 *    - Guarda físicamente la imagen en:
	 *
	 *        /public/uploads/instrumentos/
	 *
	 *    - La base de datos guarda solamente la ruta relativa.
	 *
	 * 7. Registra el instrumento en la base de datos.
	 *
	 * 8. Recupera el registro creado y lo devuelve al frontend.
	 *
	 * 9. Si ocurre un error después de haber guardado físicamente
	 *    una nueva imagen, se elimina el archivo para evitar dejar
	 *    imágenes huérfanas sin un registro asociado en la BD.
	 *
	 * FLUJO RESUMIDO:
	 *
	 * FormData
	 *    ↓
	 * Validar campos
	 *    ↓
	 * Normalizar TAG
	 *    ↓
	 * Validar imagen
	 *    ↓
	 * Comprobar TAG + planta
	 *    ↓
	 * Guardar imagen física
	 *    ↓
	 * INSERT en base de datos
	 *    ↓
	 * Recuperar registro
	 *    ↓
	 * Respuesta JSON
	 *
	 * Si falla el INSERT después de guardar la imagen:
	 *
	 * Imagen guardada
	 *    ↓
	 * INSERT falla
	 *    ↓
	 * Eliminar imagen física
	 *    ↓
	 * Respuesta de error
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

		$planta_id = Validator::requirePositiveInt(
			$data['planta_id'] ?? null,
			'planta_id',
			$errors
		);

		$area = Validator::requireString($data['area'] ?? null, 'area', $errors);
		$area = Validator::maxLength($area, 100);
		$area = Validator::sanitize($area);

		$ubicacionExacta = Validator::requireString(
			$data['ubicacion_exacta'] ?? null,
			'ubicacion_exacta',
			$errors
		);
		$ubicacionExacta = Validator::sanitize(
			$ubicacionExacta,
			true,
			false
		);

		// =========================
		// Estado
		// =========================
		$ESTADO_ALLOWED = ['activo', 'inactivo'];

		$estado = Validator::trimOrNull($data['estado'] ?? null);
		$estado = Validator::enum(
			$estado,
			'estado',
			$ESTADO_ALLOWED,
			$errors
		);
		$estado = Validator::sanitize($estado);

		// =========================
		// Campos opcionales
		// =========================
		$observacion = Validator::trimOrNull(
			$data['observacion'] ?? null
		);
		$observacion = Validator::sanitize(
			$observacion,
			true,
			false
		);

		// =========================
		// Archivo opcional
		// =========================
		$foto = $files['foto'] ?? null;

		$rutaFoto   = null;
		$rutaFisica = null;

		if (
			$foto &&
			isset($foto['error']) &&
			$foto['error'] !== UPLOAD_ERR_NO_FILE
		) {
			$codigoError    = $foto['error'] ?? null;
			$nombreOriginal = $foto['name'] ?? '';
			$tmpName        = $foto['tmp_name'] ?? '';
			$tamano         = $foto['size'] ?? 0;
			$tipoMime       = $foto['type'] ?? '';

			$tamanoMB = round(
				$tamano / 1024 / 1024,
				2
			);

			// =========================
			// Error reportado por PHP
			// =========================
			if ($codigoError !== UPLOAD_ERR_OK) {
				$mensajeUpload = match ($codigoError) {

					UPLOAD_ERR_INI_SIZE =>
						'El archivo supera upload_max_filesize configurado en PHP.',

					UPLOAD_ERR_FORM_SIZE =>
						'El archivo supera el tamaño máximo permitido por el formulario.',

					UPLOAD_ERR_PARTIAL =>
						'El archivo se subió parcialmente.',

					UPLOAD_ERR_NO_FILE =>
						'No se recibió ningún archivo.',

					UPLOAD_ERR_NO_TMP_DIR =>
						'No existe la carpeta temporal de PHP.',

					UPLOAD_ERR_CANT_WRITE =>
						'PHP no pudo escribir el archivo temporal en el servidor.',

					UPLOAD_ERR_EXTENSION =>
						'Una extensión de PHP detuvo la subida del archivo.',

					default =>
						'Error desconocido durante la subida.'
				};

				$errors['foto'][] =
					$mensajeUpload .
					' Código: ' . $codigoError .
					' | Nombre: ' . $nombreOriginal .
					' | Tamaño recibido: ' . $tamanoMB . ' MB' .
					' | MIME: ' . $tipoMime .
					' | upload_max_filesize: ' . ini_get('upload_max_filesize') .
					' | post_max_size: ' . ini_get('post_max_size');
			}

			// =========================
			// Si PHP recibió bien el archivo
			// =========================
			else {
				$extension = strtolower(
					pathinfo(
						$nombreOriginal,
						PATHINFO_EXTENSION
					)
				);

				$permitidas = [
					'jpg',
					'jpeg',
					'png',
					'webp'
				];

				if (!in_array($extension, $permitidas, true)) {
					$errors['foto'][] =
						'Formato no permitido.' .
						' Extensión recibida: ' . $extension .
						' | Nombre: ' . $nombreOriginal .
						' | MIME: ' . $tipoMime;
				}

				if ($tamano > 3 * 1024 * 1024) {
					$errors['foto'][] =
						'La imagen supera el límite interno de 3 MB.' .
						' Tamaño recibido por PHP: ' . $tamanoMB . ' MB.';
				}

				if ($tmpName === '') {
					$errors['foto'][] =
						'El archivo no contiene una ruta temporal válida.';
				}
				elseif (!is_uploaded_file($tmpName)) {
					$errors['foto'][] =
						'El archivo temporal existe como valor, pero PHP no lo reconoce como archivo subido.' .
						' tmp_name: ' . $tmpName;
				}
			}
		}

		$this->validateOrFail($errors);

		try {
			// =========================
			// Validación de negocio
			// =========================
			$existe = $this->model->getByTagAndPlanta(
				$tagNormalizado,
				$planta_id
			);

			if ($existe) {
				Response::conflict(
					'Ya existe un instrumento registrado con ese tag',
					[
						'tag' => $existe['tag'],
						'planta' => $existe['planta']
					]
				);
			}

			// =========================
			// Guardar imagen si fue enviada
			// =========================
			if (
				$foto &&
				isset($foto['error']) &&
				$foto['error'] === UPLOAD_ERR_OK
			) {
				$carpetaFisica =
					ROOT . '/public/uploads/instrumentos/';

				if (!is_dir($carpetaFisica)) {
					if (
						!mkdir(
							$carpetaFisica,
							0775,
							true
						) &&
						!is_dir($carpetaFisica)
					) {
						Response::serverError(
							'No se pudo crear la carpeta de imágenes.',
							[]
						);
					}
				}

				$extension = strtolower(
					pathinfo(
						$foto['name'],
						PATHINFO_EXTENSION
					)
				);

				$nombreFinal =
					(
						$tag
							? preg_replace(
								'/[^A-Za-z0-9_-]/',
								'_',
								$tag
							)
							: 'instrumento'
					)
					. '_'
					. time()
					. '.'
					. $extension;

				$rutaFisica =
					$carpetaFisica . $nombreFinal;

				$rutaFoto =
					'uploads/instrumentos/' . $nombreFinal;

				if (
					!move_uploaded_file(
						$foto['tmp_name'],
						$rutaFisica
					)
				) {
					Response::serverError(
						'No se pudo guardar la imagen en el servidor.',
						[
							'nombre' => $foto['name'] ?? '',
							'tamaño_mb' => round(
								($foto['size'] ?? 0) / 1024 / 1024,
								2
							),
							'destino' => $rutaFisica,
							'carpeta_escribible' =>
								is_writable($carpetaFisica)
									? 'si'
									: 'no'
						]
					);
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
					[
						'id' => $id
					]
				);
			}

			Response::created(
				$instrumento,
				'Instrumento registrado correctamente'
			);

		} catch (Throwable $e) {

			// =========================
			// Limpieza de imagen huérfana
			// =========================
			// Si la imagen alcanzó a guardarse físicamente
			// pero el INSERT falló, la eliminamos.
			if (
				$rutaFisica &&
				is_file($rutaFisica)
			) {
				unlink($rutaFisica);
			}

			Response::serverError(
				'Error al registrar el instrumento',
				[
					'detalle' => $e->getMessage()
				]
			);
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

		$planta_id = Validator::requirePositiveInt(
			$data['planta_id'] ?? null,
			'planta_id',
			$errors
		);

		$area = Validator::requireString($data['area'] ?? null, 'area', $errors);
		$area = Validator::maxLength($area, 100);
		$area = Validator::sanitize($area);

		$ubicacionExacta = Validator::requireString(
			$data['ubicacion_exacta'] ?? null,
			'ubicacion_exacta',
			$errors
		);
		$ubicacionExacta = Validator::sanitize($ubicacionExacta, true, false);

		// =========================
		// Campos opcionales
		// =========================
		$observacion = Validator::trimOrNull($data['observacion'] ?? null);
		$observacion = Validator::sanitize($observacion, true, false);

		$estado = Validator::trimOrNull($data['estado'] ?? null);
		$estado = Validator::enum(
			$estado,
			'estado',
			['activo', 'inactivo'],
			$errors
		);
		$estado = Validator::sanitize($estado);

		// quitar_foto = 1 => dejar foto vacía
		$quitarFoto =
			isset($data['quitar_foto']) &&
			(string)$data['quitar_foto'] === '1';

		// =========================
		// Archivo opcional
		// =========================
		$fotoNueva = $files['foto'] ?? null;

		if (
			$fotoNueva &&
			isset($fotoNueva['error']) &&
			$fotoNueva['error'] !== UPLOAD_ERR_NO_FILE
		) {
			$codigoError = $fotoNueva['error'];

			if ($codigoError !== UPLOAD_ERR_OK) {

				$mensajeError = match ($codigoError) {
					UPLOAD_ERR_INI_SIZE =>
						'La imagen supera el límite upload_max_filesize de PHP.',

					UPLOAD_ERR_FORM_SIZE =>
						'La imagen supera el tamaño máximo permitido por el formulario.',

					UPLOAD_ERR_PARTIAL =>
						'La imagen se subió parcialmente.',

					UPLOAD_ERR_NO_TMP_DIR =>
						'No existe la carpeta temporal de PHP.',

					UPLOAD_ERR_CANT_WRITE =>
						'PHP no pudo escribir el archivo temporal.',

					UPLOAD_ERR_EXTENSION =>
						'Una extensión de PHP detuvo la subida.',

					default =>
						'Error desconocido al subir la imagen.'
				};

				$errors['foto'][] =
					$mensajeError .
					' Código: ' . $codigoError .
					' | upload_max_filesize: ' . ini_get('upload_max_filesize') .
					' | post_max_size: ' . ini_get('post_max_size');

			} else {

				$nombreOriginal = $fotoNueva['name'] ?? '';
				$tmpName        = $fotoNueva['tmp_name'] ?? '';
				$tamano         = $fotoNueva['size'] ?? 0;

				$extension = strtolower(
					pathinfo($nombreOriginal, PATHINFO_EXTENSION)
				);

				$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

				if (!in_array($extension, $permitidas, true)) {
					$errors['foto'][] =
						'Formato de imagen no permitido. Solo: jpg, jpeg, png, webp.';
				}

				if ($tamano > 3 * 1024 * 1024) {
					$errors['foto'][] =
						'La imagen no debe superar los 3 MB. Tamaño recibido: ' .
						round($tamano / 1024 / 1024, 2) .
						' MB.';
				}

				if ($tmpName === '' || !is_uploaded_file($tmpName)) {
					$errors['foto'][] =
						'No se recibió un archivo válido.';
				}
			}
		}

		$this->validateOrFail($errors);

		try {
			$actual = $this->model->getById($id);

			if (!$actual) {
				Response::notFound('Instrumento no encontrado');
			}

			$otro = $this->model->getByTagAndPlanta(
				$tagNormalizado,
				$planta_id
			);

			if ($otro && (int)$otro['id'] !== $id) {
				Response::conflict(
					'Ya existe otro instrumento con ese tag',
					[
						'tag' => $otro['tag'],
						'planta' => $otro['planta']
					]
				);
			}

			// =========================
			// Foto actual
			// =========================
			$rutaFotoActual = $actual['foto'] ?? null;
			$rutaFotoFinal  = $rutaFotoActual;

			// Guardaremos aquí la ruta física de una nueva foto
			$rutaFisicaNueva = null;

			// =========================
			// Caso 1: quitar foto
			// =========================
			if ($quitarFoto) {
				$rutaFotoFinal = null;
			}

			// =========================
			// Caso 2: nueva foto
			// =========================
			if (
				$fotoNueva &&
				isset($fotoNueva['error']) &&
				$fotoNueva['error'] === UPLOAD_ERR_OK
			) {
				$carpetaFisica =
					ROOT . '/public/uploads/instrumentos/';

				if (!is_dir($carpetaFisica)) {
					if (
						!mkdir($carpetaFisica, 0775, true) &&
						!is_dir($carpetaFisica)
					) {
						Response::serverError(
							'No se pudo crear la carpeta de imágenes.',
							[]
						);
					}
				}

				$extension = strtolower(
					pathinfo($fotoNueva['name'], PATHINFO_EXTENSION)
				);

				$nombreFinal =
					($tag
						? preg_replace('/[^A-Za-z0-9_-]/', '_', $tag)
						: 'instrumento'
					)
					. '_'
					. time()
					. '.'
					. $extension;

				$rutaFisicaNueva =
					$carpetaFisica . $nombreFinal;

				$rutaFotoFinal =
					'uploads/instrumentos/' . $nombreFinal;

				if (
					!move_uploaded_file(
						$fotoNueva['tmp_name'],
						$rutaFisicaNueva
					)
				) {
					Response::serverError(
						'No se pudo guardar la nueva imagen en el servidor.',
						[
							'nombre' => $fotoNueva['name'] ?? '',
							'tamaño_mb' => round(
								($fotoNueva['size'] ?? 0) / 1024 / 1024,
								2
							),
							'destino' => $rutaFisicaNueva,
							'carpeta_escribible' =>
								is_writable($carpetaFisica)
									? 'si'
									: 'no'
						]
					);
				}
			}

			// =========================
			// Actualizar BD
			// =========================
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

				// Si se llegó a guardar una nueva foto,
				// pero falló la BD, la borramos para no dejar archivo huérfano.
				if (
					$rutaFisicaNueva &&
					is_file($rutaFisicaNueva)
				) {
					unlink($rutaFisicaNueva);
				}

				Response::serverError(
					'No se pudo actualizar el instrumento',
					[
						'id' => $id
					]
				);
			}

			// =========================
			// Eliminar foto anterior
			// SOLO después de actualizar correctamente la BD
			// =========================
			if (
				($quitarFoto || $rutaFisicaNueva !== null) &&
				$rutaFotoActual
			) {
				$rutaFisicaActual =
					ROOT . '/public/' .
					ltrim($rutaFotoActual, '/');

				if (is_file($rutaFisicaActual)) {
					if (!unlink($rutaFisicaActual)) {
						error_log(
							'No se pudo eliminar la foto anterior: ' .
							$rutaFisicaActual
						);
					}
				}
			}

			// =========================
			// Recuperar registro actualizado
			// =========================
			$instrumento = $this->model->getById($id);

			if (!$instrumento) {
				Response::serverError(
					'Se actualizó el instrumento, pero no se pudo recuperar el registro actualizado',
					[
						'id' => $id
					]
				);
			}

			Response::success(
				$instrumento,
				'Instrumento actualizado correctamente'
			);

		} catch (Throwable $e) {

			Response::serverError(
				'Error al actualizar el instrumento',
				[
					'detalle' => $e->getMessage()
				]
			);
		}
	}
	public function update_antiguo(): void
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
	 * Elimina un instrumento.
	 *
	 * Flujo:
	 * 1. Valida el ID.
	 * 2. Obtiene el instrumento para conocer la foto actual.
	 * 3. Elimina el registro de la BD.
	 * 4. Si la BD eliminó correctamente, elimina la imagen física.
	 * 5. Responde success.
	 */
	public function delete(int $id): void
	{
		$errors = [];

		$id = Validator::requirePositiveInt($id, 'id', $errors);

		$this->validateOrFail($errors);

		try {
			// =========================
			// Buscar instrumento
			// =========================
			$instrumento = $this->model->getById($id);

			if (!$instrumento) {
				Response::notFound('Instrumento no encontrado');
			}

			// Guardamos la ruta antes de eliminar el registro
			$rutaFoto = $instrumento['foto'] ?? null;

			// =========================
			// Eliminar registro de BD
			// =========================
			$ok = $this->model->delete($id);

			if (!$ok) {
				Response::serverError(
					'No se pudo eliminar el instrumento',
					[
						'id' => $id
					]
				);
			}

			// =========================
			// Eliminar imagen física
			// =========================
			if ($rutaFoto) {

				$rutaFisica =
					ROOT . '/public/' . ltrim($rutaFoto, '/');

				if (is_file($rutaFisica)) {

					if (!unlink($rutaFisica)) {
						// El registro ya fue eliminado correctamente.
						// No hacemos fallar toda la operación por la imagen.
						error_log(
							'No se pudo eliminar la imagen del instrumento ID ' .
							$id .
							': ' .
							$rutaFisica
						);
					}
				}
			}

			Response::success(
				[],
				'Instrumento eliminado correctamente'
			);

		} catch (Throwable $e) {

			Response::serverError(
				'Error al eliminar el instrumento',
				[
					'detalle' => $e->getMessage()
				]
			);
		}
	}
    public function delete_antiguo(int $id): void
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
