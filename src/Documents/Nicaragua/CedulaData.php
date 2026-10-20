<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Nicaragua;

use StdNumCentroamerica\Data\NicaraguaMunicipios;

/**
 * Componentes de una cédula de Nicaragua. El municipio y la letra no se contrastan con el
 * CSE: su lista de códigos y la fórmula de la letra son de terceros (ver docs/fuentes.md).
 */
final class CedulaData
{
    /**
     * @param int $municipalityCode código de municipio; 888 son nacidos en el extranjero (fuente única)
     * @param int $birthDay día de nacimiento
     * @param int $birthMonth mes de nacimiento
     * @param int $birthYearTwoDigits año de nacimiento con dos dígitos; el siglo no está en el número
     * @param string $sequence consecutivo de cuatro dígitos
     * @param string $letter letra de control tal como aparece
     */
    public function __construct(
        public readonly int $municipalityCode,
        public readonly int $birthDay,
        public readonly int $birthMonth,
        public readonly int $birthYearTwoDigits,
        public readonly string $sequence,
        public readonly string $letter,
    ) {
    }

    /** Código 888: el número no indica un municipio. */
    public function isBornAbroad(): bool
    {
        return $this->municipalityCode === NicaraguaMunicipios::FOREIGN_CODE;
    }

    /**
     * Años de nacimiento posibles: 19AA o 20AA, sin los que dejarían la fecha en el futuro ni
     * los que no tienen ese día (29 de febrero en un año no bisiesto). Puede quedar vacía:
     * 29/02/01 no existe ni en 1901 ni en 2001.
     *
     * @param string|null $today fecha de referencia AAAAMMDD; por defecto, hoy
     * @return list<int>
     */
    public function birthYearCandidates(?string $today = null): array
    {
        $today ??= date('Ymd');

        return array_values(array_filter(
            [1900 + $this->birthYearTwoDigits, 2000 + $this->birthYearTwoDigits],
            fn (int $year): bool => checkdate($this->birthMonth, $this->birthDay, $year)
                && sprintf('%04d%02d%02d', $year, $this->birthMonth, $this->birthDay) <= $today,
        ));
    }
}
