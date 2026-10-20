<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

/** Alcance de la validación de un documento. */
enum Level: string
{
    /** Valida formato y dígito o letra de control. */
    case Complete = 'complete';

    /** Valida longitud, caracteres y rangos básicos, sin dígito de control. */
    case Format = 'format';
}
