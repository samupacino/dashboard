<?php


namespace app\Models;
use app\Core\DatatableIngles;
use app\Core\Database;
use PDO;
class InstrumentoModel
{
    private PDO $db;
    private $table = 'instrumentos';

    public function __construct()
    {
        $this->db = Database::getInstance("PlataformaDB");
    }

    /**
     * Crear nuevo instrumento
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                (tag, tag_normalizado, descripcion, tipo, planta_id, area, ubicacion_exacta, foto, observacion, estado)
                VALUES
                (:tag, :tag_normalizado ,:descripcion, :tipo, :planta_id, :area, :ubicacion_exacta, :foto, :observacion, :estado)";

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':tag' => $data['tag'],
            ':tag_normalizado' => $data['tag_normalizado'],
            ':descripcion' => $data['descripcion'],
            ':tipo' => $data['tipo'],
            ':planta_id' => $data['planta_id'],
            ':area' => $data['area'],
            ':ubicacion_exacta' => $data['ubicacion_exacta'],
            ':foto' => $data['foto'] ?? null,
            ':observacion' => $data['observacion'] ?? null,
            ':estado' => $data['estado']
        ]);

        return (int) $this->db->lastInsertId();
    }

	public function datatable(?array $request = null): array
	{
		$tabla = 'instrumentos as a inner join plantas as b on a.planta_id = b.id';

		$columnas = [
			'a.id',
			'a.tag',
			'b.nombre as planta',
			'a.planta_id',
			'a.descripcion',
			'a.tipo',			
			'a.area',
			'a.ubicacion_exacta',
			'a.foto',
			'a.observacion',
			'a.estado',
			'a.created_at',
			'a.updated_at'
		];

		$pk = 'a.id';

		// columnas reales para búsqueda (SIN alias)
		$searchCols = [
			'a.tag',
			'b.nombre',
			'a.planta_id',
			'a.descripcion',
			'a.tipo',
			'a.area',
			'a.ubicacion_exacta',
			'a.observacion'
		];

		/*
		 * IMPORTANTE - USO DE $orderCols:
		 *
		 * $order	Cols funciona como una lista de las columnas permitidas
		 * para realizar el ordenamiento.
		 *
		 * El índice de cada elemento de $orderCols DEBE coincidir exactamente
		 * con el índice que ocupa esa columna en DataTables.
		 *
		 * Ejemplo de columnas en DataTables:
		 *
		 * 0 = ID
		 * 1 = Tag
		 * 2 = Descripción
		 * 3 = Tipo
		 * 4 = Planta
		 * 5 = Área
		 * 6 = Ubicación
		 * 7 = Foto
		 * 8 = Estado
		 * 9 = Acciones
		 *
		 * Si solamente se permite ordenar por ID, Tag, Planta y Estado:
		 *
		 * $orderCols = [
		 *     0 => 'i.id',
		 *     1 => 'i.tag',
		 *     4 => 'p.nombre AS planta',
		 *     8 => 'i.estado'
		 * ];
		 *
		 * REGLA:
		 * Índice de $orderCols = índice correspondiente en DataTables.
		 *
		 * Los índices NO deben renumerarse de forma consecutiva si las
		 * columnas ocupan posiciones diferentes en DataTables.
		 *
		 * Si DataTables envía un índice que no existe en $orderCols,
		 * esa columna no se considera válida para el ordenamiento y
		 * la consulta se ejecutará sin agregar ORDER BY.
		 */
		$orderCols = [
			
			1 => 'a.tag',
			
		];
		/*$orderCols = [
			'a.id',
			'a.tag',
			'a.descripcion',
			'a.tipo',
			'a.planta_id',
			'a.area',
			'a.ubicacion_exacta',
			'a.foto',
			'a.observacion',
			'a.created_at',
			'a.updated_at'
		];*/

		$datatable = new DatatableIngles(
			$this->db,
			$tabla,
			$columnas,
			$pk,
			$searchCols,
			$orderCols
		);

		return $datatable->procesar();
	}
    /**
     * Obtener todos los instrumentos
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener instrumento por ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Obtener instrumento por TAG
     */
    public function getByTagAndPlanta(string $tag_normalizado, int $planta_id): ?array
    {
        //$sql = "SELECT * FROM {$this->table} WHERE tag_normalizado = :tag_normalizado AND planta_id = :planta_id LIMIT 1";
		$sql = "SELECT
                a.id,
                a.tag,
                a.tag_normalizado,
                a.planta_id,
                b.nombre AS planta
            FROM instrumentos AS a
            INNER JOIN plantas AS b
                ON a.planta_id = b.id
            WHERE a.tag_normalizado = :tag_normalizado
              AND a.planta_id = :planta_id
            LIMIT 1";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tag_normalizado' => $tag_normalizado,
            ':planta_id' => $planta_id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
	/**
	 * Actualiza un instrumento existente.
	 *
	 * @param int   $id   ID del instrumento
	 * @param array $data Datos limpios ya validados desde el controller
	 *
	 * Espera en $data:
	 * - tag
	 * - descripcion
	 * - tipo
	 * - planta
	 * - area
	 * - ubicacion_exacta
	 * - foto            (string|null)
	 * - observacion     (string|null)
	 * - estado          (string|null)
	 *
	 * Nota:
	 * - Si foto viene null, se guarda null en BD
	 * - Si estado viene null, se guardará null; por eso en update
	 *   normalmente conviene enviarlo ya resuelto desde el controller
	 */
	public function update(int $id, array $data): bool
	{
		$sql = "UPDATE {$this->table} SET
					tag = :tag,
					tag_normalizado = :tagNormalizado,
					descripcion = :descripcion,
					tipo = :tipo,
					planta_id = :planta_id,
					area = :area,
					ubicacion_exacta = :ubicacion_exacta,
					foto = :foto,
					observacion = :observacion,
					estado = :estado
				WHERE id = :id";

		$stmt = $this->db->prepare($sql);

		return $stmt->execute([
			':tag' => $data['tag'],
			':tagNormalizado' => $data['tag_normalizado'],
			':descripcion' => $data['descripcion'],
			':tipo' => $data['tipo'],
			':planta_id' => $data['planta_id'],
			':area' => $data['area'],
			':ubicacion_exacta' => $data['ubicacion_exacta'],
			':foto' => $data['foto'] ?? null,
			':observacion' => $data['observacion'] ?? null,
			':estado' => $data['estado'] ?? 'activo',
			':id' => $id,
		]);
	}

    /**
     * Eliminar instrumento
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
