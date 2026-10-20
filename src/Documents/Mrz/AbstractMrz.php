<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Mrz;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * Zona de lectura mecánica (MRZ) de ICAO Doc 9303. Nivel: completa (dígitos de control).
 * Se recibe con o sin saltos de línea: las líneas tienen longitud fija, así que
 * compact() las concatena. Los pesos son 7-3-1; las letras valen A=10 … Z=35 y `<` vale 0.
 *
 * Valida el formato, los componentes (tipo de documento, sexo, fechas, caracteres de
 * emisor, nacionalidad y nombre) y los dígitos de control del número de documento,
 * nacimiento, vencimiento, datos opcionales (TD3) y el compuesto. Con un número de
 * documento de más de nueve caracteres no se comprueba su dígito propio, porque el
 * dígito compuesto ya cubre esas posiciones.
 */
abstract class AbstractMrz extends AbstractDocument implements GeneratesNumbers
{
    private const SAMPLE_NAMES = ['ERIKSSON' => 'ANNA<MARIA', 'SAMPLE' => 'JOHN', 'EXAMPLE' => 'JANE<ROE'];

    /** Primeras letras admitidas en el código de documento (P pasaporte; A, C, I identidad). */
    protected const DOCUMENT_CODES = '';

    /** Longitud de cada línea. */
    protected const LINE_LENGTH = 0;

    /**
     * Campos de la MRZ compacta. Los `*Check` son el carácter de control tal como aparece;
     * `numberCheck` es null cuando el número continúa en los datos opcionales (`overflow`).
     *
     * @return array{
     *     code: string, issuer: string, name: string, number: string, numberCheck: ?string,
     *     overflow: string, nationality: string, birth: string, birthCheck: string, sex: string,
     *     expiry: string, expiryCheck: string, optional: string, optionalCheck: ?string,
     *     compositeCheck: string, compositeData: string
     * }
     */
    abstract protected function fields(string $compact): array;

    abstract public function generate(): string;

    /** Dígito de control ICAO de una cadena de caracteres `A-Z`, `0-9` y `<`. */
    public static function checkDigit(string $field): int
    {
        $weights = [7, 3, 1];
        $sum = 0;
        foreach (str_split($field) as $i => $char) {
            $value = ctype_digit($char) ? (int) $char : ($char === '<' ? 0 : ord($char) - 55);
            $sum += $value * $weights[$i % 3];
        }

        return $sum % 10;
    }

    /** Datos de la MRZ, o null si no es válida. */
    public function parse(string $mrz): ?MrzData
    {
        $compact = $this->compact($mrz);
        if ($this->check($compact) !== null) {
            return null;
        }

        $f = $this->fields($compact);
        [$surname, $given] = array_pad(explode('<<', rtrim($f['name'], '<'), 2), 2, '');

        return new MrzData(
            documentCode: rtrim($f['code'], '<'),
            issuingState: rtrim($f['issuer'], '<'),
            surname: trim(str_replace('<', ' ', $surname)),
            givenNames: trim(str_replace('<', ' ', $given)),
            documentNumber: rtrim($f['number'], '<') . $f['overflow'],
            nationality: rtrim($f['nationality'], '<'),
            birthDate: str_contains($f['birth'], '<') ? null : $f['birth'],
            sex: $f['sex'] === '<' ? null : $f['sex'],
            expiryDate: str_contains($f['expiry'], '<') ? null : $f['expiry'],
            optionalData: rtrim($f['optional'], '<'),
        );
    }

    /** Cada línea en su renglón. */
    public function format(string $number): string
    {
        $compact = $this->compact($number);
        $length = static::LINE_LENGTH;

        return $length > 0 && strlen($compact) % $length === 0
            ? implode("\n", str_split($compact, $length))
            : $compact;
    }

    protected function checkContent(string $compact): ?ValidationError
    {
        $f = $this->fields($compact);

        return $this->checkComponents($f) ?? $this->checkDigits($f);
    }

    /**
     * Caracteres del número de documento que continúan en los datos opcionales: los que
     * van antes del primer `<`, sin el último, que es su dígito de control.
     */
    protected static function continuation(string $optional): string
    {
        $head = explode('<', $optional, 2)[0];

        return substr($head, 0, -1);
    }

    /** @param array<string, mixed> $f */
    private function checkComponents(array $f): ?ValidationError
    {
        $valid = str_contains(static::DOCUMENT_CODES, $f['code'][0])
            && preg_match('/^[A-Z<]$/', $f['code'][1]) === 1
            && preg_match('/^[A-Z<]{3}$/', $f['issuer']) === 1
            && preg_match('/^[A-Z<]{3}$/', $f['nationality']) === 1
            && preg_match('/^[A-Z<]+$/', $f['name']) === 1
            && in_array($f['sex'], ['M', 'F', 'X', '<'], true)
            && self::validDate($f['birth'], $f['birthCheck'])
            && self::validDate($f['expiry'], $f['expiryCheck']);

        return $valid ? null : ValidationError::Component;
    }

    /** @param array<string, mixed> $f */
    private function checkDigits(array $f): ?ValidationError
    {
        $ok = ($f['numberCheck'] === null || self::matches($f['number'], $f['numberCheck']))
            && (str_starts_with($f['birth'], '<') || self::matches($f['birth'], $f['birthCheck']))
            && (str_starts_with($f['expiry'], '<') || self::matches($f['expiry'], $f['expiryCheck']))
            && ($f['optionalCheck'] === null || self::optionalMatches($f['optional'], $f['optionalCheck']))
            && self::matches($f['compositeData'], $f['compositeCheck']);

        return $ok ? null : ValidationError::Checksum;
    }

    private static function matches(string $field, string $check): bool
    {
        return (string) self::checkDigit($field) === $check;
    }

    /** Con el campo vacío (todo `<`) el dígito puede ser `<` o `0`. */
    private static function optionalMatches(string $field, string $check): bool
    {
        return trim($field, '<') === '' ? in_array($check, ['<', '0'], true) : self::matches($field, $check);
    }

    /** Fecha AAMMDD válida, o desconocida (`<<<<<<` con dígito `<`). */
    private static function validDate(string $date, string $check): bool
    {
        if ($date === '<<<<<<') {
            return $check === '<';
        }

        return preg_match('/^\d{6}$/', $date) === 1
            && checkdate((int) substr($date, 2, 2), (int) substr($date, 4, 2), 2000);
    }

    /** @return array{number: string, birth: string, sex: string, expiry: string, surname: string, given: string} */
    protected static function randomHolder(): array
    {
        $surname = array_rand(self::SAMPLE_NAMES);

        return [
            'number' => implode('', array_map(
                static fn (): string => 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789'[random_int(0, 33)],
                range(1, 9),
            )),
            'birth' => sprintf('%02d%02d%02d', random_int(50, 99), random_int(1, 12), random_int(1, 28)),
            'sex' => ['M', 'F', '<'][random_int(0, 2)],
            'expiry' => sprintf('%02d%02d%02d', random_int(27, 40), random_int(1, 12), random_int(1, 28)),
            'surname' => $surname,
            'given' => self::SAMPLE_NAMES[$surname],
        ];
    }

    /** `<` hasta completar $length. */
    protected static function pad(string $text, int $length): string
    {
        return str_pad($text, $length, '<');
    }
}
