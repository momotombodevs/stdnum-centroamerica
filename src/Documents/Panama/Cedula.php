<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Contracts\ParsesNumbers;
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
 *
 * @implements ParsesNumbers<CedulaData>
 */
class Cedula extends AbstractDocument implements GeneratesNumbers, ParsesNumbers
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

    protected function prepare(string $number): string
    {
        return self::normalize($number);
    }

    public function generate(): string
    {
        return random_int(1, 9) . '-' . random_int(1, 999) . '-' . random_int(1, 99999);
    }

    public function parse(string $number): ?CedulaData
    {
        $normalized = $this->validated($number);
        if ($normalized === null) {
            return null;
        }

        $parts = explode('-', $normalized);

        return match ($parts[0]) {
            'PE' => new CedulaData(CedulaKind::BornAbroad, volume: $parts[1], entry: $parts[2]),
            'N' => new CedulaData(CedulaKind::Naturalized, volume: $parts[1], entry: $parts[2]),
            'E' => new CedulaData(CedulaKind::ForeignResident, provinceCode: (int) $parts[1], entry: $parts[2]),
            default => count($parts) === 4
                ? new CedulaData(CedulaKind::Birth, (int) $parts[0], $parts[1], $parts[2], $parts[3])
                : new CedulaData(CedulaKind::Birth, (int) $parts[0], volume: $parts[1], entry: $parts[2]),
        };
    }

    public function format(string $number): string
    {
        return self::normalize($number);
    }
}
