<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;

/** NITE (Tributación): 10 dígitos. Nivel: formato. */
class Nite extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^\d{10}$/';

    public function generate(): string
    {
        return self::randomDigits(10);
    }
}
