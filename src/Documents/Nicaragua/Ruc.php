<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Documents\Nicaragua;

use StdNumCentroamerica\Contracts\GeneratesNumbers;
use StdNumCentroamerica\Documents\AbstractDocument;
use StdNumCentroamerica\ValidationError;

/**
 * RUC (DGI), 14 caracteres: personas jurídicas J + 13 dígitos; nacionales sin cédula
 * N, residentes extranjeros R, no residentes E (+ 13 dígitos); nacionales con cédula
 * usan el número de cédula. Los que inician con cuatro ceros no son válidos.
 * Fuente: DGI, AVISO/072/05-2022 (Disposición Administrativa General 01-2013).
 * Nivel: formato.
 */
class Ruc extends AbstractDocument implements GeneratesNumbers
{
    public function __construct(private readonly Cedula $cedula = new Cedula())
    {
    }

    protected function check(string $compact): ?ValidationError
    {
        if (str_starts_with($compact, '0000')) {
            return ValidationError::Component;
        }

        return preg_match('/^[JNRE]\d{13}$/', $compact) === 1 ? null : $this->cedula->validate($compact)->error;
    }

    public function generate(): string
    {
        return ['J', 'N', 'R', 'E'][random_int(0, 3)] . self::randomDigits(13);
    }

    public function format(string $number): string
    {
        $compact = $this->compact($number);

        return preg_match('/^[JNRE]/', $compact) === 1 ? $compact : $this->cedula->format($compact);
    }
}
