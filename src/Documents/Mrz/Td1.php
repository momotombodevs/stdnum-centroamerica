<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

/**
 * MRZ de cédula o tarjeta de identidad (TD1): 3 líneas de 30 caracteres. ICAO Doc 9303, parte 5.
 * Línea 1: tipo, emisor, número (6-14) y su dígito (15), datos opcionales (16-30).
 * Línea 2: nacimiento y su dígito, sexo, vencimiento y su dígito, nacionalidad, datos
 * opcionales (19-29) y el compuesto (30). Línea 3: nombre.
 * Si el número pasa de nueve caracteres, la posición 15 es `<` y continúa en los datos
 * opcionales de la línea 1, seguido de su dígito.
 */
class Td1 extends AbstractMrz
{
    protected const PATTERN = '/^[A-Z0-9<]{90}$/';
    protected const DOCUMENT_CODES = 'ACI';
    protected const LINE_LENGTH = 30;

    protected function fields(string $compact): array
    {
        $l1 = substr($compact, 0, 30);
        $l2 = substr($compact, 30, 30);
        $optional1 = substr($l1, 15, 15);
        $overflow = $l1[14] === '<' ? self::continuation($optional1) : '';

        return [
            'code' => substr($l1, 0, 2),
            'issuer' => substr($l1, 2, 3),
            'name' => substr($compact, 60, 30),
            'number' => substr($l1, 5, 9),
            'numberCheck' => $l1[14] === '<' ? null : $l1[14],
            'overflow' => $overflow,
            'nationality' => substr($l2, 15, 3),
            'birth' => substr($l2, 0, 6),
            'birthCheck' => $l2[6],
            'sex' => $l2[7],
            'expiry' => substr($l2, 8, 6),
            'expiryCheck' => $l2[14],
            'optional' => $optional1 . substr($l2, 18, 11),
            'optionalCheck' => null,
            'compositeCheck' => $l2[29],
            'compositeData' => substr($l1, 5, 25) . substr($l2, 0, 7) . substr($l2, 8, 7) . substr($l2, 18, 11),
        ];
    }

    public function generate(): string
    {
        $h = self::randomHolder();
        $l1 = 'I<UTO' . $h['number'] . self::checkDigit($h['number']) . self::pad('', 15);
        $l2 = $h['birth'] . self::checkDigit($h['birth']) . $h['sex']
            . $h['expiry'] . self::checkDigit($h['expiry']) . 'UTO' . self::pad('', 11);
        $composite = self::checkDigit(substr($l1, 5, 25) . substr($l2, 0, 7) . substr($l2, 8, 7) . substr($l2, 18, 11));

        return $l1 . $l2 . $composite . self::pad($h['surname'] . '<<' . $h['given'], 30);
    }
}
