<?php

namespace app\Core;

/**
 * Clase utilitaria de validaciones estáticas.
 * - Pensada para reuso en controladores/modelos.
 * - No depende de frameworks.
 * - Cada método devuelve un valor "limpio" o agrega errores
 *   en un formato consistente:
 *
 *   $errors['campo'][] = 'mensaje';
 *
 * Ventajas:
 * - Permite múltiples errores por campo
 * - Compatible con Response::validation()
 * - Evita sobrescribir errores anteriores
 */
final class Validator
{
    /**
     * Agrega un error al arreglo de errores en formato consistente.
     *
     * @param array  &$errors
     * @param string $field
     * @param string $message
     * @return void
     */
    private static function addError(array &$errors, string $field, string $message): void
    {
        $errors[$field][] = $message;
        
    }

    /**
     * Normaliza una entrada a string "limpio":
     * - Convierte a string.
     * - Aplica trim() para quitar espacios iniciales/finales.
     * - Si queda cadena vacía, devuelve null.
     *
     * @param mixed $value
     * @return ?string
     */
    public static function trimOrNull(mixed $value): ?string
    {
        if (!isset($value)) {
            return null;
        }

        $str = (string)$value;
        $str = trim($str);

        if ($str === '') {
            return null;
        }

        return $str;
    }

    /**
     * Valida que un string requerido no sea null ni vacío.
     *
     * @param mixed  $value
     * @param string $field
     * @param array  &$errors
     * @return ?string
     */
    public static function requireString(mixed $value, string $field, array &$errors): ?string
    {
        $str = self::trimOrNull($value);

        if ($str === null) {
            self::addError($errors, $field, "{$field} es obligatorio CACHERO SAMUEL");
            return null;
        }

        return $str;
    }

    /**
     * Limita la longitud máxima de un string.
     *
     * @param ?string $value
     * @param int     $limit
     * @return ?string
     */
    public static function maxLength(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }

    /**
     * Valida un enum.
     *
     * @param ?string $value
     * @param string  $field
     * @param array   $allowed
     * @param array   &$errors
     * @return ?string
     */
    public static function enum(?string $value, string $field, array $allowed, array &$errors): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!in_array($value, $allowed, true)) {
            self::addError(
                $errors,
                $field,
                "{$field} inválido. Valores permitidos: " . implode(', ', $allowed)
            );
            return null;
        }

        return $value;
    }

    /**
     * Convierte a entero o devuelve null si vacío/no válido.
     *
     * @param mixed $value
     * @return ?int
     */
    public static function intOrNull(mixed $value): ?int
    {
        if (!isset($value) || $value === '') {
            return null;
        }

        $valid = filter_var($value, FILTER_VALIDATE_INT);

        if ($valid === false) {
            return null;
        }

        return (int)$valid;
    }

    /**
     * Asegura que sea entero positivo (>0) o null; agrega error si viene inválido.
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
            return null;
        }

        if ($i <= 0) {
            self::addError($errors, $field, "$field debe ser un entero positivo");
            return null;
        }

        return $i;
    }

    /**
     * Valida un float dentro de un rango.
     *
     * @param mixed   $value
     * @param ?float  $min
     * @param ?float  $max
     * @param string  $field
     * @param array   &$errors
     * @return ?float
     */
    public static function floatRangeOrNull(
        mixed $value,
        ?float $min,
        ?float $max,
        string $field,
        array &$errors
    ): ?float {
        if (!isset($value) || $value === '') {
            return null;
        }

        $f = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($f === false) {
            self::addError($errors, $field, "$field debe ser numérico (float)");
            return null;
        }

        $f = (float)$f;

        if ($min !== null && $f < $min) {
            self::addError($errors, $field, "$field debe ser >= $min");
            return null;
        }

        if ($max !== null && $f > $max) {
            self::addError($errors, $field, "$field debe ser <= $max");
            return null;
        }

        return $f;
    }

    /**
     * Valida un patrón regex sobre un string opcional.
     *
     * @param ?string $value
     * @param string  $pattern
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
            self::addError($errors, $field, "Pattern inválido para $field");
            return null;
        }

        if (!preg_match($pattern, $value)) {
            self::addError($errors, $field, "$field no cumple el patrón esperado");
            return null;
        }

        return $value;
    }

    /**
     * Valida email opcional.
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
            self::addError($errors, $field, "$field no es un email válido");
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

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            self::addError($errors, $field, "$field debe tener formato YYYY-MM-DD");
            return null;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $value));

        if (!checkdate($m, $d, $y)) {
            self::addError($errors, $field, "$field no es una fecha válida");
            return null;
        }

        return $value;
    }

    /**
     * Convierte strings "true"/"false"/"1"/"0" a boolean o null si vacío.
     *
     * @param mixed $value
     * @return ?bool
     */
    public static function boolOrNull(mixed $value): ?bool
    {
        if (!isset($value) || $value === '') {
            return null;
        }

        $s = strtolower((string)$value);

        if (in_array($s, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($s, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    /**
     * Sanitiza un string para almacenamiento simple.
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
            $s = preg_replace('/\s+/u', ' ', $s);
        }

        return $s === '' ? null : $s;
    }
		
	/**
	 * Valida que un valor sea obligatorio y un entero positivo (>0).
	 *
	 * Funciona para cualquier origen:
	 * - JSON
	 * - parámetros de ruta
	 * - variables internas
	 *
	 * @param mixed  $value
	 * @param string $field
	 * @param array  &$errors
	 * @return ?int
	 */
	public static function requirePositiveInt(mixed $value, string $field, array &$errors): ?int
	{
		// Si es null o vacío
		if ($value === null || $value === '') {
			self::addError($errors, $field, "$field es obligatorio");
			return null;
		}

		// Validar entero
		$valid = filter_var($value, FILTER_VALIDATE_INT);

		if ($valid === false) {
			self::addError($errors, $field, "$field debe ser un entero válido");
			return null;
		}

		$int = (int)$valid;

		// Validar positivo
		if ($int <= 0) {
			self::addError($errors, $field, "$field debe ser un entero positivo");
			return null;
		}

		return $int;
	}
}
