<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Documents\AbstractDocument;

/** Cédula de identidad (TSE): 9 dígitos sin cero inicial, 0-0000-0000. Nivel: formato. */
class CedulaFisica extends AbstractDocument
{
    protected const PATTERN = '/^[1-9]\d{8}$/';

    public function format(string $number): string
    {
        return $this->group($number, [1, 4, 4]);
    }
}
