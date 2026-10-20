<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Documents\AbstractDocument;

/** NITE (Tributación): 10 dígitos. Nivel: formato. */
class Nite extends AbstractDocument
{
    protected const PATTERN = '/^\d{10}$/';
}
