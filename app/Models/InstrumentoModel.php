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
                (tag, descripcion, tipo, planta, area, ubicacion_exacta, foto, observacion, estado)
                VALUES
                (:tag, :descripcion, :tipo, :planta, :area, :ubicacion_exacta, :foto, :observacion, :estado)";

        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':tag' => $data['tag'],
            ':descripcion' => $data['descripcion'],
            ':tipo' => $data['tipo'],
            ':planta' => $data['planta'],
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
		$tabla = 'instrumentos i';

		$columnas = [
			'i.id',
			'i.tag',
			'i.descripcion',
			'i.tipo',
			'i.planta',
			'i.area',
			'i.ubicacion_exacta',
			'i.foto',
			'i.observacion',
			'i.estado',
			'i.created_at',
			'i.updated_at'
		];

		$pk = 'i.id';

		// columnas reales para búsqueda (SIN alias)
		$searchCols = [
			'i.tag',
			'i.descripcion',
			'i.tipo',
			'i.planta',
			'i.area',
			'i.ubicacion_exacta',
			'i.observacion'
		];

		// columnas reales para ordenar (mismo orden que DataTable frontend)
		$orderCols = [
			'i.id',
			'i.tag',
			'i.descripcion',
			'i.tipo',
			'i.planta',
			'i.area',
			'i.ubicacion_exacta',
			'i.foto',
			'i.observacion',
			'i.created_at',
			'i.updated_at'
		];

		$datatable = new DatatableIngles(
			$this->db,
			$tabla,
			$columnas,
			$pk,
			$searchCols,
			$orderCols
		);

		return $datatable->procesar($request);
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
    public function getByTag(string $tag): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE tag = :tag LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tag' => $tag
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
					descripcion = :descripcion,
					tipo = :tipo,
					planta = :planta,
					area = :area,
					ubicacion_exacta = :ubicacion_exacta,
					foto = :foto,
					observacion = :observacion,
					estado = :estado
				WHERE id = :id";

		$stmt = $this->db->prepare($sql);

		return $stmt->execute([
			':tag' => $data['tag'],
			':descripcion' => $data['descripcion'],
			':tipo' => $data['tipo'],
			':planta' => $data['planta'],
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
