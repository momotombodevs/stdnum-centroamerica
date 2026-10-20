<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Guatemala;

use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * DPI / CUI (RENAP): 8 dígitos + verificador + departamento (2) + municipio (2).
 * Nivel: completa en el verificador (pesos 2..9, módulo 11; fórmula de código abierto)
 * y en el departamento (01-22). No se valida la tabla de municipios por departamento.
 */
class Dpi extends AbstractDocument
{
    protected const PATTERN = '/^\d{13}$/';

    protected function check(string $compact): ?ValidationError
    {
        if ($error = parent::check($compact)) {
            return $error;
        }

        $department = (int) substr($compact, 9, 2);
        $municipality = (int) substr($compact, 11, 2);

        if ($department < 1 || $department > 22 || $municipality < 1) {
            return ValidationError::Component;
        }

        return self::weightedSum($compact, range(2, 9)) % 11 === (int) $compact[8] ? null : ValidationError::Checksum;
    }

    public function format(string $number): string
    {
        return $this->group($number, [4, 5, 4], ' ');
    }
}
