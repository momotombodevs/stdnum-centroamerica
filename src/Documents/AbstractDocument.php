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
        return new ValidationResult($this->check($this->prepare($number)));
    }

    /** La forma validada del número, o null si no es válido: punto de partida de parse(). */
    protected function validated(string $number): ?string
    {
        $prepared = $this->prepare($number);

        return $this->check($prepared) === null ? $prepared : null;
    }

    /** Forma sobre la que se valida; por defecto la compacta. Panamá conserva los guiones. */
    protected function prepare(string $number): string
    {
        return $this->compact($number);
    }

    /**
     * Primer error de un número compacto, o null si es válido. Por defecto exige PATTERN y
     * luego las reglas de {@see checkContent()}; los documentos sin PATTERN lo sobrescriben.
     */
    protected function check(string $compact): ?ValidationError
    {
        return preg_match(static::PATTERN, $compact) === 1 ? $this->checkContent($compact) : ValidationError::Format;
    }

    /** Reglas adicionales (componentes, verificador) de un número que ya cumple PATTERN. */
    protected function checkContent(string $compact): ?ValidationError
    {
        return null;
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

    /** Cadena de $length dígitos al azar; $nonZeroFirst evita el cero inicial. */
    protected static function randomDigits(int $length, bool $nonZeroFirst = false): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= random_int($i === 0 && $nonZeroFirst ? 1 : 0, 9);
        }

        return $digits;
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
