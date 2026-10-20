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
 * nacimiento, vencimiento, datos opcionales (TD3) y el compuesto. Una fecha
 * desconocida (`<<<<<<`) lleva `<` o `0` como dígito.
 */
abstract class AbstractMrz extends AbstractDocument implements GeneratesNumbers
{
    /** Sin I ni O, que se confunden con 1 y 0. */
    private const NUMBER_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';

    private const SAMPLE_NAMES = ['ERIKSSON' => 'ANNA<MARIA', 'SAMPLE' => 'JOHN', 'EXAMPLE' => 'JANE<ROE'];

    /** Primeras letras admitidas en el código de documento. */
    protected const DOCUMENT_CODES = '';

    /** Longitud de cada línea. */
    protected const LINE_LENGTH = 0;

    /** Posición (desde 0) del dígito compuesto en la MRZ compacta. */
    protected const COMPOSITE_AT = 0;

    abstract protected function fields(string $compact): MrzFields;

    /** MRZ completa con `<` en lugar del dígito compuesto, que {@see generate()} calcula. */
    abstract protected function draft(): string;

    public function generate(): string
    {
        $draft = $this->draft();
        $composite = self::checkDigit($this->fields($draft)->compositeData);

        return substr_replace($draft, (string) $composite, static::COMPOSITE_AT, 1);
    }

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

    /**
     * Datos de la MRZ, o null si no es válida. En TD1 los dos campos de datos opcionales
     * se devuelven juntos.
     */
    public function parse(string $mrz): ?MrzData
    {
        $compact = $this->compact($mrz);
        if ($this->check($compact) !== null) {
            return null;
        }

        $fields = $this->fields($compact);
        [$surname, $given] = array_pad(explode('<<', rtrim($fields->name, '<'), 2), 2, '');

        return new MrzData(
            documentCode: rtrim($fields->code, '<'),
            issuingState: rtrim($fields->issuer, '<'),
            surname: trim(str_replace('<', ' ', $surname)),
            givenNames: trim(str_replace('<', ' ', $given)),
            documentNumber: rtrim($fields->number, '<'),
            nationality: rtrim($fields->nationality, '<'),
            birthDate: $fields->birth === '<<<<<<' ? null : $fields->birth,
            sex: $fields->sex === '<' ? null : $fields->sex,
            expiryDate: $fields->expiry === '<<<<<<' ? null : $fields->expiry,
            optionalData: rtrim($fields->optional, '<'),
        );
    }

    /** Cada línea en su renglón. */
    public function format(string $number): string
    {
        $compact = $this->compact($number);

        return strlen($compact) % static::LINE_LENGTH === 0
            ? implode("\n", str_split($compact, static::LINE_LENGTH))
            : $compact;
    }

    protected function checkContent(string $compact): ?ValidationError
    {
        $fields = $this->fields($compact);

        return $this->checkComponents($fields) ?? $this->checkDigits($fields);
    }

    /**
     * Con un número de más de nueve caracteres, la posición de su dígito lleva `<` y el resto
     * del número y su dígito van al inicio de los datos opcionales.
     *
     * @return array{string, string, string} número completo, su dígito y los datos opcionales restantes
     */
    protected static function longNumber(string $number, string $check, string $optional): array
    {
        if ($check !== '<') {
            return [$number, $check, $optional];
        }

        $head = explode('<', $optional, 2)[0];

        return $head === ''
            ? [$number, '<', $optional]
            : [$number . substr($head, 0, -1), substr($head, -1), substr($optional, strlen($head))];
    }

    /** Número de documento de ejemplo: nueve caracteres al azar. */
    protected static function randomNumber(): string
    {
        $last = strlen(self::NUMBER_ALPHABET) - 1;

        return implode('', array_map(
            static fn (): string => self::NUMBER_ALPHABET[random_int(0, $last)],
            range(1, 9),
        ));
    }

    /** @return array{number: string, birth: string, sex: string, expiry: string, surname: string, given: string} */
    protected static function randomHolder(): array
    {
        $surname = array_rand(self::SAMPLE_NAMES);

        return [
            'number' => self::randomNumber(),
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

    private function checkComponents(MrzFields $fields): ?ValidationError
    {
        $valid = str_contains(static::DOCUMENT_CODES, $fields->code[0])
            && preg_match('/^[A-Z<]$/', $fields->code[1]) === 1
            && preg_match('/^[A-Z<]{3}$/', $fields->issuer) === 1
            && preg_match('/^[A-Z<]{3}$/', $fields->nationality) === 1
            && preg_match('/^[A-Z][A-Z<]*$/', $fields->name) === 1
            && in_array($fields->sex, ['M', 'F', 'X', '<'], true)
            && self::validDate($fields->birth, $fields->birthCheck)
            && self::validDate($fields->expiry, $fields->expiryCheck);

        return $valid ? null : ValidationError::Component;
    }

    private function checkDigits(MrzFields $fields): ?ValidationError
    {
        $valid = self::matches($fields->number, $fields->numberCheck)
            && self::dateMatches($fields->birth, $fields->birthCheck)
            && self::dateMatches($fields->expiry, $fields->expiryCheck)
            && ($fields->optionalCheck === null || self::optionalMatches($fields->optional, $fields->optionalCheck))
            && self::matches($fields->compositeData, $fields->compositeCheck);

        return $valid ? null : ValidationError::Checksum;
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

    /** Una fecha desconocida (`<<<<<<`) ya se validó en {@see validDate()}. */
    private static function dateMatches(string $date, string $check): bool
    {
        return $date === '<<<<<<' || self::matches($date, $check);
    }

    /** Fecha AAMMDD válida, o desconocida (`<<<<<<` con dígito `<` o `0`). */
    private static function validDate(string $date, string $check): bool
    {
        if ($date === '<<<<<<') {
            return in_array($check, ['<', '0'], true);
        }

        return preg_match('/^\d{6}$/', $date) === 1
            && checkdate((int) substr($date, 2, 2), (int) substr($date, 4, 2), 2000);
    }
}
