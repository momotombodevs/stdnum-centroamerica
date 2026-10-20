<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\ElSalvador;

use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\ElSalvador\Nit;
use StdNumCentroamerica\Tests\DocumentTestCase;

class NitTest extends DocumentTestCase
{
    protected function document(): DocumentInterface
    {
        return new Nit();
    }

    public static function validos(): array
    {
        return [
            // NIT de muestra de la guía de integración DTE de la DGII (rutina nueva y antigua)
            'DGII correlativo 136' => ['0614-130386-136-4'],
            'DGII correlativo 001' => ['0716-200261-001-4'],
            'DGII correlativo 002' => ['0511-130963-002-1'],
            'sin guiones' => ['06141303861364'],
            'DUI como NIT (9 dígitos)' => ['12345678-4'],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'verificador incorrecto' => ['0614-130386-136-5'],
            'verificador incorrecto, rutina antigua' => ['0716-200261-001-5'],
            'relleno de la guía' => ['10101010101010'],
            'DUI con verificador incorrecto' => ['12345678-5'],
            'corto' => ['0614-130386-136'],
            'letras' => ['0614-130386-136-A'],
            'vacío' => [''],
        ];
    }

    public static function formatos(): array
    {
        return [
            '06141303861364' => '0614-130386-136-4',
            '123456784' => '12345678-4',
        ];
    }
}
