<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Guatemala;

final class DpiData
{
    /**
     * @param string $serial los ocho dígitos del número de registro
     * @param int $departmentCode departamento de vecindad 1-22
     * @param int $municipalityCode municipio dentro del departamento
     */
    public function __construct(
        public readonly string $serial,
        public readonly int $departmentCode,
        public readonly int $municipalityCode,
    ) {
    }
}
