<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\ElSalvador;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;

/**
 * NRC (Ministerio de Hacienda): 1 a 8 dígitos sin guiones, según los esquemas DTE
 * (^[0-9]{1,8}$; la factura de consumidor exige al menos 2). Nivel: formato.
 */
class Nrc extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^\d{1,8}$/';

    public function format(string $number): string
    {
        return $this->groupLast($number);
    }

    public function generate(): string
    {
        return self::randomDigits(random_int(1, 8));
    }
}
