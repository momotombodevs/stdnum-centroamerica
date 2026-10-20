<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Honduras;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Data\HondurasGeografia;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * Número de identidad (RNP): 13 dígitos, 0000-0000-00000.
 * Departamento (2) + municipio (2) + año de inscripción (4) + correlativo (5).
 *
 * Valida departamento 01-18, municipio dentro de la cantidad real del departamento
 * y año de inscripción no futuro. Acepta los códigos especiales oficiales 0880
 * (doble nacionalidad) y 0890 (naturalizados; el año es el del acuerdo).
 * No hay dígito verificador. Nivel: formato + geografía.
 */
class Identidad extends AbstractDocument implements GeneratesNumbers
{
    protected const PATTERN = '/^\d{13}$/';

    protected function checkContent(string $compact): ?ValidationError
    {
        $year = (int) substr($compact, 4, 4);
        if ($year < 1800 || $year > (int) date('Y')) {
            return ValidationError::Component;
        }

        $code = substr($compact, 0, 4);
        if (in_array($code, HondurasGeografia::SPECIAL_CODES, true)) {
            return null;
        }

        $municipalities = HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO[(int) substr($code, 0, 2)] ?? 0;
        $municipality = (int) substr($code, 2, 2);

        return $municipality >= 1 && $municipality <= $municipalities ? null : ValidationError::Component;
    }

    public function generate(): string
    {
        $department = random_int(1, 18);
        $municipality = random_int(1, HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO[$department]);

        return sprintf('%02d%02d%04d', $department, $municipality, random_int(1950, 2005)) . self::randomDigits(5);
    }

    public function format(string $number): string
    {
        return $this->group($number, [4, 4, 5]);
    }
}
