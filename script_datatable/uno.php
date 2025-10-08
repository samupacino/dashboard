<?php
// 1. Conexión a base de datos
include_once 'conexion.php'; // Asegúrate que devuelve un objeto PDO en $conexion

// 2. Parámetros que vienen desde DataTables por GET
$start = $_GET['start'] ?? 0;           // Desde qué fila empezar (paginación)
$length = $_GET['length'] ?? 10;        // Cuántas filas devolver
$draw = $_GET['draw'] ?? 1;             // Identificador de la petición (para DataTables)

$searchValue = $_GET['search']['value'] ?? '';  // Valor de búsqueda general

$orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0); // Índice de columna a ordenar
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';              // Dirección ascendente o descendente

// 3. Mapeo del índice de columna a nombre real en BD
$columns = ['id', 'nombre', 'correo']; // Asegúrate que estos nombres coincidan con tu tabla real
$orderColumn = $columns[$orderColumnIndex] ?? 'id'; // Por defecto, ordenar por id

// 4. Obtener total sin filtro
$stmtTotal = $conexion->query("SELECT COUNT(*) AS total FROM usuario");
$total = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// 5. Preparar cláusula WHERE si hay búsqueda
$where = '';
$params = [];

if (!empty($searchValue)) {
    $where = "WHERE nombre LIKE :search OR correo LIKE :search";
    $params[':search'] = "%$searchValue%";
}

// 6. Obtener total filtrado (si hay búsqueda)
if ($where) {
    $stmtFiltered = $conexion->prepare("SELECT COUNT(*) AS total FROM usuario $where");
    $stmtFiltered->execute($params);
    $totalFiltered = (int)$stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    $totalFiltered = $total;
}

// 7. Preparar consulta final con orden y paginación
$sql = "SELECT * FROM usuario $where ORDER BY $orderColumn $orderDir LIMIT :start, :length";
$stmtData = $conexion->prepare($sql);

// 8. Asociar los parámetros (busqueda, paginación)
foreach ($params as $key => $value) {
    $stmtData->bindValue($key, $value, PDO::PARAM_STR);
}
$stmtData->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmtData->bindValue(':length', (int)$length, PDO::PARAM_INT);

// 9. Ejecutar y recoger resultados
$stmtData->execute();
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

// 10. Construir respuesta esperada por DataTables
$response = [
    'draw' => intval($draw),               // Repetir el valor de draw que envió el cliente
    'recordsTotal' => $total,              // Total sin filtro
    'recordsFiltered' => $totalFiltered,   // Total con filtro
    'data' => $data                        // Datos reales
];

// 11. Devolver como JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
