<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

/** Motivo por el que un número no es válido. */
enum ValidationError: string
{
    /** Longitud, caracteres o estructura incorrectos. */
    case Format = 'format';

    /** Un componente (fecha, departamento, municipio, prefijo) fuera de lo permitido. */
    case Component = 'component';

    /** El dígito o la letra de control no coincide. */
    case Checksum = 'checksum';
}
