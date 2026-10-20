<?php

namespace StdNumCentroamerica\Tests\Honduras;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Data\HondurasGeografia;
use StdNumCentroamerica\Documents\Honduras\Identidad;

class IdentidadTest extends TestCase
{
    #[DataProvider('validos')]
    public function test_acepta_numeros_validos(string $number): void
    {
        $this->assertTrue((new Identidad())->isValid($number));
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_numeros_invalidos(string $number): void
    {
        $this->assertFalse((new Identidad())->isValid($number));
    }

    public function test_formatea_y_compacta(): void
    {
        $doc = new Identidad();

        $this->assertSame('0801-1990-12345', $doc->format('0801199012345'));
        $this->assertSame('0801199012345', $doc->compact('0801-1990-12345'));
    }

    public function test_hay_298_municipios_en_18_departamentos(): void
    {
        $this->assertCount(18, HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO);
        $this->assertSame(298, array_sum(HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO));
    }

    public function test_primer_y_ultimo_municipio_de_cada_departamento(): void
    {
        foreach (HondurasGeografia::MUNICIPIOS_POR_DEPARTAMENTO as $department => $count) {
            $first = sprintf('%02d01', $department) . '199012345';
            $last = sprintf('%02d%02d', $department, $count) . '199012345';
            $over = sprintf('%02d%02d', $department, $count + 1) . '199012345';

            $this->assertTrue((new Identidad())->isValid($first), $first);
            $this->assertTrue((new Identidad())->isValid($last), $last);
            $this->assertFalse((new Identidad())->isValid($over), $over);
        }
    }

    public static function validos(): array
    {
        return [
            ['0801-1990-12345'],
            ['0801199012345'],
            'La Ceiba' => ['0101-1985-00001'],
            'último de Francisco Morazán' => ['0828-2000-00001'],
            'Villeda Morales, Gracias a Dios (INE 09-06)' => ['0906-1990-00001'],
            'San Francisco de Opalaca, Intibucá (INE 10-17)' => ['1017-1990-00001'],
            'doble nacionalidad' => ['0880-2015-00001'],
            'naturalizado' => ['0890-2010-00012'],
        ];
    }

    public static function invalidos(): array
    {
        return [
            'departamento 00' => ['0001-1990-12345'],
            'departamento 19' => ['1901-1990-12345'],
            'municipio 00' => ['0800-1990-12345'],
            'municipio 29 de Francisco Morazán' => ['0829-1990-12345'],
            'Gracias a Dios sólo tiene 6' => ['0907-1990-00001'],
            'Intibucá sólo tiene 17' => ['1018-1990-00001'],
            'código 0881 no es especial' => ['0881-1990-12345'],
            'año futuro' => ['0801-2999-12345'],
            'año 1700' => ['0801-1700-12345'],
            'corta' => ['0801-1990-1234'],
            'letras' => ['0801-1990-1234A'],
        ];
    }
}
