<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/**
 * MRZ de pasaporte (TD3): 2 líneas de 44 caracteres. ICAO Doc 9303, parte 4.
 * Línea 2: número (1-9) y su dígito, nacionalidad, nacimiento y su dígito, sexo,
 * vencimiento y su dígito, datos opcionales (29-42) con su dígito (43) y el compuesto (44).
 */
class Td3 extends AbstractMrz
{
    protected const PATTERN = '/^[A-Z0-9<]{88}$/';
    protected const DOCUMENT_CODES = 'P';
    protected const LINE_LENGTH = 44;
    protected const COMPOSITE_AT = 87;

    protected function fields(string $compact): MrzFields
    {
        $l1 = substr($compact, 0, 44);
        $l2 = substr($compact, 44, 44);

        return new MrzFields(
            code: substr($l1, 0, 2),
            issuer: substr($l1, 2, 3),
            name: substr($l1, 5),
            number: substr($l2, 0, 9),
            numberCheck: $l2[9],
            nationality: substr($l2, 10, 3),
            birth: substr($l2, 13, 6),
            birthCheck: $l2[19],
            sex: $l2[20],
            expiry: substr($l2, 21, 6),
            expiryCheck: $l2[27],
            optional: substr($l2, 28, 14),
            optionalCheck: $l2[42],
            compositeCheck: $l2[43],
            compositeData: substr($l2, 0, 10) . substr($l2, 13, 7) . substr($l2, 21, 22),
        );
    }

    protected function draft(): string
    {
        $holder = self::randomHolder();
        $optional = random_int(0, 1) === 1 ? self::pad(self::randomDigits(9), 14) : self::pad('', 14);
        $optionalCheck = trim($optional, '<') === '' ? '<' : (string) self::checkDigit($optional);

        return self::pad('P<UTO' . $holder['surname'] . '<<' . $holder['given'], 44)
            . $holder['number'] . self::checkDigit($holder['number']) . 'UTO'
            . $holder['birth'] . self::checkDigit($holder['birth']) . $holder['sex']
            . $holder['expiry'] . self::checkDigit($holder['expiry'])
            . $optional . $optionalCheck . '<';
    }
}
