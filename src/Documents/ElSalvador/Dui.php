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

    protected function check(string $compact): ?ValidationError
    {
        if ($error = parent::check($compact)) {
            return $error;
        }

        $sum = self::weightedSum($compact, range(9, 2));

        return (10 - $sum % 10) % 10 === (int) $compact[8] ? null : ValidationError::Checksum;
    }

    public function generate(): string
    {
        $body = self::randomDigits(8);

        return $body . (10 - self::weightedSum($body, range(9, 2)) % 10) % 10;
    }

    public function format(string $number): string
    {
        return $this->group($number, [8, 1]);
    }
}
