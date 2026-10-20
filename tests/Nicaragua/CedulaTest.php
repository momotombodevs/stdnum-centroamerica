<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests\Nicaragua;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\Nicaragua\Cedula;

class CedulaTest extends TestCase
{
    #[DataProvider('validos')]
    public function test_acepta_numeros_validos(string $number): void
    {
        $this->assertTrue((new Cedula())->isValid($number));
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_numeros_invalidos(string $number): void
    {
        $this->assertFalse((new Cedula())->isValid($number));
    }

    public function test_formatea_y_compacta(): void
    {
        $doc = new Cedula();

        $this->assertSame('001-010190-0001N', $doc->format('0010101900001N'));
        $this->assertSame('0010101900001N', $doc->compact('001-010190-0001n'));
    }

    public function test_letra_se_calcula_con_modulo_23(): void
    {
        $this->assertSame('S', Cedula::checkLetter('0012510750012'));
    }

    public function test_modo_estricto_de_letra(): void
    {
        $lenient = new Cedula(strictLetter: false);

        $this->assertTrue((new Cedula())->isValid('001-251075-0012S'));
        $this->assertFalse((new Cedula())->isValid('001-251075-0012A'));
        $this->assertTrue($lenient->isValid('001-251075-0012A'));
        // Ejemplo del aviso DGI AVISO/072/05-2022: no cumple el módulo 23.
        $this->assertFalse((new Cedula())->isValid('0013007660022M'));
        $this->assertTrue($lenient->isValid('0013007660022M'));
    }

    public function test_modo_estricto_de_municipio(): void
    {
        // 282 está en el bloque de León pero es un código sin asignar en la lista conocida.
        $number = '282-010190-0001' . Cedula::checkLetter('2820101900001');

        $this->assertTrue((new Cedula())->isValid($number));
        $this->assertFalse((new Cedula(strictMunicipality: true))->isValid($number));
        $this->assertTrue((new Cedula(strictMunicipality: true))->isValid('001-010190-0001N'));
    }

    public function test_codigo_888_de_nacidos_en_el_extranjero(): void
    {
        $number = '888-010190-0001' . Cedula::checkLetter('8880101900001');

        $this->assertTrue((new Cedula())->isValid($number));
        $this->assertTrue((new Cedula(strictMunicipality: true))->isValid($number));
        $this->assertFalse((new Cedula())->isValid('887-010190-0001' . Cedula::checkLetter('8870101900001')));
    }

    public function test_cedula_de_muestra_con_mrz_consistente(): void
    {
        // Infografía de la cédula: 041 = Jinotepe, fecha 21-04-1994, número de producción 0000.
        $this->assertSame('Y', Cedula::checkLetter('0412104940000'));
        $this->assertTrue((new Cedula(strictMunicipality: true))->isValid('041-210494-0000Y'));
    }

    public static function validos(): array
    {
        return [['001-010190-0001N'], ['0010101900001n'], ['001-251075-0012S'], ['2811505850001R'], ['6010101900001X']];
    }

    public static function invalidos(): array
    {
        return [
            'mes 13' => ['001-011390-0001N'],
            'día 32' => ['001-320190-0001N'],
            'municipio 000' => ['000-010190-0001V'],
            'municipio fuera de rango' => ['641-010190-0001T'],
            'letra incorrecta' => ['001-010190-0001A'],
            'sin letra' => ['001-010190-0001'],
            'corta' => ['123'],
        ];
    }
}
