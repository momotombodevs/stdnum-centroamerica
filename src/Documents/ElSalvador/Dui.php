<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\ElSalvador;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/** DUI (RNPN): 8 dígitos + verificador (pesos 9..2, módulo 10; fórmula de código abierto). Nivel: completa. */
class Dui extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^\d{9}$/';

    protected function checkContent(string $compact): ?ValidationError
    {
        return self::checkDigit($compact) === (int) $compact[8] ? null : ValidationError::Checksum;
    }

    public function generate(): string
    {
        $body = self::randomDigits(8);

        return $body . self::checkDigit($body);
    }

    public function format(string $number): string
    {
        return $this->group($number, [8, 1]);
    }

    /** Verificador de los 8 primeros dígitos. */
    private static function checkDigit(string $digits): int
    {
        return (10 - self::weightedSum($digits, range(9, 2)) % 10) % 10;
    }
}
