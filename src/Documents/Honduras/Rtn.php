<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Honduras;

use StdNumCentroamerica\Documents\AbstractDocument;

/** RTN (SAR): 14 dígitos; en personas naturales es el número de identidad + 1 dígito. Nivel: formato. */
class Rtn extends AbstractDocument
{
    protected const PATTERN = '/^\d{14}$/';
}
