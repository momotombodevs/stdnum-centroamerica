<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Documents\CostaRica\Nite;
use StdNumCentroamerica\Documents\Guatemala\Nit;
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
            $this->assertNotNull(Registry::make($key), $key);
        }
    }

    public function test_opciones_llegan_al_constructor_del_documento(): void
    {
        // 001-010190-0001A tiene letra incorrecta (la correcta es N).
        $this->assertFalse(Registry::isValid('ni.cedula', '001-010190-0001A'));
        $this->assertTrue(Registry::isValid('ni.cedula', '001-010190-0001A', ['strictLetter' => false]));
        $this->assertFalse(Registry::isValid('ni.cedula', '282-010190-0001N', ['strictMunicipality' => true]));
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
