<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;

/** DIMEX (Migración): 11 o 12 dígitos sin cero inicial. Nivel: formato. */
class Dimex extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^[1-9]\d{10,11}$/';

    public function generate(): string
    {
        return self::randomDigits(random_int(11, 12), true);
    }
}
