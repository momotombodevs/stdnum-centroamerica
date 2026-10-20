<?php

namespace StdNumCentroamerica\Tests\Panama;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\Panama\Ruc;

class RucTest extends TestCase
{
    #[DataProvider('validos')]
    public function test_acepta_numeros_validos(string $number): void
    {
        $this->assertTrue((new Ruc())->isValid($number));
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_numeros_invalidos(string $number): void
    {
        $this->assertFalse((new Ruc())->isValid($number));
    }

    public function test_compacta_y_formatea(): void
    {
        $doc = new Ruc();

        $this->assertSame('8-NT-000-00', $doc->format('8 nt 000 00'));
        $this->assertSame('00023824-0930-212623', $doc->format('00023824 0930 212623'));
    }

    public static function validos(): array
    {
        return [
            // Ejemplos de la ficha técnica de factura electrónica de la DGI
            'NT, ficha técnica' => ['8-NT-000-00'],
            'PE, ficha técnica' => ['PE-4-000-000'],
            'jurídica, ficha técnica' => ['00023824-0930-212623'],
            'PE con letra en medio, ejemplo CUFE' => ['8-PE-3824-523'],
            'cédula' => ['8-926-1601'],
            'cédula de naturalizado' => ['N-19-473'],
            'NT, guía de terceros' => ['3-NT-3-33'],
            'jurídica' => ['155612345-2-2015'],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'NT sin tomo' => ['8-NT-000'],
            'NT con provincia 14' => ['14-NT-1-1'],
            'dos grupos numéricos' => ['123-456'],
            'demasiado corto' => ['1-1'],
            'demasiado largo' => ['12345678901-1234567-12345678'],
            'letras sueltas' => ['ABCDE'],
            'vacío' => [''],
        ];
    }
}
