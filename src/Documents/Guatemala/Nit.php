<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Guatemala;

use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * NIT (SAT). Nivel: completa (fórmula de código abierto).
 * Módulo 11, verificador 0-9 o K. Desde 2023-10-04 los NIT nuevos son los primeros 9
 * dígitos del CUI; con cuerpo de 8 dígitos ambos verificadores coinciden (los pesos
 * 9..2 y 2..9 suman 11), por lo que no requiere regla aparte.
 */
class Nit extends AbstractDocument
{
    protected const PATTERN = '/^\d{1,11}[0-9K]$/';

    protected function check(string $compact): ?ValidationError
    {
        return parent::check($compact)
            ?? (self::checkDigit(substr($compact, 0, -1)) === substr($compact, -1) ? null : ValidationError::Checksum);
    }

    public function format(string $number): string
    {
        return $this->groupLast($number);
    }

    private static function checkDigit(string $body): string
    {
        $sum = self::weightedSum($body, range(strlen($body) + 1, 2));
        $digit = (11 - $sum % 11) % 11;

        return $digit === 10 ? 'K' : (string) $digit;
    }
}
