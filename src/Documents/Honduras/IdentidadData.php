<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Honduras;

final class IdentidadData
{
    /**
     * @param int|null $departmentCode departamento 1-18; null con un código especial
     * @param int|null $municipalityCode municipio dentro del departamento; null con un código especial
     * @param int $registrationYear año de inscripción (en los naturalizados, el del acuerdo)
     * @param string|null $specialCode `0880` (doble nacionalidad) o `0890` (naturalizados)
     */
    public function __construct(
        public readonly ?int $departmentCode,
        public readonly ?int $municipalityCode,
        public readonly int $registrationYear,
        public readonly string $sequence,
        public readonly ?string $specialCode,
    ) {
    }
}
