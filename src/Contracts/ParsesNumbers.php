<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Contracts;

/**
 * Documento cuyo número codifica datos (fecha de nacimiento, municipio, tipo).
 *
 * @template T of object
 */
interface ParsesNumbers
{
    /**
     * Componentes de un número válido, o null si no lo es. Son datos personales: no se
     * validan contra ningún registro, solo se leen de la estructura del número.
     *
     * @return T|null
     */
    public function parse(string $number): ?object;
}
