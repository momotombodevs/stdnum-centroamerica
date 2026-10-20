<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Panama;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;
use StdNumCentroamerica\ValidationResult;

/**
 * RUC (DGI). Nivel: formato, sin el dígito verificador.
 * La ficha técnica de factura electrónica de la DGI define el RUC como alfanumérico de
 * 5 a 21 posiciones, con letras y guiones. El DV (2 dígitos) lo asigna el sistema de la
 * DGI y no publica fórmula, por lo que no se valida. Como en {@see Cedula}, la forma
 * canónica con guiones es format().
 *
 * Formas aceptadas:
 * - persona natural: la cédula.
 * - extranjeros con cédula PE, como los muestra la ficha: PE-P-TOMO-ASIENTO
 *   (PE-4-000-000) y P-PE-TOMO-ASIENTO (8-PE-3824-523). El Tribunal Electoral define
 *   la cédula PE con dos grupos.
 * - Número Tributario (extranjeros sin cédula): P-NT-TOMO-ASIENTO, p. ej. 8-NT-000-00
 *   (ejemplo de la ficha; la guía de terceros usa 3-NT-3-33).
 * - persona jurídica: tres grupos numéricos del Registro Público, p. ej.
 *   00023824-0930-212623 (ejemplo de la ficha). Longitud por grupo permisiva.
 */
class Ruc extends AbstractDocument implements GeneratesNumbers
{
    private const OTHER_FORMS = [
        '/^(?:PE-\d{1,6}-\d{1,6}-\d{1,6}|(?:1[0-3]|[1-9])-PE-\d{1,6}-\d{1,6})$/',
        '/^(?:1[0-3]|[1-9])-NT-\d{1,6}-\d{1,6}$/',
        '/^\d{1,10}-\d{1,6}-\d{1,8}$/',
    ];

    public function validate(string $number): ValidationResult
    {
        $normalized = Cedula::normalize($number);

        if (strlen($normalized) >= 5 && strlen($normalized) <= 21) {
            foreach ([Cedula::PATTERN, ...self::OTHER_FORMS] as $pattern) {
                if (preg_match($pattern, $normalized) === 1) {
                    return new ValidationResult();
                }
            }
        }

        return new ValidationResult(ValidationError::Format);
    }

    public function generate(): string
    {
        return (new Cedula())->generate();
    }

    public function format(string $number): string
    {
        return Cedula::normalize($number);
    }
}
