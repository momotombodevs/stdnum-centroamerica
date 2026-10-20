<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Nicaragua;

final class CedulaData
{
    /**
     * @param int $municipalityCode código de municipio (888: nacido en el extranjero)
     * @param int $birthYear2 año de nacimiento con dos dígitos; el siglo no está en el número
     * @param string $sequence consecutivo de cuatro dígitos
     */
    public function __construct(
        public readonly int $municipalityCode,
        public readonly int $birthDay,
        public readonly int $birthMonth,
        public readonly int $birthYear2,
        public readonly string $sequence,
        public readonly string $letter,
    ) {
    }

    /**
     * Años de nacimiento posibles: 19AA o 20AA, sin los futuros ni los que no tienen esa
     * fecha (29 de febrero en un año no bisiesto).
     *
     * @return list<int>
     */
    public function birthYearCandidates(?int $currentYear = null): array
    {
        $currentYear ??= (int) date('Y');

        return array_values(array_filter(
            [1900 + $this->birthYear2, 2000 + $this->birthYear2],
            fn (int $year): bool => $year <= $currentYear
                && checkdate($this->birthMonth, $this->birthDay, $year),
        ));
    }
}
