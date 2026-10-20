<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/**
 * MRZ de documento de identidad TD2: 2 líneas de 36 caracteres. ICAO Doc 9303, parte 6.
 * Línea 2: número (1-9) y su dígito, nacionalidad, nacimiento y su dígito, sexo,
 * vencimiento y su dígito, datos opcionales (29-35) y el compuesto (36).
 * Si el número pasa de nueve caracteres, la posición 10 es `<` y continúa en los datos
 * opcionales, seguido de su dígito.
 */
class Td2 extends AbstractMrz
{
    protected const PATTERN = '/^[A-Z0-9<]{72}$/';
    protected const DOCUMENT_CODES = 'ACI';
    protected const LINE_LENGTH = 36;
    protected const COMPOSITE_AT = 71;

    protected function fields(string $compact): MrzFields
    {
        $l1 = substr($compact, 0, 36);
        $l2 = substr($compact, 36, 36);
        [$number, $numberCheck, $optional] = self::longNumber(substr($l2, 0, 9), $l2[9], substr($l2, 28, 7));

        return new MrzFields(
            code: substr($l1, 0, 2),
            issuer: substr($l1, 2, 3),
            name: substr($l1, 5),
            number: $number,
            numberCheck: $numberCheck,
            nationality: substr($l2, 10, 3),
            birth: substr($l2, 13, 6),
            birthCheck: $l2[19],
            sex: $l2[20],
            expiry: substr($l2, 21, 6),
            expiryCheck: $l2[27],
            optional: $optional,
            optionalCheck: null,
            compositeCheck: $l2[35],
            compositeData: substr($l2, 0, 10) . substr($l2, 13, 7) . substr($l2, 21, 14),
        );
    }

    protected function draft(): string
    {
        $holder = self::randomHolder();

        return self::pad('I<UTO' . $holder['surname'] . '<<' . $holder['given'], 36)
            . $holder['number'] . self::checkDigit($holder['number']) . 'UTO'
            . $holder['birth'] . self::checkDigit($holder['birth']) . $holder['sex']
            . $holder['expiry'] . self::checkDigit($holder['expiry'])
            . self::pad('', 7) . '<';
    }
}
