<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Contracts;

/**
 * Documento capaz de producir números sintéticos que pasan su propia validación.
 * Los rangos que elige (años, municipios) son decisión del generador, no reglas del documento.
 */
interface GeneratesNumbers
{
    /** Número aleatorio válido, sin separadores salvo que el documento los exija (Panamá). Puede coincidir por azar con uno real. */
    public function generate(): string;
}
