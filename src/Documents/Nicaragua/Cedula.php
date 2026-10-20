<?php

namespace StdNumCentroamerica\Documents\Nicaragua;

use StdNumCentroamerica\Data\NicaraguaMunicipios;
use StdNumCentroamerica\Documents\AbstractDocument;

/**
 * Cédula de identidad (CSE): municipio (3) + fecha de nacimiento DDMMAA (6) +
 * consecutivo (4) + letra. Formato 000-000000-0000A. Ley 152, art. 31 define las
 * 13 cifras; no menciona la letra.
 *
 * Valida formato, fecha plausible, municipio en 001-640 (o 888, nacidos en el
 * extranjero) y la letra: 13 dígitos módulo 23 sobre ABCDEFGHJKLMNPQRSTUVWXY, de
 * fuentes abiertas y una cédula de muestra con MRZ consistente (ver README).
 * Opciones: $strictLetter = false desactiva la letra; $strictMunicipality exige un
 * código de la lista conocida, incompleta (ver {@see NicaraguaMunicipios}), por lo que
 * puede rechazar cédulas válidas. Se pasan a Registry::make() como opciones.
 */
class Cedula extends AbstractDocument
{
    private const LETTERS = 'ABCDEFGHJKLMNPQRSTUVWXY';

    public function __construct(
        private readonly bool $strictMunicipality = false,
        private readonly bool $strictLetter = true,
    ) {
    }

    public function isValid(string $number): bool
    {
        $compact = $this->compact($number);

        if (preg_match('/^(\d{3})(\d{2})(\d{2})\d{2}\d{4}([A-Z])$/', $compact, $m) !== 1) {
            return false;
        }

        [, $municipality, $day, $month, $letter] = $m;

        return $this->isKnownMunicipality((int) $municipality)
            && checkdate((int) $month, (int) $day, 2000)
            && (!$this->strictLetter || self::checkLetter(substr($compact, 0, 13)) === $letter);
    }

    public function format(string $number): string
    {
        return $this->group($number, [3, 6, 5]);
    }

    public static function checkLetter(string $digits): string
    {
        return self::LETTERS[(int) $digits % 23];
    }

    private function isKnownMunicipality(int $code): bool
    {
        if ($code === NicaraguaMunicipios::FOREIGN_CODE) {
            return true;
        }

        return $code >= 1 && $code <= NicaraguaMunicipios::MAX_CODE
            && (!$this->strictMunicipality || in_array($code, NicaraguaMunicipios::CODES, true));
    }
}
