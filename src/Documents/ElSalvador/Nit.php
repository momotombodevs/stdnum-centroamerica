<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\ElSalvador;

use StdNumCentroamerica\Documents\AbstractDocument;

/**
 * NIT (Ministerio de Hacienda), 0000-000000-000-0. Nivel: completa.
 * Los esquemas DTE aceptan 14 dígitos o 9 (el DUI, que sirve como NIT de las
 * personas naturales); el de 9 dígitos se valida como DUI.
 *
 * Verificador de 14 dígitos (fórmula de la librería abierta idsv-php; Hacienda no la
 * publica): con correlativo (posiciones 11-13) <= 100, pesos 14..2 y resto módulo 11
 * (10 pasa a 0); con correlativo mayor, pesos 2,7,6,5,4,3 repetidos y dígito 11 - resto
 * (0 si el resto es 0 o 1). Los tres NIT de muestra de la guía DTE de la DGII la cumplen.
 */
class Nit extends AbstractDocument
{
    public function __construct(private readonly Dui $dui = new Dui())
    {
    }

    public function isValid(string $number): bool
    {
        $compact = $this->compact($number);

        return match (strlen($compact)) {
            9 => $this->dui->isValid($compact),
            14 => ctype_digit($compact) && self::checkDigit($compact) === (int) $compact[13],
            default => false,
        };
    }

    public function format(string $number): string
    {
        return strlen($this->compact($number)) === 9
            ? $this->dui->format($number)
            : $this->group($number, [4, 6, 3, 1]);
    }

    private static function checkDigit(string $nit): int
    {
        if ((int) substr($nit, 10, 3) <= 100) {
            $remainder = self::weightedSum($nit, range(14, 2)) % 11;

            return $remainder === 10 ? 0 : $remainder;
        }

        $weights = array_map(fn (int $i) => [2, 7, 6, 5, 4, 3][$i % 6], range(0, 12));
        $remainder = self::weightedSum($nit, $weights) % 11;

        return $remainder > 1 ? 11 - $remainder : 0;
    }
}
