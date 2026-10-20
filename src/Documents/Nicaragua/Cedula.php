<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Nicaragua;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Contracts\ParsesNumbers;
use StdNumCentroamerica\Data\NicaraguaMunicipios;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

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
 *
 * @implements ParsesNumbers<CedulaData>
 */
class Cedula extends AbstractDocument implements GeneratesNumbers, ParsesNumbers
{
    private const LETTERS = 'ABCDEFGHJKLMNPQRSTUVWXY';

    public function __construct(
        private readonly bool $strictMunicipality = false,
        private readonly bool $strictLetter = true,
    ) {
    }

    protected function check(string $compact): ?ValidationError
    {
        if (preg_match('/^(\d{3})(\d{2})(\d{2})\d{2}\d{4}([A-Z])$/', $compact, $m) !== 1) {
            return ValidationError::Format;
        }

        [, $municipality, $day, $month, $letter] = $m;

        if (!$this->isKnownMunicipality((int) $municipality) || !checkdate((int) $month, (int) $day, 2000)) {
            return ValidationError::Component;
        }

        return !$this->strictLetter || self::checkLetter(substr($compact, 0, 13)) === $letter
            ? null
            : ValidationError::Checksum;
    }

    public function generate(): string
    {
        $municipality = NicaraguaMunicipios::CODES[array_rand(NicaraguaMunicipios::CODES)];
        $digits = sprintf('%03d%02d%02d%02d', $municipality, random_int(1, 28), random_int(1, 12), random_int(0, 99))
            . self::randomDigits(4);

        return $digits . self::checkLetter($digits);
    }

    public function parse(string $number): ?CedulaData
    {
        $compact = $this->validated($number);
        if ($compact === null) {
            return null;
        }

        return new CedulaData(
            municipalityCode: (int) substr($compact, 0, 3),
            birthDay: (int) substr($compact, 3, 2),
            birthMonth: (int) substr($compact, 5, 2),
            birthYearTwoDigits: (int) substr($compact, 7, 2),
            sequence: substr($compact, 9, 4),
            letter: $compact[13],
        );
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
