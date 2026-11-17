<?php

declare(strict_types=1);

namespace StdNumCentroamerica\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StdNumCentroamerica\Contracts\DocumentInterface;
use StdNumCentroamerica\Documents\CostaRica\Nite;
use StdNumCentroamerica\Documents\ElSalvador\Dui;
use StdNumCentroamerica\Documents\Guatemala\Nit;
use StdNumCentroamerica\Documents\Nicaragua\Cedula;
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
        $this->assertTrue(Registry::isValid('ni.cedula', '001-010190-0001A'));
        $this->assertFalse(Registry::isValid('ni.cedula', '001-010190-0001A', ['strictLetter' => true]));
        $this->assertFalse(Registry::isValid('ni.cedula', '282-010190-0001N', ['strictMunicipality' => true]));
        $this->assertFalse(Registry::isValid('gt.dpi', '1234567890209'));
        $this->assertTrue(Registry::isValid('gt.dpi', '1234567890209', ['strictMunicipality' => false]));
    }

    public function test_opciones_de_documentos_que_delegan_en_otro(): void
    {
        $this->assertTrue(Registry::isValid('ni.ruc', '0010101900001A'));
        $this->assertFalse(Registry::isValid('ni.ruc', '0010101900001A', ['cedula' => new Cedula(strictLetter: true)]));
        $this->assertTrue(Registry::isValid('ni.ruc', '0010101900001N', ['cedula' => new Cedula(strictLetter: true)]));
        $this->assertTrue(Registry::isValid('sv.nit', '12345678-4', ['dui' => new Dui()]));
    }

    /** @return array<string, array{string, array<string, mixed>, string}> */
    public static function opcionesDesconocidas(): array
    {
        return [
            'mayúsculas distintas' => ['ni.cedula', ['strictletter' => true], 'strictletter'],
            'documento sin opciones' => ['sv.dui', ['foo' => 1], 'ninguna'],
            'opción de la cédula en el RUC' => ['ni.ruc', ['strictLetter' => true], 'cedula'],
        ];
    }

    /** @param array<string, mixed> $options */
    #[DataProvider('opcionesDesconocidas')]
    public function test_opcion_desconocida_lanza_excepcion_con_las_aceptadas(string $key, array $options, string $mentioned): void
    {
        foreach ([
            fn () => Registry::make($key, $options),
            fn () => Registry::isValid($key, '1', $options),
            fn () => Registry::validate($key, '1', $options),
            fn () => Registry::generate($key, $options),
        ] as $call) {
            try {
                $call();
                $this->fail("{$key} aceptó " . json_encode($options));
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString($mentioned, $e->getMessage());
            }
        }
    }

    public function test_documento_propio_variadico_acepta_cualquier_opcion(): void
    {
        $document = new class () extends Nite {
            /** @var array<int|string, mixed> */
            public array $received = [];

            public function __construct(mixed ...$options)
            {
                $this->received = $options;
            }
        };
        Registry::register('xx.variadico', $document::class);

        $made = Registry::make('xx.variadico', ['cualquiera' => 1]);
        $this->assertInstanceOf($document::class, $made);
        $this->assertSame(['cualquiera' => 1], $made->received);
    }

    public function test_describe_lista_los_documentos_incluidos(): void
    {
        $info = Registry::describe();

        $this->assertCount(18, $info);
        $this->assertSame('gt.nit', $info[0]->key);
        $this->assertSame('GT', $info[0]->country);
        $this->assertSame('NIT', $info[0]->name);
        $this->assertSame(Level::Complete, $info[0]->level);
    }

    public function test_describe_coincide_con_la_tabla_del_readme(): void
    {
        $countries = [
            'GT' => 'Guatemala', 'SV' => 'El Salvador', 'HN' => 'Honduras',
            'NI' => 'Nicaragua', 'CR' => 'Costa Rica', 'PA' => 'Panamá', 'ICAO' => 'Internacional',
        ];
        $readme = (string) file_get_contents(__DIR__ . '/../README.md');
        preg_match_all('/^\| ([^|]+) \| ([^|]+) \| `([a-z]+\.[a-z0-9_]+)` \| (completa|formato)/m', $readme, $rows, PREG_SET_ORDER);

        $expected = [];
        foreach ($rows as [, $country, $name, $key, $level]) {
            $expected[$key] = [$country, $name, $level === 'completa' ? Level::Complete : Level::Format];
        }

        $actual = [];
        foreach (Registry::describe() as $info) {
            $actual[$info->key] = [$countries[$info->country], $info->name, $info->level];
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
