<?php

namespace StdNumCentroamerica\Documents\Honduras;

use StdNumCentroamerica\Data\HondurasGeografia;
use StdNumCentroamerica\Documents\AbstractDocument;

/**
 * Número de identidad (RNP): 13 dígitos, 0000-0000-00000.
 * Departamento (2) + municipio (2) + año de inscripción (4) + correlativo (5).
 *
 * Valida departamento 01-18, municipio dentro de la cantidad real del departamento
 * y año de inscripción no futuro. Acepta los códigos especiales oficiales 0880
 * (doble nacionalidad) y 0890 (naturalizados; el año es el del acuerdo).
 * No hay dígito verificador. Nivel: formato + geografía.
 */
class Identidad extends AbstractDocument
{
    public function isValid(string $number): bool
    {
        $compact = $this->compact($number);

        if (preg_match('/^\d{13}$/', $compact) !== 1) {
            return false;
        }

        $year = (int) substr($compact, 4, 4);
        if ($year < 1800 || $year > (int) date('Y')) {
            return false;
        }

        $code = substr($compact, 0, 4);
        if (in_array($code, HondurasGeografia::SPECIAL_CODES, true)) {
            return true;
        }

        $municipalities = HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO[(int) substr($code, 0, 2)] ?? 0;
        $municipality = (int) substr($code, 2, 2);

        return $municipality >= 1 && $municipality <= $municipalities;
    }

    public function format(string $number): string
    {
        return $this->group($number, [4, 4, 5]);
    }
}
