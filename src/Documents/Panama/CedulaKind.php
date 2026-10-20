<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

enum CedulaKind
{
    /** Nacido en Panamá: P-TOMO-ASIENTO. */
    case Birth;

    /** Nacido en el extranjero: PE-TOMO-ASIENTO. */
    case BornAbroad;

    /** Naturalizado: N-TOMO-ASIENTO. */
    case Naturalized;

    /** Extranjero domiciliado: E-P-ORDEN. */
    case ForeignResident;
}
