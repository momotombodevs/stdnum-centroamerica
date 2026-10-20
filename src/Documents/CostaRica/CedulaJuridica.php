<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Documents\AbstractDocument;

/** Cédula jurídica: 10 dígitos sin guiones (Hacienda, anexos v4.4), 0-000-000000. Nivel: formato. */
class CedulaJuridica extends AbstractDocument
{
    protected const PATTERN = '/^\d{10}$/';

    public function format(string $number): string
    {
        return $this->group($number, [1, 3, 6]);
    }
}
