<?php
include_once 'conexion.php'; // Tu conexión PDO

// CONFIGURACIÓN GENERAL PARA REUTILIZAR
$tabla = 'usuario'; // ← Cambia por el nombre de tu tabla
$columnas = ['id', 'nombre', 'corre']; // ← Columnas reales de tu tabla (ordenadas)


// PARÁMETROS DE DATATABLES
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$draw = $_GET['draw'] ?? 1;

$searchValue = $_GET['search']['value'] ?? '';
$orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0);
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';

$orderColumn = $columnas[$orderColumnIndex] ?? $columnas[0];


// 1. TOTAL DE REGISTROS SIN FILTRO
$sqlTotal = "SELECT COUNT(*) as total FROM $tabla";
$total = $conexion->query($sqlTotal)->fetch(PDO::FETCH_ASSOC)['total'];


// 2. CONDICIÓN DE BÚSQUEDA
$where = '';
$params = [];

if (!empty($searchValue)) {
    $busquedas = [];
    foreach ($columnas as $col) {
        $busquedas[] = "$col LIKE :search";
    }
    $where = "WHERE " . implode(" OR ", $busquedas);
    $params[':search'] = "%$searchValue%";
}


// 3. TOTAL FILTRADO
$sqlFiltered = "SELECT COUNT(*) as total FROM $tabla $where";
$stmtFiltered = $conexion->prepare($sqlFiltered);
$stmtFiltered->execute($params);
$totalFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];


// 4. CONSULTA PRINCIPAL CON ORDEN Y LIMIT
$sqlData = "SELECT * FROM $tabla $where ORDER BY $orderColumn $orderDir LIMIT :start, :length";
$stmtData = $conexion->prepare($sqlData);

// Asociar parámetros
foreach ($params as $key => $val) {
    $stmtData->bindValue($key, $val, PDO::PARAM_STR);
}
$stmtData->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmtData->bindValue(':length', (int)$length, PDO::PARAM_INT);

$stmtData->execute();
$data = $stmtData->fetchAll(PDO::FETCH_ASSOC);


// 5. RESPUESTA JSON A DATATABLES
$response = [
    'draw' => intval($draw),
    'recordsTotal' => intval($total),
    'recordsFiltered' => intval($totalFiltered),
    'data' => $data
];

header('Content-Type: application/json');
echo json_encode($response);
