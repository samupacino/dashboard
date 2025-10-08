<?php
namespace app\Core;

use PDO;

class DataTableServer
{
    private PDO $pdo;
    private string $tabla;
    private array $columnas;

    public function __construct(PDO $pdo, string $tabla, array $columnas)
    {
        $this->pdo = $pdo;
        $this->tabla = $tabla;
        $this->columnas = $columnas;
    }

    /**
     * Método principal para procesar la petición y devolver JSON para DataTables
     */
    public function procesar(): array
    {
        // Extraer parámetros enviados por DataTables (GET o POST)
        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $search = $_GET['search']['value'] ?? '';
        $orderColumnIndex = $_GET['order'][0]['column'] ?? 0;
        $orderDir = $_GET['order'][0]['dir'] ?? 'asc';

        // Obtener nombre de columna según el índice enviado por DataTables
        $orderColumn = $this->columnas[$orderColumnIndex] ?? $this->columnas[0];

        // 1. Total sin filtrar
        $stmtTotal = $this->pdo->query("SELECT COUNT(*) AS total FROM {$this->tabla}");
        $recordsTotal = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. Filtro de búsqueda
        $where = '';
        $params = [];
        if (!empty($search)) {
            $conditions = [];
            foreach ($this->columnas as $col) {
                $conditions[] = "$col LIKE :search";
            }
            $where = "WHERE " . implode(' OR ', $conditions);
            $params[':search'] = "%$search%";
        }

        // 3. Total filtrado (si hay búsqueda)
        if ($where) {
            $stmtFiltered = $this->pdo->prepare("SELECT COUNT(*) AS total FROM {$this->tabla} $where");
            $stmtFiltered->execute($params);
            $recordsFiltered = (int)$stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
        } else {
            $recordsFiltered = $recordsTotal;
        }

        // 4. Consulta con orden y paginación
        $sql = "SELECT * FROM {$this->tabla} $where ORDER BY $orderColumn $orderDir LIMIT :start, :length";
        $stmt = $this->pdo->prepare($sql);

        // Vincular parámetros
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Estructura final que espera DataTables
        return [
            'draw' => (int)$draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
}
