<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

use StdNumCentroamerica\Documents\AbstractDocument;

/**
 * Cédula de identidad personal (Tribunal Electoral). Nivel: formato; no tiene dígito
 * verificador. Tomo y asiento tienen longitud variable, así que los guiones forman
 * parte del número: la forma canónica es format() (normalize()); compact(), como en
 * el resto del paquete, quita los separadores y por eso pierde esa información.
 *
 * Formas oficiales (Decreto 53 de 1958, art. 9, según la página del Tribunal Electoral):
 * - nacimiento:              P-TOMO-ASIENTO    8-926-1601
 * - nacido en el extranjero: PE-TOMO-ASIENTO   PE-5-687
 * - naturalizado:            N-TOMO-ASIENTO    N-19-473
 * - extranjero domiciliado:  E-P-ORDEN         E-8-12345
 * La clave de provincia P es 1-9 (orden alfabético de las provincias).
 *
 * No oficial (fuentes de terceros): las claves 10-13 (comarcas y Panamá Oeste) y los
 * sufijos PI (indígena) y AV (anterior al Registro Civil) entre la provincia y el
 * tomo. Longitudes de tomo y asiento (1-6 dígitos): permisivas.
 */
class Cedula extends AbstractDocument
{
    private const PROVINCE = '(?:1[0-3]|[1-9])';

    public const PATTERN = '/^(?:'
        . self::PROVINCE . '-(?:(?:PI|AV)-)?\d{1,6}-\d{1,6}'
        . '|PE-\d{1,6}-\d{1,6}'
        . '|N-\d{1,6}-\d{1,6}'
        . '|E-' . self::PROVINCE . '-\d{1,7}'
        . ')$/';

    /** Mayúsculas, con un solo guion entre grupos. */
    public static function normalize(string $number): string
    {
        return strtoupper(preg_replace('/[\s-]+/', '-', trim($number)) ?? '');
    }

    public function isValid(string $number): bool
    {
        return preg_match(self::PATTERN, self::normalize($number)) === 1;
    }

    public function format(string $number): string
    {
        return self::normalize($number);
    }
}
