<?php

namespace StdNumCentroamerica\Tests\Nicaragua;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\Nicaragua\Ruc;

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

    public function test_formatea_y_compacta(): void
    {
        $doc = new Ruc();

        $this->assertSame('J0310000000001', $doc->format('J0310000000001'));
        $this->assertSame('J0310000000001', $doc->compact('J0310000000001'));
    }

    public static function validos(): array
    {
        return [
            ['J0310000000001'],
            ['N0010101900001'],
            ['001-010190-0001N'],
            // Ejemplos del aviso DGI AVISO/072/05-2022
            'DGI nacional sin cédula' => ['N0000000000019'],
            'DGI extranjero residente' => ['R1230003007661'],
            'DGI extranjero no residente' => ['E1230003007661'],
            'DGI persona jurídica' => ['J0310000007669'],
        ];
    }

    public static function invalidos(): array
    {
        return [
            ['K0310000000001'],
            ['J031000000000'],
            ['123'],
            'letra de cédula incorrecta' => ['001-010190-0001A'],
            'cuatro ceros iniciales (DGI)' => ['0000300766011M'],
        ];
    }
}
