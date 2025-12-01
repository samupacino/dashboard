
<?php
// asume $pdo ya creado y configurado
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// 1) Recoger datos (FormData o JSON)
$isJson = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
$data   = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

// 2) Validación mínima
$english = trim($data['english'] ?? '');
$spanish = trim($data['spanish'] ?? '');

if ($english === '' || $spanish === '') {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'english y spanish son obligatorios']);
  exit;
}

// 3) Normalizar opcionales ('' → NULL)
$pronunciation = ($data['pronunciation'] ?? '') !== '' ? trim($data['pronunciation']) : null;
$pos           = ($data['pos'] ?? '') !== '' ? $data['pos'] : null;        // deja DEFAULT si null
$level         = ($data['level'] ?? '') !== '' ? $data['level'] : null;
$example_en    = ($data['example_en'] ?? '') !== '' ? trim($data['example_en']) : null;
$example_es    = ($data['example_es'] ?? '') !== '' ? trim($data['example_es']) : null;
$notes         = ($data['notes'] ?? '') !== '' ? trim($data['notes']) : null;
$source        = ($data['source'] ?? '') !== '' ? trim($data['source']) : null;
$opposite_id   = isset($data['opposite_id']) && $data['opposite_id'] !== '' ? (int)$data['opposite_id'] : null;

// 4) SQL fijo y fácil de leer
$sql = "INSERT INTO en_vocab
  (english, pronunciation, spanish, pos, level, example_en, example_es, notes, opposite_id, source)
VALUES
  (:english, :pronunciation, :spanish, :pos, :level, :example_en, :example_es, :notes, :opposite_id, :source)";

try {
  $st = $pdo->prepare($sql);

  $st->bindValue(':english',       $english, PDO::PARAM_STR);
  $st->bindValue(':pronunciation', $pronunciation, $pronunciation === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':spanish',       $spanish, PDO::PARAM_STR);
  // si pos/level son null → aplica DEFAULT/NULL según el schema
  $st->bindValue(':pos',           $pos, $pos === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':level',         $level, $level === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':example_en',    $example_en, $example_en === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':example_es',    $example_es, $example_es === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':notes',         $notes, $notes === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
  $st->bindValue(':opposite_id',   $opposite_id, $opposite_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
  $st->bindValue(':source',        $source, $source === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

  $st->execute();
  $newId = (int)$pdo->lastInsertId();

  echo json_encode(['ok' => true, 'id' => $newId]);

} catch (PDOException $e) {
  if ($e->getCode() === '23000') { // UNIQUE o FK
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'La palabra (english) ya existe']);
  } else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error de servidor']);
  }
}



/*


Tips rápidos (estilo “práctico”)

Manténlo fijo: más fácil de leer y depurar.

Usa '' → NULL para opcionales, así no metes strings vacíos.

Deja que el DEFAULT 'expression' de pos actúe si no envías pos.

Controla duplicado con el código 23000 y responde 409.

Si mañana agregas un campo nuevo, solo añade una línea más en el INSERT y bindValue.

Si luego quieres, te paso el update() igual de sencillo (SET fijo) y listo.
*/





<?php
declare(strict_types=1);

use App\Validation\Validator;

// Supón que ya recibiste $in desde JSON o POST:
$isJson = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
$in     = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

// Arrays de enums permitidos según tu schema
$POS_ALLOWED   = ['verb','phrasal_verb','noun','adjective','adverb','expression'];
$LEVEL_ALLOWED = ['A1','A2','B1','B2','C1','C2'];

// Acumulador de errores
$errors = [];

// ========= VALIDACIONES =========

// Requeridos (devuelven string o agregan error)
$english = Validator::requireString($in['english'] ?? null, 'english', $errors);
$spanish = Validator::requireString($in['spanish'] ?? null, 'spanish', $errors);

// Opcionales (trimOrNull)
$pronunciation = Validator::trimOrNull($in['pronunciation'] ?? null);
$pos           = Validator::trimOrNull($in['pos'] ?? null);     // si queda null, aplicará DEFAULT en DB
$level         = Validator::trimOrNull($in['level'] ?? null);
$example_en    = Validator::trimOrNull($in['example_en'] ?? null);
$example_es    = Validator::trimOrNull($in['example_es'] ?? null);
$notes         = Validator::trimOrNull($in['notes'] ?? null);
$source        = Validator::trimOrNull($in['source'] ?? null);

// FK opcional (entero positivo o null)
$opposite_id   = Validator::positiveIntOrNull($in['opposite_id'] ?? null, 'opposite_id', $errors);

// Longitudes según columnas SQL
$english       = Validator::maxLength($english, 120);
$pronunciation = Validator::maxLength($pronunciation, 120);
$spanish       = Validator::maxLength($spanish, 180);
$example_en    = Validator::maxLength($example_en, 240);
$example_es    = Validator::maxLength($example_es, 240);
$notes         = Validator::maxLength($notes, 240);
$source        = Validator::maxLength($source, 120);

// Enums
$pos   = Validator::enum($pos,   'pos',   $POS_ALLOWED,   $errors);
$level = Validator::enum($level, 'level', $LEVEL_ALLOWED, $errors);

// (Opcional) sanitizar texto para almacenamiento simple
$english       = Validator::sanitize($english);
$pronunciation = Validator::sanitize($pronunciation);
$spanish       = Validator::sanitize($spanish);
$example_en    = Validator::sanitize($example_en);
$example_es    = Validator::sanitize($example_es);
$notes         = Validator::sanitize($notes);
$source        = Validator::sanitize($source);

// ========= SI HAY ERRORES, RESPONDE 422 =========
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========= CONSTRUIR PAYLOAD LIMPIO PARA EL MODELO =========
$clean = [
    'english'       => $english,        // requerido
    'pronunciation' => $pronunciation,  // opcional null
    'spanish'       => $spanish,        // requerido
    'pos'           => $pos,            // null → DEFAULT 'expression' en SQL
    'level'         => $level,          // opcional null
    'example_en'    => $example_en,     // opcional null
    'example_es'    => $example_es,     // opcional null
    'notes'         => $notes,          // opcional null
    'opposite_id'   => $opposite_id,    // null o int positivo
    'source'        => $source,         // opcional null
];

// Aquí llamas a tu modelo con datos ya validados/sanitizados:
// $id = (new InglesModel($pdo))->registro($clean);
// Response JSON...
