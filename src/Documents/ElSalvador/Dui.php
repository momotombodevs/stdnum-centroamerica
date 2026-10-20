<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\ElSalvador;

use StdNumCentroamerica\Documents\AbstractDocument;

/** DUI (RNPN): 8 dígitos + verificador (pesos 9..2, módulo 10; fórmula de código abierto). Nivel: completa. */
class Dui extends AbstractDocument
{
    protected const PATTERN = '/^\d{9}$/';

    public function isValid(string $number): bool
    {
        $compact = $this->compact($number);

        if (!parent::isValid($compact)) {
            return false;
        }

        $sum = self::weightedSum($compact, range(9, 2));

        return (10 - $sum % 10) % 10 === (int) $compact[8];
    }

    public function format(string $number): string
    {
        return $this->group($number, [8, 1]);
    }
}
