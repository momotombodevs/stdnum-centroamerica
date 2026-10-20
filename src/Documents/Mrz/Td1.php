<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/**
 * MRZ de cédula o tarjeta de identidad (TD1): 3 líneas de 30 caracteres. ICAO Doc 9303, parte 5.
 * Línea 1: tipo, emisor, número (6-14) y su dígito (15), datos opcionales (16-30).
 * Línea 2: nacimiento y su dígito, sexo, vencimiento y su dígito, nacionalidad, datos
 * opcionales (19-29) y el compuesto (30). Línea 3: nombre.
 * Si el número pasa de nueve caracteres, la posición 15 es `<` y continúa en los datos
 * opcionales de la línea 1, seguido de su dígito. Acepta también el tipo `P` de las
 * tarjetas pasaporte con este formato.
 */
class Td1 extends AbstractMrz
{
    protected const PATTERN = '/^[A-Z0-9<]{90}$/';
    protected const DOCUMENT_CODES = 'ACIP';
    protected const LINE_LENGTH = 30;
    protected const COMPOSITE_AT = 59;

    protected function fields(string $compact): MrzFields
    {
        $l1 = substr($compact, 0, 30);
        $l2 = substr($compact, 30, 30);
        [$number, $numberCheck, $optional1] = self::longNumber(substr($l1, 5, 9), $l1[14], substr($l1, 15, 15));

        return new MrzFields(
            code: substr($l1, 0, 2),
            issuer: substr($l1, 2, 3),
            name: substr($compact, 60, 30),
            number: $number,
            numberCheck: $numberCheck,
            nationality: substr($l2, 15, 3),
            birth: substr($l2, 0, 6),
            birthCheck: $l2[6],
            sex: $l2[7],
            expiry: substr($l2, 8, 6),
            expiryCheck: $l2[14],
            optional: $optional1 . substr($l2, 18, 11),
            optionalCheck: null,
            compositeCheck: $l2[29],
            compositeData: substr($l1, 5, 25) . substr($l2, 0, 7) . substr($l2, 8, 7) . substr($l2, 18, 11),
        );
    }

    protected function draft(): string
    {
        $holder = self::randomHolder();

        return 'I<UTO' . $holder['number'] . self::checkDigit($holder['number']) . self::pad('', 15)
            . $holder['birth'] . self::checkDigit($holder['birth']) . $holder['sex']
            . $holder['expiry'] . self::checkDigit($holder['expiry']) . 'UTO' . self::pad('', 11) . '<'
            . self::pad($holder['surname'] . '<<' . $holder['given'], 30);
    }
}
