<?php
//declare(strict_types=1);

namespace app\Core;

/**
 * Clase utilitaria de validaciones estáticas.
 * - Pensada para reuso en controladores/modelos.
 * - No depende de frameworks.
 * - Cada método devuelve un valor "limpio" o lanza/indica error a través de una API simple.
 *
 * Estilo de uso:
 *   - Validas campo por campo con métodos estáticos.
 *   - Vas acumulando errores en un array asociativo $errors['campo'] = 'mensaje'.
 *   - Si no hay errores, usas los valores ya normalizados en tu INSERT/UPDATE.
 */
final class Validator
{
    /**
     * Normaliza una entrada a string "limpio":
     * - Convierte a string.
     * - Aplica trim() para quitar espacios iniciales/finales.
     * - Si queda cadena vacía, devuelve null (opcional: útil para campos opcionales).
     *
     * @param mixed $value  Valor crudo (POST/JSON).
     * @return ?string      String recortado o null si vacío/no definido.
     */
    public static function trimOrNull(mixed $value): ?string
    {
        // Si no viene la clave o es null, devolvemos null
        if (!isset($value)) {
            return null;
        }

        // Convertimos a string (por si viene numero/boolean)
        $str = (string)$value;

        // Quitamos espacios alrededor
        $str = trim($str);

        // Si queda vacío, devolvemos null (para tratar como "sin valor")
        if ($str === '') {
            return null;
        }

        // Si hay contenido, devolvemos el string normalizado
        return $str;
    }

    /**
     * Valida que un string requerido NO sea null ni vacío.
     * Devuelve el string ya trim() si es válido, o agrega error.
     *
     * @param mixed  $value   Valor crudo.
     * @param string $field   Nombre del campo (para el array de errores).
     * @param array  &$errors Referencia a array de errores.
     * @return ?string        String válido o null si inválido (y se agregó error).
     */
    public static function requireString(mixed $value, string $field, array &$errors): ?string
    {
        // Normalizamos con trimOrNull
        $str = self::trimOrNull($value);

        // Si queda null, es un requerido faltante → añadimos error
        if ($str === null) {
            $errors[$field] = "{$field} es obligatorio";
            return null;
        }

        // Si tiene contenido, lo devolvemos
        return $str;
    }

    /**
     * Limita la longitud máxima de un string (si no es null).
     * Útil para garantizar que no exceda el tamaño de la columna SQL (VARCHAR).
     *
     * @param ?string $value  String o null.
     * @param int     $limit  Longitud máxima permitida.
     * @return ?string        String recortado o null si venía null.
     */
    public static function maxLength(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

		//INSTALAR php8.3-mbstring
        // mb_substr respeta UTF-8 y evita cortar mal caracteres multibyte
        return mb_substr($value, 0, $limit);
    }

    /**
     * Valida un enum:
     * - Si value es null → acepta (útil para campos opcionales).
     * - Si tiene valor, debe pertenecer al conjunto permitido $allowed.
     * Si no pertenece, agrega error.
     *
     * @param ?string $value
     * @param string  $field
     * @param array   $allowed
     * @param array   &$errors
     * @return ?string  Valor si válido, o null si inválido (con error).
     */
    public static function enum(?string $value, string $field, array $allowed, array &$errors): ?string
    {
        if ($value === null) {
            // Si es opcional y no vino, lo dejamos en null
            return null;
        }

        // Debe estar exactamente en la lista (comparación estricta)
        if (!in_array($value, $allowed, true)) {
            $errors[$field] = "{$field} inválido. Valores permitidos: " . implode(', ', $allowed);
            return null;
        }

        return $value;
    }

    /**
     * Convierte a entero o devuelve null si vacío/no válido.
     * Útil para FK opcionales (ej. opposite_id).
     *
     * @param mixed $value
     * @return ?int
     */
    public static function intOrNull(mixed $value): ?int
    {
        if (!isset($value) || $value === '') {
            return null;
        }

        // Validamos con filter_var para evitar strings no numéricos
        $valid = filter_var($value, FILTER_VALIDATE_INT);

        if ($valid === false) {
            return null;
        }

        return (int)$valid;
    }

    /**
     * Asegura que sea entero positivo (>0) o null; agrega error si viene inválido.
     * Común para IDs (FK) que deben ser positivos.
     *
     * @param mixed  $value
     * @param string $field
     * @param array  &$errors
     * @return ?int
     */
    public static function positiveIntOrNull(mixed $value, string $field, array &$errors): ?int
    {
        $i = self::intOrNull($value);

        if ($i === null) {
            // Null está permitido (campo opcional)
            return null;
        }

        if ($i <= 0) {
            $errors[$field] = "$field debe ser un entero positivo";
            return null;
        }

        return $i;
    }

    /**
     * Valida un float dentro de un rango (ambos extremos opcionales).
     * Devuelve null si entrada es vacía/no válida.
     *
     * @param mixed    $value
     * @param ?float   $min   Puede ser null si no hay límite inferior.
     * @param ?float   $max   Puede ser null si no hay límite superior.
     * @param string   $field
     * @param array    &$errors
     * @return ?float
     */
    public static function floatRangeOrNull(mixed $value, ?float $min, ?float $max, string $field, array &$errors): ?float
    {
        // Permite vacío → null
        if (!isset($value) || $value === '') {
            return null;
        }

        // Validamos float
        $f = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($f === false) {
            $errors[$field] = "$field debe ser numérico (float)";
            return null;
        }

        $f = (float)$f;

        // Rango inferior
        if ($min !== null && $f < $min) {
            $errors[$field] = "$field debe ser >= $min";
            return null;
        }

        // Rango superior
        if ($max !== null && $f > $max) {
            $errors[$field] = "$field debe ser <= $max";
            return null;
        }

        return $f;
    }

    /**
     * Valida un patrón (regex) sobre un string opcional.
     * Si es null, no valida nada; si tiene valor, debe cumplir pattern.
     *
     * @param ?string $value
     * @param string  $pattern  Delimitado, ej: '/^[a-z]+$/i'
     * @param string  $field
     * @param array   &$errors
     * @return ?string
     */
    public static function pattern(?string $value, string $pattern, string $field, array &$errors): ?string
    {
        if ($value === null) {
            return null;
        }

        if (@preg_match($pattern, '') === false) {
            // Patrones mal formados no deberían pasar a prod; avisamos claro
            $errors[$field] = "Pattern inválido para $field";
            return null;
        }

        if (!preg_match($pattern, $value)) {
            $errors[$field] = "$field no cumple el patrón esperado";
            return null;
        }

        return $value;
    }

    /**
     * Valida email opcional (si es null, lo acepta).
     *
     * @param ?string $value
     * @param string  $field
     * @param array   &$errors
     * @return ?string
     */
    public static function emailOrNull(?string $value, string $field, array &$errors): ?string
    {
        if ($value === null) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $errors[$field] = "$field no es un email válido";
            return null;
        }

        return $value;
    }

    /**
     * Valida una fecha (YYYY-MM-DD) opcional.
     *
     * @param ?string $value
     * @param string  $field
     * @param array   &$errors
     * @return ?string
     */
    public static function dateYmdOrNull(?string $value, string $field, array &$errors): ?string
    {
        if ($value === null) {
            return null;
        }

        // Chequeo básico de formato: 4-2-2 dígitos y guiones
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $errors[$field] = "$field debe tener formato YYYY-MM-DD";
            return null;
        }

        // Validación de fecha real (mes/día válidos)
        [$y, $m, $d] = array_map('intval', explode('-', $value));
        if (!checkdate($m, $d, $y)) {
            $errors[$field] = "$field no es una fecha válida";
            return null;
        }

        return $value;
    }

    /**
     * Convierte strings "true"/"false"/"1"/"0" a boolean o null si vacío.
     * Útil para checkboxes o flags enviados como texto.
     *
     * @param mixed $value
     * @return ?bool
     */
    public static function boolOrNull(mixed $value): ?bool
    {
        if (!isset($value) || $value === '') {
            return null;
        }

        // Normalizamos a string minúscula para comparar
        $s = strtolower((string)$value);

        if (in_array($s, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($s, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        // Si no coincide, lo consideramos nulo (o podrías tratarlo como error)
        return null;
    }

    /**
     * Sanitiza un string para almacenamiento simple:
     * - Recorta, opcionalmente elimina tags HTML (strip_tags), y puede convertir
     *   múltiples espacios en uno solo.
     *
     * @param ?string $value
     * @param bool    $stripTags
     * @param bool    $collapseSpaces
     * @return ?string
     */
    public static function sanitize(?string $value, bool $stripTags = true, bool $collapseSpaces = true): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = $value;

        if ($stripTags) {
            $s = strip_tags($s);
        }

        $s = trim($s);

        if ($collapseSpaces) {
            // Reemplaza secuencias de espacios/tabs/nuevas líneas por un solo espacio
            $s = preg_replace('/\s+/u', ' ', $s);
        }

        return $s === '' ? null : $s;
    }
}
