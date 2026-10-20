<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Contracts;

/** Documento capaz de producir números sintéticos que pasan su propia validación. */
interface GeneratesNumbers
{
    /** Número aleatorio válido, en forma compacta; no corresponde a una persona real. */
    public function generate(): string;
}
