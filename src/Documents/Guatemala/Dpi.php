<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Guatemala;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * DPI / CUI (RENAP): 8 dígitos + verificador + departamento (2) + municipio (2).
 * Nivel: completa en el verificador (pesos 2..9, módulo 11; fórmula de código abierto)
 * y en el departamento (01-22). No se valida la tabla de municipios por departamento.
 */
class Dpi extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^\d{13}$/';

    private const DEPARTMENTS = 22;

    protected function checkContent(string $compact): ?ValidationError
    {
        $department = (int) substr($compact, 9, 2);
        $municipality = (int) substr($compact, 11, 2);

        if ($department < 1 || $department > self::DEPARTMENTS || $municipality < 1) {
            return ValidationError::Component;
        }

        return self::checkDigit($compact) === (int) $compact[8] ? null : ValidationError::Checksum;
    }

    public function generate(): string
    {
        do {
            $body = self::randomDigits(8);
        } while (self::checkDigit($body) === 10);

        return $body . self::checkDigit($body)
            . sprintf('%02d%02d', random_int(1, self::DEPARTMENTS), random_int(1, 20));
    }

    public function format(string $number): string
    {
        return $this->group($number, [4, 5, 4], ' ');
    }

    /** Verificador de los 8 primeros dígitos; 10 no es un dígito y nunca aparece en un CUI. */
    private static function checkDigit(string $digits): int
    {
        return self::weightedSum($digits, range(2, 9)) % 11;
    }
}
