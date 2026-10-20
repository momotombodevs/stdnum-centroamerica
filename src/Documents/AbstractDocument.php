<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\ValidationError;
use StdNumCentroamerica\ValidationResult;

abstract class AbstractDocument implements DocumentInterface
{
    /** Expresión que debe cumplir el número compacto; la usa el isValid() por defecto. */
    protected const PATTERN = '';

    public function isValid(string $number): bool
    {
        return $this->validate($number)->isValid();
    }

    public function validate(string $number): ValidationResult
    {
        return ValidationResult::from($this->check($this->compact($number)));
    }

    /** Primer error de un número compacto, o null si es válido; por defecto solo comprueba PATTERN. */
    protected function check(string $compact): ?ValidationError
    {
        return preg_match(static::PATTERN, $compact) === 1 ? null : ValidationError::Format;
    }

    public function compact(string $number): string
    {
        return strtoupper(str_replace([' ', "\t", "\n", "\r", "\f", "\v", '-'], '', $number));
    }

    /** Sin agrupación propia, el formato es el número compacto. */
    public function format(string $number): string
    {
        return $this->compact($number);
    }

    /**
     * Agrupa el número compacto según los tamaños dados; si la longitud no coincide,
     * devuelve el número compacto sin cambios.
     *
     * @param int[] $sizes
     */
    protected function group(string $number, array $sizes, string $separator = '-'): string
    {
        $compact = $this->compact($number);

        if (strlen($compact) !== array_sum($sizes)) {
            return $compact;
        }

        $parts = [];
        $offset = 0;
        foreach ($sizes as $size) {
            $parts[] = substr($compact, $offset, $size);
            $offset += $size;
        }

        return implode($separator, $parts);
    }

    /** Separa con un guion el último carácter (verificador) del resto. */
    protected function groupLast(string $number): string
    {
        $compact = $this->compact($number);

        return strlen($compact) < 2 ? $compact : substr($compact, 0, -1) . '-' . substr($compact, -1);
    }

    /** @param int[] $weights un peso por dígito, de izquierda a derecha */
    protected static function weightedSum(string $digits, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $i => $weight) {
            $sum += (int) $digits[$i] * $weight;
        }

        return $sum;
    }
}
