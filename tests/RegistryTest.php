<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\Nite;
use StdNumCentroamerica\Documents\Guatemala\Nit;
use StdNumCentroamerica\Level;
use StdNumCentroamerica\Registry;

class RegistryTest extends TestCase
{
    public function test_resuelve_documentos_por_clave(): void
    {
        $this->assertInstanceOf(Nit::class, Registry::make('GT.NIT'));
        $this->assertTrue(Registry::isValid('sv.dui', '12345678-4'));
        $this->assertFalse(Registry::isValid('sv.dui', '12345678-5'));
        $this->assertTrue(Registry::isValid('pa.cedula', '8-926-1601'));
    }

    public function test_todas_las_claves_se_pueden_instanciar(): void
    {
        foreach (Registry::keys() as $key) {
            $this->assertInstanceOf(DocumentInterface::class, Registry::make($key), $key);
        }
    }

    public function test_opciones_llegan_al_constructor_del_documento(): void
    {
        // 001-010190-0001A tiene letra incorrecta (la correcta es N).
        $this->assertFalse(Registry::isValid('ni.cedula', '001-010190-0001A'));
        $this->assertTrue(Registry::isValid('ni.cedula', '001-010190-0001A', ['strictLetter' => false]));
        $this->assertFalse(Registry::isValid('ni.cedula', '282-010190-0001N', ['strictMunicipality' => true]));
    }

    public function test_describe_lista_los_documentos_incluidos(): void
    {
        $info = Registry::describe();

        $this->assertCount(15, $info);
        $this->assertSame('gt.nit', $info[0]->key);
        $this->assertSame('GT', $info[0]->country);
        $this->assertSame('NIT', $info[0]->name);
        $this->assertSame(Level::Complete, $info[0]->level);
    }

    public function test_describe_coincide_con_la_tabla_del_readme(): void
    {
        $readme = (string) file_get_contents(__DIR__ . '/../README.md');
        preg_match_all('/^\|[^|]+\|[^|]+\| `([a-z]{2}\.[a-z_]+)` \| (completa|formato)/m', $readme, $rows, PREG_SET_ORDER);

        $expected = [];
        foreach ($rows as [, $key, $level]) {
            $expected[$key] = $level === 'completa' ? Level::Complete : Level::Format;
        }

        $actual = [];
        foreach (Registry::describe() as $info) {
            $actual[$info->key] = $info->level;
        }

        $this->assertSame($expected, $actual);
    }

    public function test_clave_desconocida_lanza_excepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Registry::make('xx.nada');
    }

    public function test_register_reemplaza_un_documento_y_exige_la_interfaz(): void
    {
        Registry::register('XX.NITE', Nite::class);
        $this->assertInstanceOf(Nite::class, Registry::make('xx.nite'));
        $this->assertContains('xx.nite', Registry::keys());

        $this->expectException(InvalidArgumentException::class);
        Registry::register('xx.mal', \stdClass::class);
    }
}
